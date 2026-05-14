<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RuntimeSafety;

use Avax\Components\Application\Cache\System\PublicSurface\Cache;
use Avax\Components\Application\Container\System\PublicSurface\Container;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use Throwable;

/**
 * Scans core components for health issues that may affect runtime safety.
 */
final readonly class ComponentHealthScanner
{
    /**
     * @return list<RuntimeSafetyFinding>
     */
    public function scan() : array
    {
        $findings = [];

        // 1. Container
        try {
            if (class_exists(Container::class)) {
                $report = Container::check();
                foreach ($report->findings as $finding) {
                    if ($finding->status !== HealthStatus::Green) {
                        $findings[] = new RuntimeSafetyFinding(
                            category : 'health',
                            severity : $finding->status === HealthStatus::Red ? RuntimeSafetyFinding::SEVERITY_CRITICAL : RuntimeSafetyFinding::SEVERITY_WARNING,
                            component: 'Application/Container',
                            message  : sprintf('Container Health [%s]: %s', $finding->check, $finding->message),
                        );
                    }
                }
            }
        } catch (Throwable $e) {
            $findings[] = new RuntimeSafetyFinding(
                category : 'health',
                severity : RuntimeSafetyFinding::SEVERITY_CRITICAL,
                component: 'Application/Container',
                message  : sprintf('Container Health Check Failed: %s', $e->getMessage()),
            );
        }

        // 2. Cache
        try {
            if (class_exists(Cache::class)) {
                $report = Cache::check();
                foreach ($report->findings as $finding) {
                    if ($finding->status !== HealthStatus::Green) {
                        $findings[] = new RuntimeSafetyFinding(
                            category : 'health',
                            severity : $finding->status === HealthStatus::Red ? RuntimeSafetyFinding::SEVERITY_CRITICAL : RuntimeSafetyFinding::SEVERITY_WARNING,
                            component: 'Application/Cache',
                            message  : sprintf('Cache Health [%s]: %s', $finding->check, $finding->message),
                        );
                    }
                }
            }
        } catch (Throwable $e) {
            // Cache might not be configured, which is often a warning but doctor treats it as info/warning
            // unless it's a hard crash.
        }

        // 3. Filesystem
        try {
            if (class_exists(Filesystem::class)) {
                $fs     = new Filesystem();
                $report = $fs->check();
                foreach ($report->findings as $finding) {
                    if ($finding->status !== HealthStatus::Green) {
                        $findings[] = new RuntimeSafetyFinding(
                            category : 'health',
                            severity : $finding->status === HealthStatus::Red ? RuntimeSafetyFinding::SEVERITY_CRITICAL : RuntimeSafetyFinding::SEVERITY_WARNING,
                            component: 'Application/Filesystem',
                            message  : sprintf('Filesystem Health [%s]: %s', $finding->check, $finding->message),
                        );
                    }
                }
            }
        } catch (Throwable $e) {
            $findings[] = new RuntimeSafetyFinding(
                category : 'health',
                severity : RuntimeSafetyFinding::SEVERITY_CRITICAL,
                component: 'Application/Filesystem',
                message  : sprintf('Filesystem Health Check Failed: %s', $e->getMessage()),
            );
        }

        return $findings;
    }
}
