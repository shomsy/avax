<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\System\Flows\DispatchRoute;

use Avax\Components\HTTP\Request\System\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\System\Capabilities\RouteDefinition\RouteDefinition;

final readonly class DispatchRoute
{
    public function __construct(
        private ResolveRouteAction $resolveRouteAction,
        private InvokeRouteAction  $invokeRouteAction,
    ) {}

    public function execute(RouteDefinition $routeDefinition, RequestInterface $request) : ResponseInterface
    {
        $action = $this->resolveRouteAction->resolve($routeDefinition);

        return $this->invokeRouteAction->invoke($action, $request);
    }
}
