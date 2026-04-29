<?php
declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\PublicSurface;

use Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork\UnitOfWorkInterface;
use Avax\Components\DataStack\Persistence\System\Capabilities\Repositories\RepositoryRegistry;
use Avax\Components\DataStack\Persistence\System\Capabilities\IdentityMap\IdentityMap;

/**
 * EntityManager - Primary entry point for Entity Persistence (ORM).
 * Relocated to Persistence component per refactor.md.
 */
final readonly class EntityManager
{
    public function __construct(
        private UnitOfWorkInterface $unitOfWork,
        private RepositoryRegistry  $repositoryRegistry,
        private IdentityMap         $identityMap
    ) {}

    public function find(string $entityClass, mixed $id) : ?object
    {
        return $this->repositoryRegistry->get($entityClass)->find($id);
    }

    public function persist(object $entity) : void
    {
        $this->unitOfWork->persist($entity);
    }

    public function remove(object $entity) : void
    {
        $this->unitOfWork->remove($entity);
    }

    public function flush(?string $connectionName = null) : void
    {
        $this->unitOfWork->flush($connectionName);
    }

    public function clear() : void
    {
        $this->unitOfWork->clear();
        $this->identityMap->clear();
    }
}
