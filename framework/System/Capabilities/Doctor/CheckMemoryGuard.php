<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Doctor;

use Avax\Framework\System\Capabilities\Doctor\Types\DoctorFinding;
use Avax\Framework\System\Capabilities\Doctor\Types\DoctorSeverity;

final readonly class CheckMemoryGuard
{
    public function __invoke(): DoctorFinding
    {
        $basePath = dirname(__DIR__, 4).'/framework/System/Runtime/MemoryGuard';

        $requiredFiles = [
            'MonitorWorkerMemory.php',
            'RequestWorkerRecycle.php',
            'CalculateMemoryGrowthRate.php',
        ];

        $missing = [];

        foreach ($requiredFiles as $file) {
            if (!is_file($basePath.'/'.$file)) {
                $missing[] = $file;
            }
        }

        if ($missing !== []) {
            return new DoctorFinding(
                check    : 'memory-guard',
                severity : DoctorSeverity::Red,
                message  : 'Memory guard files missing: '.implode(', ', $missing),
            );
        }

        return new DoctorFinding(
            check    : 'memory-guard',
            severity : DoctorSeverity::Green,
            message  : 'Memory guard foundation is present',
        );
    }
}
