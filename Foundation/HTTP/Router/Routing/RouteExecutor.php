<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Executes a matched route by invoking the controller and returning the response.
 *
 * This class encapsulates the execution logic, separating it from matching.
 */
final readonly class RouteExecutor
{
    public function __construct(
        private ControllerDispatcher $controllerDispatcher
    ) {}

    /**
     * Executes the route action and returns the response.
     *
     * @param RouteDefinition $route   The matched route definition.
     * @param Request         $request The HTTP request with injected parameters.
     *
     * @return ResponseInterface The response from the controller.
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function execute(RouteDefinition $route, Request $request) : ResponseInterface
    {
        return $this->controllerDispatcher->dispatch(
            action : $route->action,
            request: $request
        );
    }
}
