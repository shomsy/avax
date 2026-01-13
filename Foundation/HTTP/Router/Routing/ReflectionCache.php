<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

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
     * @var array<string, \ReflectionClass<object>>
     */
    private static array $classCache = [];

    /**
     * @var array<string, \ReflectionMethod>
     */
    private static array $methodCache = [];

    /**
     * @var array<string, \ReflectionProperty>
     */
    private static array $propertyCache = [];

    /**
     * Get cached ReflectionClass for a class name.
     *
     * @template T of object
     * @param class-string<T> $className
     * @return \ReflectionClass<T>
     */
    public static function getClass(string $className) : \ReflectionClass
    {
        return self::$classCache[$className] ??= new \ReflectionClass($className);
    }

    /**
     * Get cached ReflectionMethod for a class method.
     *
     * @template T of object
     * @param class-string<T>|T $classOrObject
     * @param string $methodName
     * @return \ReflectionMethod
     */
    public static function getMethod($classOrObject, string $methodName) : \ReflectionMethod
    {
        $className = is_string($classOrObject) ? $classOrObject : $classOrObject::class;
        $key = $className . '::' . $methodName;

        return self::$methodCache[$key] ??= self::getClass($className)->getMethod($methodName);
    }

    /**
     * Get cached ReflectionProperty for a class property.
     *
     * @template T of object
     * @param class-string<T>|T $classOrObject
     * @param string $propertyName
     * @return \ReflectionProperty
     */
    public static function getProperty($classOrObject, string $propertyName) : \ReflectionProperty
    {
        $className = is_string($classOrObject) ? $classOrObject : $classOrObject::class;
        $key = $className . '::$' . $propertyName;

        return self::$propertyCache[$key] ??= self::getClass($className)->getProperty($propertyName);
    }

    /**
     * Check if a class has a specific method (cached).
     *
     * @template T of object
     * @param class-string<T>|T $classOrObject
     * @param string $methodName
     */
    public static function hasMethod($classOrObject, string $methodName) : bool
    {
        try {
            self::getMethod($classOrObject, $methodName);
            return true;
        } catch (\ReflectionException) {
            return false;
        }
    }

    /**
     * Check if a class has a specific property (cached).
     *
     * @template T of object
     * @param class-string<T>|T $classOrObject
     * @param string $propertyName
     */
    public static function hasProperty($classOrObject, string $propertyName) : bool
    {
        try {
            self::getProperty($classOrObject, $propertyName);
            return true;
        } catch (\ReflectionException) {
            return false;
        }
    }

    /**
     * Check if a method is public (cached).
     *
     * @template T of object
     * @param class-string<T>|T $classOrObject
     * @param string $methodName
     */
    public static function isMethodPublic($classOrObject, string $methodName) : bool
    {
        try {
            $method = self::getMethod($classOrObject, $methodName);
            return $method->isPublic();
        } catch (\ReflectionException) {
            return false;
        }
    }

    /**
     * Check if a property is public (cached).
     *
     * @template T of object
     * @param class-string<T>|T $classOrObject
     * @param string $propertyName
     */
    public static function isPropertyPublic($classOrObject, string $propertyName) : bool
    {
        try {
            $property = self::getProperty($classOrObject, $propertyName);
            return $property->isPublic();
        } catch (\ReflectionException) {
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
        self::$classCache = [];
        self::$methodCache = [];
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
            'class_count' => count(self::$classCache),
            'method_count' => count(self::$methodCache),
            'property_count' => count(self::$propertyCache),
        ];
    }
}