<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\LegacyTransfer;

use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;
use ReflectionClass;
use ReflectionProperty;

/**
 * AbstractDTO — legacy base class for constructor-hydrated DTOs.
 *
 * Compatibility-only. New code should extend DataObject directly
 * and use public typed properties + PHP attributes + no constructor.
 *
 * Migrated from DataFoundation\ObjectHandling\DTO\AbstractDTO.
 * Provides constructor-based hydration from array input.
 */
abstract class AbstractDTO extends DataObject
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $reflectionClass = new ReflectionClass($this);
        $properties      = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

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
     * Overrides DataObject::toArray() with legacy simple reflection behavior.
     * New code should use DataObject::toArray() which delegates to DataTransfer.
     *
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        $result          = [];
        $reflectionClass = new ReflectionClass($this);
        $properties      = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            if ($property->isInitialized($this)) {
                $result[$property->getName()] = $property->getValue($this);
            }
        }

        return $result;
    }
}
