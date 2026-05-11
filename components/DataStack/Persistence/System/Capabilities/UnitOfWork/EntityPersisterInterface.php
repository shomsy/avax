<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork;

/**
 * Contract for the low-level entity persistence operations.
 *
 * The UnitOfWork delegates actual INSERT/UPDATE/DELETE to this interface.
 * Implementations may use Database component queries, raw PDO, or any
 * other storage mechanism.
 */
interface EntityPersisterInterface
{
    /**
     * Extract the identifier value from an entity, or null if not yet assigned.
     */
    public function extractIdentifier(object $entity): string|int|null;

    /**
     * Insert a new entity into storage.
     */
    public function insert(object $entity, string|null $connectionName = null) : void;

    /**
     * Update an existing entity in storage.
     */
    public function update(object $entity, string|null $connectionName = null) : void;

    /**
     * Delete an entity from storage.
     */
    public function delete(object $entity, string|null $connectionName = null) : void;
}
