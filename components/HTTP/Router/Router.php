<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router;

/**
 * @phpstan-type RouterConfig array{
 *     httpRouter: HttpRequestRouter,
 *     kernel: RouterKernel,
 *     fallbackManager: RegisteredFallback,
 *     errorFactory: ErrorResponseFactory,
 *     dslRouter?: RouterInterface,
 *     groupStack?: RouteGroupFrames,
 *     routeRegistry?: RouteRegistry }
 */

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\Capabilities\RouterTrace\RouterTrace;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteRegistry;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Fallback\RegisteredFallback;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteFileRegistrar;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Groups\RouteGroupFrames;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Avax\Components\HTTP\Router\System\Flows\RunRoute\Responses\ErrorResponseFactory;
use Avax\Components\HTTP\Router\System\Flows\RunRoute\RouterKernel;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\DuplicateRouteException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\InvalidConstraintException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\ReservedRouteNameException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;
use Throwable;

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
    /**
     * @param HttpRequestRouter     $httpRequestRouter The request matcher
     * @param RouterKernel          $kernel            The execution kernel
     * @param RegisteredFallback    $fallbackManager   The fallback handler
     * @param ErrorResponseFactory  $errorFactory      The error response creator
     * @param RouterInterface|null  $dslRouter         The DSL router (optional)
     * @param RouteGroupFrames|null $groupStack        The group stack (optional)
     * @param RouteRegistry|null    $routeRegistry     The route registry (optional)
     */
    public function __construct(
        private HttpRequestRouter     $httpRequestRouter,
        private RouterKernel          $kernel,
        private RegisteredFallback    $fallbackManager,
        private ErrorResponseFactory  $errorFactory,
        private RouterInterface|null  $dslRouter = null,
        private RouteGroupFrames|null $groupStack = null,
        private RouteRegistry|null    $routeRegistry = null
    ) {}

    /**
     * @param ServerRequest $request
     *
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws InvalidConstraintException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     * @throws ReservedRouteNameException
     * @throws Throwable
     */
    public function resolve(ServerRequest $request) : ResponseInterface
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
                allowedMethods: $exception->allowedMethods
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
     * 3. Instantiates RouteFileRegistrar within scope for loading
     * 4. Delegates to RouteFileRegistrar::load() for actual file processing
     * 5. Handles route registration and cleanup automatically
     *
     * @param string $routesPath Path to the routes file
     * @param string $cacheDir   System directory for compiled routes (optional)
     *
     * @return void
     * @throws LogicException|ReservedRouteNameException If DSL router or group
     * @throws DuplicateRouteException
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
            $registrar = new RouteFileRegistrar(
                dslRouter    : $this->dslRouter,
                httpRouter   : $this->httpRequestRouter,
                groupStack   : $this->groupStack,
                routeRegistry: $registry
            );

            $registrar->load(path: $routesPath, cacheDir: $cacheDir);
        });
    }
}
