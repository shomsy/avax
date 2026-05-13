<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Execution;

use Avax\Components\DataStack\Database\System\Capabilities\Query\DTO\ExecutionResult;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\CompiledDatabaseLifecycleRegistry;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\QueryExecuted;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\QueryExecuting;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\QueryFailed;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\QueryLifecyclePhase;
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
        private readonly CompiledDatabaseLifecycleRegistry $registry = new CompiledDatabaseLifecycleRegistry(),
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
        echo "\033[33m[DRY RUN]\033[0m SQL: {$sql}\n";
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
        $listeners = $this->registry->queryListenersFor($phase);
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

        foreach ($listeners as $entry) {
            $listener = $entry['listener'];
            $instance = new $listener();
            // @phpstan-ignore-next-line
            $instance($event);
        }
    }

    /**
     * Check if query exceeded slow threshold and dispatch slow event.
     *
     * @param array<mixed> $bindings
     */
    private function checkSlowQuery(
        string $sql,
        array $bindings,
        float $durationMs,
        int $rowCount,
    ): void {
        $listeners = $this->registry->queryListenersFor(QueryLifecyclePhase::Slow);
        if ($listeners === []) {
            return;
        }

        foreach ($listeners as $entry) {
            $thresholdMs = $entry['thresholdMs'] ?? 100;
            if ($durationMs >= $thresholdMs) {
                $event = new QueryExecuted(
                    sql: $sql,
                    bindings: $bindings,
                    connection: $this->connectionName,
                    durationMs: $durationMs,
                    rowCount: $rowCount,
                    phase: QueryLifecyclePhase::Slow->value,
                );

                $listener = $entry['listener'];
                $instance = new $listener();
                // @phpstan-ignore-next-line
                $instance($event);
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
        return match ($phase) {
            QueryLifecyclePhase::Executing => new QueryExecuting(
                sql: $sql,
                bindings: $bindings,
                connection: $this->connectionName,
                startTime: microtime(as_float: true) - ($durationMs / 1000),
            ),
            QueryLifecyclePhase::Executed, QueryLifecyclePhase::Slow => new QueryExecuted(
                sql: $sql,
                bindings: $bindings,
                connection: $this->connectionName,
                durationMs: $durationMs,
                rowCount: $rowCount,
                phase: $phase->value,
            ),
            QueryLifecyclePhase::Failed => new QueryFailed(
                sql: $sql,
                bindings: $bindings,
                connection: $this->connectionName,
                exception: $exception ?? throw new RuntimeException('Exception must not be null for failed event'),
                durationMs: $durationMs,
            ),
        };
    }
}
