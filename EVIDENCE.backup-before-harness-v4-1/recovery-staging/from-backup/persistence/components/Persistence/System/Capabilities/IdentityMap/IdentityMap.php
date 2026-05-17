<?php

declare(strict_types=1);

namespace Avax\Components\Persistence\System\Capabilities\IdentityMap;

/**
 * In-memory identity map for tracking loaded entities.
 *
 * Ensures that within a single request scope, the same database row
 * always maps to the same PHP object instance. This prevents duplicate
 * hydration and enables change tracking.
 *
 * Recovered from Database/ORM/IdentityMap and placed under Persistence
 * ownership per ADR-0012.
 */
final class IdentityMap
{
    /** @var array<string, array<string, object>> Keyed by [entityClass][id] */
    private array $entities = [];

    /**
     * Get a tracked entity by class and identifier.
     */
    public function get(string $entityClass, string|int $id) : ?object
    {
        return $this->entities[$entityClass][(string) $id] ?? null;
    }

    /**
     * Register an entity in the identity map.
     */
    public function put(string $entityClass, string|int $id, object $entity) : void
    {
        $this->entities[$entityClass][(string) $id] = $entity;
    }

    /**
     * Remove an entity from the identity map.
     */
    public function remove(string $entityClass, string|int $id) : void
    {
        unset($this->entities[$entityClass][(string) $id]);
    }

    /**
     * Check if an entity is tracked.
     */
    public function has(string $entityClass, string|int $id) : bool
    {
        return isset($this->entities[$entityClass][(string) $id]);
    }

    /**
     * Get all tracked entities for a given class.
     *
     * @return array<string, object>
     */
    public function allFor(string $entityClass) : array
    {
        return $this->entities[$entityClass] ?? [];
    }

    /**
     * Clear all tracked entities.
     * Critical for worker state reset between requests.
     */
    public function clear() : void
    {
        $this->entities = [];
    }

    /**
     * Get total count of tracked entities across all classes.
     */
    public function count() : int
    {
        $count = 0;

        foreach ($this->entities as $classEntities) {
            $count += count($classEntities);
        }

        return $count;
    }
}
