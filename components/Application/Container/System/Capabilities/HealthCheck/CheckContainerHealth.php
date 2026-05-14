<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\HealthCheck;

use Avax\Components\Application\Container\System\ContainerInterface;
use Avax\Components\Application\Container\System\PublicSurface\Container;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use RuntimeException;
use Throwable;

/**
 * Health check for the Container component.
 *
 * Probes: configuration status, service resolution, compiled artifact validity.
 * Returns canonical HealthReport.
 */
final class CheckContainerHealth
{
    public function check() : HealthReport
    {
        $findings = [];
        $overall  = HealthStatus::Green;

        // 1. Is the container configured?
        try {
            $container = $this->resolveContainer();
        } catch (RuntimeException $e) {
            return new HealthReport(
                findings: [new HealthFinding('container.configuration', HealthStatus::Red, 'Container is not configured')],
                overall : HealthStatus::Red,
            );
        }

        $findings[] = new HealthFinding('container.configuration', HealthStatus::Green, 'Container is configured');

        // 2. Can services be resolved?
        $resolutionOk = $this->checkResolution($container, $findings);
        if (! $resolutionOk) {
            $overall = HealthStatus::Yellow;
        }

        // 3. Compiled artifact status (if applicable)
        $this->checkCompilation($container, $findings, $overall);

        return new HealthReport(findings: $findings, overall: $overall);
    }

    /**
     * @throws RuntimeException
     */
    private function resolveContainer() : ContainerInterface
    {
        // The Container facade delegates to the underlying ContainerInterface.
        // Use has() to verify the container is responsive.
        Container::has('__health_check__');

        // Access the engine through the facade instance.
        $facade = new Container();

        return $facade->engine();
    }

    /**
     * @param list<HealthFinding> $findings
     */
    private function checkResolution(ContainerInterface $container, array &$findings) : bool
    {
        try {
            // Validate core container entries — just check that the container itself is functional.
            // We probe PSR-11 compliance: has() and get() should not throw.
            // We can't know which services are registered, so we test with a known-self reference.
            // The container should at least respond to has() without error.
            $container->has('nonexistent_health_check_probe');

            $findings[] = new HealthFinding('container.resolution', HealthStatus::Green, 'Container responds to queries');

            return true;
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('container.resolution', HealthStatus::Red, sprintf('Resolution error: %s', $e->getMessage()));

            return false;
        }
    }

    /**
     * @param list<HealthFinding> $findings
     */
    private function checkCompilation(ContainerInterface $container, array &$findings, HealthStatus &$overall) : void
    {
        try {
            $report = $container->compileReport();

            if ($report === null) {
                $findings[] = new HealthFinding('container.compilation', HealthStatus::Green, 'Container is in dynamic mode (no compilation)');

                return;
            }

            if (! $report->available) {
                $findings[] = new HealthFinding('container.compilation', HealthStatus::Yellow, 'Compiled artifact not available');

                return;
            }

            if (! $report->compatible || $report->checksumValid === false) {
                $findings[] = new HealthFinding('container.compilation', HealthStatus::Yellow, 'Compiled artifact available but may be stale');
                if ($overall === HealthStatus::Green) {
                    $overall = HealthStatus::Yellow;
                }

                return;
            }

            $findings[] = new HealthFinding('container.compilation', HealthStatus::Green, 'Compiled artifact is valid');
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('container.compilation', HealthStatus::Yellow, sprintf('Compilation check failed: %s', $e->getMessage()));
            if ($overall === HealthStatus::Green) {
                $overall = HealthStatus::Yellow;
            }
        }
    }
}
