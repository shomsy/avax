<?php

declare(strict_types=1);

namespace Avax\Database\Identity;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Transaction\Contracts\TransactionManagerInterface;
use Avax\Database\Transaction\Exceptions\TransactionException;
use Throwable;

/**
 * Enterprise-grade technician for tracking record identities and deferring operations.
 *
 * -- intent: implement the Unit of Work pattern to optimize and batch database writes.
 */
final class IdentityMap
{
    // Collection of records retrieved and managed by this map
    private array $map = [];

    // Queue of pending database operations for deferred execution
    private array $deferred = [];

    /**
     * Constructor promoting the transaction manager dependency.
     *
     * -- intent: establish the bridge to the persistence layer for final commits.
     *
     * @param TransactionManagerInterface $transactionManager The active manager for atomic batches
     */
    public function __construct(
        private readonly TransactionManagerInterface $transactionManager,
        private readonly DatabaseConnection          $connection
    ) {}

    /**
     * Schedule a technical SQL operation for deferred execution.
     *
     * -- intent: buffer mutation queries until an explicit flush is requested.
     *
     * @param string $operation Type of operation (INSERT/UPDATE/DELETE)
     * @param string $sql       Dialect-specific SQL string
     * @param array  $bindings  Secure parameter values
     *
     * @return void
     */
    public function schedule(string $operation, string $sql, array $bindings = []) : void
    {
        $this->deferred[] = compact('operation', 'sql', 'bindings');
    }

    /**
     * Fulfill all pending operations within a single atomic batch.
     *
     * -- intent: execute all buffered mutations and clear the internal queue.
     *
     * @return void
     * @throws Throwable If any operation in the batch fails
     */
    public function execute() : void
    {
        if (empty($this->deferred)) {
            return;
        }

        $this->transactionManager->transaction(callback: function () {
            foreach ($this->deferred as $job) {
                $stmt = $this->connection->getConnection()->prepare(query: $job['sql']);
                if (! $stmt->execute(params: $job['bindings'])) {
                    throw new TransactionException(
                        message     : "Failed to execute deferred operation: " . $job['operation'],
                        nestingLevel: 0
                    );
                }
            }
        });

        $this->deferred = [];
    }
}
