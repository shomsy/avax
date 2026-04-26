<?php

declare(strict_types=1);

namespace components\Container\DI\Capabilities\Composition\Assembly;

use components\Container\DI\Capabilities\Composition\CreateContainerConfig;
use components\Container\DI\Capabilities\Diagnostics\Observability\ResolutionMetrics;
use components\Container\DI\Capabilities\Diagnostics\Observability\ResolutionTimeline;
use components\Container\DI\Foundation\Time\Clock;

/**
 * Builds the observability collaborators for one container runtime.
 */
final class AssembleObservability
{
    public function assemble(CreateContainerConfig $config) : ObservabilityAssembly
    {
        $clock   = new Clock;
        $metrics = new ResolutionMetrics;

        return new ObservabilityAssembly(
            clock   : $clock,
            metrics : $metrics,
            timeline: new ResolutionTimeline(
                          clock  : $clock,
                          enabled: $config->usesDetailedDiagnostics()
                      )
        );
    }
}
