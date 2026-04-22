<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM\Hydration;

use Avax\Database\System\Capabilities\ORM\IdentityMap\IdentityMap;
use Avax\Database\System\Capabilities\ORM\Metadata\EntityMetadata;
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

        if ($identifier !== null && array_key_exists($identifier->column, $row)) {
            $entity = $this->identityMap->get(
                entityClass: $entityClass,
                id         : $row[$identifier->column]
            );
        }

        $entity ??= (new ReflectionClass($entityClass))->newInstanceWithoutConstructor();

        foreach ($metadata->fields as $field) {
            if (! array_key_exists($field->column, $row)) {
                continue;
            }

            $property = new ReflectionProperty($entityClass, $field->property);
            $property->setAccessible(true);
            $property->setValue($entity, $row[$field->column]);
        }

        if ($identifier !== null && array_key_exists($identifier->column, $row)) {
            $this->identityMap->put(
                entityClass: $entityClass,
                id         : $row[$identifier->column],
                entity     : $entity
            );
        }

        return $entity;
    }
}
