<?php

declare(strict_types=1);

namespace Avax\Components\Persistence\System\Capabilities\Repositories;

interface RepositoryInterface
{
    public function findById(string $id): object|null;

    public function findAll(): array;

    public function findBy(array $criteria): array;

    public function save(object $entity): void;

    public function delete(object $entity): void;
}