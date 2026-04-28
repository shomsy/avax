<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\PublicSurface;

use Avax\Components\DataStack\Persistence\System\Capabilities\Hydration\HydratorInterface;
use Avax\Components\DataStack\Persistence\System\Capabilities\IdentityMap\IdentityMap;
use Avax\Components\DataStack\Persistence\System\Capabilities\Repositories\RepositoryRegistry;
use Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork\UnitOfWorkInterface;

/**
 * Persistence PublicSurface contract.
 *
 * Provides the external API for all persistence operations:
 * - UnitOfWork for tracking and flushing entity changes
 * - Repository access for entity querying
 * - IdentityMap inspection for diagnostics
 * - Hydrator access for manual row-to-entity conversion
 *
 * This is a thin delegation layer per refactor.md rules:
 * "PublicSurface receives public calls and delegates."
 */
interface PersistenceInterface
{
    /**
     * Get the UnitOfWork for tracking entity changes.
     */
    public function unitOfWork() : UnitOfWorkInterface;

    /**
     * Get the repository registry for accessing entity repositories.
     */
    public function repositories() : RepositoryRegistry;

    /**
     * Get the hydrator for converting database rows to entities.
     */
    public function hydrator() : HydratorInterface;

    /**
     * Get the identity map for entity tracking inspection.
     */
    public function identityMap() : IdentityMap;

    /**
     * Convenience: persist an entity (delegates to UnitOfWork).
     */
    public function persist(object $entity) : void;

    /**
     * Convenience: remove an entity (delegates to UnitOfWork).
     */
    public function remove(object $entity) : void;

    /**
     * Convenience: flush all pending changes (delegates to UnitOfWork).
     */
    public function flush(string|null $connectionName = null) : void;

    /**
     * Clear all tracked state.
     * Critical for worker request scope reset.
     */
    public function clear() : void;
}