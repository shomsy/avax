<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\DispatchRoute;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteDefinition;

final class DispatchRoute
{
    public function __construct(
        private ResolveRouteAction $resolver,
        private InvokeRouteAction $invoker,
    ) {}

    public function execute(RouteDefinition $route, RequestInterface $request) : ResponseInterface
    {
        $action = $this->resolver->resolve($route);

        return $this->invoker->invoke($action, $request);
    }
}
