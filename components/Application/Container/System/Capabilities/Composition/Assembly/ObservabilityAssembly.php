<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Composition\Assembly;

use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionMetrics;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionTimeline;
use Avax\Components\Application\Container\System\Foundation\Time\Clock;

/**
 * Built observability collaborators for one container runtime.
 */
final readonly class ObservabilityAssembly
{
    public Clock $clock;

    public function __construct(
        Clock $clock,
        public ResolutionMetrics $resolutionMetrics,
        public ResolutionTimeline $resolutionTimeline,
    ) {
        $this->clock = $clock;
    }
}
