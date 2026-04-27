<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\PublicSurface;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;

/**
 * Represents the runtime responsibilities of the router.
 */
interface RouterRuntimeInterface
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function resolve(ServerRequest $request) : ResponseInterface;

    public function getRouteByName(string $name) : RouteDefinition;

    /**
     * @return array<string, RouteDefinition[]>
     */
    public function allRoutes() : array;
}
