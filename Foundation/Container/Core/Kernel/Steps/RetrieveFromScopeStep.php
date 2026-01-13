<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\TerminalKernelStep;
use Avax\Container\Features\Operate\Scope\ScopeManager;

/**
 * Retrieve From Scope Step - Cache-First Resolution
 *
 * Checks if the requested service is already resolved and stored in a scope.
 * If found, it marks the context as resolved and terminates the pipeline early.
 * This is a critical performance optimization for singleton and scoped services.
 *
 * @see docs/Core/Kernel/Steps/RetrieveFromScopeStep.md#quick-summary
 */
final readonly class RetrieveFromScopeStep implements TerminalKernelStep
{
    /**
     * @param ScopeManager $scopeManager Scope-backed instance storage
     *
     * @see docs/Core/Kernel/Steps/RetrieveFromScopeStep.md#method-__construct
     */
    public function __construct(
        private ScopeManager $scopeManager
    ) {}

    /**
     * Check if instance exists in any scope.
     *
     * @param KernelContext $context The resolution context
     *
     * @see docs/Core/Kernel/Steps/RetrieveFromScopeStep.md#method-__invoke
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
