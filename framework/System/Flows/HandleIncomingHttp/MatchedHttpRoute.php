<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;

final readonly class MatchedHttpRoute
{
    public function __construct(
        private RouteDefinition $routeDefinition,
        private ServerRequest   $serverRequest,
    ) {}

    public function route() : RouteDefinition
    {
        return $this->routeDefinition;
    }

    public function request() : ServerRequest
    {
        return $this->serverRequest;
    }
}
