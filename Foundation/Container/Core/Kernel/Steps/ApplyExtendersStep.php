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
 * @see docs_md/Core/Kernel/Steps/ApplyExtendersStep.md#quick-summary
 */
final readonly class ApplyExtendersStep implements KernelStep
{
    /**
     * @param DefinitionStore $definitions Source of extender callables
     * @param ScopeManager    $scopes      Scope manager for container access
     *
     * @see docs_md/Core/Kernel/Steps/ApplyExtendersStep.md#methods
     */
    public function __construct(
        private DefinitionStore $definitions,
        private ScopeManager    $scopes
    ) {}

    /**
     * Invoke extenders for the resolved instance and update context metadata.
     *
     * @param KernelContext $context
     *
     * @return void
     * @see docs_md/Core/Kernel/Steps/ApplyExtendersStep.md#method-__invokekernelcontext-context
     */
    public function __invoke(KernelContext $context): void
    {
        if ($context->getInstance() === null || $context->getMeta('inject', 'target', false)) {
            return;
        }

        $id = $context->serviceId;
        $extenders = $this->definitions->getExtenders(abstract: $id);

        if (empty($extenders)) {
            return;
        }

        // Pull container from scope if possible (has before get pattern for safety)
        $container = null;
        $containerType = \Avax\Container\Features\Core\Contracts\ContainerInterface::class;
        if ($this->scopes->has($containerType)) {
            $container = $this->scopes->get($containerType);
        }

        foreach ($extenders as $extender) {
            $result = $extender($context->getInstance(), $container);

            if ($result !== null) {
                $context->overwriteWith(instance: $result);
            }
        }

        $context->setMeta('extenders', 'applied_count', count($extenders));
        $context->setMeta('extenders', 'completed_at', microtime(true));
    }
}
