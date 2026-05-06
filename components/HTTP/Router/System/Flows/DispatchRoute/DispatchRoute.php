<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\DispatchRoute;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;

final readonly class DispatchRoute
{
    public function __construct(
        private ResolveRouteAction $resolveRouteAction,
        private InvokeRouteAction $invokeRouteAction,
    ) {
    }

    public function execute(RouteDefinition $routeDefinition, RequestInterface $request): ResponseInterface
    {
        $action = $this->resolveRouteAction->resolve($routeDefinition);

        return $this->invokeRouteAction->invoke($action, $request);
    }
}
