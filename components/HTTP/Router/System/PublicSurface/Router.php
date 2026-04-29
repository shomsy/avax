<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\PublicSurface;

use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteDefinition;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchRoute;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\Foundation\Failure\RouterFailure;

final class Router implements RouterInterface {
    private RouteCollection $routes;
    private MatchRoute $matcher;
    public function __construct() { $this->routes = new RouteCollection(); $this->matcher = new MatchRoute(); }
    public function get(string $u, $a): void { $this->routes->add(new RouteDefinition(new RouteMethod('GET'), $u, $a)); }
    public function post(string $u, $a): void { $this->routes->add(new RouteDefinition(new RouteMethod('POST'), $u, $a)); }
    public function dispatch(RequestInterface $r): ResponseInterface {
        $route = $this->matcher->execute($this->routes, $r);
        if (!$route) throw new RouterFailure("Route not found");
        $a = $route->action();
        return is_callable($a) ? $a($r) : throw new RouterFailure("Invalid action");
    }
}
