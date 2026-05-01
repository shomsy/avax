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
    public function assemble(CreateContainerConfig $createContainerConfig): ObservabilityAssembly
    {
        $clock             = new Clock();
        $resolutionMetrics = new ResolutionMetrics();

        return new ObservabilityAssembly(
            clock   : $clock,
            metrics : $resolutionMetrics,
            timeline: new ResolutionTimeline(
                clock  : $clock,
                enabled: $createContainerConfig->usesDetailedDiagnostics(),
            ),
        );
    }
}
