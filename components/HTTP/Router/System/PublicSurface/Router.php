<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\PublicSurface;

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
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Main Router implementation.
 */
final readonly class Router implements RouterRuntimeInterface
{
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
     * @throws Throwable
     */
    public function resolve(ServerRequest $request) : ResponseInterface
    {
        try {
            return $this->kernel->handle(request: $request);
        } catch (Throwable $exception) {
            // Handle routing exceptions with error factory or fallback
            throw $exception;
        }
    }

    public function getRouteByName(string $name) : RouteDefinition
    {
        return $this->httpRequestRouter->getByName(name: $name);
    }

    public function allRoutes() : array
    {
        return $this->httpRequestRouter->allRoutes();
    }

    public function getTrace() : RouterTrace|null
    {
        return $this->httpRequestRouter->getTrace();
    }

    public function loadRoutes(string $routesPath, string $cacheDir = '') : void
    {
        if ($this->dslRouter === null || $this->groupStack === null || $this->routeRegistry === null) {
            throw new LogicException(message: 'Router must be constructed with DSL router, group stack and registry to use loadRoutes()');
        }

        $registry = $this->routeRegistry;

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
