<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\ListRoutes;

use Avax\Framework\System\Capabilities\RouteIntelligence\RouteAnalyzer;
use Avax\Framework\System\Capabilities\RouteIntelligence\RouteInfo;

final readonly class ListRoutes
{
    public function __construct(
        private RouteAnalyzer $analyzer = new RouteAnalyzer,
    ) {}

    /**
     * @param list<RouteInfo> $routes
     */
    public static function printTable(array $routes) : void
    {
        if (empty($routes)) {
            echo "No routes registered.\n";

            return;
        }

        $methodWidth  = 7;
        $patternWidth = 40;
        $handlerWidth = 50;
        $middlewareWidth = 30;

        $header = sprintf(
            "%-{$methodWidth}s | %-{$patternWidth}s | %-{$handlerWidth}s | %s\n",
            'Method',
            'URI',
            'Handler',
            'Middleware',
        );

        echo $header;
        echo str_repeat('-', strlen($header)) . "\n";

        foreach ($routes as $route) {
            $method  = $route->method;
            $pattern = $route->pattern;
            $handler = strlen($route->handler) > $handlerWidth
                ? substr($route->handler, 0, $handlerWidth - 3) . '...'
                : $route->handler;
            $middleware = implode(', ', $route->middleware);
            if (strlen($middleware) > $middlewareWidth) {
                $middleware = substr($middleware, 0, $middlewareWidth - 3) . '...';
            }

            echo sprintf(
                "%-{$methodWidth}s | %-{$patternWidth}s | %-{$handlerWidth}s | %s\n",
                $method,
                $pattern,
                $handler,
                $middleware,
            );
        }

        echo "\nTotal: " . count($routes) . " routes\n";
    }

    public function loadRoutes(array $routes) : self
    {
        $this->analyzer->setRoutes($routes);

        return $this;
    }

    /**
     * @return list<RouteInfo>
     */
    public function list() : array
    {
        return $this->analyzer->routes();
    }
}
