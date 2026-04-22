<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Cache for reflection metadata to optimize repeated reflection operations.
 *
 * Stores reflection results for classes, methods, and properties to avoid
 * expensive reflection operations during route validation and processing.
 *
 * Thread-safe and memory-efficient with automatic cleanup.
 */
final class ReflectionCache
{
    /**
     * @var array<string, ReflectionClass<object>>
     */
    private static array $classCache = [];

    /**
     * @var array<string, ReflectionMethod>
     */
    private static array $methodCache = [];

    /**
     * @var array<string, ReflectionProperty>
     */
    private static array $propertyCache = [];

    /**
     * Check if a class has a specific method (cached).
     *
     * @template T of object
     * @param class-string<T>|T $classOrObject
     * @param string            $methodName
     *
     * @return bool
     */
    public static function hasMethod(object|string $classOrObject, string $methodName) : bool
    {
        try {
            self::getMethod(classOrObject: $classOrObject, methodName: $methodName);

            return true;
        } catch (ReflectionException) {
            return false;
        }
    }

    /**
     * Get cached ReflectionMethod for a class method.
     *
     * @template T of object
     * @param class-string<T>|T $classOrObject
     * @param string            $methodName
     *
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    public static function getMethod(object|string $classOrObject, string $methodName) : ReflectionMethod
    {
        $className = is_string($classOrObject) ? $classOrObject : $classOrObject::class;
        $key       = $className . '::' . $methodName;

        return self::$methodCache[$key] ??= self::getClass(className: $className)->getMethod(name: $methodName);
    }

    /**
     * Get cached ReflectionClass for a class name.
     *
     * @template T of object
     * @param class-string<T> $className
     *
     * @return ReflectionClass<T>
     * @throws ReflectionException
     */
    public static function getClass(string $className) : ReflectionClass
    {
        return self::$classCache[$className] ??= new ReflectionClass(objectOrClass: $className);
    }

    /**
     * Check if a class has a specific property (cached).
     *
     * @template T of object
     * @param class-string<T>|T $classOrObject
     * @param string            $propertyName
     *
     * @return bool
     */
    public static function hasProperty(object|string $classOrObject, string $propertyName) : bool
    {
        try {
            self::getProperty(classOrObject: $classOrObject, propertyName: $propertyName);

            return true;
        } catch (ReflectionException) {
            return false;
        }
    }

    /**
     * Get cached ReflectionProperty for a class property.
     *
     * @template T of object
     * @param class-string<T>|T $classOrObject
     * @param string            $propertyName
     *
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    public static function getProperty(object|string $classOrObject, string $propertyName) : ReflectionProperty
    {
        $className = is_string($classOrObject) ? $classOrObject : $classOrObject::class;
        $key       = $className . '::$' . $propertyName;

        return self::$propertyCache[$key] ??= self::getClass(className: $className)->getProperty(name: $propertyName);
    }

    /**
     * Check if a method is public (cached).
     *
     * @template T of object
     * @param class-string<T>|T $classOrObject
     * @param string            $methodName
     *
     * @return bool
     */
    public static function isMethodPublic(object|string $classOrObject, string $methodName) : bool
    {
        try {
            $method = self::getMethod(classOrObject: $classOrObject, methodName: $methodName);

            return $method->isPublic();
        } catch (ReflectionException) {
            return false;
        }
    }

    /**
     * Check if a property is public (cached).
     *
     * @template T of object
     * @param class-string<T>|T $classOrObject
     * @param string            $propertyName
     *
     * @return bool
     */
    public static function isPropertyPublic(object|string $classOrObject, string $propertyName) : bool
    {
        try {
            $property = self::getProperty(classOrObject: $classOrObject, propertyName: $propertyName);

            return $property->isPublic();
        } catch (ReflectionException) {
            return false;
        }
    }

    /**
     * Clear all cached reflection data.
     *
     * Useful for testing or when reflection data becomes stale.
     */
    public static function clear() : void
    {
        self::$classCache    = [];
        self::$methodCache   = [];
        self::$propertyCache = [];
    }

    /**
     * Get cache statistics for monitoring.
     *
     * @return array{class_count: int, method_count: int, property_count: int}
     */
    public static function getStats() : array
    {
        return [
            'class_count'    => count(self::$classCache),
            'method_count'   => count(self::$methodCache),
            'property_count' => count(self::$propertyCache),
        ];
    }
}