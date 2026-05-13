<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\HealthCheck;

use Avax\Components\Operations\Logging\System\Capabilities\Logger\Logger;

/**
 * CheckLoggingHealth
 *
 * Verifies logging component runtime health:
 * - Logger class available
 */
final readonly class CheckLoggingHealth
{
    public function check(): LoggingHealthReport
    {
        $findings = [];
        $healthy = true;

        // Check 1: Logger class available
        if (class_exists(Logger::class)) {
            $findings[] = 'Logger class available';
        } else {
            $healthy = false;
            $findings[] = 'Logger class not loaded';
        }

        return new LoggingHealthReport(
            healthy: $healthy,
            findings: $findings,
        );
    }
}
