<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Resolution\Engine;

use Avax\Container\DependencyInjection\Capability\Observability\Trace\TraceObserverInterface;
use Avax\Container\DependencyInjection\Capability\Resolution\Contracts\ContainerRuntimeInterface;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;

/**
 * Internal contract for the shared resolution engine.
 */
interface EngineInterface
{
    public function setContainer(ContainerRuntimeInterface $container) : void;

    public function resolve(KernelContext $context, TraceObserverInterface|null $traceObserver = null) : mixed;
}
