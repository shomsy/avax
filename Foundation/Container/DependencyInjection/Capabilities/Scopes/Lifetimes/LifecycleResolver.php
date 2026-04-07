<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Scopes\Lifetimes;

use Avax\Container\DependencyInjection\Capabilities\Definitions\Store\ServiceDefinition;
use Avax\Container\DependencyInjection\Capabilities\Scopes\Lifetimes\Contracts\LifecycleStrategy;
use BackedEnum;

/**
 * Lifecycle Resolver
 *
 * Determines the appropriate lifecycle strategy based on service definition, enabling proper instance management and
 * resource optimization.
 *
 */
final readonly class LifecycleResolver
{
    public function __construct(
        private LifecycleStrategyRegistry $registry
    ) {}

    /**
     * Resolve the lifecycle strategy for a service definition.
     *
     * Determines the appropriate lifecycle management strategy based on the service definition's
     * lifetime configuration, enabling proper instance sharing and resource optimization.
     *
     * @param ServiceDefinition|null $definition Service definition to resolve lifecycle for, or null for transient
     *
     * @return LifecycleStrategy The resolved lifecycle strategy for managing service instances
     *
     */
    public function resolve(ServiceDefinition|null $definition) : LifecycleStrategy
    {
        $lifecycle = $definition?->lifetime ?? 'transient';

        // Convert enum to string if needed
        if ($lifecycle instanceof BackedEnum) {
            $lifecycle = $lifecycle->value;
        }

        if (! $this->registry->has(name: $lifecycle)) {
            // Fallback to transient if lifecycle not supported, or throw if in strict mode
            return $this->registry->get(name: 'transient');
        }

        return $this->registry->get(name: $lifecycle);
    }
}
