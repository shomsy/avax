<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Foundation;

use Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\DataObject;
use ReflectionClass;
use ReflectionProperty;

/**
 * AbstractDTO - Base class for all Data Transfer Objects.
 *
 * Migrated from DataFoundation\ObjectHandling\DTO\AbstractDTO.
 * Provides constructor-based hydration from array input and validation
 * via Validation component attributes.
 */
abstract class AbstractDTO implements DataObject
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $name = $property->getName();

            if (array_key_exists($name, $data)) {
                $property->setValue($this, $data[$name]);
            }
        }
    }

    /**
     * Convert DTO to associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result     = [];
        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            if ($property->isInitialized($this)) {
                $result[$property->getName()] = $property->getValue($this);
            }
        }

        return $result;
    }
}
