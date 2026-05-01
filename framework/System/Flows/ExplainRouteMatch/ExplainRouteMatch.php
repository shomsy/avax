<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ExplainRouteMatch;

use Avax\Framework\System\Capabilities\RouteIntelligence\RouteAnalyzer;

final readonly class ExplainRouteMatch
{
    public function __construct(
        private RouteAnalyzer $analyzer = new RouteAnalyzer(),
    ) {}

    public function loadRoutes(array $routes) : self
    {
        $this->analyzer->setRoutes($routes);

        return $this;
    }

    public function explain(string $method, string $path) : string
    {
        $matched = $this->analyzer->explainMatch($method, $path);

        if ($matched === null) {
            return "No route matches {$method} {$path}";
        }

        $lines = [
            "Route: {$method} {$path}",
            "Matched: {$matched->method} {$matched->pattern}",
            "Handler: {$matched->handler}",
        ];

        if (! empty($matched->middleware)) {
            $lines[] = 'Middleware: ' . implode(', ', $matched->middleware);
        }

        $params = $matched->parameters();
        if (! empty($params)) {
            $lines[] = 'Parameters: ' . implode(', ', $params);
        }

        return implode("\n", $lines);
    }
}
