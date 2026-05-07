<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Flows\FinishTrace;

use Avax\Components\Operations\Observability\System\Capabilities\Tracing\Span;
use Avax\Components\Operations\Observability\System\Capabilities\Tracing\TraceTimeline;

final readonly class FinishTrace
{
    public function finish(Span $span, TraceTimeline $timeline) : Span
    {
        $span->end();
        $timeline->add($span);

        return $span;
    }
}
