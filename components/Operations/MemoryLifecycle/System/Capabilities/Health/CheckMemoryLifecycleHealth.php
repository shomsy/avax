<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Health;

use Avax\Components\Operations\MemoryLifecycle\System\Capabilities\Snapshot\MemorySnapshot;

class CheckMemoryLifecycleHealth
{
    public function check(): MemoryLifecycleHealthReport
    {
        $memorySnapshot = new MemorySnapshot();
        $memorySnapshot->capture();

        $memoryUsed = $memorySnapshot->getUsed();
        $memoryPeak = $memorySnapshot->getPeak();
        $usagePercent = $memoryPeak > 0 ? ($memoryUsed / $memoryPeak) * 100 : 0;

        $healthy = $usagePercent < 90;

        return new MemoryLifecycleHealthReport(
            healthy: $healthy,
            message: $healthy ? 'Memory healthy' : 'Memory usage high',
            details: [
                'memory_used' => $memoryUsed,
                'memory_peak' => $memoryPeak,
                'usage_percent' => round($usagePercent, 2),
            ],
        );
    }
}
