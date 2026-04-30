<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Dispatcher\System\Flows\DispatchRouteAction;

use Avax\Components\HTTP\Dispatcher\System\Capabilities\ActionResolution\ControllerResolver;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution\ArgumentResolver;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;
use RuntimeException;

/**
 * DispatchRouteAction - Core flow for invoking route controllers.
 */
final readonly class DispatchRouteAction
{
    public function __construct(
        private ControllerResolver $controllerResolver,
        private ArgumentResolver $argumentResolver,
    ) {}

    public function execute(callable|array|string $action, ServerRequestInterface $request) : ResponseInterface
    {
        return match (true) {
            is_callable($action) => $this->dispatchCallable($action, $request),
            is_array($action)    => $this->dispatchControllerAndMethod($action, $request),
            is_string($action)   => $this->dispatchInvokableController($action, $request),
            default              => throw new InvalidArgumentException('Invalid route action provided.')
        };
    }

    private function dispatchCallable(callable $callable, ServerRequestInterface $request) : ResponseInterface
    {
        $result = $callable($request);

        return $this->ensureResponse($result, 'Callable');
    }

    private function dispatchControllerAndMethod(array $action, ServerRequestInterface $request) : ResponseInterface
    {
        if (count($action) !== 2) {
            throw new InvalidArgumentException('Controller action must be [Class, "method"]');
        }

        [$controllerClass, $method] = $action;

        $instance = $this->controllerResolver->resolve($controllerClass);

        if (! method_exists($instance, $method)) {
            throw new RuntimeException("Method '{$method}' not found in '{$controllerClass}'.");
        }

        $reflection = new ReflectionMethod($instance, $method);
        $arguments = $this->argumentResolver->resolve($reflection, $request);

        $result = $reflection->invokeArgs($instance, $arguments);

        return $this->ensureResponse($result, "Method {$method} in {$controllerClass}");
    }

    private function dispatchInvokableController(string $controllerClass, ServerRequestInterface $request) : ResponseInterface
    {
        $instance = $this->controllerResolver->resolve($controllerClass);

        if (! is_callable($instance)) {
            throw new RuntimeException("Controller class '{$controllerClass}' must be invokable.");
        }

        $result = $instance($request);

        return $this->ensureResponse($result, "Invokable controller {$controllerClass}");
    }

    private function ensureResponse(mixed $result, string $source) : ResponseInterface
    {
        if ($result === null) {
            return Response::text("{$source} returned null");
        }

        if (is_string($result)) {
            return Response::text($result);
        }

        if (! $result instanceof ResponseInterface) {
            throw new RuntimeException("{$source} must return a ResponseInterface.");
        }

        return $result;
    }
}
