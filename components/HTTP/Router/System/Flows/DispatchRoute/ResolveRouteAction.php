<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\DispatchRoute;

use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteDefinition;

final class ResolveRouteAction
{
    public function resolve(RouteDefinition $routeDefinition) : mixed
    {
        return $routeDefinition->action();
    }
}
