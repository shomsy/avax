<?php

declare(strict_types=1);

namespace Avax\Container\Attributes;

use Avax\Container\Containers\DependencyInjector;
use Avax\Container\Exceptions\InvalidInjectionException;
use ReflectionClass;
use ReflectionProperty;

/**
 * AttributeInjector is responsible for resolving and injecting dependencies
 * marked with the #[Inject] attribute into the properties of an object at runtime.
 * This class leverages constructor promotion for clean and expressive instantiation.
 *
 * The class ensures:
 * - Only properties with #[Inject] are processed.
 * - Properties without a proper type or with builtin types are invalid for injection.
 * - Dependencies are fetched from the provided DependencyInjector container.
 */
final readonly class AttributeInjector
{
    /**
     * Constructor for AttributeInjector.
     *
     * @param DependencyInjector $container The dependency injection container responsible for
     *                                      resolving instances required by the object properties.
     */
    public function __construct(private DependencyInjector $container) {}

    /**
     * Traverses and injects dependencies into the properties of the given object
     * that are marked with the #[Inject] attribute.
     *
     * @template T of object
     *
     * @param object $object The target object whose injectable properties need to be processed.
     *
     * @return object The same object instance with dependencies injected into its properties.
     *
     * @throws InvalidInjectionException If the type of a property to be injected is missing or invalid.
     */
    public function inject(object $object) : object
    {
        // Create a reflection of the given object to analyze its properties and metadata.
        $reflection = new ReflectionClass(objectOrClass: $object);

        // Iterate over all properties of the object.
        foreach ($reflection->getProperties() as $property) {
            // Process and attempt to inject dependency for the current property.
            $this->processProperty(object: $object, property: $property);
        }

        // Return the object after injection processing.
        return $object;
    }

    /**
     * Inspects and injects a property of the given object if it is marked with #[Inject].
     *
     * This method ensures:
     * - Properties that are already initialized are skipped.
     * - Only properties with #[Inject] are processed.
     * - A valid dependency type is required for successful resolution.
     *
     * @param object             $object   The target object that owns the property to be injected.
     * @param ReflectionProperty $property The reflection instance of the property to analyze and inject.
     *
     * @throws InvalidInjectionException If the property lacks a valid type hint or its type is builtin.
     */
    private function processProperty(object $object, ReflectionProperty $property) : void
    {
        // Skip properties that are already initialized to avoid overriding existing values.
        if ($property->isInitialized(object: $object)) {
            return;
        }

        // Retrieve attributes of the property, specifically looking for #[Inject].
        $attributes = $property->getAttributes(name: Inject::class);

        // If the #[Inject] attribute isn't present, skip further processing for this property.
        if (empty($attributes)) {
            return;
        }

        // Retrieve the type of the property to validate its suitability for injection.
        $type = $property->getType();

        // Ensure the property has a valid type and is not a built-in PHP type.
        // Injection is only applicable for custom or class types.
        if (! $type || $type->isBuiltin()) {
            throw new InvalidInjectionException(
                property: $property,
                message : "Cannot inject property '{$property->getName()}': Missing or invalid type hint."
            );
        }

        // Resolve the dependency instance using the container based on the property’s type name.
        $dependency = $this->container->get(id: $type->getName());

        // Enable modification of the property value, even if it is private or protected.
        /** @noinspection PhpExpressionResultUnusedInspection */
        $property->setAccessible(accessible: true);

        // Inject the resolved dependency into the property's value.
        $property->setValue(objectOrValue: $object, value: $dependency);
    }
}