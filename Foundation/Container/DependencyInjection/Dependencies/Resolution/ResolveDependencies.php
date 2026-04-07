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
     * @param array<string, mixed> $overrides
     * @return array<int, mixed>
     */
    public function resolveParameters(
        array $parameters,
        array $overrides,
        ServiceResolver $resolver,
        ResolveRequest|null $request = null
    ) : array {
        $resolved = [];

        foreach ($parameters as $parameter) {
            $resolved[] = $this->resolveParameter(
                parameter: $parameter,
                overrides: $overrides,
                resolver : $resolver,
                request  : $request
            );
        }

        return $resolved;
    }

    private function resolveParameter(
        ReflectionParameter $parameter,
        array $overrides,
        ServiceResolver $resolver,
        ResolveRequest|null $request
    ) : mixed {
        if (array_key_exists($parameter->getName(), $overrides)) {
            return $overrides[$parameter->getName()];
        }

        $serviceId = $this->serviceIdFor(parameter: $parameter);
        if ($serviceId !== null) {
            return $resolver->resolveRequest(
                request: $request?->child(serviceId: $serviceId) ?? new ResolveRequest(serviceId: $serviceId)
            );
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->allowsNull()) {
            return null;
        }

        throw new ContainerException(
            message: "Cannot resolve parameter [\${$parameter->getName()}] for service [{$request?->serviceId}]."
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
