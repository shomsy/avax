<?php

declare(strict_types=1);

namespace Avax\Components\Persistence\System\Capabilities\UnitOfWork;

final class UnitOfWork implements UnitOfWorkInterface
{
    /**
     * @var array<object>
     */
    private array $newEntities = [];

    /**
     * @var array<object>
     */
    private array $dirtyEntities = [];

    /**
     * @var array<object>
     */
    private array $removedEntities = [];

    public function persist(object $entity): void
    {
        $this->dirtyEntities[] = $entity;
    }

    public function remove(object $entity): void
    {
        $this->removedEntities[] = $entity;
    }

    public function flush(): void
    {
        foreach ($this->newEntities as $entity) {
            $this->persistEntity(entity: $entity);
        }

        foreach ($this->dirtyEntities as $entity) {
            $this->updateEntity(entity: $entity);
        }

        foreach ($this->removedEntities as $entity) {
            $this->deleteEntity(entity: $entity);
        }

        $this->clear();
    }

    public function clear(): void
    {
        $this->newEntities = [];
        $this->dirtyEntities = [];
        $this->removedEntities = [];
    }

    private function persistEntity(object $entity): void
    {
    }

    private function updateEntity(object $entity): void
    {
    }

    private function deleteEntity(object $entity): void
    {
    }
}