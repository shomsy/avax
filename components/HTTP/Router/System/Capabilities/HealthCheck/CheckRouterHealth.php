<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\HealthCheck;

use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;

/**
 * CheckRouterHealth
 *
 * Verifies router/HTTP component runtime health:
 * - Route collection class available
 * - Route definition class available
 */
final readonly class CheckRouterHealth
{
    public function check(): RouterHealthReport
    {
        $findings = [];
        $healthy = true;

        // Check 1: Route collection available
        if (class_exists(RouteCollection::class)) {
            $findings[] = 'Route collection class available';
        } else {
            $healthy = false;
            $findings[] = 'Route collection class not loaded';
        }

        // Check 2: Route definition available
        $routeDefClass = 'Avax\\Components\\HTTP\\Router\\System\\Capabilities\\RouteDefinition\\RouteDefinition';
        if (class_exists($routeDefClass)) {
            $findings[] = 'Route definition class available';
        } else {
            $healthy = false;
            $findings[] = 'Route definition class not loaded';
        }

        return new RouterHealthReport(
            healthy: $healthy,
            findings: $findings,
        );
    }
}
