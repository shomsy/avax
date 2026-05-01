<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Monitoring\System\Capabilities\Health;

final readonly class HealthEndpoint
{
    public function report(array $checks = []): HealthReport
    {
        $resolvedChecks = $checks === [] ? ['runtime' => new HealthCheckResult(status: 'up')] : $checks;
        $allUp = ! in_array(needle: false, haystack: array_map(
            callback: static fn (HealthCheckResult $healthCheckResult) : bool => $healthCheckResult->status === 'up',
            array   : $resolvedChecks,
        ), strict: true);

        return new HealthReport(
            status: $allUp ? 'up' : 'degraded',
            checks: $resolvedChecks,
        );
    }
}
