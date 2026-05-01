<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Projections;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

final readonly class ResultMapper
{
    private array $propertyMappings;

    public function __construct(private string $className)
    {
        $this->propertyMappings = $this->buildMappings(className: $this->className);
    }

    /**
     * @throws ReflectionException
     */
    private function buildMappings(string $className): array
    {
        $reflectionClass = new ReflectionClass(objectOrClass: $className);
        $mappings = [];

        foreach ($reflectionClass->getProperties(filter: ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $dbColumn = $propertyName;

            $attributes = $reflectionProperty->getAttributes(name: Column::class);
            if (! empty($attributes)) {
                $attr = $attributes[0]->newInstance();
                $dbColumn = $attr->name ?? $propertyName;
            }

            $mappings[$dbColumn] = $propertyName;
        }

        return $mappings;
    }

    /**
     * @throws ReflectionException
     */
    public function map(array $row): object
    {
        $instance = new ($this->className)();

        foreach ($this->propertyMappings as $dbColumn => $propertyName) {
            if (array_key_exists(key: $dbColumn, array: $row)) {
                $value = $row[$dbColumn];

                if (method_exists(object_or_class: ($this->className), method: '__set')) {
                    $instance->$propertyName = $value;
                } else {
                    $property = new ReflectionClass(objectOrClass: $this->className)
                        ->getProperty(name: $propertyName);
                    $property->setValue(value: $value, object: $instance);
                }
            }
        }

        return $instance;
    }

    public function getFieldMappings(): array
    {
        return $this->propertyMappings;
    }

    public function getTargetClass(): string
    {
        return $this->className;
    }
}
