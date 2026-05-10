<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\MatchRoute;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;

final class MatchRoute
{
    public function execute(RouteCollection $routeCollection, RequestInterface $request) : ?RouteDefinition
    {
        foreach ($routeCollection->all() as $route) {
            if ($route->method()->value !== $request->getMethod()) {
                continue;
            }

            if ($this->matchUri($route->uri(), $request->getUri()->getPath())) {
                return $route;
            }
        }

        return null;
    }

    private function matchUri(string $pattern, string $path) : bool
    {
        return $pattern === $path;
    }
}
