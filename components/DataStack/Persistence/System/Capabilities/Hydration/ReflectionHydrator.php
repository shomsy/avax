<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Hydration;

use Avax\Components\DataStack\Persistence\System\Capabilities\IdentityMap\IdentityMap;
use ReflectionClass;
use ReflectionProperty;

/**
 * Reflection-based entity hydrator.
 *
 * Converts raw database row arrays into entity objects using Reflection.
 * Uses the IdentityMap to ensure that the same database row always returns
 * the same PHP object instance within a single request scope.
 *
 * Recovered from Database/ORM/Hydration/Hydrator and placed under
 * Persistence ownership per ADR-0012.
 */
final readonly class ReflectionHydrator implements HydratorInterface
{
    public function __construct(
        private IdentityMap $identityMap,
    ) {}

    public function hydrateAll(string $entityClass, array $rows): array
    {
        return array_map(
            fn (array $row): object => $this->hydrate($entityClass, $row),
            $rows,
        );
    }

    public function hydrate(string $entityClass, array $row): object
    {
        // Check IdentityMap first using 'id' column convention
        $idValue = $row['id'] ?? null;

        if ($idValue !== null) {
            $existing = $this->identityMap->get(entityClass: $entityClass, id: $idValue);

            if ($existing !== null) {
                return $existing;
            }
        }

        // Create new instance without constructor
        $entity = new ReflectionClass(objectOrClass: $entityClass)->newInstanceWithoutConstructor();

        // Map row columns to object properties
        foreach ($row as $column => $value) {
            $propertyName = $this->columnToProperty($column);

            if (! property_exists($entity, $propertyName)) {
                continue;
            }

            $property = new ReflectionProperty(class: $entityClass, property: $propertyName);
            $property->setValue(objectOrValue: $entity, value: $value);
        }

        // Register in IdentityMap
        if ($idValue !== null) {
            $this->identityMap->put(
                entityClass: $entityClass,
                id         : $idValue,
                entity     : $entity,
            );
        }

        return $entity;
    }

    /**
     * Convert a snake_case column name to a camelCase property name.
     */
    private function columnToProperty(string $column): string
    {
        return lcfirst(
            str_replace(' ', '', ucwords(str_replace('_', ' ', $column))),
        );
    }
}
