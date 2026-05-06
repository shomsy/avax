<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\DetectRouteConflict;

use Avax\Framework\System\Capabilities\RouteIntelligence\RouteAnalyzer;
use Avax\Framework\System\Capabilities\RouteIntelligence\RouteConflict;

final readonly class DetectRouteConflict
{
    public function __construct(
        private RouteAnalyzer $routeAnalyzer = new RouteAnalyzer(),
    ) {
    }

    /**
     * @param list<RouteConflict> $conflicts
     */
    public static function printReport(array $conflicts): int
    {
        if ($conflicts === []) {
            echo "\033[32mNo route conflicts detected.\033[0m\n";

            return 0;
        }

        foreach ($conflicts as $conflict) {
            $color = $conflict->isExact() ? '31' : '33';
            echo sprintf(
                "\033[%sm[%s] %s\033[0m\n",
                $color,
                strtoupper($conflict->type),
                $conflict->reason,
            );

            echo sprintf("  Route A: %s %s -> %s\n", $conflict->routeA->method, $conflict->routeA->pattern, $conflict->routeA->handler);
            echo sprintf("  Route B: %s %s -> %s\n", $conflict->routeB->method, $conflict->routeB->pattern, $conflict->routeB->handler);
            echo "\n";
        }

        echo 'Total: ' . count($conflicts) . " conflict(s)\n";

        return array_filter($conflicts, static fn ($c) => $c->isExact()) !== [] ? 1 : 0;
    }

    /**
     * @param list<array{method: string, path: string, handler: mixed, middleware?: list<string>}> $routes
     */
    public function loadRoutes(array $routes): self
    {
        $this->routeAnalyzer->setRoutes($routes);

        return $this;
    }

    /**
     * @return list<RouteConflict>
     */
    public function detect(): array
    {
        return [...$this->routeAnalyzer->detectConflicts(), ...$this->routeAnalyzer->findUnreachableRoutes()];
    }
}
