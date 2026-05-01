<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\PublicSurface;

use Avax\Components\DataStack\Persistence\System\Capabilities\Hydration\HydratorInterface;
use Avax\Components\DataStack\Persistence\System\Capabilities\IdentityMap\IdentityMap;
use Avax\Components\DataStack\Persistence\System\Capabilities\Repositories\RepositoryRegistry;
use Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork\UnitOfWorkInterface;

/**
 * Persistence PublicSurface contract.
 */
interface PersistenceInterface
{
    public function run(callable $operation): mixed;

    public function unitOfWork(): UnitOfWorkInterface;

    public function repositories(): RepositoryRegistry;

    public function hydrator(): HydratorInterface;

    public function identityMap(): IdentityMap;

    public function entities(): Entities;

    public function find(string $entityClass, mixed $id): ?object;

    public function persist(object $entity): void;

    public function remove(object $entity): void;

    public function flush(string $connectionName = null) : void;

    public function clear(): void;
}
