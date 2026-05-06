<?php

declare(strict_types=1);

namespace Avax\Components\Persistence\System\PublicSurface;

use Avax\Components\Persistence\System\Capabilities\Hydration\HydratorInterface;
use Avax\Components\Persistence\System\Capabilities\IdentityMap\IdentityMap;
use Avax\Components\Persistence\System\Capabilities\Repositories\RepositoryRegistry;
use Avax\Components\Persistence\System\Capabilities\UnitOfWork\UnitOfWorkInterface;

/**
 * Persistence PublicSurface implementation.
 *
 * Thin delegation layer that provides access to all persistence capabilities.
 * No business logic lives here — only delegation per refactor.md.
 */
final readonly class Persistence implements PersistenceInterface
{
    public function __construct(
        private UnitOfWorkInterface $unitOfWork,
        private RepositoryRegistry $repositoryRegistry,
        private HydratorInterface $hydrator,
        private IdentityMap $identityMap,
    ) {
    }

    public function unitOfWork(): UnitOfWorkInterface
    {
        return $this->unitOfWork;
    }

    public function repositories(): RepositoryRegistry
    {
        return $this->repositoryRegistry;
    }

    public function hydrator(): HydratorInterface
    {
        return $this->hydrator;
    }

    public function identityMap(): IdentityMap
    {
        return $this->identityMap;
    }

    public function persist(object $entity): void
    {
        $this->unitOfWork->persist($entity);
    }

    public function remove(object $entity): void
    {
        $this->unitOfWork->remove($entity);
    }

    public function flush(?string $connectionName = null): void
    {
        $this->unitOfWork->flush($connectionName);
    }

    public function clear(): void
    {
        $this->unitOfWork->clear();
    }
}
