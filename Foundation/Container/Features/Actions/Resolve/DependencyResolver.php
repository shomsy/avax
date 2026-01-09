<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Resolve;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Resolve\Contracts\DependencyResolverInterface;
use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
use Avax\Container\Features\Core\Exceptions\ResolutionException;
use Avax\Container\Features\Core\Exceptions\ServiceNotFoundException;
use Throwable;

/**
 * Specialized resolver for dependency injection parameters.
 *
 * In standard mode, re-enters the container to resolve types.
 * In advanced mode, preserves context for circular dependency guards.
 *
 * @see docs_md/Features/Actions/Resolve/DependencyResolver.md#quick-summary
 */
final readonly class DependencyResolver implements DependencyResolverInterface
{
    /**
     * Resolves a list of method or constructor parameters.
     *
     * @throws \Avax\Container\Features\Core\Exceptions\ServiceNotFoundException
     * @see docs_md/Features/Actions/Resolve/DependencyResolver.md#method-resolveparameters
     */
    public function resolveParameters(
        array              $parameters,
        array              $overrides,
        ContainerInterface $container,
        KernelContext|null $context
    ) : array
    {
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

    /**
     * Resolves a single parameter using the internal resolver sequence.
     *
     * @throws \Avax\Container\Features\Core\Exceptions\ServiceNotFoundException
     * @see docs_md/Features/Actions/Resolve/DependencyResolver.md#methods
     */
    private function resolveParameter(
        mixed              $parameter,
        array              $overrides,
        ContainerInterface $container,
        KernelContext|null $context
    ) : mixed
    {
        if (array_key_exists($parameter->name, $overrides)) {
            return $overrides[$parameter->name];
        }

        if ($this->canResolveType(type: $parameter->type)) {
            try {
                // Circular Dependency Guard: 
                // Check if the type we are about to resolve is already being built in the current context chain.
                if ($context !== null && $context->contains(serviceId: $parameter->type)) {
                    throw new ResolutionException(
                        message: "Circular dependency detected: " . $context->getPath() . " -> " . $parameter->type
                    );
                }

                // Recursive Resolution:
                // If the container supports internal context, resolve within a child context.
                // This preserves the "building stack" for circular dependency detection.
                if ($context !== null && $container instanceof ContainerInternalInterface) {
                    return $container->resolveContext(context: $context->child(serviceId: $parameter->type));
                }

                // Fallback to standard resolve (starts a fresh context if parent context is missing)
                return $container->get(id: $parameter->type);
            } catch (ResolutionException|ServiceNotFoundException $e) {
                // Let these bubble up or throw new if needed.
                throw $e;
            } catch (Throwable $e) {
                // Wrap unexpected errors
                throw new ResolutionException(
                    message : "Error resolving dependency [{$parameter->type}] for [\${$parameter->name}]: " . $e->getMessage(),
                    previous: $e
                );
            }
        }

        if ($parameter->hasDefault) {
            return $parameter->default;
        }

        if ($parameter->allowsNull) {
            return null;
        }

        if ($parameter->required) {
            throw new ResolutionException(
                message: "Required parameter \${$parameter->name} cannot be resolved in class: " . ($context?->serviceId ?? 'unknown')
            );
        }

        return null;
    }

    /**
     * @see docs_md/Features/Actions/Resolve/DependencyResolver.md#how-it-works-technical
     */
    private function canResolveType(string|null $type) : bool
    {
        if ($type === null || $type === '') {
            return false;
        }

        return class_exists($type) || interface_exists($type) || (function_exists('enum_exists') && enum_exists($type));
    }
}
