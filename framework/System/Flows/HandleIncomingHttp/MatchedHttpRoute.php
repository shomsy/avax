<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;

final readonly class MatchedHttpRoute
{
    public function __construct(
        private RouteDefinition $route,
        private ServerRequest $request,
    ) {}

    public function route() : RouteDefinition
    {
        return $this->route;
    }

    public function request() : ServerRequest
    {
        return $this->request;
    }
}
