<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Flows\ReadRuntimeTimeline;

use Avax\Components\Operations\Observability\System\Capabilities\Tracing\TraceTimeline;

final readonly class ReadRuntimeTimeline
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function read(TraceTimeline $timeline) : array
    {
        return $timeline->toArray();
    }
}
