<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Doctor;

use Avax\Framework\System\Capabilities\Doctor\Types\DoctorFinding;
use Avax\Framework\System\Capabilities\Doctor\Types\DoctorSeverity;
use Avax\Framework\System\Configuration\LoadApplicationConfiguration;
use Avax\Framework\System\Configuration\LoadRuntimeConfiguration;
use Avax\Framework\System\Configuration\ValidateApplicationConfiguration;
use Avax\Framework\System\Configuration\ValidateRuntimeConfiguration;
use Throwable;

final readonly class CheckConfiguration
{
    public function __invoke(): DoctorFinding
    {
        $configPath = dirname(__DIR__, 4).'/config';
        $appConfigPath = $configPath.'/app.php';
        $runtimeConfigPath = $configPath.'/runtime.php';

        $issues = [];

        if (!is_file($appConfigPath)) {
            $issues[] = 'config/app.php not found';
        }

        if (!is_file($runtimeConfigPath)) {
            $issues[] = 'config/runtime.php not found';
        }

        if ($issues !== []) {
            return new DoctorFinding(
                check    : 'configuration',
                severity : DoctorSeverity::Red,
                message  : 'Missing config files: '.implode(', ', $issues),
            );
        }

        try {
            $appConfig = (new LoadApplicationConfiguration())->load();
            (new ValidateApplicationConfiguration())->validate($appConfig);
        } catch (Throwable $e) {
            return new DoctorFinding(
                check    : 'configuration',
                severity : DoctorSeverity::Red,
                message  : 'Application config invalid: '.$e->getMessage(),
            );
        }

        try {
            $runtimeConfig = (new LoadRuntimeConfiguration())->load();
            (new ValidateRuntimeConfiguration())->validate($runtimeConfig);
        } catch (Throwable $e) {
            return new DoctorFinding(
                check    : 'configuration',
                severity : DoctorSeverity::Red,
                message  : 'Runtime config invalid: '.$e->getMessage(),
            );
        }

        return new DoctorFinding(
            check    : 'configuration',
            severity : DoctorSeverity::Green,
            message  : 'Configuration files load and validate successfully',
        );
    }
}
