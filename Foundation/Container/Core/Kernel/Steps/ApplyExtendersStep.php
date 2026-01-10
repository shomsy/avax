<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Operate\Scope\ScopeManager;

/**
 * Apply Extenders Step - Post-Resolution Modification
 *
 * This step retrieves and applies all registered extenders (decorators/callbacks)
 * to the newly resolved instance, allowing for runtime modification without
 * altering the original service definition.
 *
 * @package Avax\Container\Core\Kernel\Steps
 * @see docs_md/Core/Kernel/Steps/ApplyExtendersStep.md#quick-summary
 */
final readonly class ApplyExtendersStep implements KernelStep
{
    /**
     * @param DefinitionStore $definitions Source of registered extenders.
     * @param ScopeManager    $scopes      System for retrieving system-level services.
     * @see docs_md/Core/Kernel/Steps/ApplyExtendersStep.md#method-__construct
     */
    public function __construct(
        private DefinitionStore $definitions,
        private ScopeManager    $scopes
    ) {}

    /**
     * Invoke extenders for the resolved instance and update context metadata.
     *
     * @param KernelContext $context The resolution context.
     * @return void
     * @throws \Throwable If an extender fails.
     * @see docs_md/Core/Kernel/Steps/ApplyExtendersStep.md#method-__invoke
     */
    public function __invoke(KernelContext $context): void
    {
        if ($context->getMeta('inject', 'target', false)) {
            return;
        }

        $extenders = $this->definitions->getExtenders(abstract: $context->serviceId);

        if (empty($extenders)) {
            return;
        }

        $instance = $context->getInstance();

        foreach ($extenders as $extender) {
            // Apply extender, allowing it to return a new instance (decoration)
            $result = $extender($instance, $this->scopes->get(abstract: \Avax\Container\Features\Core\Contracts\ContainerInterface::class));

            if ($result !== null) {
                $instance = $result;
            }
        }

        // Safely overwrite the instance in context with the extended version
        $context->overwriteWith(instance: $instance);

        // Record metrics
        $context->setMeta('extenders', 'applied', count($extenders));
        $context->setMeta('extenders', 'completed_at', microtime(as_float: true));
    }
}
