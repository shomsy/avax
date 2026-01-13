<?php

declare(strict_types=1);

namespace Avax\Entity;

use ReflectionClass;

/**
 * Base Entity class to define a domain object.
 *
 * This serves as a base class for all domain entities, providing common functionality.
 * Currently, it is automating table name derivation from the class name.
 */
abstract class Entity
{
    /**
     * Get the table name for the entity.
     *
     * This method derives the table name from the class name of the entity. By default,
     * it converts the class name to lowercase and appends an 's' to follow convention
     * (e.g., the class 'User' becomes 'users'). Override this method in specific entities
     * if a different table name is required.
     *
     * @return string The table name associated with the entity.
     */
    public static function getTableName(): string
    {
        // Using reflection to acquire the short name of the class.
        // Allows automatic table name determination based on class naming conventions.
        $shortName = (new ReflectionClass(objectOrClass: static::class))->getShortName();

        // Converting to lowercase and appending 's' to standardize table naming.
        return strtolower(string: $shortName).'s';
    }
}
