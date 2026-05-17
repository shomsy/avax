<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Query\Execution;

use components\Database\System\Capabilities\Query\DTO\ExecutionResult;
use components\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use Random\RandomException;
use Throwable;

/**
 * Coordinates query execution and pretend mode for the Query capability.
 */
final class QueryOrchestrator
{
    private bool $isPretending = false;

    private ?ExecutionScope $scope
        = null {
            get {
                return $this->scope;
            }
        }

    private readonly ExecutorInterface $executor;

    /**
     * @param ExecutorInterface   $executor Low-level executor.
     * @param ExecutionScope|null $scope    Correlation scope (optional).
     *
     * @throws RandomException
     */
    public function __construct(
        ExecutorInterface $executor,
        ?ExecutionScope   $scope = null
    )
    {
        $this->executor = $executor;
        $this->scope    = $scope;
        $this->scope    ??= ExecutionScope::fresh();
    }

    public function __clone()
    {
        if ($this->scope !== null) {
            $this->scope = clone $this->scope;
        }
    }

    /**
     * Switch to pretend (dry-run) mode.
     */
    public function pretend(bool $value = true) : void
    {
        $this->isPretending = $value;
    }

    /**
     * Execute a SELECT and return rows.
     *
     * @throws Throwable
     */
    public function query(string $sql, array $bindings = []) : array
    {
        if ($this->isPretending) {
            $this->logPretend(sql: $sql);

            return [];
        }

        return $this->executor->query(sql: $sql, bindings: $bindings, scope: $this->scope);
    }

    private function logPretend(string $sql) : void
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
        ?array $bindings = null
    ) : ExecutionResult
    {
        $bindings ??= [];

        if ($this->isPretending) {
            $this->logPretend(sql: $sql);

            return ExecutionResult::success(affectedRows: 1);
        }

        return $this->executor->execute(sql: $sql, bindings: $bindings, scope: $this->scope);
    }

    public function withScope(ExecutionScope $scope) : self
    {
        return clone(object: $this, withProperties: [
            'scope' => $scope,
        ]);
    }
}
