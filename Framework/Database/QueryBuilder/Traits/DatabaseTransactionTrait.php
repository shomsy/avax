<?php

declare(strict_types=1);

/**
 * Trait DatabaseTransactionTrait
 *
 * Provides utility methods for handling database transactions, supporting both
 * standard and nested transactions through savepoints. Ensures robust error handling
 * by rolling back appropriately in case of failures and logging transaction errors.
 *
 * Applicable to classes managing a PDO-based database connection and requiring
 * transactional operations.
 */

namespace Gemini\Database\QueryBuilder\Traits;

use Exception;
use Gemini\Database\QueryBuilder\Exception\QueryBuilderException;
use PDO;

/**
 * Provides functionality for managing database transactions,
 * including support for nested transactions using savepoints.
 */
trait DatabaseTransactionTrait
{
    private const string SAVEPOINT_PREFIX = 'SAVEPOINT_';

    /**
     * Runs a series of database operations within a single transaction.
     *
     * Supports nested transactions using SAVEPOINTS.
     * If any operation fails, it rolls back to the last savepoint or the main transaction.
     *
     * @param callable $operations A callable that contains the operations to be performed within the transaction.
     *
     * @throws Exception If any operation fails, an exception is thrown, and the transaction is rolled back.
     */
    public function transaction(callable $operations) : void
    {
        $pdo = $this->getDatabaseConnection();
        $isNested = $pdo->inTransaction();

        if ($isNested) {
            $savepoint = $this->createSavepoint(pdo: $pdo);
        } else {
            $this->beginTransaction();
        }

        try {
            $operations(); // Execute the operations
            $isNested ? $this->releaseSavepoint(pdo: $pdo, savepoint: $savepoint) : $this->commit();
        } catch (Exception $exception) {
            $isNested ? $this->rollbackToSavepoint(pdo: $pdo, savepoint: $savepoint) : $this->rollbackTransaction();
            $this->logTransactionError(exception: $exception);
            throw $exception;
        }
    }

    /**
     * Gets the current database connection.
     */
    public function getDatabaseConnection() : PDO
    {
        return $this->databaseConnection->getConnection();
    }

    /**
     * Creates a savepoint for nested transactions.
     *
     * @throws \Gemini\Database\QueryBuilder\Exception\QueryBuilderException
     */
    private function createSavepoint(PDO $pdo) : string
    {
        $savepoint = self::SAVEPOINT_PREFIX . uniqid();

        $quotedSavepoint = $pdo->quote(string: $savepoint);

        if ($quotedSavepoint === false) {
            throw new QueryBuilderException(message: "PDO::quote() failed to quote the savepoint name.");
        }

        $pdo->exec(statement: "SAVEPOINT " . $quotedSavepoint);

        return $savepoint;
    }


    /**
     * Begins a new transaction on the current database connection.
     * If a transaction is already active, it does nothing.
     */
    public function beginTransaction() : void
    {
        $pdo = $this->getDatabaseConnection();
        if (! $pdo->inTransaction()) {
            $pdo->beginTransaction();
        }
    }

    /**
     * Releases a savepoint for nested transactions.
     */
    private function releaseSavepoint(PDO $pdo, string $savepoint) : void
    {
        $stmt = $pdo->prepare(query: "RELEASE SAVEPOINT :savepoint");
        $stmt->execute(params: ['savepoint' => $savepoint]);
    }

    /**
     * Commits the current database transaction.
     */
    public function commit() : void
    {
        $pdo = $this->getDatabaseConnection();
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
    }

    /**
     * Rolls back to a savepoint in nested transactions.
     */
    private function rollbackToSavepoint(PDO $pdo, string $savepoint) : void
    {
        $stmt = $pdo->prepare(query: "ROLLBACK TO SAVEPOINT :savepoint");
        $stmt->execute(params: ['savepoint' => $savepoint]);
    }

    /**
     * Rolls back the current database transaction.
     */
    public function rollbackTransaction() : void
    {
        $pdo = $this->getDatabaseConnection();
        if ($pdo->inTransaction()) {
            try {
                $pdo->rollBack();
            } catch (Exception $e) {
                $this->logger->error(message: 'Rollback failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Logs transaction-related errors.
     */
    private function logTransactionError(Exception $exception) : void
    {
        $this->logger->error(
            message: 'Transaction failed: ' . $exception->getMessage(),
            context: ['exception' => $exception]
        );
    }
}