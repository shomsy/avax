<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata;

use ReflectionProperty;

final readonly class EntityMetadata
{
    /**
     * @param array<string, FieldMetadata> $fields
     * @param array<string, RelationMetadata> $relations
     */
    public function __construct(
        public string      $className,
        public string      $table,
        public array       $fields,
        public array       $relations,
        public string|null $repositoryClass = null,
    ) {}

    public function identifierField() : FieldMetadata|null
    {
        foreach ($this->fields as $field) {
            if ($field->id) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function extractColumnValues(object $entity) : array
    {
        $values = [];

        foreach ($this->fields as $field) {
            $property = new ReflectionProperty(class: $entity, property: $field->property);
            $property->setAccessible(accessible: true);
            $values[$field->column] = $property->getValue(object: $entity);
        }

        return $values;
    }

    public function propertyForColumn(string $column) : string|null
    {
        foreach ($this->fields as $field) {
            if ($field->column === $column) {
                return $field->property;
            }
        }

        return null;
    }
}
