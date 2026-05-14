<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Request\System\Capabilities\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchRoute;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;

final readonly class MatchHttpRoute
{
    private MatchRoute $matchRoute;

    public function __construct(MatchRoute $matchRoute)
    {
        $this->matchRoute = $matchRoute;
    }

    /**
     * @throws MethodNotAllowedException When the HTTP method is not allowed
     * @throws RouteNotFoundException When the route is not found
     */
    public function match(RegisteredHttpRoutes $registeredHttpRoutes, ServerRequest $serverRequest): MatchedHttpRoute
    {
        $routeCollection = new RouteCollection();
        foreach ($registeredHttpRoutes->routesByMethod() as $method => $definitions) {
            foreach ($definitions as $definition) {
                $routeCollection->add($definition);
            }
        }

        $match = $this->matchRoute->execute(routeCollection: $routeCollection, request: $serverRequest);

        if ($match->isMatch() && $match->route !== null) {
            return new MatchedHttpRoute(
                routeDefinition: $match->route,
                serverRequest  : $serverRequest,
            );
        }

        $allowedMethods = $this->allowedMethodsFor(
            routeCollection: $routeCollection,
            serverRequest  : $serverRequest,
        );

        if ($allowedMethods !== []) {
            throw new MethodNotAllowedException('Method '.$serverRequest->getMethod().' is not allowed.');
        }

        throw new RouteNotFoundException('Route '.$serverRequest->getUri()->getPath().' not found.');
    }

    /**
     * @return array<int, string>
     */
    private function allowedMethodsFor(RouteCollection $routeCollection, ServerRequest $serverRequest): array
    {
        $allowedMethods = [];
        $path = $serverRequest->getUri()->getPath();

        foreach ($routeCollection->all() as $routeDefinition) {
            if ($routeDefinition->uri() === $path) {
                $allowedMethods[] = $routeDefinition->method()->value;
            }
        }

        $allowedMethods = array_values(array_unique(array: $allowedMethods));
        sort($allowedMethods);

        return $allowedMethods;
    }
}
