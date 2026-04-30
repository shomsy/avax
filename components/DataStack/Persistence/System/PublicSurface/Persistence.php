<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\PublicSurface;

use Avax\Components\DataStack\Persistence\System\Capabilities\Hydration\HydratorInterface;
use Avax\Components\DataStack\Persistence\System\Capabilities\IdentityMap\IdentityMap;
use Avax\Components\DataStack\Persistence\System\Capabilities\Repositories\RepositoryRegistry;
use Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork\UnitOfWorkInterface;
use Avax\Components\DataStack\Persistence\System\Flows\RunUnitOfWork\RunUnitOfWork;

/**
 * Persistence PublicSurface implementation.
 */
final readonly class Persistence implements PersistenceInterface
{
    public function __construct(
        private UnitOfWorkInterface $unitOfWork,
        private RepositoryRegistry                                                              $repositoryRegistry,
        private HydratorInterface                                                               $hydrator,
        private IdentityMap                                                                     $identityMap,
        private EntityManager                                                                   $entityManager,
        private RunUnitOfWork $runUnitOfWork,
    ) {}

    public function run(callable $operation) : mixed
    {
        return $this->runUnitOfWork->execute($operation);
    }

    public function unitOfWork() : UnitOfWorkInterface
    {
        return $this->unitOfWork;
    }

    public function repositories() : RepositoryRegistry
    {
        return $this->repositoryRegistry;
    }

    public function hydrator() : HydratorInterface
    {
        return $this->hydrator;
    }

    public function identityMap() : IdentityMap
    {
        return $this->identityMap;
    }

    public function manager() : EntityManager
    {
        return $this->entityManager;
    }

    public function find(string $entityClass, mixed $id) : ?object
    {
        return $this->entityManager->find($entityClass, $id);
    }

    public function persist(object $entity) : void
    {
        $this->unitOfWork->persist($entity);
    }

    public function remove(object $entity) : void
    {
        $this->unitOfWork->remove($entity);
    }

    public function flush(string|null $connectionName = null) : void
    {
        $this->unitOfWork->flush($connectionName);
    }

    public function clear() : void
    {
        $this->unitOfWork->clear();
    }
}
