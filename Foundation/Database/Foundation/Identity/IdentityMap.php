<?php

declare(strict_types=1);

namespace Avax\Database\Identity;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Transaction\Contracts\TransactionManagerInterface;
use Avax\Database\Transaction\Exceptions\TransactionException;
use Throwable;

/**
 * Unit-of-Work IdentityMap that buffers mutations and tracks loaded records.
 *
 * @see docs/Concepts/IdentityMap.md
 */
final class IdentityMap
{
    /** @var array<string, mixed> A memory of every record we've already loaded. */
    private array $map = [];

    /** @var array<int, array{operation: string, sql: string, bindings: array}> The list of pending chores. */
    private array $deferred = [];

    /**
     * @param TransactionManagerInterface $transactionManager
     * @param DatabaseConnection          $connection
     */
    public function __construct(
        private readonly TransactionManagerInterface $transactionManager,
        private readonly DatabaseConnection          $connection
    ) {}

    /**
     * Schedule a mutation operation for deferred execution.
     *
     * @param string $operation Operation type (e.g., INSERT).
     * @param string $sql       Pre-compiled SQL string.
     * @param array  $bindings  Secure tokens for parameterization.
     */
    public function schedule(string $operation, string $sql, array $bindings = []): void
    {
        $this->deferred[] = compact('operation', 'sql', 'bindings');
    }

    /**
     * Execute all scheduled mutations within a transaction.
     *
     * @throws Throwable If any operation fails or connection is lost.
     */
    public function execute(): void
    {
        if (empty($this->deferred)) {
            return;
        }

        $this->transactionManager->transaction(callback: function (TransactionManagerInterface $tx) {
            foreach ($this->deferred as $job) {
                $stmt = $tx->getConnection()->getConnection()->prepare(query: $job['sql']);
                if (! $stmt->execute(params: $job['bindings'])) {
                    throw new TransactionException(
                        message: "Failed to execute deferred operation: " . $job['operation'],
                        nestingLevel: 0
                    );
                }
            }
        });

        // Clear the list after we're done.
        $this->deferred = [];
    }
}
