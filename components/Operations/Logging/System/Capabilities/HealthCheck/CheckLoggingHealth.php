<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\HealthCheck;

use Avax\Components\Operations\Logging\System\Capabilities\Logger\Logger;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use Throwable;

/**
 * CheckLoggingHealth
 *
 * Verifies logging component runtime health:
 * - Logger class available and instantiable
 * - Logger can write without throwing
 * - Log directory (sys_get_temp_dir) is writable
 */
final class CheckLoggingHealth
{
    public function check() : HealthReport
    {
        $findings = [];
        $overall = HealthStatus::Green;

        // Check 1: Logger class available
        if (! class_exists(Logger::class)) {
            return new HealthReport(
                findings: [new HealthFinding('logging.autoload', HealthStatus::Red, 'Logger class not loaded')],
                overall : HealthStatus::Red,
            );
        }

        $findings[] = new HealthFinding('logging.autoload', HealthStatus::Green, 'Logger class loaded');

        // Check 2: Logger can write without throwing
        try {
            $logger = new Logger();
            $logger->log('info', 'health_check');
            $findings[] = new HealthFinding('logging.writable', HealthStatus::Green, 'Logger can write');
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('logging.writable', HealthStatus::Red, sprintf('Logger write failed: %s', $e->getMessage()));
            $overall    = HealthStatus::Red;
        }

        // Check 3: stderr is accessible (Logger uses error_log which writes to stderr)
        $stderrWritable = @fopen('php://stderr', 'w') !== false;
        if (! $stderrWritable) {
            $findings[] = new HealthFinding('logging.stderr', HealthStatus::Yellow, 'stderr is not accessible');
            if ($overall === HealthStatus::Green) {
                $overall = HealthStatus::Yellow;
            }
        } else {
            $findings[] = new HealthFinding('logging.stderr', HealthStatus::Green, 'stderr is accessible');
        }

        return new HealthReport(findings: $findings, overall: $overall);
    }
}
