<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Executor;

use Avax\Database\Identity\IdentityMap;
use Avax\Database\QueryBuilder\DTO\ExecutionResult;
use Avax\Database\Support\ExecutionScope;
use Avax\Database\Transaction\Contracts\TransactionManagerInterface;
use Random\RandomException;
use RuntimeException;
use Throwable;

/**
 * Coordinates query execution, transactions, identity-map scheduling, and pretend mode.
 */
final class QueryOrchestrator
{
    private bool $isPretending = false;

    /**
     * @param ExecutorInterface                $executor           Low-level executor.
     * @param TransactionManagerInterface|null $transactionManager Transaction coordinator (optional).
     * @param IdentityMap|null                 $identityMap        IdentityMap for deferred scheduling (optional).
     * @param ExecutionScope|null              $scope              Correlation scope (optional).
     *
     * @throws RandomException
     */
    public function __construct(
        private readonly ExecutorInterface                $executor,
        private readonly TransactionManagerInterface|null $transactionManager = null,
        private IdentityMap|null                          $identityMap = null,
        private ExecutionScope|null                       $scope = null
    )
    {
        $this->scope ??= ExecutionScope::fresh();
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
     * Execute a callback inside a transaction, flushing the IdentityMap after success.
     *
     * @throws Throwable
     */
    public function transaction(callable $callback) : mixed
    {
        if (! $this->transactionManager) {
            throw new RuntimeException(message: 'Transaction manager not available in Orchestrator.');
        }

        return $this->transactionManager->transaction(callback: function () use ($callback) {
            $result = $callback($this);

            if ($this->identityMap !== null) {
                $this->identityMap->execute();
            }

            return $result;
        });
    }

    /**
     * Execute a mutation (INSERT/UPDATE/DELETE/DDL) and return an execution result.
     *
     * @throws Throwable
     */
    public function execute(
        string      $sql,
        array|null  $bindings = null,
        string|null $operation = null
    ) : ExecutionResult
    {
        $bindings ??= [];

        if ($this->isPretending) {
            $this->logPretend(sql: $sql);

            return ExecutionResult::success(affectedRows: 1);
        }

        if ($operation && $this->identityMap) {
            $this->identityMap->schedule(operation: $operation, sql: $sql, bindings: $bindings);

            return ExecutionResult::success(affectedRows: 1);
        }

        return $this->executor->execute(sql: $sql, bindings: $bindings, scope: $this->scope);
    }

    public function getTransactionManager() : TransactionManagerInterface|null
    {
        return $this->transactionManager;
    }

    public function withIdentityMap(IdentityMap|null $map) : self
    {
        $clone              = clone $this;
        $clone->identityMap = $map;

        return $clone;
    }

    public function withScope(ExecutionScope $scope) : self
    {
        $clone        = clone $this;
        $clone->scope = $scope;

        return $clone;
    }

    public function getScope() : ExecutionScope
    {
        return $this->scope;
    }

    public function getIdentityMap() : IdentityMap|null
    {
        return $this->identityMap;
    }
}
