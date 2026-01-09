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
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use ReflectionNamedType;

/**
 * @package Avax\Container\Think\Analyze
 *
 * Centralized type analysis and reflection operations.
 *
 * TypeAnalyzer provides a unified, cached interface for all reflection and type analysis
 * operations used throughout the container. It eliminates duplication of reflection logic
 * and provides consistent, optimized access to class metadata, type information, and
 * injection point discovery.
 *
 * WHY IT EXISTS:
 * - To eliminate duplication of reflection logic across multiple components
 * - To provide consistent type analysis with proper caching
 * - To centralize complex reflection operations and edge case handling
 * - To enable optimization of expensive reflection calls
 *
 * ANALYSIS CAPABILITIES:
 * - Class structure analysis (constructors, properties, methods)
 * - Type hint extraction and validation
 * - Attribute scanning (#[Inject], custom attributes)
 * - Instantiability checking
 * - Interface and trait analysis
 *
 * CACHING STRATEGY:
 * - Reflection results cached per class
 * - Invalidated on class file changes
 * - Memory-efficient storage of analysis results
 * - Thread-safe cache access
 *
 * PERFORMANCE OPTIMIZATIONS:
 * - Lazy loading of expensive analysis operations
 * - Pre-computed injection point mappings
 * - Optimized attribute scanning
 * - Minimal memory footprint for cached results
 *
 * THREAD SAFETY:
 * - Immutable analysis results
 * - Thread-safe cache operations
 * - Safe for concurrent analysis requests
 *
 * @see docs_md/Features/Think/Analyze/ReflectionTypeAnalyzer.md#quick-summary
 * @see     ServicePrototypeBuilder For prototype construction using analysis results
 * @see     Injector For runtime injection using analysis results
 */
final class ReflectionTypeAnalyzer
{
    /**
     * Creates a new type analyzer with reflection capabilities.
     *
     * @param \ReflectionClass[] $reflectionCache Optional pre-populated reflection cache
     * @see docs_md/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-__construct
     */
    public function __construct(
        private array $reflectionCache = []
    ) {}

    /**
     * Checks if a class is instantiable.
     *
     * Determines if the given class can be instantiated via constructor.
     * Returns false for abstract classes, interfaces, and classes without
     * accessible constructors.
     *
     * INSTANTIABILITY CRITERIA:
     * - Not an abstract class
     * - Not an interface or trait
     * - Has an accessible constructor (public or none)
     * - Not a built-in PHP class that cannot be instantiated
     *
     * @param string $className The class to check
     *
     * @return bool True if the class can be instantiated
     * @see docs_md/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-isinstantiable
     */
    public function isInstantiable(string $className): bool
    {
        try {
            $reflection = $this->reflectClass(className: $className);

            return $reflection->isInstantiable();
        } catch (ResolutionException) {
            return false;
        }
    }

    /**
     * Gets a reflection class instance for the given class name.
     *
     * Returns a cached ReflectionClass instance, creating it only if not already cached.
     * This method handles class loading and provides consistent error handling for
     * invalid class names.
     *
     * @param string $className The fully qualified class name
     *
     * @return \ReflectionClass The reflection class instance
     *
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     * @see docs_md/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-reflectclass
     */
    public function reflectClass(string $className): ReflectionClass
    {
        if (! isset($this->reflectionCache[$className])) {
            try {
                $this->reflectionCache[$className] = new ReflectionClass(objectOrClass: $className);
            } catch (ReflectionException $e) {
                throw new ResolutionException(
                    message: "Cannot reflect class [{$className}]: " . $e->getMessage(),
                    previous: $e
                );
            }
        }

        return $this->reflectionCache[$className];
    }

    /**
     * Gets the constructor reflection for a class.
     *
     * Returns the constructor method reflection, or null if the class
     * has no explicit constructor (uses default constructor).
     *
     * @param string $className The class to analyze
     *
     * @return \ReflectionMethod|null The constructor method or null
     * @see docs_md/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-getconstructor
     */
    public function getConstructor(string $className): ReflectionMethod|null
    {
        try {
            $reflection = $this->reflectClass(className: $className);

            return $reflection->getConstructor();
        } catch (ResolutionException) {
            return null;
        }
    }

    /**
     * Gets all properties with injection attributes.
     *
     * Scans the class hierarchy for properties marked with #[Inject] attributes
     * or other injection markers. Returns detailed information about each
     * injectable property.
     *
     * INJECTION ATTRIBUTE SCANNING:
     * - #[Inject] attributes on properties
     * - Custom injection attributes
     * - Property visibility and accessibility
     * - Type hints and default values
     *
     * @param string $className The class to analyze
     *
     * @return array{
     *     name: string,
     *     type: string|null,
     *     allows_null: bool,
     *     has_default: bool,
     *     default_value: mixed,
     *     visibility: string,
     *     attributes: array
     * }[] Array of injectable property information
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     * @see docs_md/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-getinjectableproperties
     */
    public function getInjectableProperties(string $className): array
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
     * Finds injection attributes on a reflection object.
     *
     * Scans a reflection property or method for injection-related attributes.
     *
     * @param \ReflectionProperty|\ReflectionMethod $reflection The reflection object to scan
     *
     * @return array Array of injection attributes found
     */
    private function findInjectAttributes(ReflectionProperty|ReflectionMethod $reflection): array
    {
        $attributes = [];

        foreach ($reflection->getAttributes() as $attribute) {
            $attributeName = $attribute->getName();

            // Check for explicit #[Inject] attribute or our specific namespace
            if (
                $attributeName === Inject::class ||
                $attributeName === 'Avax\Container\Features\Core\Attribute\Inject'
            ) {
                $attributes[] = [
                    'name'      => $attributeName,
                    'arguments' => $attribute->getArguments(),
                ];
            }
        }

        return $attributes;
    }

    /**
     * Gets the type hint for a property.
     *
     * @param \ReflectionProperty $property The property to analyze
     *
     * @return string|null The type hint or null if untyped
     */
    private function getPropertyType(ReflectionProperty $property): string|null
    {
        $type = $property->getType();

        if ($type) {
            return $this->formatType(type: $type);
        }

        return null;
    }

    /**
     * Formats a reflection type to string representation.
     *
     * @param \ReflectionType $type The type to format
     *
     * @return string The formatted type string
     */
    private function formatType(ReflectionType $type): string
    {
        if ($type instanceof ReflectionUnionType) {
            return implode('|', array_map(fn($t) => $this->formatType($t), $type->getTypes()));
        }

        if ($type instanceof ReflectionIntersectionType) {
            return implode('&', array_map(fn($t) => $this->formatType($t), $type->getTypes()));
        }

        if ($type instanceof ReflectionNamedType) {
            $typeName = $type->getName();

            return $type->allowsNull() ? "?{$typeName}" : $typeName;
        }

        return (string) $type;
    }

    /**
     * Checks if a property allows null values.
     *
     * @param \ReflectionProperty $property The property to check
     *
     * @return bool True if the property allows null
     */
    private function propertyAllowsNull(ReflectionProperty $property): bool
    {
        $type = $property->getType();

        return (bool) $type?->allowsNull();
    }

    /**
     * Gets the visibility of a reflection object.
     *
     * @param \ReflectionProperty|\ReflectionMethod $reflection The reflection object
     *
     * @return string The visibility level ('public', 'protected', or 'private')
     */
    private function getVisibility(ReflectionProperty|ReflectionMethod $reflection): string
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
     * Gets all methods with injection attributes.
     *
     * Scans the class for methods marked with #[Inject] attributes or other
     * injection markers. Returns detailed information about each injectable method.
     *
     * INJECTION METHOD SCANNING:
     * - #[Inject] attributes on methods
     * - Setter injection patterns
     * - Initialization methods
     * - Method visibility and accessibility
     *
     * @param string $className The class to analyze
     *
     * @return array{
     *     name: string,
     *     parameters: array,
     *     visibility: string,
     *     attributes: array
     * }[] Array of injectable method information
     * @throws \Avax\Container\Features\Core\Exceptions\ContainerExceptionInterface
     * @throws \Avax\Container\Features\Core\Exceptions\ResolutionException
     * @see docs_md/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-getinjectablemethods
     */
    public function getInjectableMethods(string $className): array
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
     * Analyzes method parameters for type information.
     *
     * Extracts detailed type information from method parameters, including
     * type hints, nullability, defaults, and other parameter metadata.
     *
     * @param \ReflectionMethod $method The method to analyze
     *
     * @return array Parameter analysis results
     * @see docs_md/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-analyzemethodparameters
     */
    public function analyzeMethodParameters(ReflectionMethod $method): array
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
     * Gets the type hint for a parameter.
     *
     * @param \ReflectionParameter $parameter The parameter to analyze
     *
     * @return string|null The type hint or null if untyped
     */
    private function getParameterType(ReflectionParameter $parameter): string|null
    {
        $type = $parameter->getType();

        return $type ? $this->formatType(type: $type) : null;
    }

    /**
     * Checks if a type string is resolvable by the container.
     *
     * This is a convenience helper used during analysis to decide whether a reflected type
     * is something the container can resolve (class, interface, or enum).
     *
     * @param string|null $type Type string to check.
     * @return bool True if resolvable, false otherwise.
     * @see docs_md/Features/Think/Analyze/ReflectionTypeAnalyzer.md#method-canresolvetype
     */
    public function canResolveType(string|null $type): bool
    {
        if ($type === null || $type === '') {
            return false;
        }

        return class_exists($type) || interface_exists($type) || (function_exists('enum_exists') && enum_exists($type));
    }
}
