<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Observability\Trace;

/**
 * Optional hook for consuming resolution traces.
 *
 */
interface TraceObserverInterface
{
    public function record(ResolutionTrace $trace) : void;
}
