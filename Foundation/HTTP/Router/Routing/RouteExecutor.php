<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Request\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;

/**
 * Executes a matched route by invoking the controller and returning the response.
 *
 * This class encapsulates the execution logic, separating it from matching.
 */
final readonly class RouteExecutor
{
    private ControllerDispatcher $controllerDispatcher;

    public function __construct(
        ControllerDispatcher $controllerDispatcher
    )
    {
        $this->controllerDispatcher = $controllerDispatcher;
    }

    /**
     * Executes the route action and returns the response.
     *
     * @param RouteDefinition $route   The matched route definition.
     * @param Request         $request The HTTP request with injected parameters.
     *
     * @return ResponseInterface The response from the controller.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function execute(RouteDefinition $route, Request $request) : ResponseInterface
    {
        return $this->controllerDispatcher->dispatch(
            action : $route->action,
            request: $request
        );
    }
}
