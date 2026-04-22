<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM\UnitOfWork;

use Avax\Database\System\Capabilities\ORM\IdentityMap\IdentityMap;
use Avax\Database\System\Capabilities\ORM\Metadata\AttributeMetadataReader;
use Avax\Database\System\Capabilities\ORM\Persisters\EntityPersister;
use ReflectionProperty;
use RuntimeException;
use Throwable;

final class UnitOfWork
{
    /** @var array<int, object> */
    private array $new = [];

    /** @var array<int, object> */
    private array $dirty = [];

    /** @var array<int, object> */
    private array $removed = [];

    public function __construct(
        private readonly AttributeMetadataReader $metadata,
        private readonly EntityPersister         $persister,
        private readonly IdentityMap             $identityMap
    ) {}

    public function persist(object $entity) : void
    {
        $metadata        = $this->metadata->for(entityClass: $entity::class);
        $identifier      = $metadata->identifierField();
        $objectId        = spl_object_id($entity);
        $identifierValue = $identifier === null ? null : $this->readProperty($entity, $identifier->property);

        unset($this->removed[$objectId]);

        if ($identifierValue === null) {
            $this->new[$objectId] = $entity;

            return;
        }

        $this->dirty[$objectId] = $entity;
    }

    private function readProperty(object $entity, string $property) : mixed
    {
        $reflection = new ReflectionProperty($entity, $property);
        $reflection->setAccessible(true);

        return $reflection->getValue($entity);
    }

    /**
     * @throws Throwable
     */
    public function flush(string|null $connectionName = null) : void
    {
        foreach ($this->new as $objectId => $entity) {
            $this->persister->insert(entity: $entity, connectionName: $connectionName);
            unset($this->new[$objectId]);

            $metadata   = $this->metadata->for(entityClass: $entity::class);
            $identifier = $metadata->identifierField();
            if ($identifier === null) {
                throw new RuntimeException(message: sprintf('Entity %s has no identifier mapping.', $entity::class));
            }

            $identifierValue = $this->readProperty($entity, $identifier->property);
            if ($identifierValue !== null) {
                $this->identityMap->put(entityClass: $entity::class, id: $identifierValue, entity: $entity);
            }
        }

        foreach ($this->dirty as $objectId => $entity) {
            $this->persister->update(entity: $entity, connectionName: $connectionName);
            unset($this->dirty[$objectId]);
        }

        foreach ($this->removed as $objectId => $entity) {
            $metadata        = $this->metadata->for(entityClass: $entity::class);
            $identifier      = $metadata->identifierField();
            $identifierValue = $identifier === null ? null : $this->readProperty($entity, $identifier->property);

            $this->persister->delete(entity: $entity, connectionName: $connectionName);
            unset($this->removed[$objectId]);

            if ($identifierValue !== null) {
                $this->identityMap->remove(entityClass: $entity::class, id: $identifierValue);
            }
        }
    }

    public function remove(object $entity) : void
    {
        $objectId = spl_object_id($entity);
        unset($this->new[$objectId], $this->dirty[$objectId]);
        $this->removed[$objectId] = $entity;
    }

    public function clear() : void
    {
        $this->new     = [];
        $this->dirty   = [];
        $this->removed = [];
    }
}
