<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Execution;

use Avax\Components\DataStack\Database\System\Capabilities\Query\DTO\ExecutionResult;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope;
use Random\RandomException;
use Throwable;

/**
 * Coordinates query execution and pretend mode for the Query capability.
 */
final class QueryOrchestrator
{
    private bool $isPretending = false;

    /**
     * @param ExecutorInterface   $executor Low-level executor.
     * @param ExecutionScope|null $executionScope Correlation scope (optional).
     *
     * @throws RandomException
     */
    public function __construct(
        private readonly ExecutorInterface $executor,
        private ?ExecutionScope            $executionScope
        = null {
            get {
                return $this->executionScope;
            }
        },
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

        return $this->executor->query(sql: $sql, bindings: $bindings, scope: $this->executionScope);
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
        string $sql,
        ?array $bindings = null,
    ): ExecutionResult {
        $bindings ??= [];

        if ($this->isPretending) {
            $this->logPretend(sql: $sql);

            return ExecutionResult::success(affectedRows: 1);
        }

        return $this->executor->execute(sql: $sql, bindings: $bindings, scope: $this->executionScope);
    }

    public function withScope(ExecutionScope $executionScope) : self
    {
        return clone (object: $this, withProperties: [
            'scope' => $executionScope,
        ]);
    }
}
