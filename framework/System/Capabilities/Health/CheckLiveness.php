<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Health;

use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;

final readonly class CheckLiveness
{
    /**
     * Check if the process is alive.
     *
     * @param list<callable(): bool> $checks
     */
    public function __construct(
        private array $checks = [],
    ) {
    }

    public function check(): HealthReport
    {
        foreach ($this->checks as $check) {
            if (!$check()) {
                return HealthReport::unhealthy('Liveness check failed.');
            }
        }

        return HealthReport::healthy('Process is alive.');
    }
}
