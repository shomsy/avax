<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\RunApplication;

use Avax\Components\Application\Container\System\Container;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ActionResolution\ControllerResolver;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution\ArgumentResolver;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Response\ResponseFactory;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;
use Avax\Components\Operations\Observability\System\Capabilities\MetricsCollector\MetricsCollector;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\ResponseNormalization\NormalizeControllerResult;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Flows\HandleIncomingHttp\MatchedHttpRoute;
use Avax\Framework\System\Flows\HandleIncomingHttp\MatchHttpRoute;
use Avax\Framework\System\Flows\HandleIncomingHttp\ReadIncomingHttpRequest;
use Avax\Framework\System\Flows\HandleIncomingHttp\RegisteredHttpRoutes;
use Avax\Framework\System\Flows\HandleIncomingHttp\RouteFacadeContainer;
use Closure;
use Psr\Http\Message\ResponseInterface;
use ReflectionMethod;
use RuntimeException;
use Throwable;

/**
 * RunApplication — Dispatches routes with V4 response normalization.
 *
 * Unlike the existing RunHttpRoute (which requires ResponseInterface returns),
 * this flow normalizes controller results:
 * - string -> text response
 * - array -> JSON response
 * - DataObject -> JSON response
 * - ResponseInterface -> as-is
 *
 * This is the V4-01 boundary between controller invocation and HTTP response.
 */
final readonly class RunApplication
{
    private ReadIncomingHttpRequest $readRequest;

    private MatchHttpRoute $matchRoute;

    private NormalizeControllerResult $normalizer;

    private ResponseFactory $responseFactory;

    private ControllerResolver $controllerResolver;

    private ArgumentResolver $argumentResolver;

    public function __construct(
        private ?MetricsCollector $metricsCollector = null,
    )
    {
        $this->readRequest = new ReadIncomingHttpRequest();
        $this->matchRoute = new MatchHttpRoute();
        $this->normalizer = new NormalizeControllerResult();
        $this->responseFactory = new ResponseFactory();

        // Build resolution pipeline
        $container = new RouteFacadeContainer();
        $this->controllerResolver = new ControllerResolver(container: clone $container);
        $this->argumentResolver = new ArgumentResolver(container: clone $container);
    }

    public function handle(
        RuntimeRequest $runtimeRequest,
        RegisteredHttpRoutes $routes,
    ): ResponseInterface {
        $this->metricsCollector?->incrementCounter(name: 'request.count');

        $startTime = microtime(true);

        $serverRequest = $this->readRequest->read(runtimeRequest: $runtimeRequest);

        try {
            $matchedRoute = $this->matchRoute->match(
                registeredHttpRoutes: $routes,
                serverRequest: $serverRequest,
            );

            $response = $this->dispatchRoute(
                route: $matchedRoute->route(),
                serverRequest: $serverRequest,
            );

            $this->recordLatency(startTime: $startTime);

            return $response;
        } catch (RouteNotFoundException) {
            $this->metricsCollector?->incrementCounter(name: 'request.error');
            $this->recordLatency(startTime: $startTime);

            if ($routes->hasFallback()) {
                $fallback = $routes->fallback();

                if ($fallback === null) {
                    return $this->responseFactory->createErrorResponse(
                        message: 'Route not found',
                        statusCode: 404,
                    );
                }

                return $this->dispatchFallback(fallback: $fallback, serverRequest: $serverRequest);
            }

            return $this->responseFactory->createErrorResponse(
                message: 'Route not found',
                statusCode: 404,
            );
        } catch (MethodNotAllowedException $e) {
            $this->metricsCollector?->incrementCounter(name: 'request.error');
            $this->recordLatency(startTime: $startTime);

            return $this->responseFactory->createErrorResponse(
                message: $e->getMessage(),
                statusCode: 405,
            );
        } catch (Throwable $e) {
            $this->metricsCollector?->incrementCounter(name: 'request.error');
            $this->recordLatency(startTime: $startTime);

            throw $e;
        }
    }

    private function recordLatency(float $startTime) : void
    {
        $latencyMs = (microtime(true) - $startTime) * 1000;
        $this->metricsCollector?->observeHistogram(name: 'request.latency', value: $latencyMs);
    }

    private function dispatchRoute(RouteDefinition $route, ServerRequest $serverRequest): ResponseInterface
    {
        $action = $route->action();

        $result = $this->executeAction(action: $action, serverRequest: $serverRequest);

        return $this->normalizer->normalize(result: $result);
    }

    /**
     * @param Closure|array<mixed>|string $fallback
     */
    private function dispatchFallback(Closure|array|string $fallback, ServerRequest $serverRequest): ResponseInterface
    {
        if (is_callable($fallback)) {
            $result = $fallback($serverRequest);
        } else {
            $result = $fallback;
        }

        return $this->normalizer->normalize(result: $result);
    }

    /**
     * @param callable|list<mixed>|string $action
     */
    private function executeAction(callable|array|string $action, ServerRequest $serverRequest): mixed
    {
        if (is_callable($action)) {
            return $action($serverRequest);
        }

        if (is_array($action)) {
            return $this->invokeControllerAndMethod($action, $serverRequest);
        }

        return $this->invokeController($action, $serverRequest);
    }

    /**
     * @param list<mixed> $action
     */
    private function invokeControllerAndMethod(array $action, ServerRequest $serverRequest): mixed
    {
        if (count($action) !== 2) {
            throw new RuntimeException('Controller action must be [Class, "method"]');
        }

        [$controllerClass, $method] = $action;

        $instance = $this->controllerResolver->resolve($controllerClass);

        if (! method_exists($instance, $method)) {
            throw new RuntimeException(sprintf("Method '%s' not found in '%s'.", $method, $controllerClass));
        }

        $reflectionMethod = new ReflectionMethod($instance, $method);
        $arguments = $this->argumentResolver->resolve($reflectionMethod, $serverRequest);

        return $reflectionMethod->invokeArgs($instance, $arguments);
    }

    private function invokeController(string $controllerClass, ServerRequest $serverRequest): mixed
    {
        $instance = $this->controllerResolver->resolve($controllerClass);

        if (! is_callable($instance)) {
            throw new RuntimeException(sprintf("Controller class '%s' must be invokable.", $controllerClass));
        }

        return $instance($serverRequest);
    }
}
