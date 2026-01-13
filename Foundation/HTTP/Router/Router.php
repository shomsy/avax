<?php

declare(strict_types=1);

namespace Avax\HTTP\Router;

/**
 * @phpstan-type RouterConfig array{
 *     httpRouter: \Avax\HTTP\Router\Routing\HttpRequestRouter,
 *     kernel: \Avax\HTTP\Router\Kernel\RouterKernel,
 *     fallbackManager: \Avax\HTTP\Router\Support\FallbackManager,
 *     errorFactory: \Avax\HTTP\Router\Routing\ErrorResponseFactory,
 *     dslRouter?: \Avax\HTTP\Router\RouterInterface,
 *     groupStack?: \Avax\HTTP\Router\Routing\RouteGroupStack,
 *     routeRegistry?: \Avax\HTTP\Router\Support\RouteRegistry
 * }
 */

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Kernel\RouterKernel;
use Avax\HTTP\Router\Routing\ErrorResponseFactory;
use Avax\HTTP\Router\Routing\Exceptions\MethodNotAllowedException;
use Avax\HTTP\Router\Routing\Exceptions\RouteNotFoundException;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Routing\RouteGroupStack;
use Avax\HTTP\Router\Support\FallbackManager;
use Avax\HTTP\Router\Support\RouteRegistry;
use Avax\HTTP\Router\Tracing\RouterTrace;
use LogicException;
use Psr\Http\Message\ResponseInterface;

/**
 * Public API Contract: Runtime Router
 *
 * BC GUARANTEED: Core runtime interface for request resolution and error handling.
 *
 * Runtime router responsible for handling requests and delegating to the kernel.
 * Registration and DSL logic now live in {@see RouterDsl}, keeping this class focused on execution.
 *
 * @api
 */
final readonly class Router implements RouterRuntimeInterface
{
    public function __construct(
        private HttpRequestRouter    $httpRequestRouter,
        private RouterKernel         $kernel,
        private FallbackManager      $fallbackManager,
        private ErrorResponseFactory $errorFactory,
        private RouterInterface|null $dslRouter = null,
        private RouteGroupStack|null $groupStack = null,
        private RouteRegistry|null   $routeRegistry = null
    ) {}

    public function resolve(Request $request) : ResponseInterface
    {
        try {
            return $this->kernel->handle(request: $request);
        } catch (RouteNotFoundException $exception) {
            if ($this->fallbackManager->has()) {
                return $this->fallbackManager->invoke(request: $request);
            }

            return $this->errorFactory->createNotFoundResponse(method: $request->getMethod(), path: $request->getUri()->getPath());
        } catch (MethodNotAllowedException $exception) {
            return $this->errorFactory->createMethodNotAllowedResponse(
                method        : $request->getMethod(),
                path          : $request->getUri()->getPath(),
                allowedMethods: $exception->getAllowedMethods()
            );
        }
    }

    public function getRouteByName(string $name) : RouteDefinition
    {
        return $this->httpRequestRouter->getByName(name: $name);
    }

    /**
     * Dumps the registered routes map for diagnostics.
     *
     * @return array<string, RouteDefinition[]> The route map grouped by method.
     */
    public function dumpMap() : array
    {
        return $this->httpRequestRouter->allRoutes();
    }

    public function allRoutes() : array
    {
        return $this->httpRequestRouter->allRoutes();
    }

    /**
     * Gets the current trace data for debugging and profiling.
     *
     * Returns null if tracing is not enabled.
     */
    public function getTrace() : RouterTrace|null
    {
        return $this->httpRequestRouter->getTrace();
    }

    /**
     * Load routes with proper registry scoping (DSL facade).
     *
     * Human-grade DSL method that encapsulates the complex registry scoping
     * logic into a simple, readable API. This facade method provides the primary
     * entry point for route loading operations in the application.
     *
     * USAGE:
     * ```php
     * $router = new Router($httpRouter, $kernel, $fallbackManager, $errorFactory, $dslRouter, $groupStack, $registry);
     * $router->loadRoutes($routesPath, $cacheDir);
     * ```
     *
     * INTERNAL FLOW:
     * 1. Validates required dependencies (DSL router and group stack)
     * 2. Creates registry scoped closure for isolation
     * 3. Instantiates RouteRegistrar within scope for loading
     * 4. Delegates to RouteRegistrar::load() for actual file processing
     * 5. Handles route registration and cleanup automatically
     *
     * @param string $routesPath Path to the routes file
     * @param string $cacheDir   Cache directory for compiled routes (optional)
     *
     * @return void
     * @throws \LogicException|\Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException If DSL router or group
     *                                                                                         stack dependencies are
     *                                                                                         missing
     */
    public function loadRoutes(string $routesPath, string $cacheDir = '') : void
    {
        if ($this->dslRouter === null || $this->groupStack === null) {
            throw new LogicException(
                message: 'Router must be constructed with DSL router and group stack dependencies to use loadRoutes()'
            );
        }

        $registry = $this->routeRegistry ?? new RouteRegistry;

        $registry->scoped(callback: function () use ($registry, $routesPath, $cacheDir) : void {
            $registrar = new Bootstrap\RouteRegistrar(
                dslRouter    : $this->dslRouter,
                httpRouter   : $this->httpRequestRouter,
                groupStack   : $this->groupStack,
                routeRegistry: $registry
            );

            $registrar->load(path: $routesPath, cacheDir: $cacheDir);
        });
    }
}