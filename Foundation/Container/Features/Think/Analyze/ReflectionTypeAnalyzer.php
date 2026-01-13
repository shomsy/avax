<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Analyze;

use Avax\Container\Features\Core\Attribute\Inject;
use Avax\Container\Features\Core\Exceptions\ResolutionException;
use Avax\Container\Features\Think\Prototype\ServicePrototypeBuilder;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

/**
 * The "Swiss Army Knife" for low-level PHP type discovery and validation.
 *
 * ReflectionTypeAnalyzer provides a unified, cached interface for all expensive
 * reflection operations. It centralizes the complexity of handling modern PHP
 * types (Union types, Intersection types, Enums) and provides clear answers
 * to questions like "Can the container resolve this?" or "What is the
 * string name of this complex type-hint?".
 *
 * @see     docs/Features/Think/Analyze/ReflectionTypeAnalyzer.md
 * @see     ServicePrototypeBuilder For usage in the prototype pipeline.
 */
final class ReflectionTypeAnalyzer
{
    /**
     * Initializes the analyzer with an optional pre-populated reflection cache.
     *
     * @param ReflectionClass[] $reflectionCache Internal cache for already reflected classes.
     */
    public function __construct(
        private array $reflectionCache = []
    ) {}

    /**
     * Determine if a class can be physically instantiated.
     *
     * @param string $className Fully qualified class name.
     *
     * @return bool True if the class is not abstract, not an interface, and has an accessible constructor.
     *
     * @see docs/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-isinstantiable
     */
    public function isInstantiable(string $className) : bool
    {
        try {
            $reflection = $this->reflectClass(className: $className);

            return $reflection->isInstantiable();
        } catch (ResolutionException) {
            return false;
        }
    }

    /**
     * Retrieve a cached reflection object for the given class name.
     *
     * @param string $className Fully qualified class name.
     *
     * @return ReflectionClass The generated reflection object.
     *
     * @throws ResolutionException If the class does not exist or cannot be reflected.
     *
     * @see docs/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-reflectclass
     */
    public function reflectClass(string $className) : ReflectionClass
    {
        if (! isset($this->reflectionCache[$className])) {
            try {
                if (! class_exists(class: $className) && ! interface_exists(interface: $className) && ! trait_exists(trait: $className)) {
                    throw new ResolutionException(message: "Class [{$className}] not found.");
                }
                $this->reflectionCache[$className] = new ReflectionClass(objectOrClass: $className);
            } catch (ReflectionException $e) {
                throw new ResolutionException(
                    message : "Cannot reflect class [{$className}]: " . $e->getMessage(),
                    previous: $e
                );
            }
        }

        return $this->reflectionCache[$className];
    }

    /**
     * Retrieve the reflected constructor for a class.
     *
     * @param string $className Fully qualified class name.
     *
     * @return ReflectionMethod|null The constructor reflector, or null if none exists.
     *
     * @see docs/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-getconstructor
     */
    public function getConstructor(string $className) : ReflectionMethod|null
    {
        try {
            $reflection = $this->reflectClass(className: $className);

            return $reflection->getConstructor();
        } catch (ResolutionException) {
            return null;
        }
    }

    /**
     * Advanced discovery of properties marked for dependency injection.
     *
     * @param string $className The class to scan.
     *
     * @return array<int, array<string, mixed>> List of descriptive property metadata.
     *
     * @throws ResolutionException
     *
     * @see docs/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-getinjectableproperties
     */
    public function getInjectableProperties(string $className) : array
    {
        $properties = [];
        $reflection = $this->reflectClass(className: $className);

        foreach ($reflection->getProperties() as $property) {
            $injectAttributes = $this->findInjectAttributes(reflection: $property);

            if (! empty($injectAttributes)) {
                $properties[] = [
                    'name'          => $property->getName(),
                    'type'          => $this->getPropertyType(property: $property),
                    'allows_null'   => $this->propertyAllowsNull(property: $property),
                    'has_default'   => $property->hasDefaultValue(),
                    'default_value' => $property->hasDefaultValue() ? $property->getDefaultValue() : null,
                    'visibility'    => $this->getVisibility(reflection: $property),
                    'attributes'    => $injectAttributes,
                ];
            }
        }

        return $properties;
    }

    /**
     * Internal utility for finding the #[Inject] attribute on code elements.
     *
     * @param ReflectionProperty|ReflectionMethod $reflection The element to scan.
     *
     * @return array<int, array<string, mixed>> Map of attribute names and their arguments.
     */
    private function findInjectAttributes(ReflectionProperty|ReflectionMethod $reflection) : array
    {
        $attributes = [];

        foreach ($reflection->getAttributes() as $attribute) {
            $attributeName = $attribute->getName();

            // Check for explicit #[Inject] attribute
            if ($attributeName === Inject::class || $attributeName === 'Avax\Container\Features\Core\Attribute\Inject') {
                $attributes[] = [
                    'name'      => $attributeName,
                    'arguments' => $attribute->getArguments(),
                ];
            }
        }

        return $attributes;
    }

    /**
     * Resolves the string type name for a property.
     */
    private function getPropertyType(ReflectionProperty $property) : string|null
    {
        $type = $property->getType();

        return $type ? $this->formatType(type: $type) : null;
    }

    /**
     * Recursively transforms complex reflection types into normalized strings.
     *
     * Handles:
     * - Named Types (`User`)
     * - Union Types (`A|B`)
     * - Intersection Types (`A&B`)
     * - Nullability (`?User`)
     *
     * @param ReflectionType $type The native reflection type.
     *
     * @return string Normalized type string.
     */
    public function formatType(ReflectionType $type) : string
    {
        if ($type instanceof ReflectionUnionType) {
            return implode('|', array_map(callback: fn($t) => $this->formatType(type: $t), array: $type->getTypes()));
        }

        if ($type instanceof ReflectionIntersectionType) {
            return implode('&', array_map(callback: fn($t) => $this->formatType(type: $t), array: $type->getTypes()));
        }

        if ($type instanceof ReflectionNamedType) {
            $typeName = $type->getName();

            return $type->allowsNull() ? "?{$typeName}" : $typeName;
        }

        return (string) $type;
    }

    /**
     * Checks if a property's type-hint allows null values.
     */
    private function propertyAllowsNull(ReflectionProperty $property) : bool
    {
        $type = $property->getType();

        return (bool) $type?->allowsNull();
    }

    /**
     * Resolves the visibility level for a code element.
     *
     *
     * @return string 'public', 'protected', or 'private'.
     */
    private function getVisibility(ReflectionProperty|ReflectionMethod $reflection) : string
    {
        if ($reflection->isPublic()) {
            return 'public';
        }

        if ($reflection->isProtected()) {
            return 'protected';
        }

        return 'private';
    }

    /**
     * Advanced discovery of methods marked for dependency injection (Setters).
     *
     * @param string $className The class to scan.
     *
     * @return array<int, array<string, mixed>> List of descriptive method metadata.
     *
     * @throws ResolutionException
     *
     * @see docs/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-getinjectablemethods
     */
    public function getInjectableMethods(string $className) : array
    {
        $methods    = [];
        $reflection = $this->reflectClass(className: $className);

        foreach ($reflection->getMethods() as $method) {
            $injectAttributes = $this->findInjectAttributes(reflection: $method);

            if (! empty($injectAttributes)) {
                $methods[] = [
                    'name'       => $method->getName(),
                    'parameters' => $this->analyzeMethodParameters(method: $method),
                    'visibility' => $this->getVisibility(reflection: $method),
                    'attributes' => $injectAttributes,
                ];
            }
        }

        return $methods;
    }

    /**
     * Decomposes a method's parameters into descriptive metadata arrays.
     *
     *
     * @return array<int, array<string, mixed>>
     *
     * @see docs/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-analyzemethodparameters
     */
    public function analyzeMethodParameters(ReflectionMethod $method) : array
    {
        $parameters = [];

        foreach ($method->getParameters() as $parameter) {
            $parameters[] = [
                'name'          => $parameter->getName(),
                'type'          => $this->getParameterType(parameter: $parameter),
                'allows_null'   => $parameter->allowsNull(),
                'has_default'   => $parameter->isDefaultValueAvailable(),
                'default_value' => $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
                'is_variadic'   => $parameter->isVariadic(),
                'position'      => $parameter->getPosition(),
            ];
        }

        return $parameters;
    }

    /**
     * Resolves the type name for a parameter.
     */
    private function getParameterType(ReflectionParameter $parameter) : string|null
    {
        $type = $parameter->getType();

        return $type ? $this->formatType(type: $type) : null;
    }

    /**
     * Utility: Determine if a type string refers to a resolvable container service.
     *
     * @param string|null $type Fully qualified name to check.
     *
     * @return bool True if the type represents a class, interface, or enum.
     *
     * @see docs/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-canresolvetype
     */
    public function canResolveType(string|null $type) : bool
    {
        if ($type === null || $type === '') {
            return false;
        }

        return class_exists(class: $type) || interface_exists(interface: $type) || (function_exists(function: 'enum_exists') && enum_exists(enum: $type));
    }
}
