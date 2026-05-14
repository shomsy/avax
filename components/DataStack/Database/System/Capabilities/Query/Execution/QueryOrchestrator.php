<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Execution;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\DataStack\Database\System\Capabilities\Query\DTO\ExecutionResult;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\CompiledDatabaseLifecycleRegistry;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\GlobalDatabaseLifecycleState;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleEvents\QueryExecuted;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleEvents\QueryExecuting;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\LifecycleEvents\QueryFailed;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecyclePhase;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\RedactBindings;
use Random\RandomException;
use RuntimeException;
use Throwable;

/**
 * Coordinates query execution with lifecycle hook integration.
 *
 * Fires query lifecycle events through the compiled registry:
 * - QueryExecuting before execution
 * - QueryExecuted after success
 * - QueryFailed after exception (exception still bubbles)
 * - SlowQueryDetected when duration >= threshold
 *
 * No-listener path has minimal overhead — registry returns empty list immediately.
 */
final class QueryOrchestrator
{
    private bool $isPretending = false;

    /**
     * @param  ExecutorInterface  $executor  Low-level executor.
     * @param  CompiledDatabaseLifecycleRegistry  $registry  Compiled lifecycle registry.
     * @param  ExecutionScope|null  $executionScope  Correlation scope (optional).
     * @param  string  $connectionName  Connection identifier for events.
     *
     * @throws RandomException
     */
    public function __construct(
        private readonly ExecutorInterface $executor,
        private readonly CompiledDatabaseLifecycleRegistry|null $registry
        = null,
        private ExecutionScope|null $executionScope
        = null {
            get {
                return $this->executionScope;
            }
        },
        private readonly string $connectionName = 'default',
    ) {
        $this->executionScope ??= ExecutionScope::fresh();
    }

    private function getRegistry(): CompiledDatabaseLifecycleRegistry
    {
        return $this->registry ?? GlobalDatabaseLifecycleState::registry();
    }

    public function __clone()
    {
        if ($this->executionScope instanceof ExecutionScope) {
            $this->executionScope = clone $this->executionScope;
        }
    }

    /**
     * Switch to pretend (dry-run) mode.
     */
    public function pretend(bool $value = true): void
    {
        $this->isPretending = $value;
    }

    /**
     * Execute a SELECT and return rows.
     *
     * @throws Throwable
     */
    public function query(string $sql, array $bindings = []): array
    {
        if ($this->isPretending) {
            $this->logPretend(sql: $sql);

            return [];
        }

        $start = microtime(as_float: true);

        // Before: executing
        $this->dispatchQueryLifecycle(
            phase: QueryLifecyclePhase::Executing,
            sql: $sql,
            bindings: $bindings,
            durationMs: 0,
            rowCount: 0,
            exception: null,
        );

        try {
            $rows = $this->executor->query(sql: $sql, bindings: $bindings, executionScope: $this->executionScope);

            $durationMs = (microtime(as_float: true) - $start) * 1000;

            // After: executed
            $this->dispatchQueryLifecycle(
                phase: QueryLifecyclePhase::Executed,
                sql: $sql,
                bindings: $bindings,
                durationMs: $durationMs,
                rowCount: count($rows),
                exception: null,
            );

            // Slow detection
            $this->checkSlowQuery(
                sql: $sql,
                bindings: $bindings,
                durationMs: $durationMs,
                rowCount: count($rows),
            );

            return $rows;
        } catch (Throwable $e) {
            $durationMs = (microtime(as_float: true) - $start) * 1000;

            // Failed (exception still bubbles)
            $this->dispatchQueryLifecycle(
                phase: QueryLifecyclePhase::Failed,
                sql: $sql,
                bindings: $bindings,
                durationMs: $durationMs,
                rowCount: 0,
                exception: $e,
            );

            throw $e;
        }
    }

    private function logPretend(string $sql): void
    {
        // Pretend mode is silent by default.
        // To inspect pretend queries, wrap QueryOrchestrator with a logging decorator.
    }

    /**
     * Execute a mutation (INSERT/UPDATE/DELETE/DDL) and return an execution result.
     *
     * @throws Throwable
     */
    public function execute(
        string $sql, array|null $bindings = null,
    ): ExecutionResult {
        $bindings ??= [];

        if ($this->isPretending) {
            $this->logPretend(sql: $sql);

            return ExecutionResult::success(affectedRows: 1);
        }

        $start = microtime(as_float: true);

        // Before: executing
        $this->dispatchQueryLifecycle(
            phase: QueryLifecyclePhase::Executing,
            sql: $sql,
            bindings: $bindings,
            durationMs: 0,
            rowCount: 0,
            exception: null,
        );

        try {
            $result = $this->executor->execute(sql: $sql, bindings: $bindings, executionScope: $this->executionScope);

            $durationMs = (microtime(as_float: true) - $start) * 1000;

            // After: executed
            $this->dispatchQueryLifecycle(
                phase: QueryLifecyclePhase::Executed,
                sql: $sql,
                bindings: $bindings,
                durationMs: $durationMs,
                rowCount: $result->getAffectedRows(),
                exception: null,
            );

            // Slow detection
            $this->checkSlowQuery(
                sql: $sql,
                bindings: $bindings,
                durationMs: $durationMs,
                rowCount: $result->getAffectedRows(),
            );

            return $result;
        } catch (Throwable $e) {
            $durationMs = (microtime(as_float: true) - $start) * 1000;

            // Failed (exception still bubbles)
            $this->dispatchQueryLifecycle(
                phase: QueryLifecyclePhase::Failed,
                sql: $sql,
                bindings: $bindings,
                durationMs: $durationMs,
                rowCount: 0,
                exception: $e,
            );

            throw $e;
        }
    }

    public function withScope(ExecutionScope $executionScope): self
    {
        return clone (object: $this, withProperties: [
            'executionScope' => $executionScope,
        ]);
    }

    /**
     * Dispatch query lifecycle events through the compiled registry.
     *
     * No-listener path: registry returns empty list, zero overhead.
     * Listeners resolved via central ResolveCallable — no direct instantiation.
     * Listener failure bubbles by default.
     *
     * @param array<mixed> $bindings
     */
    private function dispatchQueryLifecycle(
        QueryLifecyclePhase $phase,
        string $sql,
        array $bindings,
        float $durationMs,
        int $rowCount,
        ?Throwable $exception,
    ): void {
        $listeners = $this->getRegistry()->queryListenersFor($phase);
        if ($listeners === []) {
            return;
        }

        $event = $this->buildQueryEvent(
            phase: $phase,
            sql: $sql,
            bindings: $bindings,
            durationMs: $durationMs,
            rowCount: $rowCount,
            exception: $exception,
        );

        $resolver = new ResolveCallable();
        foreach ($listeners as $entry) {
            $callable = $resolver->resolve($entry['listener']);
            $callable($event);
        }
    }

    /**
     * Check if query exceeded slow threshold and dispatch slow event.
     *
     * Slow query path uses the same redaction as normal executed/failed path.
     * Listeners resolved via central ResolveCallable — no direct instantiation.
     *
     * @param array<mixed> $bindings
     */
    private function checkSlowQuery(
        string $sql,
        array $bindings,
        float $durationMs,
        int $rowCount,
    ): void {
        $listeners = $this->getRegistry()->queryListenersFor(QueryLifecyclePhase::Slow);
        if ($listeners === []) {
            return;
        }

        // Use redacted bindings — same path as normal executed/failed events.
        $redactedBindings = RedactBindings::redact($bindings);

        $resolver = new ResolveCallable();
        foreach ($listeners as $entry) {
            $thresholdMs = $entry['thresholdMs'] ?? 100;
            if ($durationMs >= $thresholdMs) {
                $event = new QueryExecuted(
                    sql: $sql,
                    bindings: $redactedBindings,
                    connection: $this->connectionName,
                    durationMs: $durationMs,
                    rowCount: $rowCount,
                    phase: QueryLifecyclePhase::Slow->value,
                );

                $callable = $resolver->resolve($entry['listener']);
                $callable($event);
            }
        }
    }

    /**
     * Build the appropriate query lifecycle event.
     *
     * @param array<mixed> $bindings
     */
    private function buildQueryEvent(
        QueryLifecyclePhase $phase,
        string $sql,
        array $bindings,
        float $durationMs,
        int $rowCount,
        ?Throwable $exception,
    ): object {
        $redactedBindings = RedactBindings::redact($bindings);

        return match ($phase) {
            QueryLifecyclePhase::Executing => new QueryExecuting(
                sql: $sql,
                bindings: $redactedBindings,
                connection: $this->connectionName,
                startTime: microtime(as_float: true) - ($durationMs / 1000),
            ),
            QueryLifecyclePhase::Executed, QueryLifecyclePhase::Slow => new QueryExecuted(
                sql: $sql,
                bindings: $redactedBindings,
                connection: $this->connectionName,
                durationMs: $durationMs,
                rowCount: $rowCount,
                phase: $phase->value,
            ),
            QueryLifecyclePhase::Failed => new QueryFailed(
                sql: $sql,
                bindings: $redactedBindings,
                connection: $this->connectionName,
                exception: $exception ?? throw new RuntimeException('Exception must not be null for failed event'),
                durationMs: $durationMs,
            ),
        };
    }
}
