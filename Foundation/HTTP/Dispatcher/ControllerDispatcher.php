<?php

declare(strict_types=1);

namespace Avax\HTTP\Dispatcher;

use Avax\HTTP\Request\Request;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
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
    /**
     * Constructs the class with a dependency injection container.
     *
     * @param ContainerInterface $container The container instance used for dependency injection.
     *
     * @return void
     */
    public function __construct(private ContainerInterface $container) {}

    /**
     * Dispatches a controller action or callable based on the route action definition.
     *
     * @param callable|array|string $action  The route's target action (controller, method, or callable).
     * @param Request               $request The PSR-7 compatible HTTP request instance.
     *
     * @return ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    /**
     * Dispatches a controller action or callable based on the route action definition.
     *
     * @param callable|array|string $action  The route's target action (controller, method, or callable).
     * @param Request               $request The PSR-7 compatible HTTP request instance.
     *
     * @return ResponseInterface
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function dispatch(callable|array|string $action, Request $request) : ResponseInterface
    {
        // Delegate to the appropriate handler based on action type
        // Evaluate the expression based on the provided $action input using the `match` expression.
        return match (true) {
            // If $action is callable (like a closure, anonymous function, or valid callable object),
            // invoke `dispatchCallable`, passing $action and the $request as arguments.
            is_callable(value: $action) => $this->dispatchCallable(callable: $action, request: $request),

            // If $action is an array (typically [ControllerClass, "method"] format),
            // invoke `dispatchControllerAndMethod`, passing $action and the $request.
            is_array(value: $action)    => $this->dispatchControllerAndMethod(action: $action, request: $request),

            // If $action is a string (usually indicating an invokable controller class name),
            // invoke `dispatchInvokableController`, passing the $action and $request.
            is_string(value: $action)   => $this->dispatchInvokableController(controller: $action, request: $request),

            // If none of the above conditions match, throw an exception because the action provided
            // is invalid or unsupported.
            default                     => throw new InvalidArgumentException(message: 'Invalid route action provided.')
        };
    }


    /**
     * Handles a directly callable action (e.g., anonymous function or Closure).
     *
     * @param callable $callable The callable to invoke.
     * @param Request  $request  The PSR-7 compatible HTTP request instance.
     *
     * @return ResponseInterface
     */
    private function dispatchCallable(callable $callable, Request $request) : ResponseInterface
    {
        // Passes the $request object to the provided callable function and
        // immediately returns the resulting ResponseInterface instance.
        return $callable($request);
    }

    /**
     * Handles an action that specifies a controller class and method.
     *
     * @param array   $action  [ControllerClass::class, 'method'].
     * @param Request $request The PSR-7 compatible HTTP request instance.
     *
     * @return ResponseInterface
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    private function dispatchControllerAndMethod(array $action, Request $request) : ResponseInterface
    {
        // Check if the `$action` array has exactly 2 elements ([Class, "method"] format).
        if (count(value: $action) !== 2) {
            // If not, throw an exception to indicate improper structure.
            throw new InvalidArgumentException(message: 'Controller action must be [Class, "method"]');
        }

        // Decompose the `$action` array into `$controller` (class) and `$method`.
        [$controller, $method] = $action;

        // Check if the `$controller` (class name) exists.
        if (! class_exists(class: $controller)) {
            // Throw an exception if the provided class does not exist.
            throw new RuntimeException(message: "Controller class '{$controller}' not found.");
        }

        // Resolve the controller object instance (either from the container or by instantiating it directly).
        $instance = $this->resolveController(className: $controller);

        // Check if the `method` exists in the resolved controller instance.
        if (! method_exists(object_or_class: $instance, method: $method)) {
            // Throw an exception if the method is not found in the class.
            throw new RuntimeException(message: "Method '{$method}' not found in '{$controller}'.");
        }

        // Create a new ReflectionMethod object to introspect the method's parameters and metadata.
        $reflection = new ReflectionMethod(objectOrMethod: $instance, method: $method);

        // Initialize an array to store resolved arguments for the method call.
        $arguments = [];

        // Loop through all parameters of the method.
        foreach ($reflection->getParameters() as $param) {
            // Get the name of the current parameter.
            $paramName = $param->getName();
            // Get the parameter's type (if declared).
            $paramType = $param->getType();

            // Check if the parameter type is a named type (not union or mixed).
            if ($paramType instanceof ReflectionNamedType) {
                // Get the name of the type (e.g., class or scalar type).
                $typeName = $paramType->getName();

                // If the type corresponds to a class that is a `Request` (or extends it).
                if (is_a(object_or_class: $typeName, class: Request::class, allow_string: true)) {
                    // Inject the `$request` instance as the value for this parameter.
                    $arguments[] = $request;
                    continue;
                }

                // Check if the type name is available in the dependency injection container.
                if ($this->container->has(id: $typeName)) {
                    // Fetch the dependency from the container and add it to the arguments array.
                    $arguments[] = $this->container->get(id: $typeName);
                    continue;
                }
            }

            // Attempt to resolve the parameter using a route attribute (from the `$request` object).
            // For example, if the parameter name matches a route placeholder.
            $attributeValue = $request->getAttribute(name: $paramName);
            if ($attributeValue !== null) {
                // Add the attribute value to the arguments array if found.
                $arguments[] = $attributeValue;
                continue;
            }

            // Check if the parameter has a default value provided in the method signature.
            if ($param->isDefaultValueAvailable()) {
                // Use the default value for the parameter and add it to the arguments array.
                $arguments[] = $param->getDefaultValue();
                continue;
            }

            // If the parameter cannot be resolved, throw an exception with detailed information.
            throw new RuntimeException(
                message: "Unable to resolve parameter '{$paramName}' for method '{$method}' in '{$controller}'"
            );
        }

        // Invoke the controller's method with the resolved arguments using reflection.
        return $reflection->invokeArgs(object: $instance, args: $arguments);
    }

    /**
     * Resolves a controller instance using the DI container.
     *
     * @param string $className The fully qualified name of the controller class.
     *
     * @return object
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function resolveController(string $className) : object
    {
        if ($this->container->has(id: $className)) {
            return $this->container->get(id: $className);
        }

        if (class_exists(class: $className)) {
            return new $className();
        }

        throw new RuntimeException(message: "Unable to resolve controller class '{$className}'.");
    }

    /**
     * Handles an action represented by an invokable controller.
     *
     * @param string  $controller The fully qualified name of the invokable controller class.
     * @param Request $request    The PSR-7 compatible HTTP request instance.
     *
     * @return ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function dispatchInvokableController(string $controller, Request $request) : ResponseInterface
    {
        // Check if the specified controller class exists.
        // If the class is not found, throw a RuntimeException with a descriptive error message.
        if (! class_exists(class: $controller)) {
            throw new RuntimeException(message: "Controller class '{$controller}' does not exist.");
        }

        // Instantiate the specified controller class by resolving it from the container or creating it directly.
        // This ensures the controller instance is properly resolved, respecting dependency injection rules.
        $instance = $this->resolveController(className: $controller);

        // Check if the resolved controller instance is callable (i.e., it must be an invokable class).
        // If the controller is not callable, throw a RuntimeException indicating the issue.
        if (! is_callable(value: $instance)) {
            throw new RuntimeException(message: "Controller class '{$controller}' must be invokable.");
        }

        // If the controller is valid and invokable, call it and pass the incoming request as an argument.
        // The return value of the controller (usually a Response object) is returned as the method's result.
        return $instance($request);
    }
}