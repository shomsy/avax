<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Resolution;

use Avax\Container\Errors\ContainerException;
use Avax\Container\DependencyInjection\Injection\Attributes\Inject;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

final class ResolveDependencies
{
    /**
     * @param list<ReflectionParameter> $parameters
     */
    public function createPlan(array $parameters) : ResolvePlan
    {
        $compiled = [];

        foreach ($parameters as $parameter) {
            $compiled[] = [
                'name' => $parameter->getName(),
                'serviceId' => $this->serviceIdFor(parameter: $parameter),
                'hasDefault' => $parameter->isDefaultValueAvailable(),
                'default' => $parameter->isDefaultValueAvailable()
                    ? base64_encode(serialize($parameter->getDefaultValue()))
                    : '',
                'allowsNull' => $parameter->allowsNull(),
            ];
        }

        return new ResolvePlan(parameters: $compiled);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<int, mixed>
     */
    public function resolveParameters(
        array $parameters,
        array $overrides,
        ServiceResolver $resolver,
        ResolveRequest|null $request = null
    ) : array {
        return $this->resolvePlan(
            plan     : $this->createPlan(parameters: $parameters),
            overrides: $overrides,
            resolver : $resolver,
            request  : $request
        );
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<int, mixed>
     */
    public function resolvePlan(
        ResolvePlan $plan,
        array $overrides,
        ServiceResolver $resolver,
        ResolveRequest|null $request
    ) : array {
        $resolved = [];

        foreach ($plan->parameters as $parameter) {
            $resolved[] = $this->resolveCompiledParameter(
                parameter: $parameter,
                overrides: $overrides,
                resolver : $resolver,
                request  : $request
            );
        }

        return $resolved;
    }

    /**
     * @param array{name: string, serviceId: string|null, hasDefault: bool, default: string, allowsNull: bool} $parameter
     * @param array<string, mixed> $overrides
     */
    private function resolveCompiledParameter(
        array $parameter,
        array $overrides,
        ServiceResolver $resolver,
        ResolveRequest|null $request
    ) : mixed {
        if (array_key_exists($parameter['name'], $overrides)) {
            return $overrides[$parameter['name']];
        }

        if ($parameter['serviceId'] !== null) {
            return $resolver->resolveRequest(
                request: $request?->child(serviceId: $parameter['serviceId'])
                    ?? new ResolveRequest(serviceId: $parameter['serviceId'])
            );
        }

        if ($parameter['hasDefault']) {
            return unserialize(base64_decode($parameter['default']), ['allowed_classes' => true]);
        }

        if ($parameter['allowsNull']) {
            return null;
        }

        throw new ContainerException(
            message: "Cannot resolve parameter [\${$parameter['name']}] for service [{$request?->serviceId}]."
        );
    }

    private function serviceIdFor(ReflectionParameter $parameter) : string|null
    {
        $attributes = $parameter->getAttributes(Inject::class);
        if ($attributes !== []) {
            $inject = $attributes[0]->newInstance();
            if (is_string($inject->abstract) && $inject->abstract !== '') {
                return $inject->abstract;
            }
        }

        $type = $parameter->getType();
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
}
