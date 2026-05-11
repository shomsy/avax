<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork;

/**
 * UnitOfWork contract.
 *
 * Tracks entity state changes (new, dirty, removed) and flushes them
 * to the persistence layer in a single transactional batch.
 */
interface UnitOfWorkInterface
{
    /**
     * Schedule an entity for persistence (insert or update).
     */
    public function persist(object $entity): void;

    /**
     * Schedule an entity for removal.
     */
    public function remove(object $entity): void;

    /**
     * Flush all pending changes to the underlying storage.
     */
    public function flush(string|null $connectionName = null) : void;

    /**
     * Clear all tracked entities without flushing.
     * Essential for worker state reset between requests.
     */
    public function clear(): void;

    /**
     * Check if the UnitOfWork has any pending changes.
     */
    public function hasPendingChanges(): bool;

    /**
     * Get the count of entities currently scheduled for each operation.
     *
     * @return array{new: int, dirty: int, removed: int}
     */
    public function pendingSummary(): array;
}
