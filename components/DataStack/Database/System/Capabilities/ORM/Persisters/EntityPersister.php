<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\Persisters;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\Hydration\Hydrator;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\AttributeMetadataReader;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\EntityMetadata;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Query;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;
use Throwable;

final readonly class EntityPersister
{
    public function __construct(
        private Query    $query,
        private AttributeMetadataReader $attributeMetadataReader,
        private Hydrator $hydrator,
    ) {}

    /**
     * @throws Throwable
     */
    public function insert(object $entity, string|null $connectionName = null) : void
    {
        $metadata = $this->attributeMetadataReader->for(entityClass: $entity::class);
        $identifier = $metadata->identifierField();
        $payload    = $this->payload(entity: $entity, metadata: $metadata, includeIdentifier: false);

        $builder     = $this->query->builder(connectionName: $connectionName)->from(table: $metadata->table);
        $generatedId = $builder->insertGetId(values: $payload);

        if ($identifier !== null && $identifier->generated) {
            $this->setPropertyValue(entity: $entity, property: $identifier->property, value: $generatedId);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(object $entity, EntityMetadata $entityMetadata, bool $includeIdentifier) : array
    {
        $payload = [];

        foreach ($entityMetadata->fields as $field) {
            if (! $includeIdentifier && ($field->id || $field->generated)) {
                continue;
            }

            $payload[$field->column] = $this->propertyValue(entity: $entity, property: $field->property);
        }

        return $payload;
    }

    /**
     * @throws ReflectionException
     */
    private function propertyValue(object $entity, string $property) : mixed
    {
        $reflectionProperty = new ReflectionProperty(class: $entity, property: $property);
        $reflectionProperty->setAccessible(accessible: true);

        return $reflectionProperty->getValue(object: $entity);
    }

    /**
     * @throws ReflectionException
     */
    private function setPropertyValue(object $entity, string $property, mixed $value) : void
    {
        $reflectionProperty = new ReflectionProperty(class: $entity, property: $property);
        $reflectionProperty->setAccessible(accessible: true);
        $reflectionProperty->setValue(objectOrValue: $entity, value: $value);
    }

    /**
     * @throws Throwable
     */
    public function update(object $entity, string|null $connectionName = null) : void
    {
        $metadata = $this->attributeMetadataReader->for(entityClass: $entity::class);
        $identifier = $metadata->identifierField();

        if ($identifier === null) {
            throw new RuntimeException(message: sprintf('Entity %s has no identifier mapping.', $entity::class));
        }

        $identifierValue = $this->propertyValue(entity: $entity, property: $identifier->property);
        if ($identifierValue === null) {
            throw new RuntimeException(message: 'Cannot update an entity without an identifier value.');
        }

        $payload = $this->payload(entity: $entity, metadata: $metadata, includeIdentifier: false);

        $this->query->builder(connectionName: $connectionName)
            ->from(table: $metadata->table)
            ->where(column: $identifier->column, operator: '=', value: $identifierValue)
            ->update(values: $payload);
    }

    /**
     * @throws Throwable
     */
    public function delete(object $entity, string|null $connectionName = null) : void
    {
        $metadata = $this->attributeMetadataReader->for(entityClass: $entity::class);
        $identifier = $metadata->identifierField();

        if ($identifier === null) {
            throw new RuntimeException(message: sprintf('Entity %s has no identifier mapping.', $entity::class));
        }

        $identifierValue = $this->propertyValue(entity: $entity, property: $identifier->property);
        if ($identifierValue === null) {
            throw new RuntimeException(message: 'Cannot delete an entity without an identifier value.');
        }

        $this->query->builder(connectionName: $connectionName)
            ->from(table: $metadata->table)
            ->where(column: $identifier->column, operator: '=', value: $identifierValue)
            ->delete();
    }

    /**
     * @throws Throwable
     */
    public function refresh(object $entity, string|null $connectionName = null) : object
    {
        $metadata = $this->attributeMetadataReader->for(entityClass: $entity::class);
        $identifier = $metadata->identifierField();

        if ($identifier === null) {
            throw new RuntimeException(message: sprintf('Entity %s has no identifier mapping.', $entity::class));
        }

        $identifierValue = $this->propertyValue(entity: $entity, property: $identifier->property);
        if ($identifierValue === null) {
            throw new RuntimeException(message: 'Cannot refresh an entity without an identifier value.');
        }

        $fresh = $this->find(entityClass: $entity::class, id: $identifierValue, connectionName: $connectionName);
        if ($fresh === null) {
            throw new RuntimeException(message: 'Entity could not be refreshed because it no longer exists.');
        }

        foreach ($metadata->fields as $field) {
            $this->setPropertyValue(
                entity  : $entity,
                property: $field->property,
                value   : $this->propertyValue(entity: $fresh, property: $field->property),
            );
        }

        return $entity;
    }

    /**
     * @param class-string $entityClass
     *
     * @throws Throwable
     */
    public function find(string $entityClass, mixed $id, string|null $connectionName = null) : object|null
    {
        $metadata = $this->attributeMetadataReader->for(entityClass: $entityClass);
        $identifier = $metadata->identifierField();

        if ($identifier === null) {
            throw new RuntimeException(message: sprintf('Entity %s has no identifier mapping.', $entityClass));
        }

        $row = $this->query
            ->builder(connectionName: $connectionName)
            ->from(table: $metadata->table)
            ->where(column: $identifier->column, operator: '=', value: $id)
            ->first();

        if (! is_array(value: $row)) {
            return null;
        }

        return $this->hydrator->hydrate(entityClass: $entityClass, row: $row, metadata: $metadata);
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<object>
     * @throws Throwable
     */
    public function findAll(string $entityClass, array|null $criteria = null, string|null $connectionName = null) : array
    {
        $criteria ??= [];

        return $this->findBy(entityClass: $entityClass, criteria: $criteria, connectionName: $connectionName);
    }

    /**
     * @param class-string $entityClass
     * @param array<string, mixed> $criteria
     *
     * @return list<object>
     * @throws Throwable
     */
    public function findBy(
        string $entityClass,
        array  $criteria,
        string|null $orderBy = null,
        string|null $direction = null,
        ?int $limit = null,
        ?int $offset = null,
        string|null $connectionName = null,
    ) : array
    {
        $metadata = $this->attributeMetadataReader->for(entityClass: $entityClass);
        $query    = $this->query->builder(connectionName: $connectionName)->from(table: $metadata->table);

        foreach ($criteria as $column => $value) {
            $actualColumn = $metadata->fields[$column]->column ?? $column;
            $query        = $query->where(column: $actualColumn, operator: '=', value: $value);
        }

        if ($orderBy !== null) {
            $actualOrderBy = $metadata->fields[$orderBy]->column ?? $orderBy;
            $query         = $query->orderBy(column: $actualOrderBy, direction: $direction ?? 'ASC');
        }

        if ($limit !== null) {
            $query = $query->limit(limit: $limit);
        }

        if ($offset !== null) {
            $query = $query->offset(offset: $offset);
        }

        return array_map(
            callback: fn (array $row) => $this->hydrator->hydrate(
                entityClass: $entityClass,
                row        : $row,
                metadata   : $metadata,
            ),
            array   : $query->get(),
        );
    }
}
