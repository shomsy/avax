<?php

declare(strict_types=1);

namespace Avax\Database\Identity;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Transaction\Contracts\TransactionManagerInterface;
use Avax\Database\Transaction\Exceptions\TransactionException;

/**
 * Unit-of-Work IdentityMap that buffers mutations and tracks loaded records.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/IdentityMap.md
 */
final class IdentityMap
{
    /** @var array<string, mixed> A memory of every record we've already loaded. */
    private array $map = [];

    /** @var array<int, array{operation: string, sql: string, bindings: array}> The list of pending chores. */
    private array $deferred = [];

    public function __construct(
        private readonly TransactionManagerInterface $transactionManager,
        private readonly DatabaseConnection $connection
    ) {}

    /**
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/IdentityMap.md#deferred-execution
     */
    public function schedule(string $operation, string $sql, array $bindings = []): void
    {
        $this->deferred[] = compact('operation', 'sql', 'bindings');
    }

    /**
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/IdentityMap.md#unit-of-work-pattern
     *
     * @throws \Throwable
     */
    public function execute(): void
    {
        if (empty($this->deferred)) {
            return;
        }

        // Use the same connection that the transaction manager uses for atomicity
        $this->transactionManager->transaction(callback: function (TransactionManagerInterface $tx) {
            // Get PDO from the connection that was injected (same one transaction uses)
            $pdo = $this->connection->getConnection();

            foreach ($this->deferred as $job) {
                $stmt = $pdo->prepare(query: $job['sql']);
                if (! $stmt->execute(params: $job['bindings'])) {
                    throw new TransactionException(
                        message     : 'Failed to execute deferred operation: '.$job['operation'],
                        nestingLevel: 0
                    );
                }
            }
        });

        // Clear the list after we're done.
        $this->deferred = [];
    }
}
