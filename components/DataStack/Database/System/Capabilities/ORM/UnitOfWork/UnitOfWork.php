<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\UnitOfWork;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\IdentityMap\IdentityMap;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\AttributeMetadataReader;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\FieldMetadata;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Persisters\EntityPersister;
use ReflectionException;
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
        private readonly AttributeMetadataReader $attributeMetadataReader,
        private readonly EntityPersister         $entityPersister,
        private readonly IdentityMap     $identityMap,
    ) {}

    public function persist(object $entity) : void
    {
        $entityMetadata  = $this->attributeMetadataReader->for(entityClass: $entity::class);
        $identifier      = $entityMetadata->identifierField();
        $objectId        = spl_object_id(object: $entity);
        $identifierValue = $identifier instanceof FieldMetadata ? $this->readProperty(entity: $entity, property: $identifier->property) : null;

        unset($this->removed[$objectId]);

        if ($identifierValue === null) {
            $this->new[$objectId] = $entity;

            return;
        }

        $this->dirty[$objectId] = $entity;
    }

    /**
     * @throws ReflectionException
     */
    private function readProperty(object $entity, string $property) : mixed
    {
        $reflectionProperty = new ReflectionProperty(class: $entity, property: $property);
        $reflectionProperty->setAccessible(accessible: true);

        return $reflectionProperty->getValue(object: $entity);
    }

    /**
     * @throws Throwable
     */
    public function flush(string|null $connectionName = null) : void
    {
        foreach ($this->new as $objectId => $entity) {
            $this->entityPersister->insert(entity: $entity, connectionName: $connectionName);
            unset($this->new[$objectId]);

            $metadata = $this->attributeMetadataReader->for(entityClass: $entity::class);
            $identifier = $metadata->identifierField();
            if (! $identifier instanceof FieldMetadata) {
                throw new RuntimeException(message: sprintf('Entity %s has no identifier mapping.', $entity::class));
            }

            $identifierValue = $this->readProperty(entity: $entity, property: $identifier->property);
            if ($identifierValue !== null) {
                $this->identityMap->put(entityClass: $entity::class, id: $identifierValue, entity: $entity);
            }
        }

        foreach ($this->dirty as $objectId => $entity) {
            $this->entityPersister->update(entity: $entity, connectionName: $connectionName);
            unset($this->dirty[$objectId]);
        }

        foreach ($this->removed as $objectId => $entity) {
            $metadata = $this->attributeMetadataReader->for(entityClass: $entity::class);
            $identifier      = $metadata->identifierField();
            $identifierValue = $identifier instanceof FieldMetadata ? $this->readProperty(entity: $entity, property: $identifier->property) : null;

            $this->entityPersister->delete(entity: $entity, connectionName: $connectionName);
            unset($this->removed[$objectId]);

            if ($identifierValue !== null) {
                $this->identityMap->remove(entityClass: $entity::class, id: $identifierValue);
            }
        }
    }

    public function remove(object $entity) : void
    {
        $objectId = spl_object_id(object: $entity);
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
