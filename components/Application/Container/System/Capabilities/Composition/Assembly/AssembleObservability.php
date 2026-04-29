<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Composition\Assembly;

use Avax\Components\Application\Container\System\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionMetrics;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionTimeline;
use Avax\Components\Application\Container\System\Foundation\Time\Clock;

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
