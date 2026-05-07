<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition;

use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;

final readonly class RouteDefinition
{
    public function __construct(
        private RouteMethod $method,
        private string      $uri,
        private mixed       $action,
    ) {}

    public function method() : RouteMethod
    {
        return $this->method;
    }

    public function uri() : string
    {
        return $this->uri;
    }

    public function action() : mixed
    {
        return $this->action;
    }
}
