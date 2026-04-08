<?php

declare(strict_types=1);

namespace Avax\Container\Configuration\Assembly;

use Avax\Container\Configuration\CreateContainerConfig;
use Avax\Container\Foundation\Time\Clock;
use Avax\Container\Observability\ResolutionMetrics;
use Avax\Container\Observability\ResolutionTimeline;

/**
 * Builds the observability collaborators for one container runtime.
 */
final class AssembleObservability
{
    public function assemble(CreateContainerConfig $config) : ObservabilityAssembly
    {
        $clock = new Clock;
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
