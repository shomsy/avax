<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Analyze;

use Avax\Container\Features\Core\Attribute\Inject;
use Avax\Container\Features\Core\Exceptions\ResolutionException;
use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Avax\Container\Features\Think\Model\PropertyPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use RuntimeException;

/**
 * The core static analysis engine for code structure discovery.
 *
 * This analyzer uses PHP Reflection to scan classes for dependency metadata. 
 * it is responsible for discovering:
 * 1. Constructor signatures (Autowiring sources)
 * 2. Annotated properties (via the #[Inject] attribute)
 * 3. Annotated setter methods (via the #[Inject] attribute)
 * 
 * It produces an immutable {@see ServicePrototype} which acts as the 
 * "Blueprint" for all subsequent container actions.
 *
 * @package Avax\Container\Features\Think\Analyze
 * @see docs/Features/Think/Analyze/PrototypeAnalyzer.md
 */
final readonly class PrototypeAnalyzer
{
    /**
     * Initializes the analyzer with its type-processing collaborator.
     *
     * @param ReflectionTypeAnalyzer $typeAnalyzer Helper for processing and validating reflection types.
     */
    public function __construct(
        private ReflectionTypeAnalyzer $typeAnalyzer
    ) {}

    /**
     * Get the underlying reflection helper used by this analyzer.
     *
     * @return ReflectionTypeAnalyzer
     * @see docs/Features/Think/Analyze/PrototypeAnalyzer.md#method-gettypeanalyzer
     */
    public function getTypeAnalyzer(): ReflectionTypeAnalyzer
    {
        return $this->typeAnalyzer;
    }

    /**
     * Performs full reflection analysis on a target class.
     *
     * @param string $class Fully qualified class name to analyze.
     * @return ServicePrototype The generated blueprint/prototype.
     * @throws RuntimeException If the class cannot be instantiated or doesn't exist.
     *
     * @see docs/Features/Think/Analyze/PrototypeAnalyzer.md#method-analyze
     */
    public function analyze(string $class): ServicePrototype
    {
        $reflection = $this->typeAnalyzer->reflectClass(className: $class);

        if (! $reflection->isInstantiable()) {
            throw new RuntimeException(message: "Cannot create prototype for non-instantiable class: {$class}");
        }

        return new ServicePrototype(
            class: $class,
            constructor: $this->analyzeConstructor(reflector: $reflection),
            injectedProperties: $this->analyzeProperties(reflector: $reflection),
            injectedMethods: $this->analyzeMethods(reflector: $reflection),
            isInstantiable: true
        );
    }

    /**
     * Extract the constructor signature into a method prototype.
     *
     * @param ReflectionClass $reflector The reflection instance for the class.
     * @return MethodPrototype|null The constructor prototype, or null if no constructor exists.
     *
     * @see docs/Features/Think/Analyze/PrototypeAnalyzer.md#method-analyzeconstructor
     */
    public function analyzeConstructor(ReflectionClass $reflector): MethodPrototype|null
    {
        $constructor = $reflector->getConstructor();

        return $constructor ? $this->buildMethodPrototype(method: $constructor) : null;
    }

    /**
     * Discover and analyze properties marked with the #[Inject] attribute.
     *
     * @param ReflectionClass $reflector The reflection instance for the class.
     * @return PropertyPrototype[] A map of property names to their injection prototypes.
     * @throws ResolutionException If an #[Inject] attribute is used on a property without a resolvable type.
     *
     * @see docs/Features/Think/Analyze/PrototypeAnalyzer.md#method-analyzeproperties
     */
    public function analyzeProperties(ReflectionClass $reflector): array
    {
        $prototypes = [];
        foreach ($reflector->getProperties() as $property) {
            // Readonly properties cannot be injected after construction
            if ($property->isReadOnly()) {
                continue;
            }

            $attribute = $property->getAttributes(name: Inject::class)[0] ?? null;
            if (! $attribute) {
                continue;
            }

            $serviceId = $attribute->newInstance()->abstract;
            $type      = $property->getType();

            // If no explicit ID is in #[Inject(id)], fall back to the native type-hint
            if (! $serviceId) {
                $serviceId = $this->resolveType(type: $type);
            }

            if (! $serviceId) {
                throw new ResolutionException(
                    message: "Property [{$property->getName()}] in [{$reflector->getName()}] has #[Inject] but no resolvable type."
                );
            }

            $prototypes[$property->getName()] = new PropertyPrototype(
                name: $property->getName(),
                type: $serviceId,
                hasDefault: $property->hasDefaultValue(),
                default: $property->hasDefaultValue() ? $property->getDefaultValue() : null,
                allowsNull: ! $type || $type->allowsNull(),
                required: ! $property->hasDefaultValue() && (! $type || ! $type->allowsNull())
            );
        }

        return $prototypes;
    }

    /**
     * Discover and analyze methods (other than the constructor) marked with #[Inject].
     *
     * @param ReflectionClass $reflector The reflection instance for the class.
     * @return MethodPrototype[] A list of method prototypes for setter injection.
     *
     * @see docs/Features/Think/Analyze/PrototypeAnalyzer.md#method-analyzemethods
     */
    public function analyzeMethods(ReflectionClass $reflector): array
    {
        $prototypes = [];
        foreach ($reflector->getMethods() as $method) {
            if ($method->isConstructor()) {
                continue;
            }

            if (! empty($method->getAttributes(name: Inject::class))) {
                $prototypes[] = $this->buildMethodPrototype(method: $method);
            }
        }

        return $prototypes;
    }

    /**
     * Transform a reflected parameter into a core-compatible prototype.
     *
     * @param ReflectionParameter $param The parameter reflection.
     * @return ParameterPrototype The generated parameter blueprint.
     *
     * @see docs/Features/Think/Analyze/PrototypeAnalyzer.md#method-analyzeparameter
     */
    public function analyzeParameter(ReflectionParameter $param): ParameterPrototype
    {
        return new ParameterPrototype(
            name: $param->getName(),
            type: $this->resolveType(type: $param->getType()),
            hasDefault: $param->isDefaultValueAvailable(),
            default: $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
            isVariadic: $param->isVariadic(),
            allowsNull: $param->allowsNull(),
            required: ! $param->isDefaultValueAvailable() && ! $param->allowsNull()
        );
    }

    /**
     * Internal factory for building method prototypes from reflection.
     *
     * @param ReflectionMethod $method The method reflector.
     * @return MethodPrototype The generated blueprint.
     */
    private function buildMethodPrototype(ReflectionMethod $method): MethodPrototype
    {
        $params = [];
        foreach ($method->getParameters() as $param) {
            $params[] = $this->analyzeParameter(param: $param);
        }

        return new MethodPrototype(name: $method->getName(), parameters: $params);
    }

    /**
     * Normalizes complex reflection types into simple string service IDs.
     *
     * @param ReflectionType|null $type The type reflection.
     * @return string|null The class name/ID, or null for scalar/builtin types.
     */
    private function resolveType(ReflectionType|null $type): string|null
    {
        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            return $type->getName();
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $subType) {
                if ($subType instanceof ReflectionNamedType && ! $subType->isBuiltin()) {
                    return $subType->getName();
                }
            }
        }

        return null;
    }
}
