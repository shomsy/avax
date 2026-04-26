<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM;

use ReflectionClass;

/**
 * Base Entity class to define a domain object.
 */
abstract class Entity
{
    /**
     * Get the table name for the entity.
     *
     * @return string The table name associated with the entity.
     */
    public static function getTableName() : string
    {
        $shortName = new ReflectionClass(objectOrClass: static::class)->getShortName();

        return strtolower(string: $shortName) . 's';
    }
}
