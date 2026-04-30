<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\ObjectHandling;

/**
 * ObjectMapper - handles mapping arrays to objects and vice-versa.
 * Migrated from legacy DataFoundation.
 */
final class ObjectMapper
{
    public static function map(array $data, object $object) : object
    {
        foreach ($data as $key => $value) {
            if (property_exists($object, $key)) {
                $object->$key = $value;
            }
        }

        return $object;
    }

    public static function toArray(object $object) : array
    {
        return (array) $object;
    }
}
