<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Composition\Assembly;

use Avax\Container\DI\Foundation\Time\Clock;
use Avax\Container\DI\Capabilities\Diagnostics\Observability\ResolutionMetrics;
use Avax\Container\DI\Capabilities\Diagnostics\Observability\ResolutionTimeline;

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
