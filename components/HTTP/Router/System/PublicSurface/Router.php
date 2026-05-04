<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\PublicSurface;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchRoute;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\Registrar;
use Avax\Components\HTTP\Router\System\Foundation\Failure\RouterFailure;
use Override;

final readonly class Router implements RouterInterface
{
    private RouteCollection $routeCollection;

    private MatchRoute $matchRoute;

    public function __construct()
    {
        $this->routeCollection = new RouteCollection();
        $this->matchRoute      = new MatchRoute();
    }

    #[Override]
    public function get(string $path, mixed $action): Registrar
    {
        return $this->addRoute('GET', $path, $action);
    }

    #[Override]
    public function post(string $path, mixed $action): Registrar
    {
        return $this->addRoute('POST', $path, $action);
    }

    #[Override]
    public function put(string $path, mixed $action): Registrar
    {
        return $this->addRoute('PUT', $path, $action);
    }

    #[Override]
    public function patch(string $path, mixed $action): Registrar
    {
        return $this->addRoute('PATCH', $path, $action);
    }

    #[Override]
    public function delete(string $path, mixed $action): Registrar
    {
        return $this->addRoute('DELETE', $path, $action);
    }

    #[Override]
    public function options(string $path, mixed $action): Registrar
    {
        return $this->addRoute('OPTIONS', $path, $action);
    }

    #[Override]
    public function head(string $path, mixed $action): Registrar
    {
        return $this->addRoute('HEAD', $path, $action);
    }

    private function addRoute(string $method, string $path, mixed $action): Registrar
    {
        $route = new RouteDefinition(new RouteMethod($method), $path, $action);
        $this->routeCollection->add($route);
        return new Registrar($route);
    }

    #[Override]
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
