<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ExplainRouteMatch;

use Avax\Framework\System\Capabilities\RouteIntelligence\RouteAnalyzer;
use Avax\Framework\System\Capabilities\RouteIntelligence\RouteInfo;

final readonly class ExplainRouteMatch
{
    public function __construct(
        private RouteAnalyzer $routeAnalyzer = new RouteAnalyzer(),
    ) {
    }

    public function loadRoutes(array $routes): self
    {
        $this->routeAnalyzer->setRoutes($routes);

        return $this;
    }

    public function explain(string $method, string $path): string
    {
        $matched = $this->routeAnalyzer->explainMatch($method, $path);

        if (! $matched instanceof RouteInfo) {
            return sprintf('No route matches %s %s', $method, $path);
        }

        $lines = [
            sprintf('Route: %s %s', $method, $path),
            sprintf('Matched: %s %s', $matched->method, $matched->pattern),
            'Handler: ' . $matched->handler,
        ];

        if ($matched->middleware !== []) {
            $lines[] = 'Middleware: ' . implode(', ', $matched->middleware);
        }

        $params = $matched->parameters();
        if ($params !== []) {
            $lines[] = 'Parameters: ' . implode(', ', $params);
        }

        return implode("\n", $lines);
    }
}
