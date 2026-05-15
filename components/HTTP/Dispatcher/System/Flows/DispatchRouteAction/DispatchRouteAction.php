<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Dispatcher\System\Flows\DispatchRouteAction;

use Avax\Components\HTTP\Dispatcher\System\Capabilities\ActionResolution\ControllerResolver;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution\ArgumentResolver;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
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
        private ArgumentResolver   $argumentResolver,
        private CreateHttpResponse $createHttpResponse,
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public function execute(callable|array|string $action, ServerRequestInterface $serverRequest): ResponseInterface
    {
        return match (true) {
            is_callable($action) => $this->dispatchCallable($action, $serverRequest),
            is_array($action)    => $this->dispatchControllerAndMethod($action, $serverRequest),
            is_string($action)   => $this->dispatchInvokableController($action, $serverRequest),
            default              => throw new InvalidArgumentException('Invalid route action provided.')
        };
    }

    private function dispatchCallable(callable $callable, ServerRequestInterface $serverRequest): ResponseInterface
    {
        $result = $callable($serverRequest);

        return $this->ensureResponse($result, 'Callable');
    }

    private function ensureResponse(mixed $result, string $source): ResponseInterface
    {
        if ($result === null) {
            return $this->createHttpResponse->text(content: $source.' returned null');
        }

        if (is_string($result)) {
            return $this->createHttpResponse->text(content: $result);
        }

        if (! $result instanceof ResponseInterface) {
            throw new RuntimeException($source.' must return a ResponseInterface.');
        }

        return $result;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function dispatchControllerAndMethod(array $action, ServerRequestInterface $serverRequest): ResponseInterface
    {
        if (count($action) !== 2) {
            throw new InvalidArgumentException('Controller action must be [Class, "method"]');
        }

        [$controllerClass, $method] = $action;

        $instance = $this->controllerResolver->resolve($controllerClass);

        if (! method_exists($instance, $method)) {
            throw new RuntimeException(sprintf("Method '%s' not found in '%s'.", $method, $controllerClass));
        }

        $reflectionMethod = new ReflectionMethod($instance, $method);
        $arguments        = $this->argumentResolver->resolve($reflectionMethod, $serverRequest);

        $result = $reflectionMethod->invokeArgs($instance, $arguments);

        return $this->ensureResponse($result, sprintf('Method %s in %s', $method, $controllerClass));
    }

    /**
     * @throws RuntimeException
     */
    private function dispatchInvokableController(string $controllerClass, ServerRequestInterface $serverRequest): ResponseInterface
    {
        $instance = $this->controllerResolver->resolve($controllerClass);

        if (! is_callable($instance)) {
            throw new RuntimeException(sprintf("Controller class '%s' must be invokable.", $controllerClass));
        }

        $result = $instance($serverRequest);

        return $this->ensureResponse($result, 'Invokable controller '.$controllerClass);
    }
}
