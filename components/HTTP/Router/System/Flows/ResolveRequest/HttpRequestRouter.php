<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\ResolveRequest;

use Avax\Components\Application\Text\System\PublicSurface\Pattern;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Arrhae;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\DuplicatePolicy;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteKey;
use Avax\Components\HTTP\Router\System\Capabilities\RouterTrace\RouterTrace;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Constraints\RouteConstraintValidator;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching\RouteMatcherInterface;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Request\RouteRequestInjector;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\DuplicateRouteException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\InvalidConstraintException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\ReservedRouteNameException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;

/**
 * HTTP ServerRequest Router Engine.
 */
final class HttpRequestRouter
{
    /**
     * @var array<string, array<string, RouteDefinition[]>>
     */
    private array $routes = [];

    /**
     * @var array<string, RouteDefinition>
     */
    private array $namedRoutes = [];

    /**
     * @var array<string, bool>
     */
    private array $routeKeys = [];

    private readonly RouterTrace|null         $trace;
    private readonly RouteMatcherInterface    $matcher;
    private readonly RouteConstraintValidator $constraintValidator;

    public function __construct(
        RouteConstraintValidator $constraintValidator,
        RouteMatcherInterface    $matcher,
        RouterTrace|null         $trace = null
    )
    {
        $this->constraintValidator = $constraintValidator;
        $this->matcher             = $matcher;
        $this->trace               = $trace;
    }

    public function getByName(string $name) : RouteDefinition
    {
        if (! isset($this->namedRoutes[$name])) {
            throw new RouteNotFoundException(message: "Named route [{$name}] not found.");
        }

        return $this->namedRoutes[$name];
    }

    public function hasNamedRoute(string $name) : bool
    {
        return isset($this->namedRoutes[$name]);
    }

    /**
     * @throws MethodNotAllowedException
     * @throws RouteNotFoundException
     * @throws ReservedRouteNameException
     * @throws InvalidConstraintException
     */
    public function resolve(ServerRequest $request) : RouteResolutionContext
    {
        $startTime = microtime(as_float: true);
        $path      = $request->getUri()->getPath();
        $method    = $request->getMethod();
        $host      = $request->getUri()->getHost();

        $resolutionPath = [
            ['timestamp' => date(format: 'H:i:s.u'), 'description' => "Started resolution for {$method} {$path} from {$host}"],
        ];

        $matchResult = $this->matcher->match(routes: $this->routes, request: $request);

        if ($matchResult === null) {
            $allowedMethods = $this->findAllowedMethodsForPath(path: $path);

            if ($allowedMethods !== []) {
                throw MethodNotAllowedException::for(method: $method, path: $path, allowedMethods: $allowedMethods);
            }

            throw RouteNotFoundException::for(method: $method, path: $path);
        }

        [$route, $matches] = $matchResult;

        $parameters = $this->extractParameters(matches: $matches);

        $request = RouteRequestInjector::injectExtractedParameters(
            request   : $request,
            defaults  : $route->defaults,
            parameters: $parameters
        );

        $this->constraintValidator->validate(route: $route, request: $request);

        $matchTime     = (microtime(as_float: true) - $startTime) * 1000;
        $matchedDomain = $route->domain ?? null;

        return RouteResolutionContext::success(
            route         : $route,
            parameters    : $parameters,
            matchedDomain : $matchedDomain,
            matchTimeMs   : $matchTime,
            resolutionPath: $resolutionPath
        );
    }

    private function findAllowedMethodsForPath(string $path) : array
    {
        $allowedMethods = [];

        foreach ($this->routes as $method => $pathsForMethod) {
            foreach ($pathsForMethod as $routesForPath) {
                foreach ($routesForPath as $route) {
                    if (Pattern::of(raw: $route->compiledPathRegex)->test(subject: $path)) {
                        $allowedMethods[] = $method;
                        break 2;
                    }
                }
            }
        }

        return (new Arrhae(items: $allowedMethods))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function extractParameters(array $matches) : array
    {
        return (new Arrhae(items: $matches))
            ->filter(callback: static fn (mixed $value, mixed $key) => ! is_int(value: $key))
            ->all();
    }

    public function allRoutes() : array
    {
        $flattened = [];
        foreach ($this->routes as $method => $pathsForMethod) {
            $flattened[$method] = [];
            foreach ($pathsForMethod as $routesForPath) {
                foreach ($routesForPath as $route) {
                    $flattened[$method][] = $route;
                }
            }
        }

        return $flattened;
    }

    /**
     * @throws ReservedRouteNameException
     * @throws DuplicateRouteException
     */
    public function registerRoute(string $method, string $path, callable|array|string $action) : void
    {
        $route = new RouteDefinition(
            method       : $method,
            path         : $path,
            action       : $action,
            middleware   : [],
            name         : null,
            constraints  : [],
            defaults     : [],
            domain       : null,
            attributes   : [],
            authorization: null
        );

        $this->add(route: $route);
    }

    /**
     * @throws DuplicateRouteException
     */
    public function add(RouteDefinition $route) : void
    {
        $routeKey  = RouteKey::fromRoute(route: $route);
        $keyString = $routeKey->toString();

        if (isset($this->routeKeys[$keyString])) {
            $this->handleDuplicateRoute(existingKey: $routeKey, newRoute: $route);

            return;
        }

        $this->routeKeys[$keyString] = true;

        $method = strtoupper(string: $route->method);

        if (! isset($this->routes[$method][$route->path])) {
            $this->routes[$method][$route->path] = [];
        }
        $this->routes[$method][$route->path][] = $route;

        if (! empty($route->name)) {
            $this->namedRoutes[$route->name] = $route;
        }
    }

    /**
     * @throws DuplicateRouteException
     */
    private function handleDuplicateRoute(RouteKey $existingKey, RouteDefinition $newRoute) : void
    {
        $policy = DuplicatePolicy::THROW;

        match ($policy) {
            DuplicatePolicy::THROW   => throw new DuplicateRouteException(
                method: $newRoute->method,
                path  : $newRoute->path,
                domain: $newRoute->domain,
                name  : $newRoute->name
            ),
            DuplicatePolicy::REPLACE => $this->replaceRoute(key: $existingKey, newRoute: $newRoute),
            DuplicatePolicy::IGNORE  => null,
        };
    }

    private function replaceRoute(RouteKey $key, RouteDefinition $newRoute) : void
    {
        $method = strtoupper(string: $key->method);

        if (isset($this->routes[$method][$key->path])) {
            $this->routes[$method][$key->path] = (new Arrhae(items: $this->routes[$method][$key->path]))
                ->filter(callback: static fn (RouteDefinition $route) => $route->domain !== $key->domain)
                ->all();
        }

        if (! isset($this->routes[$method][$key->path])) {
            $this->routes[$method][$key->path] = [];
        }
        $this->routes[$method][$key->path][] = $newRoute;

        if (! empty($newRoute->name)) {
            $this->namedRoutes[$newRoute->name] = $newRoute;
        }
    }
}
