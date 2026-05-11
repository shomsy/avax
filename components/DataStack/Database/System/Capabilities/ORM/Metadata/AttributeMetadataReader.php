<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Column;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Entity;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\GeneratedValue;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Id;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\JoinColumn;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\ManyToMany;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\ManyToOne;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\OneToMany;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\OneToOne;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes\Table;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Relations\RelationKind;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\AttributeCompiler;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CompiledAttributeMetadata;
use ReflectionClass;
use RuntimeException;

final class AttributeMetadataReader
{
    /** @var array<class-string, EntityMetadata> */
    private array $cache = [];

    private AttributeCompiler|null $attributeCompiler;

    public function __construct(AttributeCompiler|null $attributeCompiler = null)
    {
        $this->attributeCompiler = $attributeCompiler;
    }

    /**
     * @param  class-string  $entityClass
     */
    public function for(string $entityClass): EntityMetadata
    {
        return $this->cache[$entityClass] ??= $this->read(entityClass: $entityClass);
    }

    /**
     * @param  class-string  $entityClass
     */
    private function read(string $entityClass): EntityMetadata
    {
        // Try compiled metadata first (V5-08)
        $compiled = $this->attributeCompiler?->resolve($entityClass);
        if ($compiled !== null) {
            return $this->readFromCompiled($entityClass, $compiled);
        }

        // Fall back to reflection
        return $this->readFromReflection($entityClass);
    }

    /**
     * Read entity metadata from compiled attribute metadata.
     *
     * @param class-string $entityClass
     */
    private function readFromCompiled(string $entityClass, CompiledAttributeMetadata $compiled): EntityMetadata
    {
        $classAttrs = $compiled->classAttributes;
        if (! isset($classAttrs['Entity'])) {
            throw new RuntimeException(message: sprintf('Entity class %s must declare #[Entity].', $entityClass));
        }

        $table = $classAttrs['Table']['name']
            ?? strtolower((new ReflectionClass($entityClass))->getShortName()) . 's';

        $fields = [];
        $relations = [];
        foreach ($compiled->propertyAttributes as $propertyName => $attrs) {
            if (isset($attrs['Column']) || isset($attrs['Id'])) {
                $columnArgs = $attrs['Column'] ?? [];
                $fields[$propertyName] = new FieldMetadata(
                    property: $propertyName,
                    column: $columnArgs['name'] ?? $propertyName,
                    type: $columnArgs['type'] ?? null,
                    id: isset($attrs['Id']),
                    generated: isset($attrs['GeneratedValue']),
                    nullable: $columnArgs['nullable'] ?? false,
                );
            }

            if (isset($attrs['ManyToOne']) || isset($attrs['OneToMany']) || isset($attrs['OneToOne']) || isset($attrs['ManyToMany'])) {
                $kind = match (true) {
                    isset($attrs['ManyToOne']) => RelationKind::ManyToOne,
                    isset($attrs['OneToMany']) => RelationKind::OneToMany,
                    isset($attrs['OneToOne']) => RelationKind::OneToOne,
                    default => RelationKind::ManyToMany,
                };

                $relationArgs = $attrs[$this->getRelationKind($attrs)] ?? [];
                $joinColumnArgs = $attrs['JoinColumn'] ?? [];

                $relations[$propertyName] = new RelationMetadata(
                    property: $propertyName,
                    targetEntity: $relationArgs['targetEntity'],
                    mappedBy: $relationArgs['mappedBy'] ?? null,
                    inversedBy: $relationArgs['inversedBy'] ?? null,
                    joinColumn: $joinColumnArgs['name'] ?? null,
                    referencedColumn: $joinColumnArgs['referencedColumnName'] ?? 'id',
                    cascade: $relationArgs['cascade'] ?? [],
                    lazy: $relationArgs['lazy'] ?? true,
                    relationKind: $kind,
                );
            }
        }

        return new EntityMetadata(
            className: $entityClass,
            table: $table,
            fields: $fields,
            relations: $relations,
            repositoryClass: $classAttrs['Entity']['repositoryClass'] ?? null,
        );
    }

    /**
     * @param array<string, array<string, mixed>> $attrs
     */
    private function getRelationKind(array $attrs): string
    {
        foreach (['ManyToOne', 'OneToMany', 'OneToOne', 'ManyToMany'] as $kind) {
            if (isset($attrs[$kind])) {
                return $kind;
            }
        }

        return 'ManyToMany';
    }

    /**
     * Read entity metadata via reflection (original behavior).
     *
     * @param class-string $entityClass
     */
    private function readFromReflection(string $entityClass): EntityMetadata
    {
        $reflectionClass = new ReflectionClass(objectOrClass: $entityClass);

        $entityAttribute = $reflectionClass->getAttributes(name: Entity::class)[0] ?? null;
        if ($entityAttribute === null) {
            throw new RuntimeException(message: sprintf('Entity class %s must declare #[Entity].', $entityClass));
        }

        $tableAttribute = $reflectionClass->getAttributes(name: Table::class)[0] ?? null;
        $table = $tableAttribute !== null
            ? $tableAttribute->newInstance()->name
            : strtolower(string: $reflectionClass->getShortName()).'s';

        $entity = $entityAttribute->newInstance();
        $fields = [];
        $relations = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $columnAttribute = $reflectionProperty->getAttributes(name: Column::class)[0] ?? null;
            $idAttribute = $reflectionProperty->getAttributes(name: Id::class)[0] ?? null;
            $generatedAttribute = $reflectionProperty->getAttributes(name: GeneratedValue::class)[0] ?? null;

            if ($columnAttribute !== null || $idAttribute !== null) {
                $column = $columnAttribute?->newInstance() ?? new Column(name: $reflectionProperty->getName());
                $fields[$reflectionProperty->getName()] = new FieldMetadata(
                    property : $reflectionProperty->getName(),
                    column   : $column->name ?? $reflectionProperty->getName(),
                    type     : $column->type,
                    id       : $idAttribute !== null,
                    generated: $generatedAttribute !== null,
                    nullable : $column->nullable,
                );
            }

            $joinColumn = $reflectionProperty->getAttributes(name: JoinColumn::class)[0] ?? null;
            $relation = $reflectionProperty->getAttributes(name: ManyToOne::class)[0]
                ?? $reflectionProperty->getAttributes(name: OneToMany::class)[0]
                ?? $reflectionProperty->getAttributes(name: OneToOne::class)[0]
                ?? $reflectionProperty->getAttributes(name: ManyToMany::class)[0]
                ?? null;

            if ($relation === null) {
                continue;
            }

            $instance = $relation->newInstance();
            $kind = match (true) {
                $instance instanceof ManyToOne => RelationKind::ManyToOne,
                $instance instanceof OneToMany => RelationKind::OneToMany,
                $instance instanceof OneToOne => RelationKind::OneToOne,
                default => RelationKind::ManyToMany,
            };

            $relations[$reflectionProperty->getName()] = new RelationMetadata(
                property        : $reflectionProperty->getName(),
                targetEntity    : $instance->targetEntity,
                mappedBy        : $instance->mappedBy ?? null,
                inversedBy      : $instance->inversedBy ?? null,
                joinColumn      : $joinColumn?->newInstance()->name ?? null,
                referencedColumn: $joinColumn?->newInstance()->referencedColumnName ?? 'id',
                cascade         : $instance->cascade ?? [],
                lazy            : $instance->lazy ?? true,
                relationKind    : $kind,
            );
        }

        return new EntityMetadata(
            className      : $entityClass,
            table          : $table,
            fields         : $fields,
            relations      : $relations,
            repositoryClass: $entity->repositoryClass,
        );
    }
}
