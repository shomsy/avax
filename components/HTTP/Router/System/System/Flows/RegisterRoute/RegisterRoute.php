<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\System\Flows\RegisterRoute;

use Avax\Components\HTTP\Router\System\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\System\Capabilities\RouteDefinition\RouteDefinition;

final class RegisterRoute
{
    public function execute(RouteCollection $routeCollection, RouteDefinition $routeDefinition) : void
    {
        $routeCollection->add($routeDefinition);
    }
}
