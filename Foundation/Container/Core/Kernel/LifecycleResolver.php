<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Core\Kernel\Contracts\LifecycleStrategy;
use Avax\Container\Features\Define\Store\ServiceDefinition;
use BackedEnum;

/**
 * Lifecycle Resolver
 *
 * Determines the appropriate lifecycle strategy based on service definition, enabling proper instance management and resource optimization.
 *
 * @see docs_md/Core/Kernel/LifecycleResolver.md#quick-summary
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
     * @return LifecycleStrategy The resolved lifecycle strategy for managing service instances
     * @see docs_md/Core/Kernel/LifecycleResolver.md#method-resolve
     */
    public function resolve(ServiceDefinition|null $definition) : LifecycleStrategy
    {
        $lifecycle = $definition?->lifetime ?? 'transient';

        // Convert enum to string if needed
        if ($lifecycle instanceof BackedEnum) {
            $lifecycle = $lifecycle->value;
        }

        if (! $this->registry->has($lifecycle)) {
            // Fallback to transient if lifecycle not supported, or throw if in strict mode
            return $this->registry->get('transient');
        }

        return $this->registry->get($lifecycle);
    }
}
