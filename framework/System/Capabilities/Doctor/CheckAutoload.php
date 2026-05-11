<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Doctor;

use Avax\Framework\System\Capabilities\Doctor\Types\DoctorFinding;
use Avax\Framework\System\Capabilities\Doctor\Types\DoctorSeverity;

final readonly class CheckAutoload
{
    public function __invoke(): DoctorFinding
    {
        $autoloadPath = dirname(__DIR__, 4).'/vendor/autoload.php';

        if (!is_file($autoloadPath)) {
            return new DoctorFinding(
                check    : 'autoload',
                severity : DoctorSeverity::Red,
                message  : 'vendor/autoload.php not found. Run: composer install',
            );
        }

        $classMapPath = dirname(__DIR__, 4).'/vendor/composer/autoload_classmap.php';

        if (!is_file($classMapPath)) {
            return new DoctorFinding(
                check    : 'autoload',
                severity : DoctorSeverity::Yellow,
                message  : 'Autoload not optimized. Run: composer dump-autoload -o',
            );
        }

        return new DoctorFinding(
            check    : 'autoload',
            severity : DoctorSeverity::Green,
            message  : 'Autoload is present and optimized',
        );
    }
}
