<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Health;

use Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Snapshot\MemorySnapshot;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;

class CheckMemoryLifecycleHealth
{
    public function check(): HealthReport
    {
        $memorySnapshot = new MemorySnapshot();
        $memorySnapshot->capture();

        $memoryUsed = $memorySnapshot->getUsed();
        $memoryPeak = $memorySnapshot->getPeak();
        $usagePercent = $memoryPeak > 0 ? ($memoryUsed / $memoryPeak) * 100 : 0;

        $status = match (true) {
            $usagePercent >= 95 => HealthStatus::Red,
            $usagePercent >= 80 => HealthStatus::Yellow,
            default             => HealthStatus::Green,
        };

        $message = sprintf(
            'Memory: %s / %s (%.1f%%)',
            $this->formatBytes($memoryUsed),
            $this->formatBytes($memoryPeak),
            $usagePercent,
        );

        return new HealthReport(
            findings: [new HealthFinding('memory.usage', $status, $message)],
            overall : $status,
        );
    }

    private function formatBytes(int $bytes) : string
    {
        if ($bytes < 1024) {
            return $bytes . 'B';
        }

        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . 'KB';
        }

        return round($bytes / 1048576, 1) . 'MB';
    }
}
