<?php

declare(strict_types=1);

namespace Gemini\Database\QueryBuilder;

use Exception;
use Gemini\Database\DatabaseConnection;
use Gemini\Database\QueryBuilder\Enums\QueryBuilderEnum;
use Gemini\Database\QueryBuilder\Exception\QueryBuilderException;
use Gemini\DataHandling\ArrayHandling\Arrhae;
use PDO;
use PDOException;
use PDOStatement;

/**
 * **UnitOfWork**
 *
 * Implements the **Unit of Work** pattern to manage database operations within a single transaction.
 *
 * This service allows multiple **INSERT, UPDATE, DELETE, and SELECT** queries to be queued
 * and executed in a single batch transaction, improving **data consistency** and **performance**.
 *
 * ## **Key Features**
 * - 🏗 **Batch Execution:** Defers multiple queries and executes them in a single transaction.
 * - 🔄 **Atomic Transactions:** Ensures all queries succeed or the transaction rolls back.
 * - 🚀 **Performance Optimization:** Reduces the number of database connections per request.
 * - ✅ **Consistency:** Guarantees that queries are executed in a controlled order.
 *
 * **🚀 Example Usage:**
 * ```
 * $unitOfWork->registerQuery(QueryBuilderEnum::QUERY_TYPE_INSERT, $stmt, $params);
 * $results = $unitOfWork->flush(); // Executes all registered queries in a transaction
 * ```
 *
 * @package Gemini\Database\QueryBuilder
 */
class UnitOfWork
{
    /**
     * Stores all queries scheduled for deferred execution.
     *
     * @var array<int, array{operation: QueryBuilderEnum, statement: PDOStatement, parameters: array}>
     */
    private array $unitOfWorkQueue = [];

    /**
     * UnitOfWork constructor.
     *
     * @param DatabaseConnection $databaseConnection The database connection manager.
     */
    public function __construct(private readonly DatabaseConnection $databaseConnection) {}

    /**
     * Registers a database query for deferred execution.
     *
     * Queries added here will be executed when `flush()` is called.
     *
     * @param QueryBuilderEnum $operation  The type of database operation (INSERT, UPDATE, DELETE, SELECT).
     * @param PDOStatement     $statement  The prepared PDO statement.
     * @param array            $parameters Optional parameters for the query.
     *
     * @throws QueryBuilderException If the query string is empty.
     */
    public function registerQuery(
        QueryBuilderEnum $operation,
        PDOStatement     $statement,
        PDO              $pdo,
        array            $parameters = [],
    ) : void {
        if (empty(trim($statement->queryString))) {
            throw new QueryBuilderException(message: "Cannot register an empty query in Unit of Work.");
        }

        $this->unitOfWorkQueue[] = compact('operation', 'statement', 'pdo', 'parameters');
    }

    /**
     * Executes all registered queries within a **single database transaction**.
     *
     * If an error occurs, all changes are rolled back to maintain **data consistency**.
     *
     * @return Arrhae Collection of query execution results.
     *
     * @throws QueryBuilderException If the transaction fails.
     */
    public function flush() : Arrhae
    {
        if (empty($this->unitOfWorkQueue)) {
            return new Arrhae(items: []);
        }

        $pdo = $this->databaseConnection->getConnection();
        $pdo->beginTransaction();
        $results = [];

        try {
            foreach ($this->unitOfWorkQueue as $query) {
                $results[] = $this->executeQuery(unitOfWork: $query);
            }
            $pdo->commit();
        } catch (PDOException|Exception $exception) {
            $pdo->rollBack();
            $this->unitOfWorkQueue = [];
            throw new QueryBuilderException(message: "Transaction failed in UnitOfWork: " . $exception->getMessage());
        }

        // Clear queue after successful execution.
        $this->unitOfWorkQueue = [];

        // Flatten the result structure if only a single query was executed.
        // This improves downstream readability and avoids unnecessary array nesting.
        if (count($results) === 1) {
            return new Arrhae(items: $results[0]);
        }

        // For multiple queries, wrap results under a 'batch' key to preserve structure.
        // Consumers can detect batch mode via Arrhae::isBatch().
        return new Arrhae(items: ['batch' => $results]);
    }

    /**
     * Executes a single query from the Unit of Work queue.
     *
     * If the query is an INSERT, returns both the affected rows and the last insert ID.
     *
     * @param array{operation: QueryBuilderEnum, statement: PDOStatement, pdo: PDO, parameters: array} $unitOfWork
     *     The queued query operation details.
     *
     * @return array Structured result containing affected_rows and optionally lastInsertId.
     *
     * @throws \Gemini\Database\QueryBuilder\Exception\QueryBuilderException When the execution of the query fails.
     */
    private function executeQuery(array $unitOfWork) : array
    {
        $statement  = $unitOfWork['statement'];
        $parameters = $unitOfWork['parameters'] ?? [];
        $operation  = $unitOfWork['operation'];
        $pdo        = $unitOfWork['pdo'];

        try {
            $statement->execute($parameters);

            $result = ['affected_rows' => $statement->rowCount()];

            if ($operation === QueryBuilderEnum::QUERY_TYPE_INSERT) {
                $result['lastInsertId'] = (int) $pdo->lastInsertId();
            }

            return $result;
        } catch (PDOException $exception) {
            throw new QueryBuilderException(
                message: "Error executing query: " . $exception->getMessage(), code: 0, previous: $exception
            );
        }
    }

}
