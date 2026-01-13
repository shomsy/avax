<?php

declare(strict_types=1);

namespace Avax\Container\Observe\Trace;

/**
 * Optional hook for consuming resolution traces.
 *
 * @see docs/Observe/Trace/TraceObserverInterface.md#quick-summary
 */
interface TraceObserverInterface
{
    public function record(ResolutionTrace $trace) : void;
}
