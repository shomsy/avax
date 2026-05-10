<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Health;

use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use Avax\Framework\System\Capabilities\Health\Foundation\ReadinessReport;

final readonly class CheckReadiness
{
    /**
     * @param list<callable(): HealthFinding> $checks
     */
    public function __construct(
        private array $checks = [],
        private string $version = '',
    ) {
    }

    public function check(): ReadinessReport
    {
        $findings = [];
        $overall = HealthStatus::Green;

        foreach ($this->checks as $check) {
            $finding = $check();
            $findings[] = $finding;

            if ($finding->status === HealthStatus::Red) {
                $overall = HealthStatus::Red;
            } elseif ($finding->status === HealthStatus::Yellow && $overall === HealthStatus::Green) {
                $overall = HealthStatus::Yellow;
            }
        }

        return new ReadinessReport($overall, $findings, $this->version);
    }
}
