<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Resolve;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Resolve\Contracts\DependencyResolverInterface;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
use Avax\Container\Features\Core\Exceptions\ServiceNotFoundException;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Psr\Container\ContainerInterface;

/**
 * Intelligent resolver for constructor and method dependencies.
 *
 * This component is responsible for "filling in the blanks" when a class needs
 * to be instantiated. It analyzes the requirements of a method or constructor
 * and attempts to satisfy them using a prioritized strategy.
 *
 * @see     docs/Features/Actions/Resolve/DependencyResolver.md
 */
final class DependencyResolver implements DependencyResolverInterface
{
    /**
     * Resolve a collection of parameter prototypes into an ordered array of arguments.
     *
     * @param array<int, ParameterPrototype> $parameters List of parameter requirements.
     * @param array<string, mixed>           $overrides  Manual values to use instead of resolution.
     * @param ContainerInterface             $container  The container to use for type resolution.
     * @param KernelContext|null             $context    The current resolution context (for loop detection).
     *
     * @return array<int, mixed> The resolved argument list, ordered correctly for invocation.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @see docs/Features/Actions/Resolve/DependencyResolver.md#method-resolveparameters
     */
    public function resolveParameters(
        array              $parameters,
        array              $overrides,
        ContainerInterface $container,
        KernelContext|null $context = null
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
     * Resolve a single parameter prototype using the prioritized strategy.
     *
     * @param ParameterPrototype   $parameter The requirement profile.
     * @param array<string, mixed> $overrides Available manual values.
     * @param ContainerInterface   $container The resolution source.
     * @param KernelContext|null   $context   The current chain context.
     *
     * @return mixed The resolved value.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function resolveParameter(
        ParameterPrototype $parameter,
        array              $overrides,
        ContainerInterface $container,
        KernelContext|null $context
    ) : mixed
    {
        $name = $parameter->name;

        // 1. Explicit Override (Highest Priority)
        if (array_key_exists(key: $name, array: $overrides)) {
            return $overrides[$name];
        }

        // 2. Type-based Resolution (Automated Sourcing)
        if ($parameter->type !== null) {
            try {
                // If we have a contextual kernel, use it to maintain the resolution chain/guards
                if ($container instanceof ContainerInternalInterface && $context !== null) {
                    return $container->resolveContext(context: $context->child(serviceId: $parameter->type));
                }

                // Fallback to basic PSR-11 get
                return $container->get(id: $parameter->type);
            } catch (ServiceNotFoundException $e) {
                // If optional, we might swallow this and fall back to default/null
                if ($parameter->isRequired) {
                    throw $e;
                }
            }
        }

        // 3. Constant Default Value
        if ($parameter->hasDefault) {
            return $parameter->defaultValue;
        }

        // 4. Nullable Fallback
        if ($parameter->allowsNull) {
            return null;
        }

        // 5. Final Failure: Cannot satisfy requirement
        throw new ServiceNotFoundException(
            serviceId: $parameter->type ?? 'mixed',
            message  : "Unresolvable dependency [{$name}] in resolution chain for [{$context?->serviceId}]."
        );
    }
}
