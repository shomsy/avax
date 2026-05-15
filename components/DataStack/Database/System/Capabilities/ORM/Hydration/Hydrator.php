<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\Hydration;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\IdentityMap\IdentityMap;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\EntityMetadata;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\FieldMetadata;
use ReflectionClass;
use ReflectionProperty;

final readonly class Hydrator
{
    public function __construct(private IdentityMap $identityMap)
    {
    }

    /**
     * @param  class-string  $entityClass
     * @param  array<string, mixed>  $row
     */
    public function hydrate(string $entityClass, array $row, EntityMetadata $entityMetadata): object
    {
        $identifier = $entityMetadata->identifierField();
        $entity = null;

        if ($identifier instanceof FieldMetadata && array_key_exists(key: $identifier->column, array: $row)) {
            $entity = $this->identityMap->get(
                entityClass: $entityClass,
                id         : $row[$identifier->column],
            );
        }

        $entity = (new ReflectionClass(objectOrClass: $entityClass))->newInstanceWithoutConstructor();

        foreach ($entityMetadata->fields as $field) {
            if (! array_key_exists(key: $field->column, array: $row)) {
                continue;
            }

            $property = new ReflectionProperty(class: $entityClass, property: $field->property);
            $property->setValue(objectOrValue: $entity, value: $row[$field->column]);
        }

        if ($identifier instanceof FieldMetadata && array_key_exists(key: $identifier->column, array: $row)) {
            $this->identityMap->put(
                entityClass: $entityClass,
                id         : $row[$identifier->column],
                entity     : $entity,
            );
        }

        return $entity;
    }
}
