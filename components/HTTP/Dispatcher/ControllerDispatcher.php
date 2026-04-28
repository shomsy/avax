<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Dispatcher;

use Avax\Components\HTTP\Request\Request as RequestDto;
use Avax\Components\HTTP\Request\RequestDtoFactory;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Response\Response;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

/**
 * Dispatches a controller or callable based on the route action.
 * Supports:
 * - Invokable classes
 * - [ControllerClass::class, 'method']
 * - Callable (e.g., anonymous functions)
 */
final readonly class ControllerDispatcher
{
    public function __construct(private ContainerInterface $container) {}

    /**
     * @param callable|array|string $action  The route's target action
     * @param ServerRequest         $request The PSR-7 HTTP request
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function dispatch(callable|array|string $action, ServerRequest $request) : ResponseInterface
    {
        return match (true) {
            is_callable(value: $action) => $this->dispatchCallable(callable: $action, request: $request),
            is_array(value: $action)    => $this->dispatchControllerAndMethod(action: $action, request: $request),
            is_string(value: $action)   => $this->dispatchInvokableController(controller: $action, request: $request),
            default                     => throw new InvalidArgumentException(message: 'Invalid route action provided.')
        };
    }

    private function dispatchCallable(callable $callable, ServerRequest $request) : ResponseInterface
    {
        $result = $callable($request);

        return $this->ensureResponse(result: $result, source: 'Callable');
    }

    private function ensureResponse(mixed $result, string $source) : ResponseInterface
    {
        if ($result === null) {
            return Response::text(content: "{$source} returned null");
        }

        if (is_string(value: $result)) {
            return Response::text(content: $result);
        }

        if (! $result instanceof ResponseInterface) {
            throw new RuntimeException(message: "{$source} must return a ResponseInterface.");
        }

        return $result;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function dispatchControllerAndMethod(array $action, ServerRequest $request) : ResponseInterface
    {
        if (count(value: $action) !== 2) {
            throw new InvalidArgumentException(message: 'Controller action must be [Class, "method"]');
        }

        [$controller, $method] = $action;

        if (! class_exists(class: $controller)) {
            throw new RuntimeException(message: "Controller class '{$controller}' not found.");
        }

        $instance = $this->resolveController(className: $controller);

        if (! method_exists(object_or_class: $instance, method: $method)) {
            throw new RuntimeException(message: "Method '{$method}' not found in '{$controller}'.");
        }

        $reflection = new ReflectionMethod(objectOrMethod: $instance, method: $method);
        $arguments  = $this->resolveArguments(reflection: $reflection, request: $request);

        $result = $reflection->invokeArgs(object: $instance, args: $arguments);

        return $this->ensureResponse(result: $result, source: "Method {$method} in {$controller}");
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function resolveController(string $className) : object
    {
        if ($this->container->has(id: $className)) {
            return $this->container->get(id: $className);
        }

        if (class_exists(class: $className)) {
            return new $className;
        }

        throw new RuntimeException(message: "Unable to resolve controller class '{$className}'.");
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function resolveArguments(ReflectionMethod $reflection, ServerRequest $request) : array
    {
        $arguments = [];

        foreach ($reflection->getParameters() as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            if ($paramType instanceof ReflectionNamedType) {
                $typeName = $paramType->getName();

                if (is_a(object_or_class: $typeName, class: ServerRequest::class, allow_string: true)) {
                    $arguments[] = $request;
                    continue;
                }

                if (is_a(object_or_class: $typeName, class: RequestDto::class, allow_string: true)) {
                    $arguments[] = $this->resolveRequestDto(typeName: $typeName, request: $request);
                    continue;
                }

                if ($this->container->has(id: $typeName)) {
                    $arguments[] = $this->container->get(id: $typeName);
                    continue;
                }
            }

            $attributeValue = $request->getAttribute(name: $paramName);
            if ($attributeValue !== null) {
                $arguments[] = $attributeValue;
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
                continue;
            }

            throw new RuntimeException(
                message: sprintf(
                             'Unable to resolve parameter "%s" for method "%s" in "%s"',
                             $paramName,
                             $reflection->getName(),
                             $reflection->getDeclaringClass()->getName()
                         )
            );
        }

        return $arguments;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function resolveRequestDto(string $typeName, ServerRequest $request) : RequestDto
    {
        /** @var RequestDtoFactory $factory */
        $factory = $this->container->has(id: RequestDtoFactory::class)
            ? $this->container->get(id: RequestDtoFactory::class)
            : new RequestDtoFactory();

        return $factory->create(serverRequest: $request, requestClass: $typeName);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function dispatchInvokableController(string $controller, ServerRequest $request) : ResponseInterface
    {
        if (! class_exists(class: $controller)) {
            throw new RuntimeException(message: "Controller class '{$controller}' does not exist.");
        }

        $instance = $this->resolveController(className: $controller);

        if (! is_callable(value: $instance)) {
            throw new RuntimeException(message: "Controller class '{$controller}' must be invokable.");
        }

        $result = $instance($request);

        return $this->ensureResponse(result: $result, source: "Invokable controller {$controller}");
    }
}
