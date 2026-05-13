<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\Persisters;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\Hydration\Hydrator;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\AttributeMetadataReader;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\EntityMetadata;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\FieldMetadata;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Query;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\CompiledDatabaseLifecycleRegistry;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecyclePhase;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityCreated;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityCreating;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityDeleted;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityDeleting;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntitySaved;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntitySaving;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityUpdated;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\EntityUpdating;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\FailedToDelete;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\Events\FailedToSave;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;
use Throwable;

/**
 * Entity persistence with lifecycle hook integration.
 *
 * Fires entity lifecycle events through the compiled registry:
 * - creating/created around insert
 * - updating/updated around update
 * - saving/saved as superset for create and update
 * - deleting/deleted around delete
 * - failedToSave on save failure (exception still bubbles)
 * - failedToDelete on delete failure (exception still bubbles)
 */
final readonly class EntityPersister
{
    public function __construct(
        private Query $query,
        private AttributeMetadataReader $attributeMetadataReader,
        private Hydrator $hydrator,
        private CompiledDatabaseLifecycleRegistry $registry = new CompiledDatabaseLifecycleRegistry(),
        private string $connectionName = 'default',
    ) {
    }

    /**
     * @throws Throwable
     */
    public function insert(object $entity, string|null $connectionName = null) : void
    {
        $entityMetadata = $this->attributeMetadataReader->for(entityClass: $entity::class);
        $connection = $connectionName ?? $this->connectionName;
        $payload = $this->payload(entity: $entity, includeIdentifier: false, metadata: $entityMetadata);

        // Before: creating + saving
        $this->dispatchEntityLifecycle(
            entityClass: $entity::class,
            phases: [EntityLifecyclePhase::Creating, EntityLifecyclePhase::Saving],
            entity: null,
            attributes: $payload,
            connection: $connection,
        );

        try {
            $queryBuilder = $this->query->builder(connectionName: $connection)->from(table: $entityMetadata->table);
            $generatedId = $queryBuilder->insertGetId(values: $payload);

            $identifier = $entityMetadata->identifierField();
            if ($identifier instanceof FieldMetadata && $identifier->generated) {
                $this->setPropertyValue(entity: $entity, property: $identifier->property, value: $generatedId);
            }

            // After: created + saved
            $this->dispatchEntityLifecycleAfter(
                entityClass: $entity::class,
                phases: [EntityLifecyclePhase::Created, EntityLifecyclePhase::Saved],
                entity: $entity,
                attributes: $payload,
                connection: $connection,
                lastInsertId: (string) $generatedId,
            );
        } catch (Throwable $e) {
            $this->dispatchEntityFailure(
                entityClass: $entity::class,
                phase: EntityLifecyclePhase::FailedToSave,
                attributes: $payload,
                connection: $connection,
                exception: $e,
            );

            throw $e;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(object $entity, EntityMetadata $entityMetadata, bool $includeIdentifier): array
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
    private function propertyValue(object $entity, string $property): mixed
    {
        $reflectionProperty = new ReflectionProperty(class: $entity, property: $property);

        return $reflectionProperty->getValue(object: $entity);
    }

    /**
     * @throws ReflectionException
     */
    private function setPropertyValue(object $entity, string $property, mixed $value): void
    {
        $reflectionProperty = new ReflectionProperty(class: $entity, property: $property);
        $reflectionProperty->setValue(objectOrValue: $entity, value: $value);
    }

    /**
     * @throws Throwable
     */
    public function update(object $entity, string|null $connectionName = null) : void
    {
        $entityMetadata = $this->attributeMetadataReader->for(entityClass: $entity::class);
        $connection = $connectionName ?? $this->connectionName;
        $identifier = $entityMetadata->identifierField();

        if (! $identifier instanceof FieldMetadata) {
            throw new RuntimeException(message: sprintf('Entity %s has no identifier mapping.', $entity::class));
        }

        $identifierValue = $this->propertyValue(entity: $entity, property: $identifier->property);
        if ($identifierValue === null) {
            throw new RuntimeException(message: 'Cannot update an entity without an identifier value.');
        }

        $payload = $this->payload(entity: $entity, includeIdentifier: false, metadata: $entityMetadata);

        // Before: updating + saving
        $this->dispatchEntityLifecycle(
            entityClass: $entity::class,
            phases: [EntityLifecyclePhase::Updating, EntityLifecyclePhase::Saving],
            entity: $entity,
            attributes: $payload,
            connection: $connection,
        );

        try {
            $this->query->builder(connectionName: $connection)
                ->from(table: $entityMetadata->table)
                ->where(column: $identifier->column, operator: '=', value: $identifierValue)
                ->update(values: $payload);

            // After: updated + saved
            $this->dispatchEntityLifecycleAfter(
                entityClass: $entity::class,
                phases: [EntityLifecyclePhase::Updated, EntityLifecyclePhase::Saved],
                entity: $entity,
                attributes: $payload,
                connection: $connection,
            );
        } catch (Throwable $e) {
            $this->dispatchEntityFailure(
                entityClass: $entity::class,
                phase: EntityLifecyclePhase::FailedToSave,
                attributes: $payload,
                connection: $connection,
                exception: $e,
            );

            throw $e;
        }
    }

    /**
     * @throws Throwable
     */
    public function delete(object $entity, string|null $connectionName = null) : void
    {
        $entityMetadata = $this->attributeMetadataReader->for(entityClass: $entity::class);
        $connection = $connectionName ?? $this->connectionName;
        $identifier = $entityMetadata->identifierField();

        if (! $identifier instanceof FieldMetadata) {
            throw new RuntimeException(message: sprintf('Entity %s has no identifier mapping.', $entity::class));
        }

        $identifierValue = $this->propertyValue(entity: $entity, property: $identifier->property);
        if ($identifierValue === null) {
            throw new RuntimeException(message: 'Cannot delete an entity without an identifier value.');
        }

        // Before: deleting
        $this->dispatchEntityLifecycle(
            entityClass: $entity::class,
            phases: [EntityLifecyclePhase::Deleting],
            entity: $entity,
            attributes: [],
            connection: $connection,
        );

        try {
            $this->query->builder(connectionName: $connection)
                ->from(table: $entityMetadata->table)
                ->where(column: $identifier->column, operator: '=', value: $identifierValue)
                ->delete();

            // After: deleted
            $this->dispatchEntityEvent(
                event: new EntityDeleted(
                    entityClass: $entity::class,
                    entity: $entity,
                    connection: $connection,
                ),
            );
        } catch (Throwable $e) {
            $this->dispatchEntityFailure(
                entityClass: $entity::class,
                phase: EntityLifecyclePhase::FailedToDelete,
                attributes: [],
                connection: $connection,
                exception: $e,
                entity: $entity,
            );

            throw $e;
        }
    }

    /**
     * @throws Throwable
     */
    public function refresh(object $entity, string|null $connectionName = null) : object
    {
        $entityMetadata = $this->attributeMetadataReader->for(entityClass: $entity::class);
        $connection = $connectionName ?? $this->connectionName;
        $identifier = $entityMetadata->identifierField();

        if (! $identifier instanceof FieldMetadata) {
            throw new RuntimeException(message: sprintf('Entity %s has no identifier mapping.', $entity::class));
        }

        $identifierValue = $this->propertyValue(entity: $entity, property: $identifier->property);
        if ($identifierValue === null) {
            throw new RuntimeException(message: 'Cannot refresh an entity without an identifier value.');
        }

        $fresh = $this->find(entityClass: $entity::class, id: $identifierValue, connectionName: $connection);
        if ($fresh === null) {
            throw new RuntimeException(message: 'Entity could not be refreshed because it no longer exists.');
        }

        foreach ($entityMetadata->fields as $field) {
            $this->setPropertyValue(
                entity  : $entity,
                property: $field->property,
                value   : $this->propertyValue(entity: $fresh, property: $field->property),
            );
        }

        return $entity;
    }

    /**
     * @param  class-string  $entityClass
     *
     * @throws Throwable
     */
    public function find(string $entityClass, mixed $id, string|null $connectionName = null) : object|null
    {
        $entityMetadata = $this->attributeMetadataReader->for(entityClass: $entityClass);
        $identifier = $entityMetadata->identifierField();

        if (! $identifier instanceof FieldMetadata) {
            throw new RuntimeException(message: sprintf('Entity %s has no identifier mapping.', $entityClass));
        }

        $row = $this->query
            ->builder(connectionName: $connectionName)
            ->from(table: $entityMetadata->table)
            ->where(column: $identifier->column, operator: '=', value: $id)
            ->first();

        if (! is_array(value: $row)) {
            return null;
        }

        return $this->hydrator->hydrate(entityClass: $entityClass, row: $row, metadata: $entityMetadata);
    }

    /**
     * @param  array<string, mixed>  $criteria
     * @return list<object>
     *
     * @throws Throwable
     */
    public function findAll(string $entityClass, array|null $criteria = null, string|null $connectionName = null) : array
    {
        $criteria ??= [];

        return $this->findBy(entityClass: $entityClass, criteria: $criteria, connectionName: $connectionName);
    }

    /**
     * @param  class-string  $entityClass
     * @param  array<string, mixed>  $criteria
     * @return list<object>
     *
     * @throws Throwable
     */
    public function findBy(
        string $entityClass,
        array $criteria, string|null $orderBy = null, string|null $direction = null, int|null $limit = null, int|null $offset = null, string|null $connectionName = null,
    ): array {
        $entityMetadata = $this->attributeMetadataReader->for(entityClass: $entityClass);
        $query = $this->query->builder(connectionName: $connectionName)->from(table: $entityMetadata->table);

        foreach ($criteria as $column => $value) {
            $actualColumn = $entityMetadata->fields[$column]->column ?? $column;
            $query = $query->where(column: $actualColumn, operator: '=', value: $value);
        }

        if ($orderBy !== null) {
            $actualOrderBy = $entityMetadata->fields[$orderBy]->column ?? $orderBy;
            $query = $query->orderBy(column: $actualOrderBy, direction: $direction ?? 'ASC');
        }

        if ($limit !== null) {
            $query = $query->limit(limit: $limit);
        }

        if ($offset !== null) {
            $query = $query->offset(offset: $offset);
        }

        return array_map(
            callback: fn (array $row): object => $this->hydrator->hydrate(
                entityClass: $entityClass,
                row        : $row,
                metadata   : $entityMetadata,
            ),
            array   : $query->get(),
        );
    }

    /**
     * Dispatch pre-event lifecycle events (before persistence).
     *
     * @param list<EntityLifecyclePhase> $phases
     * @param array<string, mixed> $attributes
     */
    private function dispatchEntityLifecycle(
        string $entityClass,
        array $phases,
        ?object $entity,
        array $attributes,
        string $connection,
    ): void {
        foreach ($phases as $phase) {
            $listeners = $this->registry->entityListenersFor($entityClass, $phase);
            if ($listeners === []) {
                continue;
            }

            $event = $this->buildPreEvent(
                phase: $phase,
                entityClass: $entityClass,
                entity: $entity,
                attributes: $attributes,
                connection: $connection,
            );

            $this->dispatchEntityEvent($event);
        }
    }

    /**
     * Dispatch post-event lifecycle events (after successful persistence).
     *
     * @param list<EntityLifecyclePhase> $phases
     * @param array<string, mixed> $attributes
     */
    private function dispatchEntityLifecycleAfter(
        string $entityClass,
        array $phases,
        object $entity,
        array $attributes,
        string $connection,
        string $lastInsertId = '',
    ): void {
        foreach ($phases as $phase) {
            $listeners = $this->registry->entityListenersFor($entityClass, $phase);
            if ($listeners === []) {
                continue;
            }

            $event = $this->buildPostEvent(
                phase: $phase,
                entityClass: $entityClass,
                entity: $entity,
                attributes: $attributes,
                connection: $connection,
                lastInsertId: $lastInsertId,
            );

            $this->dispatchEntityEvent($event);
        }
    }

    /**
     * Dispatch failure lifecycle event. Exception still bubbles.
     *
     * @param array<string, mixed> $attributes
     */
    private function dispatchEntityFailure(
        string $entityClass,
        EntityLifecyclePhase $phase,
        array $attributes,
        string $connection,
        Throwable $exception,
        ?object $entity = null,
    ): void {
        $listeners = $this->registry->entityListenersFor($entityClass, $phase);
        if ($listeners === []) {
            return;
        }

        $event = $entity !== null
            ? new FailedToDelete(
                entityClass: $entityClass,
                entity: $entity,
                connection: $connection,
                exception: $exception,
            )
            : new FailedToSave(
                entityClass: $entityClass,
                attributes: $attributes,
                connection: $connection,
                exception: $exception,
            );

        $this->dispatchEntityEvent($event);
    }

    /**
     * Build a pre-persistence event object.
     *
     * @param array<string, mixed> $attributes
     */
    private function buildPreEvent(
        EntityLifecyclePhase $phase,
        string $entityClass,
        ?object $entity,
        array $attributes,
        string $connection,
    ): object {
        return match ($phase) {
            EntityLifecyclePhase::Creating => new EntityCreating(
                entityClass: $entityClass,
                attributes: $attributes,
                connection: $connection,
            ),
            EntityLifecyclePhase::Updating => new EntityUpdating(
                entityClass: $entityClass,
                entity: $entity ?? throw new RuntimeException('Entity must not be null for updating event'),
                attributes: $attributes,
                connection: $connection,
            ),
            EntityLifecyclePhase::Saving => new EntitySaving(
                entityClass: $entityClass,
                entity: $entity,
                attributes: $attributes,
                connection: $connection,
            ),
            EntityLifecyclePhase::Deleting => new EntityDeleting(
                entityClass: $entityClass,
                entity: $entity ?? throw new RuntimeException('Entity must not be null for deleting event'),
                connection: $connection,
            ),
            default => throw new RuntimeException("Unexpected pre-event phase: {$phase->value}"),
        };
    }

    /**
     * Build a post-persistence event object.
     *
     * @param array<string, mixed> $attributes
     */
    private function buildPostEvent(
        EntityLifecyclePhase $phase,
        string $entityClass,
        object $entity,
        array $attributes,
        string $connection,
        string $lastInsertId = '',
    ): object {
        return match ($phase) {
            EntityLifecyclePhase::Created => new EntityCreated(
                entityClass: $entityClass,
                entity: $entity,
                attributes: $attributes,
                connection: $connection,
                lastInsertId: $lastInsertId,
            ),
            EntityLifecyclePhase::Updated => new EntityUpdated(
                entityClass: $entityClass,
                entity: $entity,
                attributes: $attributes,
                connection: $connection,
            ),
            EntityLifecyclePhase::Saved => new EntitySaved(
                entityClass: $entityClass,
                entity: $entity,
                attributes: $attributes,
                connection: $connection,
            ),
            default => throw new RuntimeException("Unexpected post-event phase: {$phase->value}"),
        };
    }

    /**
     * Dispatch an entity lifecycle event through the registry.
     *
     * Listeners are invoked via the compiled registry — no reflection in hot path.
     * Listener failure bubbles by default.
     */
    private function dispatchEntityEvent(object $event): void
    {
        $entityClass = match (true) {
            $event instanceof EntityCreating => $event->entityClass,
            $event instanceof EntityCreated => $event->entityClass,
            $event instanceof EntityUpdating => $event->entityClass,
            $event instanceof EntityUpdated => $event->entityClass,
            $event instanceof EntitySaving => $event->entityClass,
            $event instanceof EntitySaved => $event->entityClass,
            $event instanceof EntityDeleting => $event->entityClass,
            $event instanceof EntityDeleted => $event->entityClass,
            $event instanceof FailedToSave => $event->entityClass,
            $event instanceof FailedToDelete => $event->entityClass,
            default => null,
        };

        if ($entityClass === null) {
            return;
        }

        $phase = match (true) {
            $event instanceof EntityCreating => EntityLifecyclePhase::Creating,
            $event instanceof EntityCreated => EntityLifecyclePhase::Created,
            $event instanceof EntityUpdating => EntityLifecyclePhase::Updating,
            $event instanceof EntityUpdated => EntityLifecyclePhase::Updated,
            $event instanceof EntitySaving => EntityLifecyclePhase::Saving,
            $event instanceof EntitySaved => EntityLifecyclePhase::Saved,
            $event instanceof EntityDeleting => EntityLifecyclePhase::Deleting,
            $event instanceof EntityDeleted => EntityLifecyclePhase::Deleted,
            $event instanceof FailedToSave => EntityLifecyclePhase::FailedToSave,
            $event instanceof FailedToDelete => EntityLifecyclePhase::FailedToDelete,
            default => null,
        };

        if ($phase === null) {
            return;
        }

        $listeners = $this->registry->entityListenersFor($entityClass, $phase);

        foreach ($listeners as $entry) {
            $listener = $entry['listener'];
            $instance = new $listener();
            // @phpstan-ignore-next-line
            $instance($event);
        }
    }
}
