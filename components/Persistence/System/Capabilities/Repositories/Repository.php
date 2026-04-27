<?php

declare(strict_types=1);

namespace Avax\Components\Persistence\System\Capabilities\Repositories;

final readonly class Repository implements RepositoryInterface
{
    public function __construct(
        private string $entityName,
        private RepositoryBackend $backend,
    ) {
    }

    public function findById(string $id): object|null
    {
        return $this->backend->find(entityName: $this->entityName, id: $id);
    }

    public function findAll(): array
    {
        return $this->backend->all(entityName: $this->entityName);
    }

    public function findBy(array $criteria): array
    {
        return $this->backend->query(entityName: $this->entityName, criteria: $criteria);
    }

    public function save(object $entity): void
    {
        $this->backend->persist(entity: $entity);
    }

    public function delete(object $entity): void
    {
        $this->backend->remove(entity: $entity);
    }
}

interface RepositoryBackend
{
    public function find(string $entityName, string $id): object|null;

    public function all(string $entityName): array;

    public function query(string $entityName, array $criteria): array;

    public function persist(object $entity): void;

    public function remove(object $entity): void;
}