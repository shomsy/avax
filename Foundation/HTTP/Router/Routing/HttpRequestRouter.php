<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\Exceptions\InvalidRouteException;
use Avax\HTTP\Router\Routing\Exceptions\RouteNotFoundException;
use Avax\HTTP\Router\Support\DomainPatternCompiler;
use Avax\HTTP\Router\Validation\RouteConstraintValidator;

/**
 * Handles HTTP request routing by matching registered routes to incoming requests.
 *
 * Fully supports:
 * - HTTP method-based route matching
 * - Named route support
 * - Prefix grouping
 * - Domain-aware routes
 * - Parameter constraints (via regex)
 * - Optional and wildcard path segments
 * - Route defaults for missing parameters
 * - Middleware and authorization metadata
 *
 * @internal This class acts as the internal route resolution engine.
 */
final class HttpRequestRouter
{
    /**
     * All registered routes, grouped by HTTP method.
     *
     * @var array<string, RouteDefinition[]>
     */
    private array $routes = [];

    /**
     * Current prefix (used for nested route groups).
     */
    private string $currentPrefix = '';

    /**
     * A map of named routes for reverse routing.
     *
     * @var array<string, RouteDefinition>
     */
    private array $namedRoutes = [];

    /**
     * Constructor for initializing the class with a RouteConstraintValidator.
     *
     * @param RouteConstraintValidator $constraintValidator The route constraint validator instance.
     */
    public function __construct(private readonly RouteConstraintValidator $constraintValidator) {}

    /**
     * Sets the current prefix for subsequent routes.
     *
     * @param string $prefix URI path prefix (without trailing slash).
     */
    public function setPrefix(string $prefix) : void
    {
        $this->currentPrefix = rtrim($prefix, '/');
    }

    /**
     * Clears any existing prefix used for route groupings.
     */
    public function clearPrefix() : void
    {
        $this->currentPrefix = '';
    }

    /**
     * Registers all routes defined in a RouteGroupBuilder instance.
     *
     * @param RouteGroupBuilder $group
     *
     * @return void
     */
    public function registerGroup(RouteGroupBuilder $group) : void
    {
        foreach ($group->build() as $route) {
            $this->registerRoute(
                method       : $route->method,
                path         : $route->path,
                action       : $route->action,
                middleware   : $route->middleware,
                name         : $route->name,
                constraints  : $route->constraints,
                defaults     : $route->defaults,
                domain       : $route->domain,
                attributes   : $route->attributes,
                authorization: $route->authorization
            );
        }
    }

    /**
     * Registers a route to the internal route collection.
     *
     * @param string                $method        HTTP method (GET, POST, etc.)
     * @param string                $path          Route path (e.g. /users/{id})
     * @param callable|array|string $action        Route handler (controller, callable, etc.)
     * @param array<string>         $middleware    Middleware stack
     * @param string|null           $name          Optional route name
     * @param array<string, string> $constraints   Param constraints via regex
     * @param array<string, string> $defaults      Default values for optional parameters
     * @param string|null           $domain        Optional domain pattern (e.g. admin.{org}.com)
     * @param array<string, mixed>  $attributes    Arbitrary metadata for the route
     * @param string|null           $authorization Authorization policy key (optional)
     *
     * @throws InvalidRouteException If the path is invalid.
     */
    public function registerRoute(
        string                $method,
        string                $path,
        callable|array|string $action,
        array|null            $middleware = null,
        string|null           $name = null,
        array|null            $constraints = null,
        array|null            $defaults = null,
        string|null           $domain = null,
        array|null            $attributes = null,
        string|null           $authorization = null
    ) : void {
        $this->validateRoutePath(path: $path);

        $route = new RouteDefinition(
            method       : strtoupper($method),
            path         : $this->applyPrefix(path: $path),
            action       : $action,
            middleware   : $middleware ?? [],
            name         : $name ?? '',
            constraints  : $constraints ?? [],
            defaults     : $defaults ?? [],
            domain       : $domain,
            attributes   : $attributes ?? [],
            authorization: $authorization
        );

        $this->routes[$route->method][] = $route;
        if (! empty($route->name)) {
            $this->namedRoutes[$route->name] = $route;
        }
    }

    /**
     * Validates that a route path begins with a slash and is not empty.
     *
     * @param string $path
     *
     * @throws InvalidRouteException
     */
    private function validateRoutePath(string $path) : void
    {
        if (empty($path) || ! str_starts_with($path, '/')) {
            throw new InvalidRouteException(message: 'Route path must start with a "/" and cannot be empty.');
        }
    }

    /**
     * Applies the currently active prefix to a path.
     *
     * @param string $path
     *
     * @return string
     */
    private function applyPrefix(string $path) : string
    {
        return $this->currentPrefix . $path;
    }

    /**
     * Resolves the given HTTP request and determines the corresponding route definition.
     *
     * @param Request $request The HTTP request to resolve, containing method, URI, and other details.
     *
     * @return RouteDefinition The resolved route definition that matches the request.
     * @throws RouteNotFoundException If no matching route is found.
     */
    public function resolve(Request $request) : RouteDefinition
    {
        // Retrieve the HTTP method of the request, convert it to uppercase for consistency.
        $method = strtoupper($request->getMethod());

        // Retrieve the URI path of the request to determine the path being accessed.
        $uriPath = $request->getUri()->getPath();

        // Retrieve the host (domain name) from the request URI.
        $host = $request->getUri()->getHost();

        // Iterate over all registered routes corresponding to the HTTP method of the request.
        foreach ($this->routes[$method] ?? [] as $route) {
            // If the route specifies a domain and the domain does not match the current host, skip this route.
            if ($route->domain !== null) {
                $compiled = DomainPatternCompiler::compile($route->domain);
                if (! DomainPatternCompiler::match($host, $compiled)) {
                    continue;
                }
            }

            // Compile the route's path into a regex pattern, taking into account any constraints defined.
            $pattern = $this->compileRoutePattern(
                template   : $route->path,       // The route path (e.g., "/users/{id}").
                constraints: $route->constraints // Route parameter constraints (e.g., regex for {id}).
            );

            // Check if the requested URI path matches the compiled route pattern.
            if (preg_match($pattern, $uriPath, $matches)) {
                // Extract any parameters captured from the regex match (e.g., {id} = 123).
                $parameters = $this->extractParameters(matches: $matches);

                // Apply default route parameters and merge them with extracted parameters into the request object.
                $request = $this->applyRouteDefaults(
                    request   : $request,
                    defaults  : $route->defaults,   // Default values (e.g., {lang} = "en" if not provided).
                    parameters: $parameters         // Extracted parameters from the request URI path.
                );

                // Calls the validate method of the RouteConstraintValidator instance.
                // This method ensures that all route parameter values in the request
                // comply with the regex constraints defined in the RouteDefinition.
                $this->constraintValidator->validate(route: $route, request: $request);

                // 🧠 Return the same object, but bind modified request
                return new RouteDefinition(
                    method       : $route->method,
                    path         : $route->path,
                    action       : $route->action,
                    middleware   : $route->middleware,
                    name         : $route->name,
                    constraints  : $route->constraints,
                    defaults     : $route->defaults,
                    domain       : $route->domain,
                    attributes   : $route->attributes,
                    authorization: $route->authorization,
                    parameters   : $parameters
                );
            }
        }

        throw RouteNotFoundException::for(method: $method, path: $uriPath);
    }

    /**
     * Builds a route-matching regular expression from a route path template.
     *
     * Supports:
     * - Required parameters: `/users/{id}`
     * - Optional segments:   `/users/{id?}`
     * - Wildcard catch-all:  `/files/{path*}`
     *
     * @param string               $template
     * @param array<string,string> $constraints
     *
     * @return string Regex pattern.
     */
    private function compileRoutePattern(string $template, array $constraints = []) : string
    {
        return '#^' . preg_replace_callback(
                pattern : '/\{(\w+)([?*]?)}/',
                callback: static function (array $match) use ($constraints) : string {
                    [$param, $modifier] = [$match[1], $match[2]];
                    $pattern = $constraints[$param] ?? '[^/]+';

                    return match ($modifier) {
                        '?'     => '(?:/(?P<' . $param . '>' . $pattern . '))?',
                        '*'     => '(?P<' . $param . '>.*)',
                        default => '(?P<' . $param . '>' . $pattern . ')'
                    };
                },
                subject : $template
            ) . '$#';
    }

    /**
     * Filters out numeric keys from regex match results to isolate named route parameters.
     *
     * @param array $matches Regex matches from `preg_match`.
     *
     * @return array<string, string>
     */
    private function extractParameters(array $matches) : array
    {
        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    /**
     * Applies both resolved route parameters and default values to the request object.
     *
     * @param Request              $request
     * @param array<string,string> $defaults
     * @param array<string,string> $parameters
     *
     * @return Request
     */
    private function applyRouteDefaults(Request $request, array $defaults, array $parameters) : Request
    {
        foreach ($parameters as $key => $value) {
            $request = $request->withAttribute(name: $key, value: $value);
        }

        foreach ($defaults as $key => $default) {
            if ($request->getAttribute(name: $key) === null) {
                $request = $request->withAttribute(name: $key, value: $default);
            }
        }

        return $request;
    }

    /**
     * Retrieves all registered routes.
     *
     * @return array<string, RouteDefinition[]>
     */
    public function allRoutes() : array
    {
        return $this->routes;
    }

    /**
     * Directly adds a compiled route definition to the router's table.
     * This bypasses validation and is used by the RouteCacheLoader.
     *
     * @param RouteDefinition $route The precompiled route to register.
     */
    public function add(RouteDefinition $route) : void
    {
        $method = strtoupper($route->method);

        if (! array_key_exists($method, $this->routes)) {
            $this->routes[$method] = [];
        }

        $this->routes[$method][$route->path] = $route;
    }

    /**
     * Retrieves a route by its unique name.
     *
     * @param string $name The name of the route.
     *
     * @return RouteDefinition
     *
     * @throws RouteNotFoundException
     */
    public function getByName(string $name) : RouteDefinition
    {
        if (! isset($this->namedRoutes[$name])) {
            throw new RouteNotFoundException(message: "Named route [{$name}] not found.");
        }

        return $this->namedRoutes[$name];
    }

    /**
     * Checks if a named route exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasNamedRoute(string $name) : bool
    {
        return isset($this->namedRoutes[$name]);
    }

    /**
     * Sets the fallback handler to be used when no route matches.
     *
     * @param callable|array|string $handler
     *
     * @return void
     */
    public function fallback(callable|array|string $handler) : void
    {
        $this->registerRoute(
            method: 'ANY',
            path  : '__fallback__',
            action: $handler,
            name  : '__router.fallback'
        );
    }
}
