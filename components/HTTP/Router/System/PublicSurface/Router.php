<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\PublicSurface;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\Capabilities\RouteGroup\RouteGroupRegistrar;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchResult;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchRoute;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\Registrar;
use Avax\Components\HTTP\Router\System\Foundation\Failure\RouterFailure;
use Override;

final class Router implements RouterInterface, RouterRuntimeInterface
{
    /** @var list<string|callable> */
    private array $globalMiddleware = [];

    public function __construct(
        private readonly ResolveCallable $callableResolver,
        private readonly RouteCollection $routeCollection,
        private readonly MatchRoute      $matchRoute,
        /**
         * Base URI for absolute URL generation.
         * Configure this in production — the default is for development only.
         */
        private readonly string          $baseUri = 'http://localhost',
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

        $url = $this->substituteRouteParams($route->uri(), $parameters, $name);

        if ($absolute) {
            $url = $this->baseUri . ($url[0] !== '/' ? '/' : '') . $url;
        }

        return $url;
    }

    /** @param array<string, mixed> $parameters */
    private function substituteRouteParams(string $uri, array $parameters, string $name) : string
    {
        $url = (string) preg_replace_callback(
            '/\{(\w+)\}/',
            static function (array $matches) use ($parameters, $name) : string {
                $param = $matches[1];
                if (! array_key_exists($param, $parameters)) {
                    throw new RouterFailure(
                        sprintf("Missing required parameter '%s' for route '%s'", $param, $name),
                    );
                }

                return (string) $parameters[$param];
            },
            $uri,
        );

        // Append extra parameters as query string
        $remaining = [];
        foreach ($parameters as $key => $value) {
            if (! preg_match('/\{' . $key . '\}/', $uri)) {
                $remaining[$key] = $value;
            }
        }

        if ($remaining !== []) {
            $query = http_build_query($remaining);
            if ($query !== '') {
                $url .= '?' . $query;
            }
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
            return $this->createMethodNotAllowedResponse($match->allowedMethods);
        }

        if ($match->isNotFound()) {
            return $this->createNotFoundResponse();
        }

        $route = $match->route;
        if ($route === null) {
            return $this->createNotFoundResponse();
        }

        $action        = $route->action();
        $allMiddleware = [...$this->globalMiddleware, ...$route->middleware()];

        $handler = function (RequestInterface $req) use ($action, $match) : ResponseInterface {
            if (is_callable($action)) {
                $params = $match->parameters ?? [];
                $result = $action($req, ...array_values($params));

                return $this->normalizeToResponse($result);
            }

            throw new RouterFailure('Invalid route action');
        };

        if ($allMiddleware === []) {
            return $handler($request);
        }

        // Build the middleware pipeline from inside out.
        // The last middleware wraps the handler, the first middleware is the outermost.
        $pipeline = $handler;
        foreach (array_reverse($allMiddleware) as $middleware) {
            if (is_string($middleware)) {
                $middleware = $this->resolveMiddleware($middleware);
            }

            $next     = $pipeline;
            $mw       = $middleware;
            $pipeline = static function (RequestInterface $req) use ($mw, $next) : ResponseInterface {
                $result = $mw($req, $next);
                if ($result instanceof ResponseInterface) {
                    return $result;
                }

                return $next($req);
            };
        }

        return $pipeline($request);
    }

    /**
     * Resolve middleware class-string through DI container via ResolveCallable.
     */
    private function resolveMiddleware(string $middlewareClass) : callable
    {
        return $this->callableResolver->resolve($middlewareClass);
    }

    /** @phpstan-return ResponseInterface */
    private function normalizeToResponse(mixed $result) : ResponseInterface
    {
        if ($result instanceof ResponseInterface) {
            return $result;
        }

        if (is_string($result)) {
            return Response::text($result);
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        return Response::text((string) $result);
    }

    /** @param list<RouteMethod> $allowedMethods */
    private function createMethodNotAllowedResponse(array $allowedMethods) : ResponseInterface
    {
        $methods = array_map(
            static fn (RouteMethod $m) => $m->value,
            $allowedMethods,
        );

        $response = Response::json(
            ['error' => 'Method Not Allowed', 'allowed' => $methods],
            405,
        );

        return $response->withHeader('Allow', implode(', ', $methods));
    }

    private function createNotFoundResponse() : ResponseInterface
    {
        return Response::json(['error' => 'Not Found'], 404);
    }
}
