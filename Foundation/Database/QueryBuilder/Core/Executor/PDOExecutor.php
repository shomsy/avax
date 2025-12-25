<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Executor;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\QueryBuilder\DTO\MutationResult;
use Avax\Database\QueryBuilder\Exceptions\QueryException;
use PDO;
use Throwable;

/**
 * Practical technician for executing queries via the native PHP Data Object driver.
 *
 * -- intent: provide a secure and reliable implementation of the executor contract using PDO.
 */
final readonly class PDOExecutor implements ExecutorInterface
{
    /**
     * @param DatabaseConnection $connection The technician for connection resolution
     */
    public function __construct(private DatabaseConnection $connection) {}

    /**
     * Fulfill a retrieval query while maintaining secure parameter binding.
     *
     * -- intent: execute SELECT statements and deliver normalized result arrays.
     *
     * @param string $sql      Technical SQL string
     * @param array  $bindings Sanitized values for placeholding
     *
     * @return array<array-key, mixed>
     * @throws QueryException If the SQL is invalid or execution fails
     */
    public function query(string $sql, array $bindings = []) : array
    {
        try {
            $statement = $this->getPdo()->prepare(query: $sql);
            $statement->execute(params: $bindings);

            return $statement->fetchAll();
        } catch (Throwable $e) {
            throw new QueryException(
                message : "Query execution failed: " . $e->getMessage(),
                sql     : $sql,
                bindings: $bindings,
                previous: $e
            );
        }
    }

    /**
     * Resolve the active PDO instance from the connection technician.
     *
     * -- intent: lazily retrieve the physical driver for query fulfillment.
     *
     * @return PDO
     */
    private function getPdo() : PDO
    {
        return $this->connection->getConnection();
    }

    /**
     * Fulfill a mutation query and return the mutation result.
     *
     * -- intent: execute write operations (INSERT/UPDATE/DELETE) with atomicity support.
     *
     * @param string $sql      Technical SQL string
     * @param array  $bindings Sanitized values for placeholding
     *
     * @return MutationResult Encapsulates success status and affected row count
     * @throws QueryException If mutation fails
     */
    public function execute(string $sql, array $bindings = []) : MutationResult
    {
        try {
            $statement = $this->getPdo()->prepare(query: $sql);
            $statement->execute(params: $bindings);

            return MutationResult::success(count: $statement->rowCount());
        } catch (Throwable $e) {
            throw new QueryException(
                message : "Execution failed: " . $e->getMessage(),
                sql     : $sql,
                bindings: $bindings,
                previous: $e
            );
        }
    }

    /**
     * Determine the active driver name from the physical PDO instance.
     *
     * -- intent: facilitate dialect-aware logic in grammar and builder layers.
     *
     * @return string
     */
    public function getDriverName() : string
    {
        return $this->getPdo()->getAttribute(attribute: PDO::ATTR_DRIVER_NAME);
    }
}
