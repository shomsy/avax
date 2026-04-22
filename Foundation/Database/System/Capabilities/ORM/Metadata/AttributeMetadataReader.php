<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM\Metadata;

use Avax\Database\System\Capabilities\ORM\Attributes\Column;
use Avax\Database\System\Capabilities\ORM\Attributes\Entity;
use Avax\Database\System\Capabilities\ORM\Attributes\GeneratedValue;
use Avax\Database\System\Capabilities\ORM\Attributes\Id;
use Avax\Database\System\Capabilities\ORM\Attributes\JoinColumn;
use Avax\Database\System\Capabilities\ORM\Attributes\ManyToMany;
use Avax\Database\System\Capabilities\ORM\Attributes\ManyToOne;
use Avax\Database\System\Capabilities\ORM\Attributes\OneToMany;
use Avax\Database\System\Capabilities\ORM\Attributes\OneToOne;
use Avax\Database\System\Capabilities\ORM\Attributes\Table;
use Avax\Database\System\Capabilities\ORM\Relations\RelationKind;
use ReflectionClass;
use RuntimeException;

final class AttributeMetadataReader
{
    /** @var array<class-string, EntityMetadata> */
    private array $cache = [];

    /**
     * @param class-string $entityClass
     */
    public function for(string $entityClass) : EntityMetadata
    {
        return $this->cache[$entityClass] ??= $this->read(entityClass: $entityClass);
    }

    /**
     * @param class-string $entityClass
     */
    private function read(string $entityClass) : EntityMetadata
    {
        $reflection = new ReflectionClass(objectOrClass: $entityClass);

        $entityAttribute = $reflection->getAttributes(name: Entity::class)[0] ?? null;
        if ($entityAttribute === null) {
            throw new RuntimeException(message: sprintf('Entity class %s must declare #[Entity].', $entityClass));
        }

        $tableAttribute = $reflection->getAttributes(name: Table::class)[0] ?? null;
        $table          = $tableAttribute !== null
            ? $tableAttribute->newInstance()->name
            : strtolower(string: $reflection->getShortName()) . 's';

        $entity    = $entityAttribute->newInstance();
        $fields    = [];
        $relations = [];

        foreach ($reflection->getProperties() as $property) {
            $columnAttribute    = $property->getAttributes(name: Column::class)[0] ?? null;
            $idAttribute        = $property->getAttributes(name: Id::class)[0] ?? null;
            $generatedAttribute = $property->getAttributes(name: GeneratedValue::class)[0] ?? null;

            if ($columnAttribute !== null || $idAttribute !== null) {
                $column                       = $columnAttribute?->newInstance() ?? new Column(name: $property->getName());
                $fields[$property->getName()] = new FieldMetadata(
                    property : $property->getName(),
                    column   : $column->name ?? $property->getName(),
                    type     : $column->type,
                    id       : $idAttribute !== null,
                    generated: $generatedAttribute !== null,
                    nullable : $column->nullable
                );
            }

            $joinColumn = $property->getAttributes(name: JoinColumn::class)[0] ?? null;
            $relation   = $property->getAttributes(name: ManyToOne::class)[0]
                ?? $property->getAttributes(name: OneToMany::class)[0]
                ?? $property->getAttributes(name: OneToOne::class)[0]
                ?? $property->getAttributes(name: ManyToMany::class)[0]
                ?? null;

            if ($relation === null) {
                continue;
            }

            $instance = $relation->newInstance();
            $kind     = match (true) {
                $instance instanceof ManyToOne => RelationKind::ManyToOne,
                $instance instanceof OneToMany => RelationKind::OneToMany,
                $instance instanceof OneToOne  => RelationKind::OneToOne,
                default                        => RelationKind::ManyToMany,
            };

            $relations[$property->getName()] = new RelationMetadata(
                property        : $property->getName(),
                kind            : $kind,
                targetEntity    : $instance->targetEntity,
                mappedBy        : $instance->mappedBy ?? null,
                inversedBy      : $instance->inversedBy ?? null,
                joinColumn      : $joinColumn?->newInstance()->name ?? null,
                referencedColumn: $joinColumn?->newInstance()->referencedColumnName ?? 'id',
                cascade         : $instance->cascade ?? [],
                lazy            : $instance->lazy ?? true
            );
        }

        return new EntityMetadata(
            className      : $entityClass,
            table          : $table,
            fields         : $fields,
            relations      : $relations,
            repositoryClass: $entity->repositoryClass
        );
    }
}
