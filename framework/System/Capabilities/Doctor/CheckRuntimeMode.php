<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Doctor;

use Avax\Framework\System\Capabilities\Doctor\Types\DoctorFinding;
use Avax\Framework\System\Capabilities\Doctor\Types\DoctorSeverity;
use Avax\Framework\System\Configuration\LoadApplicationConfiguration;
use Avax\Framework\System\Configuration\LoadRuntimeConfiguration;
use Throwable;

/**
 * CheckRuntimeMode — checks that the selected runtime mode is valid and available.
 */
final readonly class CheckRuntimeMode
{
    public function __invoke(): DoctorFinding
    {
        $basePath = dirname(__DIR__, 4).'/framework/System/Capabilities/Runtime/RunApplication';

        $availableRuntimes = [];

        if (is_dir($basePath.'/ReactPhp')) {
            $availableRuntimes[] = 'reactphp';
        }

        if (is_dir($basePath.'/FrankenPhp')) {
            $availableRuntimes[] = 'frankenphp';
        }

        if (is_dir($basePath.'/RoadRunner')) {
            $availableRuntimes[] = 'roadrunner';
        }

        if (is_dir($basePath.'/Swoole')) {
            $availableRuntimes[] = 'swoole';
        }

        if (is_dir($basePath.'/Workerman')) {
            $availableRuntimes[] = 'workerman';
        }

        $availableRuntimes[] = 'built-in';

        try {
            $runtimeConfig = (new LoadRuntimeConfiguration())->load();
            $selectedRuntime = $runtimeConfig->defaultRuntime;

            if (!in_array($selectedRuntime, $availableRuntimes, true)) {
                return new DoctorFinding(
                    check    : 'runtime-mode',
                    severity : DoctorSeverity::Red,
                    message  : "Runtime '{$selectedRuntime}' not available. Available: ".implode(', ', $availableRuntimes),
                );
            }
        } catch (Throwable $e) {
            return new DoctorFinding(
                check    : 'runtime-mode',
                severity : DoctorSeverity::Yellow,
                message  : 'Could not load runtime config: '.$e->getMessage(),
            );
        }

        return new DoctorFinding(
            check    : 'runtime-mode',
            severity : DoctorSeverity::Green,
            message  : "Runtime mode is valid: {$runtimeConfig->defaultRuntime}",
        );
    }
}
