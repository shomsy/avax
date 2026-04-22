<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Projections;

use ReflectionClass;
use ReflectionProperty;

final class ResultMapper
{
    private string $className;

    private array $propertyMappings;

    public function __construct(string $className)
    {
        $this->className        = $className;
        $this->propertyMappings = $this->buildMappings(className: $className);
    }

    private function buildMappings(string $className) : array
    {
        $ref      = new ReflectionClass(objectOrClass: $className);
        $mappings = [];

        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $propertyName = $property->getName();
            $dbColumn     = $propertyName;

            $attributes = $property->getAttributes(Column::class);
            if (! empty($attributes)) {
                $attr     = $attributes[0]->newInstance();
                $dbColumn = $attr->name ?? $propertyName;
            }

            $mappings[$dbColumn] = $propertyName;
        }

        return $mappings;
    }

    public function map(array $row) : object
    {
        $instance = new ($this->className)();

        foreach ($this->propertyMappings as $dbColumn => $propertyName) {
            if (array_key_exists(key: $dbColumn, array: $row)) {
                $value = $row[$dbColumn];

                if (method_exists(($this->className), '__set')) {
                    $instance->$propertyName = $value;
                } else {
                    $property = (new ReflectionClass(objectOrClass: $this->className))
                        ->getProperty(name: $propertyName);
                    $property->setValue(object: $instance, value: $value);
                }
            }
        }

        return $instance;
    }

    public function getFieldMappings() : array
    {
        return $this->propertyMappings;
    }

    public function getTargetClass() : string
    {
        return $this->className;
    }
}
