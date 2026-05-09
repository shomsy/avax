<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Doctor;

use Avax\Framework\System\Capabilities\Doctor\Foundation\DoctorFinding;
use Avax\Framework\System\Capabilities\Doctor\Foundation\DoctorSeverity;

final readonly class CheckWarmSafety
{
    public function __invoke(): DoctorFinding
    {
        $basePath = dirname(__DIR__, 4).'/framework/System/Runtime/WarmApplication';

        $requiredFiles = [
            'WarmStateContract.php',
            'HandleWarmRequest.php',
            'FlushScopedInstances.php',
            'DetectLeakedState.php',
        ];

        $missing = [];

        foreach ($requiredFiles as $file) {
            if (!is_file($basePath.'/'.$file)) {
                $missing[] = $file;
            }
        }

        if ($missing !== []) {
            return new DoctorFinding(
                check    : 'warm-safety',
                severity : DoctorSeverity::Red,
                message  : 'Warm worker safety files missing: '.implode(', ', $missing),
            );
        }

        return new DoctorFinding(
            check    : 'warm-safety',
            severity : DoctorSeverity::Green,
            message  : 'Warm worker safety foundation is present',
        );
    }
}
