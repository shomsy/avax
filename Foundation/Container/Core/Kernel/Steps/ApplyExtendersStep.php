<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Operate\Scope\ScopeManager;

/**
 * Apply Extenders Step - Post-Resolution Modification
 *
 * This step retrieves and applies all registered extenders (decorators/callbacks)
 * to the newly resolved instance, allowing for runtime modification without
 * altering the original service definition.
 *
 * @see     docs/Core/Kernel/Steps/ApplyExtendersStep.md#quick-summary
 */
final readonly class ApplyExtendersStep implements KernelStep
{
    /**
     * @param DefinitionStore $definitions Source of registered extenders.
     * @param ScopeManager    $scopes      System for retrieving system-level services.
     *
     * @see docs/Core/Kernel/Steps/ApplyExtendersStep.md#method-__construct
     */
    public function __construct(
        private DefinitionStore $definitions,
        private ScopeManager    $scopes
    ) {}

    /**
     * Invoke extenders for the resolved instance and update context metadata.
     *
     * @param KernelContext $context The resolution context.
     *
     * @throws \Throwable If an extender fails.
     *
     * @see docs/Core/Kernel/Steps/ApplyExtendersStep.md#method-__invoke
     */
    public function __invoke(KernelContext $context) : void
    {
        if ($context->getMeta(namespace: 'inject', key: 'target', default: false)) {
            return;
        }

        $extenders = $this->definitions->getExtenders(abstract: $context->serviceId);

        if (empty($extenders)) {
            return;
        }

        $instance = $context->getInstance();

        foreach ($extenders as $extender) {
            // Apply extender, allowing it to return a new instance (decoration)
            $result = $extender($instance, $this->scopes->get(abstract: ContainerInterface::class));

            if ($result !== null) {
                $instance = $result;
            }
        }

        // Safely overwrite the instance in context with the extended version
        $context->overwriteWith(instance: $instance);

        // Record metrics
        $context->setMeta(namespace: 'extenders', key: 'applied', value: count($extenders));
        $context->setMeta(namespace: 'extenders', key: 'completed_at', value: microtime(as_float: true));
    }
}
