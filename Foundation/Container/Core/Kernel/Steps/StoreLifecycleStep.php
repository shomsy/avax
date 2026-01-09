<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Core\Kernel\LifecycleResolver;
use Avax\Container\Features\Operate\Scope\ScopeManager;

/**
 * Store Lifecycle Step - Scope and Lifecycle Management
 *
 * Manages the storage of resolved instances according to their lifecycle policies.
 * This step ensures that instances are stored in the appropriate scope (singleton,
 * scoped, transient) and handles cleanup for scoped instances.
 *
 * Lifecycle management includes:
 * - Singleton storage in global scope
 * - Scoped storage in request/session scope
 * - Transient instances (no storage)
 * - Instance cleanup and disposal
 *
 * @package Avax\Container\Core\Kernel\Steps
 * @see docs_md/Core/Kernel/Steps/StoreLifecycleStep.md#quick-summary
 */
final readonly class StoreLifecycleStep implements KernelStep
{
    /**
     * @param LifecycleResolver $lifecycleResolver Resolver that maps definitions to lifecycle strategies
     *
     * @see docs_md/Core/Kernel/Steps/StoreLifecycleStep.md#method-__construct
     */
    public function __construct(
        private LifecycleResolver $lifecycleResolver
    ) {}

    /**
     * Store the resolved instance according to its lifecycle.
     *
     * @param KernelContext $context The resolution context with resolved instance
     *
     * @return void
     * @see docs_md/Core/Kernel/Steps/StoreLifecycleStep.md#method-__invoke
     */
    public function __invoke(KernelContext $context): void
    {
        // Skip for injectInto operations
        if ($context->getMeta('inject', 'target', false)) {
            $context->setMeta('storage', 'skipped', 'inject_target');
            return;
        }

        if ($context->getInstance() === null) {
            return; // Nothing to store
        }

        // Determine lifecycle from definition and get appropriate strategy
        $definition = $context->getMeta('definition', 'instance');
        $strategy = $this->lifecycleResolver->resolve($definition);

        // Store using the strategy pattern
        $strategy->store($context->serviceId, $context->getInstance());

        // Determine location based on strategy type
        $location = match (true) {
            $strategy instanceof \Avax\Container\Core\Kernel\Strategies\SingletonLifecycleStrategy => 'global',
            $strategy instanceof \Avax\Container\Core\Kernel\Strategies\ScopedLifecycleStrategy => 'scoped',
            $strategy instanceof \Avax\Container\Core\Kernel\Strategies\TransientLifecycleStrategy => 'transient',
            default => 'unknown'
        };

        // Record storage metadata
        $lifecycle = $definition?->lifetime ?? 'transient';
        if ($lifecycle instanceof \BackedEnum) {
            $lifecycle = $lifecycle->value;
        }

        $context->setMeta('storage', 'lifecycle', $lifecycle);
        $context->setMeta('storage', 'location', $location);
        $context->setMeta('storage', 'managed', true);
        $context->setMeta('storage', 'completed_at', microtime(true));
    }
}
