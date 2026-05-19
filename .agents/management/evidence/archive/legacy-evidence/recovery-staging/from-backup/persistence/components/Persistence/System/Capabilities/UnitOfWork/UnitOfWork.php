<?php

declare(strict_types=1);

namespace Avax\Components\Persistence\System\Capabilities\UnitOfWork;

use Avax\Components\Persistence\System\Capabilities\IdentityMap\IdentityMap;

/**
 * Tracks entity lifecycle changes and flushes them to the persistence backend.
 *
 * Recovered from Database/ORM/UnitOfWork with proper Persistence ownership.
 * Uses IdentityMap for entity identity tracking and EntityPersisterInterface
 * for actual storage delegation.
 */
final class UnitOfWork implements UnitOfWorkInterface
{
    /** @var array<int, object> Entities scheduled for INSERT */
    private array $new = [];

    /** @var array<int, object> Entities scheduled for UPDATE */
    private array $dirty = [];

    /** @var array<int, object> Entities scheduled for DELETE */
    private array $removed = [];

    /** @var array<int, array> Snapshots of entity states for dirty tracking */
    private array $snapshots = [];

    public function __construct(
        private readonly IdentityMap $identityMap,
        private readonly EntityPersisterInterface $persister,
    ) {
    }

    public function persist(object $entity): void
    {
        $objectId = spl_object_id(object: $entity);

        // If it was previously scheduled for removal, cancel the removal
        unset($this->removed[$objectId]);

        $identifier = $this->persister->extractIdentifier($entity);

        if ($identifier === null) {
            // New entity (no ID yet)
            $this->new[$objectId] = $entity;

            return;
        }

        // Existing entity (has ID) → mark dirty for update if changed
        if ($this->isDirty($entity)) {
            $this->dirty[$objectId] = $entity;
        }
    }

    private function isDirty(object $entity): bool
    {
        $objectId = spl_object_id($entity);
        if (! isset($this->snapshots[$objectId])) {
            return true; // No snapshot, assume dirty
        }

        $currentData = $this->persister->extractData($entity);

        return $currentData !== $this->snapshots[$objectId];
    }

    public function registerClean(object $entity): void
    {
        $objectId = spl_object_id($entity);
        $this->snapshots[$objectId] = $this->persister->extractData($entity);
    }

    public function remove(object $entity): void
    {
        $objectId = spl_object_id(object: $entity);

        // Remove from new/dirty if present
        unset($this->new[$objectId], $this->dirty[$objectId]);

        $this->removed[$objectId] = $entity;
    }

    public function flush(?string $connectionName = null): void
    {
        // Process inserts
        foreach ($this->new as $objectId => $entity) {
            $this->persister->insert(entity: $entity, connectionName: $connectionName);
            unset($this->new[$objectId]);

            // After insert, register in identity map
            $identifier = $this->persister->extractIdentifier($entity);

            if ($identifier !== null) {
                $this->identityMap->put(
                    entityClass: $entity::class,
                    id         : $identifier,
                    entity     : $entity,
                );
            }
        }

        // Process updates
        foreach ($this->dirty as $objectId => $entity) {
            if ($this->isDirty($entity)) {
                $this->persister->update(entity: $entity, connectionName: $connectionName);
                $this->registerClean($entity);
            }
            unset($this->dirty[$objectId]);
        }

        // Process deletions
        foreach ($this->removed as $objectId => $entity) {
            $identifier = $this->persister->extractIdentifier($entity);

            $this->persister->delete(entity: $entity, connectionName: $connectionName);
            unset($this->removed[$objectId]);

            if ($identifier !== null) {
                $this->identityMap->remove(
                    entityClass: $entity::class,
                    id         : $identifier,
                );
            }
        }
    }

    public function clear(): void
    {
        $this->new = [];
        $this->dirty = [];
        $this->removed = [];
        $this->identityMap->clear();
    }

    public function hasPendingChanges(): bool
    {
        return $this->new !== [] || $this->dirty !== [] || $this->removed !== [];
    }

    public function pendingSummary(): array
    {
        return [
            'new' => count($this->new),
            'dirty' => count($this->dirty),
            'removed' => count($this->removed),
        ];
    }
}
