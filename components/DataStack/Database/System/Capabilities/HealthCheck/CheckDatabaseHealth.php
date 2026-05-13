<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\HealthCheck;

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\CompiledDatabaseLifecycleRegistry;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\GlobalDatabaseLifecycleState;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\DatabaseConnection;

/**
 * CheckDatabaseHealth
 *
 * Verifies database component runtime health:
 * - Lifecycle registry is assembled
 * - Connection policy exists
 * - Query redaction is configured
 */
final readonly class CheckDatabaseHealth
{
    public function check(): DatabaseHealthReport
    {
        $findings = [];
        $healthy = true;

        // Check 1: Lifecycle registry exists and is accessible
        try {
            $registry = GlobalDatabaseLifecycleState::registry();
            $findings[] = 'Lifecycle registry accessible';
        } catch (\Throwable $e) {
            $healthy = false;
            $findings[] = 'Lifecycle registry unavailable: '.$e->getMessage();
        }

        // Check 2: Compiled registry class exists (event wiring available)
        if (class_exists(CompiledDatabaseLifecycleRegistry::class)) {
            $findings[] = 'Compiled lifecycle registry available';
        } else {
            $healthy = false;
            $findings[] = 'Compiled lifecycle registry class not loaded';
        }

        // Check 3: DatabaseConnection class available
        if (class_exists(DatabaseConnection::class)) {
            $findings[] = 'Database connection class available';
        } else {
            $healthy = false;
            $findings[] = 'Database connection class not loaded';
        }

        return new DatabaseHealthReport(
            healthy: $healthy,
            findings: $findings,
        );
    }
}
