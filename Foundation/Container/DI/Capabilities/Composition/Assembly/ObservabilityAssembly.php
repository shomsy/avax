<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Composition\Assembly;

use Avax\Container\DI\Capabilities\Diagnostics\Observability\ResolutionMetrics;
use Avax\Container\DI\Capabilities\Diagnostics\Observability\ResolutionTimeline;
use Avax\Container\DI\Foundation\Time\Clock;

/**
 * Built observability collaborators for one container runtime.
 */
final readonly class ObservabilityAssembly
{
    public ResolutionTimeline $timeline;
    public ResolutionMetrics  $metrics;
    public Clock              $clock;

    public function __construct(
        Clock              $clock,
        ResolutionMetrics  $metrics,
        ResolutionTimeline $timeline
    )
    {
        $this->clock    = $clock;
        $this->metrics  = $metrics;
        $this->timeline = $timeline;
    }
}
