<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\PublicSurface;

/**
 * RepositoryInterface - Standard contract for entity repositories.
 */
interface RepositoryInterface
{
    public function find(mixed $id): ?object;

    public function findAll(): array;

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null) : array;

    public function findOneBy(array $criteria): ?object;
}
