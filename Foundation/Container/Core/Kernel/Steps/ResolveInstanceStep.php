<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Features\Actions\Resolve\Contracts\EngineInterface;

/**
 * Resolve Instance Step - Core Service Resolution
 *
 * @see docs_md/Core/Kernel/Steps/ResolveInstanceStep.md#quick-summary
 */
final readonly class ResolveInstanceStep implements KernelStep
{
    /**
     * @param EngineInterface $engine Resolution engine
     *
     * @see docs_md/Core/Kernel/Steps/ResolveInstanceStep.md#method-__construct
     */
    public function __construct(
        private EngineInterface $engine
    ) {}

    /**
     * Resolve the instance via the engine and mark the context as resolved.
     *
     * @param KernelContext $context
     * @return void
     * @see docs_md/Core/Kernel/Steps/ResolveInstanceStep.md#method-__invoke
     */
    public function __invoke(KernelContext $context): void
    {
        if ($context->getMeta('inject', 'target', false)) {
            return;
        }

        $instance = $this->engine->resolve(context: $context);
        $context->resolvedWith(instance: $instance);

        // Record metrics
        $context->setMeta('resolution', 'completed_at', microtime(true));
    }
}
