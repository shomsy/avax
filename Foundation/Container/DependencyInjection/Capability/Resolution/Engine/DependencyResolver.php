<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Resolution\Engine;

use Avax\Container\DependencyInjection\Capability\Prototypes\Model\ParameterPrototype;
use Avax\Container\DependencyInjection\Capability\Resolution\Contracts\ContainerRuntimeInterface;
use Avax\Container\DependencyInjection\Capability\Resolution\Errors\ServiceNotFoundException;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;
use Psr\Container\ContainerInterface;

/**
 * Resolves constructor and method parameters from overrides, types, and defaults.
 */
final class DependencyResolver
{
    /**
     * @param array<int, ParameterPrototype> $parameters
     * @param array<string, mixed> $overrides
     * @return array<int, mixed>
     */
    public function resolveParameters(
        array $parameters,
        array $overrides,
        ContainerInterface $container,
        KernelContext|null $context = null
    ) : array {
        $resolved = [];

        foreach ($parameters as $parameter) {
            $resolved[] = $this->resolveParameter(
                parameter: $parameter,
                overrides: $overrides,
                container: $container,
                context  : $context
            );
        }

        return $resolved;
    }

    private function resolveParameter(
        ParameterPrototype $parameter,
        array $overrides,
        ContainerInterface $container,
        KernelContext|null $context
    ) : mixed {
        $name = $parameter->name;

        if (array_key_exists(key: $name, array: $overrides)) {
            return $overrides[$name];
        }

        if ($parameter->type !== null) {
            try {
                if ($container instanceof ContainerRuntimeInterface && $context !== null) {
                    return $container->resolveContext(context: $context->child(serviceId: $parameter->type));
                }

                return $container->get(id: $parameter->type);
            } catch (ServiceNotFoundException $exception) {
                if ($parameter->isRequired) {
                    throw $exception;
                }
            }
        }

        if ($parameter->hasDefault) {
            return $parameter->defaultValue;
        }

        if ($parameter->allowsNull) {
            return null;
        }

        throw new ServiceNotFoundException(
            serviceId: $parameter->type ?? 'mixed',
            message  : "Unresolvable dependency [{$name}] in resolution chain for [{$context?->serviceId}]."
        );
    }
}
