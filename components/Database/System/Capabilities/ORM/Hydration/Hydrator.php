<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\ORM\Hydration;

use Avax\Components\Database\System\Capabilities\ORM\IdentityMap\IdentityMap;
use Avax\Components\Database\System\Capabilities\ORM\Metadata\EntityMetadata;
use ReflectionClass;
use ReflectionProperty;

final class Hydrator
{
    public function __construct(private readonly IdentityMap $identityMap) {}

    /**
     * @param class-string         $entityClass
     * @param array<string, mixed> $row
     */
    public function hydrate(string $entityClass, array $row, EntityMetadata $metadata) : object
    {
        $identifier = $metadata->identifierField();
        $entity     = null;

        if ($identifier !== null && array_key_exists(key: $identifier->column, array: $row)) {
            $entity = $this->identityMap->get(
                entityClass: $entityClass,
                id         : $row[$identifier->column]
            );
        }

        $entity ??= new ReflectionClass(objectOrClass: $entityClass)->newInstanceWithoutConstructor();

        foreach ($metadata->fields as $field) {
            if (! array_key_exists(key: $field->column, array: $row)) {
                continue;
            }

            $property = new ReflectionProperty(class: $entityClass, property: $field->property);
            $property->setAccessible(accessible: true);
            $property->setValue(objectOrValue: $entity, value: $row[$field->column]);
        }

        if ($identifier !== null && array_key_exists(key: $identifier->column, array: $row)) {
            $this->identityMap->put(
                entityClass: $entityClass,
                id         : $row[$identifier->column],
                entity     : $entity
            );
        }

        return $entity;
    }
}
