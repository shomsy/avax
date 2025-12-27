<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Executor;

use Avax\Database\QueryBuilder\DTO\ExecutionResult;
use Avax\Database\Support\ExecutionScope;
use Throwable;

/**
 * Interface defining capabilities for the physical execution of database operations.
 *
 * @see docs/DSL/QueryExecution.md
 */
interface ExecutorInterface
{
    /**
     * Dispatch a retrieval instruction (SELECT) to the persistence driver.
     *
     * @param string              $sql      Pre-compiled SQL retrieval string.
     * @param array               $bindings Secure tokens for parameterization.
     * @param ExecutionScope|null $scope    Optional context for correlation.
     * @throws Throwable If persistence connection failure occurs.
     * @return array<array-key, mixed>
     */
    public function query(
        string           $sql,
        array            $bindings = [],
        ExecutionScope|null $scope = null
    ): array;

    /**
     * Dispatch a mutation instruction (INSERT/UPDATE/DELETE/DDL) to the persistence driver.
     *
     * @param string              $sql      Pre-compiled SQL mutation string.
     * @param array               $bindings Secure tokens for parameterization.
     * @param ExecutionScope|null $scope    Optional context for correlation.
     * @throws Throwable If technical modification fails.
     * @return ExecutionResult
     */
    public function execute(
        string           $sql,
        array            $bindings = [],
        ExecutionScope|null $scope = null
    ): ExecutionResult;

    /**
     * Retrieve the authoritative technical identifier of the underlying persistence driver.
     *
     * -- intent:
     * Enables high-level components to adapt their behavior based on the specific 
     * characteristics and capabilities of the active persistence engine.
     *
     * @return string THE technical identifier of the driver (e.g., 'mysql').
     */
    public function getDriverName(): string;
}
