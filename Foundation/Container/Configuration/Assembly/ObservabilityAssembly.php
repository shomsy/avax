<?php

declare(strict_types=1);

namespace Avax\Container\Configuration\Assembly;

use Avax\Container\Foundation\Time\Clock;
use Avax\Container\Observability\ResolutionMetrics;
use Avax\Container\Observability\ResolutionTimeline;

/**
 * Built observability collaborators for one container runtime.
 */
final readonly class ObservabilityAssembly
{
    public function __construct(
        public Clock $clock,
        public ResolutionMetrics $metrics,
        public ResolutionTimeline $timeline
    ) {}
}
