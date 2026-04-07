<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Injection\Properties;

use Avax\Container\DependencyInjection\Capability\Prototypes\Analyze\ReflectionTypeAnalyzer;
use Avax\Container\DependencyInjection\Capability\Prototypes\Model\PropertyPrototype;
use Avax\Container\DependencyInjection\Capability\Resolution\Contracts\ContainerRuntimeInterface;
use Avax\Container\DependencyInjection\Capability\Resolution\Errors\ResolutionException;
use Avax\Container\DependencyInjection\Capability\Resolution\Errors\ServiceNotFoundException;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Resolves a single property injection target from overrides and container state.
 */
final class PropertyInjector
{
    public function __construct(
        private ContainerInterface|null         $container,
        private readonly ReflectionTypeAnalyzer $typeAnalyzer = new ReflectionTypeAnalyzer
    ) {}

    public function setContainer(ContainerInterface $container) : void
    {
        $this->container = $container;
    }

    public function resolve(
        PropertyPrototype $property,
        array $overrides,
        KernelContext $context,
        string $ownerClass
    ) : PropertyResolution
    {
        if ($this->container === null) {
            throw new RuntimeException(message: 'PropertyInjector container reference not initialized.');
        }

        $name = $property->name;

        // 1. Explicit Override (Highest Priority)
        if (array_key_exists(key: $name, array: $overrides)) {
            return PropertyResolution::resolved(value: $overrides[$name]);
        }

        if ($this->typeAnalyzer->canResolveType(type: $property->type)) {
            try {
                $type = (string) $property->type;

                if ($this->container instanceof ContainerRuntimeInterface) {
                    return PropertyResolution::resolved(
                        value: $this->container->resolveContext(context: $context->child(serviceId: $type))
                    );
                }

                return PropertyResolution::resolved(
                    value: $this->container->get(id: $type)
                );
            } catch (ResolutionException|ServiceNotFoundException) {
                // Continue to default/null handling.
            }
        }

        if ($property->hasDefault) {
            return PropertyResolution::unresolved();
        }

        if ($property->allowsNull) {
            return PropertyResolution::resolved(value: null);
        }

        if ($property->required) {
            throw new ResolutionException(
                message: "Required property \${$name} in class {$ownerClass} cannot be resolved. " .
                'No service found for type: ' . ($property->type ?? 'null')
            );
        }

        return PropertyResolution::unresolved();
    }
}
