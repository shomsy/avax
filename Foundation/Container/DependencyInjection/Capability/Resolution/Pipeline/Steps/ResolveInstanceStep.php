<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Resolution\Pipeline\Steps;

use Avax\Container\DependencyInjection\Capability\Resolution\Engine\EngineInterface;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;
use Avax\Container\DependencyInjection\Capability\Resolution\Pipeline\Contracts\KernelStep;

/**
 * Pipeline step that asks the resolution engine to produce the instance.
 */
final readonly class ResolveInstanceStep implements KernelStep
{
    public function __construct(
        private EngineInterface $engine
    ) {}

    public function __invoke(KernelContext $context) : void
    {
        if ($context->isResolved() || $context->getMeta(namespace: 'inject', key: 'target', default: false)) {
            return;
        }

        $instance = $this->engine->resolve(context: $context);
        $context->resolvedWith(instance: $instance);
        $context->setMeta(namespace: 'resolution', key: 'completed_at', value: microtime(as_float: true));
    }
}
