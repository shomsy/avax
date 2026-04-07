<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps;

use Avax\Container\DependencyInjection\Capabilities\Resolution\Kernel\KernelContext;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Contracts\TerminalKernelStep;
use Avax\Container\DependencyInjection\Capabilities\Scopes\ScopeManager;

/**
 * Retrieve From Scope Step - Cache-First Resolution
 *
 * Checks if the requested service is already resolved and stored in a scope.
 * If found, it marks the context as resolved and terminates the pipeline early.
 * This is a critical performance optimization for singleton and scoped services.
 *
 */
final readonly class RetrieveFromScopeStep implements TerminalKernelStep
{
    /**
     * @param ScopeManager $scopeManager Scope-backed instance storage
     *
     */
    public function __construct(
        private ScopeManager $scopeManager
    ) {}

    /**
     * Check if instance exists in any scope.
     *
     * @param KernelContext $context The resolution context
     *
     */
    public function __invoke(KernelContext $context) : void
    {
        // Skip for injectInto operations as they deal with existing instances
        if ($context->getMeta(namespace: 'inject', key: 'target', default: false)) {
            return;
        }

        if ($this->scopeManager->has(abstract: $context->serviceId)) {
            $context->resolvedWith(instance: $this->scopeManager->get(abstract: $context->serviceId));

            // Mark as resolved from scope for telemetry
            $context->setMeta(namespace: 'resolution', key: 'strategy', value: 'scope');
            $context->setMeta(namespace: 'resolution', key: 'cached', value: true);
        }
    }
}
