<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Resolution;

use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Attributes\Inject;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Attributes\RuntimeInput;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Throwable;

/**
 * Compiles and resolves parameter plans for constructors, methods, and calls.
 */
final class ResolveDependencies
{
    /**
     * @param array<string, mixed> $overrides
     * @return array<int, mixed>
     *
     * @throws Throwable
     */
    public function resolveParameters(
        array          $parameters,
        array          $overrides,
        ResolveDependency $resolveDependency,
        ?ResolveRequest   $resolveRequest = null,
    ) : array
    {
        return $this->resolvePlan(
            overrides: $overrides,
            plan     : $this->createPlan(parameters: $parameters),
            resolver : $resolveDependency,
            request  : $resolveRequest,
        );
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<int, mixed>
     *
     * @throws ContainerException
     * @throws Throwable
     */
    public function resolvePlan(
        ResolvePlan       $resolvePlan,
        array $overrides,
        ResolveDependency $resolveDependency,
        ?ResolveRequest   $resolveRequest,
    ): array {
        $resolved = [];

        foreach ($resolvePlan->parameters as $parameter) {
            $resolved[] = $this->resolveCompiledParameter(
                parameter: $parameter,
                overrides: $overrides,
                resolver : $resolveDependency,
                request  : $resolveRequest,
            );
        }

        return $resolved;
    }

    /**
     * @param array{name: string, serviceId: string|null, source: string, inputName: string, hasDefault: bool, default:
     *                            string, allowsNull: bool} $parameter
     * @param array<string, mixed> $overrides
     *
     * @throws Throwable
     */
    private function resolveCompiledParameter(
        array $parameter,
        array $overrides,
        ResolveDependency $resolveDependency,
        ?ResolveRequest   $resolveRequest,
    ): mixed {
        if (array_key_exists(key: $parameter['name'], array: $overrides)) {
            return $overrides[$parameter['name']];
        }

        if (
            $resolveRequest instanceof ResolveRequest
            && $parameter['serviceId'] === null
            && array_key_exists(key: $parameter['inputName'], array: $resolveRequest->context)
        ) {
            return $resolveRequest->context[$parameter['inputName']];
        }

        if ($parameter['serviceId'] !== null) {
            return $resolveDependency->resolveRequest(
                request: $resolveRequest?->child(serviceId: $parameter['serviceId'])
                             ?? new ResolveRequest(serviceId: $parameter['serviceId']),
            );
        }

        if ($parameter['hasDefault']) {
            return unserialize(data: base64_decode(string: (string) $parameter['default']), options: ['allowed_classes' => false]);
        }

        if ($parameter['allowsNull']) {
            return null;
        }

        throw new ContainerException(
            message: $parameter['source'] === 'runtime'
                         ? sprintf('Runtime input [$%s] is missing for [%s]. ', $parameter['inputName'], $resolveRequest?->serviceId)
                       . sprintf('Dependency path [%s]. Likely fix: pass an explicit override, use forContext(), or add a default value.', $resolveRequest?->getPath())
                         : sprintf('Cannot resolve parameter [$%s] for service [%s]. ', $parameter['name'], $resolveRequest?->serviceId)
                       . sprintf('Dependency path [%s]. Likely fix: register the dependency, add an Inject attribute, or provide an override.', $resolveRequest?->getPath()),
        );
    }

    /**
     * @param list<ReflectionParameter> $parameters
     */
    public function createPlan(array $parameters): ResolvePlan
    {
        $compiled = [];

        foreach ($parameters as $parameter) {
            $compiled[] = [
                'name'       => $parameter->getName(),
                'serviceId'  => $this->serviceIdFor(parameter: $parameter),
                'source'     => $this->sourceFor(parameter: $parameter),
                'inputName'  => $this->inputNameFor(parameter: $parameter),
                'hasDefault' => $parameter->isDefaultValueAvailable(),
                'default'    => $parameter->isDefaultValueAvailable()
                    ? $parameter->getDefaultValue()
                        |> serialize(...)
                        |> base64_encode(...)
                    : '',
                'allowsNull' => $parameter->allowsNull(),
            ];
        }

        return new ResolvePlan(parameters: $compiled);
    }

    /**
     * Infers one service id from the parameter attribute or object type.
     */
    private function serviceIdFor(ReflectionParameter $reflectionParameter) : ?string
    {
        if ($reflectionParameter->getAttributes(name: RuntimeInput::class) !== []) {
            return null;
        }

        $attributes = $reflectionParameter->getAttributes(name: Inject::class);
        if ($attributes !== []) {
            $inject = $attributes[0]->newInstance();
            if (is_string(value: $inject->abstract) && $inject->abstract !== '') {
                return $inject->abstract;
            }
        }

        $type = $reflectionParameter->getType();
        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            return $type->getName();
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $namedType) {
                if ($namedType instanceof ReflectionNamedType && ! $namedType->isBuiltin()) {
                    return $namedType->getName();
                }
            }
        }

        return null;
    }

    private function sourceFor(ReflectionParameter $reflectionParameter) : string
    {
        return $this->serviceIdFor(parameter: $reflectionParameter) !== null ? 'service' : 'runtime';
    }

    private function inputNameFor(ReflectionParameter $reflectionParameter) : string
    {
        $attributes = $reflectionParameter->getAttributes(name: RuntimeInput::class);
        if ($attributes === []) {
            return $reflectionParameter->getName();
        }

        $runtimeInput = $attributes[0]->newInstance();
        if (is_string(value: $runtimeInput->name) && trim(string: $runtimeInput->name) !== '') {
            return trim(string: $runtimeInput->name);
        }

        return $reflectionParameter->getName();
    }
}
