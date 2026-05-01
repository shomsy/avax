<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\TracingTimeline;

use Closure;

/**
 * Represents a timed span in the trace timeline.
 */
final class TraceSpan
{
    private float $startMS;
    private Closure $onFinish;

    public function __construct(
        private string $name,
        private string|null $category = null,
        Closure        $onFinish = null,
    )
    {
        $this->startMS  = microtime(true) * 1000;
        $this->onFinish = $onFinish ?? static fn () => null;
    }

    public function end() : float
    {
        $duration = microtime(true) * 1000 - $this->startMS;

        ($this->onFinish)($duration);

        return $duration;
    }

    public function name() : string
    {
        return $this->name;
    }
}
