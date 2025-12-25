<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Executor;

use Avax\Database\QueryBuilder\DTO\MutationResult;
use Throwable;

/**
 * Functional contract for the physical execution of database queries.
 *
 * -- intent: decouple the builder's logic from the technicalities of driver-level communication.
 */
interface ExecutorInterface
{
    /**
     * Execute a data retrieval query (SELECT) and return the resulting collection.
     *
     * -- intent: provide a pragmatic entry point for fetching raw datasets.
     *
     * @param string $sql      Technical query dialect
     * @param array  $bindings Secure parameter values
     *
     * @return array<array-key, mixed>
     * @throws Throwable If driver or connection failure occurs
     */
    public function query(string $sql, array $bindings = []) : array;

    /**
     * Execute a data mutation query (INSERT/UPDATE/DELETE) and return mutation result.
     *
     * -- intent: provide a pragmatic entry point for structural or data changes.
     *
     * @param string $sql      Technical query dialect
     * @param array  $bindings Secure parameter values
     *
     * @return MutationResult Encapsulates success status and affected row count
     * @throws Throwable If driver or connection failure occurs
     */
    public function execute(string $sql, array $bindings = []) : MutationResult;

    /**
     * Retrieve the identifying technical name of the underlying database driver.
     *
     * -- intent: allow components to adapt behavior based on the active storage engine.
     *
     * @return string
     */
    public function getDriverName() : string;
}
