<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel\Steps;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Core\Kernel\Contracts\KernelStep;
use Avax\Container\Features\Actions\Resolve\Contracts\EngineInterface;

/**
 * Resolve Instance Step - Core Service Resolution
 *
 * This pivotal step delegates the actual construction of the service instance
 * to the resolution engine, marking the context as resolved upon success.
 *
 * @package Avax\Container\Core\Kernel\Steps
 * @see docs/Core/Kernel/Steps/ResolveInstanceStep.md#quick-summary
 */
final readonly class ResolveInstanceStep implements KernelStep
{
    /**
     * @param EngineInterface $engine The resolution engine responsible for object creation.
     * @see docs/Core/Kernel/Steps/ResolveInstanceStep.md#method-__construct
     */
    public function __construct(
        private EngineInterface $engine
    ) {}

    /**
     * Resolve the instance via the engine and mark the context as resolved.
     *
     * @param KernelContext $context The resolution context.
     * @return void
     * @see docs/Core/Kernel/Steps/ResolveInstanceStep.md#method-__invoke
     */
    public function __invoke(KernelContext $context): void
    {
        if ($context->isResolved() || $context->getMeta('inject', 'target', false)) {
            return;
        }

        $instance = $this->engine->resolve(context: $context);
        $context->resolvedWith(instance: $instance);

        // Record metrics
        $context->setMeta('resolution', 'completed_at', microtime(as_float: true));
    }
}
