<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\HealthCheck;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\CompiledDatabaseLifecycleRegistry;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\GlobalDatabaseLifecycleState;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use ReflectionClass;
use Throwable;

/**
 * CheckDatabaseHealth
 *
 * Verifies database component runtime health:
 * - Lifecycle registry is accessible
 * - Connection class is instantiable
 * - Compiled lifecycle registry is loadable
 */
final class CheckDatabaseHealth
{
    public function check() : HealthReport
    {
        $findings = [];
        $overall = HealthStatus::Green;

        // Check 1: Lifecycle registry accessible
        try {
            $registry = GlobalDatabaseLifecycleState::registry();
            $findings[] = new HealthFinding('database.lifecycle', HealthStatus::Green, 'Lifecycle registry accessible');
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('database.lifecycle', HealthStatus::Red, sprintf('Lifecycle registry unavailable: %s', $e->getMessage()));

            return new HealthReport(findings: $findings, overall: HealthStatus::Red);
        }

        // Check 2: DatabaseConnection is instantiable
        try {
            $class      = new ReflectionClass(DatabaseConnection::class);
            $findings[] = new HealthFinding('database.connection', HealthStatus::Green, 'DatabaseConnection class is loadable');
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('database.connection', HealthStatus::Red, sprintf('DatabaseConnection unavailable: %s', $e->getMessage()));
            $overall    = HealthStatus::Red;
        }

        // Check 3: Compiled lifecycle registry class loadable
        if (class_exists(CompiledDatabaseLifecycleRegistry::class)) {
            $findings[] = new HealthFinding('database.compiled', HealthStatus::Green, 'Compiled lifecycle registry available');
        } else {
            $findings[] = new HealthFinding('database.compiled', HealthStatus::Yellow, 'Compiled lifecycle registry class not loaded');
            if ($overall === HealthStatus::Green) {
                $overall = HealthStatus::Yellow;
            }
        }

        return new HealthReport(findings: $findings, overall: $overall);
    }
}
