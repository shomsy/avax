<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\PublicSurface;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\Capabilities\ErrorResponseBuilding\BuildErrorResponse;
use Avax\Components\HTTP\Router\System\Capabilities\MiddlewarePipeline\BuildPipeline;
use Avax\Components\HTTP\Router\System\Capabilities\ResponseNormalization\NormalizeControllerResult;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\Capabilities\RouteGroup\RouteGroupRegistrar;
use Avax\Components\HTTP\Router\System\Capabilities\UrlBuilding\SubstituteRouteParameters;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchRoute;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\Registrar;
use Avax\Components\HTTP\Router\System\Foundation\Failure\RouterFailure;
use Override;

final class Router implements RouterInterface, RouterRuntimeInterface
{
    /** @var list<string|callable> */
    private array $globalMiddleware = [];

    public function __construct(
        private readonly RouteCollection           $routeCollection,
        private readonly MatchRoute                $matchRoute,
        private readonly BuildPipeline             $pipelineBuilder,
        private readonly BuildErrorResponse        $errorResponse,
        private readonly SubstituteRouteParameters $urlBuilder,
        private readonly NormalizeControllerResult $responseNormalizer,
        private readonly string                    $baseUri = 'http://localhost',
    ) {
    }

    #[Override]
    public function get(string $path, mixed $action) : Registrar
    {
        return $this->addRoute(RouteMethod::GET, $path, $action);
    }

    private function addRoute(RouteMethod $method, string $path, mixed $action) : Registrar
    {
        $route = new RouteDefinition(method: $method, uri: $path, action: $action);
        $this->routeCollection->add($route);

        return new Registrar($this->routeCollection, $route);
    }

    #[Override]
    public function post(string $path, mixed $action) : Registrar
    {
        return $this->addRoute(RouteMethod::POST, $path, $action);
    }

    #[Override]
    public function put(string $path, mixed $action) : Registrar
    {
        return $this->addRoute(RouteMethod::PUT, $path, $action);
    }

    #[Override]
    public function patch(string $path, mixed $action) : Registrar
    {
        return $this->addRoute(RouteMethod::PATCH, $path, $action);
    }

    #[Override]
    public function delete(string $path, mixed $action) : Registrar
    {
        return $this->addRoute(RouteMethod::DELETE, $path, $action);
    }

    #[Override]
    public function options(string $path, mixed $action) : Registrar
    {
        return $this->addRoute(RouteMethod::OPTIONS, $path, $action);
    }

    #[Override]
    public function head(string $path, mixed $action) : Registrar
    {
        return $this->addRoute(RouteMethod::HEAD, $path, $action);
    }

    #[Override]
    public function any(string $path, mixed $action) : Registrar
    {
        $registrar = $this->addRoute(RouteMethod::GET, $path, $action);
        $this->addRoute(RouteMethod::POST, $path, $action);
        $this->addRoute(RouteMethod::PUT, $path, $action);
        $this->addRoute(RouteMethod::PATCH, $path, $action);
        $this->addRoute(RouteMethod::DELETE, $path, $action);
        $this->addRoute(RouteMethod::OPTIONS, $path, $action);
        $this->addRoute(RouteMethod::HEAD, $path, $action);

        return $registrar;
    }

    #[Override]
    public function group(string $prefix, callable $groupFn, string|array|callable|null $middleware = null) : void
    {
        $prefix = rtrim($prefix, '/');

        $groupFn(new RouteGroupRegistrar(
            router: $this,
            prefix: $prefix,
            groupMiddleware: $middleware,
        ));
    }

    #[Override]
    public function fallback(mixed $handler) : void
    {
        $fallback = new RouteDefinition(
            method: RouteMethod::GET,
            uri   : '/__fallback__',
            action: $handler,
        );
        $this->routeCollection->setFallback($fallback);
    }

    #[Override]
    public function url(string $name, array $parameters = [], bool $absolute = false) : string
    {
        $route = $this->routeCollection->getByName($name);
        if ($route === null) {
            throw new RouterFailure(sprintf("Route '%s' is not registered", $name));
        }

        $url = $this->urlBuilder->substitute($route->uri(), $parameters, $name);

        if ($absolute) {
            $url = $this->urlBuilder->makeAbsolute($url);
        }

        return $url;
    }

    /**
     * Register global middleware that runs on every request.
     *
     * @param string|callable|list<string|callable> $middleware
     */
    public function use(string|array|callable $middleware) : void
    {
        $items                  = is_array($middleware) ? array_values($middleware) : [$middleware];
        $this->globalMiddleware = [...$this->globalMiddleware, ...$items];
    }

    #[Override]
    public function resolve(RequestInterface $request) : ResponseInterface
    {
        return $this->dispatch(request: $request);
    }

    #[Override]
    public function dispatch(RequestInterface $request) : ResponseInterface
    {
        $match = $this->matchRoute->execute($this->routeCollection, $request);

        if ($match->isMethodNotAllowed()) {
            return $this->errorResponse->methodNotAllowed($match->allowedMethods);
        }

        if ($match->isNotFound()) {
            return $this->errorResponse->notFound();
        }

        $route = $match->route;
        if ($route === null) {
            return $this->errorResponse->notFound();
        }

        $action        = $route->action();
        $allMiddleware = [...$this->globalMiddleware, ...$route->middleware()];

        $handler = function (RequestInterface $req) use ($action, $match) : ResponseInterface {
            if (is_callable($action)) {
                $params = $match->parameters ?? [];
                $result = $action($req, ...array_values($params));

                return $this->responseNormalizer->normalize($result);
            }

            throw new RouterFailure('Invalid route action');
        };

        return $this->pipelineBuilder->build($handler, $allMiddleware)($request);
    }
}
