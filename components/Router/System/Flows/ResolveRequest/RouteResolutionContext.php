<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Flows\ResolveRequest;

use Avax\Components\Router\System\Capabilities\RouteDefinition\RouteDefinition;

/**
 * Encapsulates the state and results of a route resolution attempt.
 */
final class RouteResolutionContext
{
    public function __construct(
        public readonly string       $method,
        public readonly string       $path,
        public readonly string       $domain,
        private array                $parameters = [],
        private RouteDefinition|null $route = null
    ) {}

    public function matched(RouteDefinition $route, array $parameters = []) : void
    {
        $this->route      = $route;
        $this->parameters = $parameters;
    }

    public function isResolved() : bool { return $this->route !== null; }

    public function route() : RouteDefinition|null { return $this->route; }

    public function parameters() : array { return $this->parameters; }
}
