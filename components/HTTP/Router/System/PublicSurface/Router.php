<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\PublicSurface;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteDefinition;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchRoute;
use Avax\Components\HTTP\Router\System\Foundation\Failure\RouterFailure;

final readonly class Router implements RouterInterface
{
    private RouteCollection $routeCollection;

    private MatchRoute $matchRoute;

    public function __construct()
    {
        $this->routeCollection = new RouteCollection();
        $this->matchRoute      = new MatchRoute();
    }

    public function get(string $u, $a): void
    {
        $this->routeCollection->add(new RouteDefinition(new RouteMethod('GET'), $u, $a));
    }

    public function post(string $u, $a): void
    {
        $this->routeCollection->add(new RouteDefinition(new RouteMethod('POST'), $u, $a));
    }

    public function dispatch(RequestInterface $request) : ResponseInterface
    {
        $route = $this->matchRoute->execute($this->routeCollection, $request);
        if (! $route instanceof RouteDefinition) {
            throw new RouterFailure('Route not found');
        }

        $a = $route->action();

        return is_callable($a) ? $a($request) : throw new RouterFailure('Invalid action');
    }
}
