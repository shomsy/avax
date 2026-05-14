<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\HealthCheck;

use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use Throwable;

/**
 * CheckRouterHealth
 *
 * Verifies router/HTTP component runtime health:
 * - Route collection class available and functional
 * - Route definition class available and instantiable
 * - RouteCollection can accept, store, and clear routes
 * - RouteMethod enum resolves correctly
 */
final class CheckRouterHealth
{
    public function check() : HealthReport
    {
        $findings = [];
        $overall = HealthStatus::Green;

        // Check 1: Route collection and definition classes available
        if (! class_exists(RouteCollection::class) || ! class_exists(RouteDefinition::class)) {
            return new HealthReport(
                findings: [new HealthFinding('router.autoload', HealthStatus::Red, 'Router classes not loaded')],
                overall : HealthStatus::Red,
            );
        }

        $findings[] = new HealthFinding('router.autoload', HealthStatus::Green, 'Router classes loaded');

        // Check 2: RouteCollection is functional
        try {
            $collection = new RouteCollection();

            // Verify starts empty
            if ($collection->all() !== [] || $collection->hasFallback()) {
                $findings[] = new HealthFinding('router.collection', HealthStatus::Red, 'RouteCollection has unexpected initial state');

                return new HealthReport(findings: $findings, overall: HealthStatus::Red);
            }

            // Add a route and verify
            $route = new RouteDefinition(
                method: RouteMethod::GET,
                uri   : '/health-probe',
                action: static fn () => 'ok',
                name  : 'health_probe',
            );
            $collection->add($route);

            if (count($collection->all()) !== 1) {
                $findings[] = new HealthFinding('router.collection', HealthStatus::Red, 'RouteCollection failed to store route');

                return new HealthReport(findings: $findings, overall: HealthStatus::Red);
            }

            $byName = $collection->getByName('health_probe');
            if ($byName === null || $byName->uri() !== '/health-probe') {
                $findings[] = new HealthFinding('router.collection', HealthStatus::Red, 'RouteCollection named lookup failed');

                return new HealthReport(findings: $findings, overall: HealthStatus::Red);
            }

            // Verify fallback
            $collection->setFallback($route);
            if (! $collection->hasFallback()) {
                $findings[] = new HealthFinding('router.collection', HealthStatus::Red, 'RouteCollection fallback failed');

                return new HealthReport(findings: $findings, overall: HealthStatus::Red);
            }

            // Clear and verify
            $collection->clear();
            if ($collection->all() !== [] || $collection->hasFallback()) {
                $findings[] = new HealthFinding('router.collection', HealthStatus::Red, 'RouteCollection clear failed');

                return new HealthReport(findings: $findings, overall: HealthStatus::Red);
            }

            $findings[] = new HealthFinding('router.collection', HealthStatus::Green, 'RouteCollection is functional');
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('router.collection', HealthStatus::Red, sprintf('RouteCollection check failed: %s', $e->getMessage()));
            $overall    = HealthStatus::Red;
        }

        // Check 3: RouteMethod enum resolves
        try {
            $getMethod = RouteMethod::fromString('GET');
            if ($getMethod !== RouteMethod::GET) {
                $findings[] = new HealthFinding('router.method', HealthStatus::Red, 'RouteMethod::fromString returned unexpected value');
                if ($overall === HealthStatus::Green) {
                    $overall = HealthStatus::Yellow;
                }
            } else {
                $findings[] = new HealthFinding('router.method', HealthStatus::Green, 'RouteMethod enum resolves correctly');
            }
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('router.method', HealthStatus::Yellow, sprintf('RouteMethod check failed: %s', $e->getMessage()));
            if ($overall === HealthStatus::Green) {
                $overall = HealthStatus::Yellow;
            }
        }

        return new HealthReport(findings: $findings, overall: $overall);
    }
}
