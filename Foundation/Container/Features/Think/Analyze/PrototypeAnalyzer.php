<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Analyze;

use Avax\Container\Features\Core\Attribute\Inject;
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
 * PrototypeAnalyzer - Pure reflection analysis logic.
 *
 * This class is responsible for the CPU-intensive task of inspecting a class
 * via Reflection and building a ServicePrototype model. It is stateless
 * and intended primarily for the "Think" (Build) phase.
 *
 * @see docs_md/Features/Think/Analyze/PrototypeAnalyzer.md#quick-summary
 */
final readonly class PrototypeAnalyzer
{
    public function __construct(
        private ReflectionTypeAnalyzer $typeAnalyzer
    ) {}

    /**
     * Get the underlying reflection helper used by this analyzer.
     *
     * @return ReflectionTypeAnalyzer
     * @see docs_md/Features/Think/Analyze/PrototypeAnalyzer.md#method-gettypeanalyzer
     */
    public function getTypeAnalyzer(): ReflectionTypeAnalyzer
    {
        return $this->typeAnalyzer;
    }

    /**
     * Performs full reflection analysis on a class.
     *
     * @param string $class Fully qualified class name to analyze.
     * @return ServicePrototype Injection blueprint for the class.
     * @throws RuntimeException If the class is not instantiable or an injection point cannot be resolved.
     * @see docs_md/Features/Think/Analyze/PrototypeAnalyzer.md#method-analyze
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

    // --- Granular Analysis Methods ---

    /**
     * Analyze the class constructor (if present) into a MethodPrototype.
     *
     * @param ReflectionClass $reflector Reflected class.
     * @return MethodPrototype|null Constructor blueprint or null when no constructor exists.
     * @see docs_md/Features/Think/Analyze/PrototypeAnalyzer.md#method-analyzeconstructor
     */
    public function analyzeConstructor(ReflectionClass $reflector): MethodPrototype|null
    {
        $constructor = $reflector->getConstructor();

        return $constructor ? $this->buildMethodPrototype(method: $constructor) : null;
    }

    /**
     * Analyze injectable properties (marked with #[Inject]) into PropertyPrototype entries.
     *
     * @param ReflectionClass $reflector Reflected class.
     * @return array<string, PropertyPrototype> Map of property name to prototype.
     * @throws RuntimeException If an injectable property has no resolvable type/service id.
     * @see docs_md/Features/Think/Analyze/PrototypeAnalyzer.md#method-analyzeproperties
     */
    public function analyzeProperties(ReflectionClass $reflector): array
    {
        $prototypes = [];
        foreach ($reflector->getProperties() as $property) {
            if ($property->isReadOnly()) {
                continue;
            }

            $attribute = $property->getAttributes(name: Inject::class)[0] ?? null;
            if (! $attribute) {
                continue;
            }

            $serviceId = $attribute->newInstance()->abstract;
            $type      = $property->getType();

            if (! $serviceId) {
                $serviceId = $this->resolveType(type: $type);
            }

            if (! $serviceId) {
                throw new RuntimeException(message: "Property [{$property->getName()}] in [{$reflector->getName()}] has #[Inject] but no resolvable type.");
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
     * Analyze injectable methods (marked with #[Inject]) into MethodPrototype entries.
     *
     * @param ReflectionClass $reflector Reflected class.
     * @return MethodPrototype[] List of method injection prototypes.
     * @see docs_md/Features/Think/Analyze/PrototypeAnalyzer.md#method-analyzemethods
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
     * Convert a reflected parameter into a ParameterPrototype.
     *
     * @param ReflectionParameter $param Reflected parameter.
     * @return ParameterPrototype Parameter resolution blueprint.
     * @see docs_md/Features/Think/Analyze/PrototypeAnalyzer.md#method-analyzeparameter
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

    // --- Internal Helpers ---

    private function buildMethodPrototype(ReflectionMethod $method): MethodPrototype
    {
        $params = [];
        foreach ($method->getParameters() as $param) {
            $params[] = $this->analyzeParameter(param: $param);
        }

        return new MethodPrototype(name: $method->getName(), parameters: $params);
    }

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
