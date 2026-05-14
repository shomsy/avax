<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Health;

use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Process\ProcessRecord;
use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Supervision\Supervisor;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;

/**
 * Health check for the RuntimeSupervision component.
 *
 * Probes the Supervisor's monitor() output and process registry
 * instead of returning hardcoded healthy status.
 */
final class SupervisorHealthCheck
{
    public function __construct(
        private readonly Supervisor|null $supervisor = null,
    ) {}

    public function check() : HealthReport
    {
        $findings = [];
        $overall  = HealthStatus::Green;

        // If no supervisor is injected, the component is not configured.
        if ($this->supervisor === null) {
            return new HealthReport(
                findings: [new HealthFinding('supervisor.configuration', HealthStatus::Yellow, 'Supervisor is not configured')],
                overall : HealthStatus::Yellow,
            );
        }

        $monitor = $this->supervisor->monitor();
        $status  = $monitor['status'] ?? 'unknown';

        // Check overall supervisor status
        $statusLevel = match ($status) {
            'running'  => HealthStatus::Green,
            'degraded' => HealthStatus::Yellow,
            'stopped'  => HealthStatus::Red,
            default    => HealthStatus::Yellow,
        };

        $findings[] = new HealthFinding(
            'supervisor.status',
            $statusLevel,
            sprintf('Supervisor status: %s', $status),
        );

        if ($statusLevel !== HealthStatus::Green) {
            $overall = $statusLevel;
        }

        // Check process registry
        $processes    = $monitor['processes'] ?? [];
        $runningCount = 0;
        $stoppedCount = 0;

        foreach ($processes as $process) {
            $processStatus = $process instanceof ProcessRecord
                ? $process->status
                : ($process['status'] ?? null);

            if ($processStatus === 'running') {
                $runningCount++;
            } elseif ($processStatus === 'stopped') {
                $stoppedCount++;
            }
        }

        $findings[] = new HealthFinding(
            'supervisor.processes',
            $runningCount > 0 || empty($processes) ? HealthStatus::Green : HealthStatus::Yellow,
            sprintf('Processes: %d running, %d stopped', $runningCount, $stoppedCount),
        );

        if ($runningCount === 0 && ! empty($processes) && $overall === HealthStatus::Green) {
            $overall = HealthStatus::Yellow;
        }

        // Check failure threshold
        $failureCount     = $monitor['failure_count'] ?? 0;
        $failureThreshold = $monitor['failure_threshold'] ?? 3;

        if ($failureCount >= $failureThreshold) {
            $findings[] = new HealthFinding(
                'supervisor.failures',
                HealthStatus::Red,
                sprintf('Failure threshold reached: %d/%d', $failureCount, $failureThreshold),
            );
            $overall    = HealthStatus::Red;
        } elseif ($failureCount > 0) {
            $findings[] = new HealthFinding(
                'supervisor.failures',
                HealthStatus::Yellow,
                sprintf('Failures recorded: %d/%d', $failureCount, $failureThreshold),
            );
            if ($overall === HealthStatus::Green) {
                $overall = HealthStatus::Yellow;
            }
        } else {
            $findings[] = new HealthFinding('supervisor.failures', HealthStatus::Green, 'No failures recorded');
        }

        return new HealthReport(findings: $findings, overall: $overall);
    }
}
