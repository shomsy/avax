<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Resolve\Contracts;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Core\Contracts\ContainerInternalInterface;
use Avax\Container\Observe\Trace\TraceObserverInterface;

/**
 * Resolution engine contract.
 *
 * @see docs/Features/Actions/Resolve/Contracts/EngineInterface.md#quick-summary
 */
interface EngineInterface
{
    /**
     * @see docs/Features/Actions/Resolve/Contracts/EngineInterface.md#method-setcontainer
     */
    public function setContainer(ContainerInternalInterface $container) : void;

    /**
     * @see docs/Features/Actions/Resolve/Contracts/EngineInterface.md#method-resolve
     */
    public function resolve(KernelContext $context, TraceObserverInterface|null $traceObserver = null) : mixed;
}
