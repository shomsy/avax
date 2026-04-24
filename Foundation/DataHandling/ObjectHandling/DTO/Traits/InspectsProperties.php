<?php

declare(strict_types=1);

namespace Gemini\DataHandling\ObjectHandling\DTO\Traits;

use Gemini\DataHandling\ObjectHandling\DTO\Support\PropertyMetadata;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Trait InspectsProperties
 *
 * Provides a set of metadata reflection utilities for Data Transfer Objects (DTOs),
 * enabling efficient and consistent introspection of public properties.
 *
 * Key features:
 * - Caches reflection metadata per class for optimized performance.
 * - Supplies structured metadata constructs (`PropertyMetadata`), making them reusable across the application.
 * - Supports property-level logic that promotes composition, testability, and maintainability.
 */
trait InspectsProperties
{
    /**
     * @var array<class-string, PropertyMetadata[]> Stores cached metadata for each class.
     *                                              This cache prevents redundant reflection calls, thereby improving
     *                                              performance.
     */
    private static array $metadataCache = [];

    /**
     * Queries and retrieves metadata for a specific property of the DTO by its name.
     *
     * @param string $name The name of the property for which metadata is being retrieved.
     *
     * @return PropertyMetadata|null The structured metadata for the given property, or `null`
     *                               if the property does not exist or is inaccessible.
     *
     * @throws ReflectionException If there are issues during reflection (e.g., invalid class or property access).
     */
    protected function reflectField(string $name) : PropertyMetadata|null
    {
        foreach ($this->reflectPublicFields() as $metadata) {
            if ($metadata->name === $name) {
                return $metadata;
            }
        }

        return null;
    }

    /**
     * Retrieves metadata for all publicly accessible properties of the current DTO class.
     *
     * @return PropertyMetadata[] An array of `PropertyMetadata` instances, representing all metadata
     *                            for the public properties of the DTO.
     *
     * @throws ReflectionException If reflection fails during metadata construction.
     */
    protected function reflectPublicFields() : array
    {
        $class = static::class;

        return self::$metadataCache[$class] ??= $this->buildMetadataFor(class: $class);
    }

    /**
     * Dynamically builds and caches property metadata for a given class.
     *
     * This provides efficient inspection of all public properties and ensures a reusable
     * metadata structure for future operations like validation or serialization.
     *
     * @param class-string $class The fully qualified name of the class whose properties will be inspected.
     * @return PropertyMetadata[] An array of `PropertyMetadata` objects, one for each public property of the class.
     * @throws ReflectionException If the class cannot be reflected upon (e.g., invalid class name).
     */
    private function buildMetadataFor(string $class) : array
    {
        $reflection = new ReflectionClass(objectOrClass: $class);

        return array_map(
            static fn(ReflectionProperty $property) : PropertyMetadata => new PropertyMetadata(
                name      : $property->getName(),
                property  : $property,
                attributes: $property->getAttributes()
            ),
            $reflection->getProperties(filter: ReflectionProperty::IS_PUBLIC)
        );
    }

    /**
     * Determines whether a given property allows null values.
     *
     * This function is useful for validation or type safety checks where
     * nullable types impact business logic.
     *
     * @param ReflectionProperty $property The property to evaluate.
     * @return bool Returns `true` if the property allows null values; otherwise, `false`.
     */
    protected function isPropertyNullable(ReflectionProperty $property) : bool
    {
        $type = $property->getType();

        return $type?->allowsNull() ?? true;
    }

    /**
     * Checks if a specific attribute has been applied to a property.
     *
     * This method supports reflection-based checks for attributes, enabling flexible configuration
     * and behavior customization driven by annotations or metadata.
     *
     * @param ReflectionProperty $property     The property to check for attributes.
     * @param class-string       $attributeFqn The fully qualified name of the attribute class to look for.
     * @return bool `true` if the property has been annotated with the given attribute; `false` otherwise.
     */
    protected function hasAttribute(ReflectionProperty $property, string $attributeFqn) : bool
    {
        return ! empty($property->getAttributes(name: $attributeFqn));
    }

    /**
     * Retrieves the first resolved attribute instance of a given type on a property.
     *
     * @param ReflectionProperty $property     The property to inspect. Represents a class property.
     * @param class-string       $attributeFqn Fully qualified class name (FQN) of the attribute.
     * @return object|null The resolved attribute instance, or null if the attribute is not present on the property.
     */
    protected function getAttribute(ReflectionProperty $property, string $attributeFqn) : ?object
    {
        $attributes = $property->getAttributes(name: $attributeFqn);

        if (empty($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * Checks if the property has at least one attribute from a given list.
     *
     * Designed to optimize lookups when validating if a property contains any of several related attributes.
     *
     * @param ReflectionProperty $property The property to inspect. Provides introspection features
     *                                     for examining attribute metadata and existence.
     * @param string[]           $fqns     A list of fully qualified attribute class names (FQNs) to check against.
     * @return bool True if at least one of the specified attributes is defined on the property,
     *              otherwise false.
     */
    protected function hasAnyAttribute(ReflectionProperty $property, array $fqns) : bool
    {
        foreach ($fqns as $attributeFqn) {
            if (! empty($property->getAttributes(name: $attributeFqn))) {
                return true;
            }
        }

        return false;
    }
}
