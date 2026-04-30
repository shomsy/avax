<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\RegisterRoute;

use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteDefinition;

final class RegisterRoute
{
    public function execute(RouteCollection $collection, RouteDefinition $route) : void
    {
        $collection->add($route);
    }
}
