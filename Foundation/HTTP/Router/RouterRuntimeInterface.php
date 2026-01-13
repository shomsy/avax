<?php

declare(strict_types=1);

namespace Avax\HTTP\Router;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Psr\Http\Message\ResponseInterface;

/**
 * Represents the runtime responsibilities of the router: resolving requests
 * and exposing runtime metadata used by helpers and tools.
 */
interface RouterRuntimeInterface
{
    /**
     * Resolves the request and returns a PSR-7 response.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function resolve(Request $request) : ResponseInterface;

    /**
     * Retrieves a route definition by name.
     */
    public function getRouteByName(string $name) : RouteDefinition;

    /**
     * Returns all registered routes grouped by HTTP method.
     *
     * @return array<string, RouteDefinition[]>
     */
    public function allRoutes() : array;
}
