<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\ORM\IdentityMap;

final class IdentityMap
{
    /** @var array<string, array<string, object>> */
    private array $entities = [];

    public function get(string $entityClass, mixed $id) : object|null
    {
        return $this->entities[$entityClass][(string) $id] ?? null;
    }

    public function put(string $entityClass, mixed $id, object $entity) : void
    {
        $this->entities[$entityClass][(string) $id] = $entity;
    }

    public function remove(string $entityClass, mixed $id) : void
    {
        unset($this->entities[$entityClass][(string) $id]);
    }

    public function clear() : void
    {
        $this->entities = [];
    }
}
