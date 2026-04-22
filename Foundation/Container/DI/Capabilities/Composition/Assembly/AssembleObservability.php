<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Composition\Assembly;

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Capabilities\Diagnostics\Observability\ResolutionMetrics;
use Avax\Container\DI\Capabilities\Diagnostics\Observability\ResolutionTimeline;
use Avax\Container\DI\Foundation\Time\Clock;

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
