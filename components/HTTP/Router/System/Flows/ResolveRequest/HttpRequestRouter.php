<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\System\Flows\ResolveRequest;

/**
 * @phpstan-type RoutesMap array<string, array<string, RouteDefinition[]>>
 * @phpstan-type RouteResolutionContext array{
 *     route: RouteDefinition,
 *     parameters: array<string, string>,
 *     matchedDomain: string|null,
 *     matchTimeMs: float,
 *     resolutionPath: array<array{time: float, description: string}>
 * }
 * @phpstan-type RouteMethodMap array<string, array<string, RouteDefinition[]>>
 */

use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\HTTP\Router\System\Capabilities\RouteDefinition\DuplicatePolicy;
use Avax\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\HTTP\Router\System\Capabilities\RouteDefinition\RouteKey;
use Avax\HTTP\Router\System\Capabilities\RouterTrace\RouterTrace;
use Avax\HTTP\Router\System\Flows\ResolveRequest\Constraints\RouteConstraintValidator;
use Avax\HTTP\Router\System\Flows\ResolveRequest\Matching\RouteMatcherInterface;
use Avax\HTTP\Router\System\Flows\ResolveRequest\Request\RouteRequestInjector;
use Avax\HTTP\Router\System\Foundation\Exceptions\DuplicateRouteException;
use Avax\HTTP\Router\System\Foundation\Exceptions\InvalidConstraintException;
use Avax\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\HTTP\Router\System\Foundation\Exceptions\ReservedRouteNameException;
use Avax\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;
use Avax\Text\Pattern;

/**
 * INTERNAL: HTTP ServerRequest Router Engine
 *
 * This is an internal implementation detail. Behavior may change without notice.
 * Do not depend on this class directly - use RouterInterface and Router instead.
 *
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
 * @internal
 */
final class HttpRequestRouter
{
    /**
     * All registered routes, grouped by HTTP method and path.
     * Supports multiple routes per path for domain-aware routing.
     *
     * @var array<string, array<string, RouteDefinition[]>>
     */
    private array $routes = [];
    /**
     * Prefix stack (used for nested route groups).
     *
     * @var string[]
     */
    private array $prefixStack = [];
    /**
     * A map of named routes for reverse routing.
     *
     * @var array<string, RouteDefinition>
     */
    private array $namedRoutes = [];
    /**
     * Route keys for deduplication.
     *
     * @var array<string, bool>
     */
    private array                             $routeKeys     = [];
    private RouteDefinition|null              $fallbackRoute = null;
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

    /**
     * Retrieves a route by its unique name.
     *
     * @param string $name The name of the route.
     *
     * @throws RouteNotFoundException
     */
    public function getByName(string $name) : RouteDefinition
    {
        if (! isset($this->namedRoutes[$name])) {
            throw new RouteNotFoundException(message: "Named route [{$name}] not found.", httpStatusCode: 404, context: [], isRetryable: false);
        }

        return $this->namedRoutes[$name];
    }

    /**
     * Checks if a named route exists.
     */
    public function hasNamedRoute(string $name) : bool
    {
        return isset($this->namedRoutes[$name]);
    }

    /**
     * Sets the fallback handler to be used when no route matches.
     *
     * @deprecated This method is deprecated. Use RegisteredFallback directly.
     * Fallback handling should be unified through RegisteredFallback only.
     */
    public function fallback(callable|array|string $handler) : void
    {
        // Fallback is now handled exclusively through RegisteredFallback
        // This method is kept for backward compatibility but does nothing
        // TODO: Remove this method in a future version
    }

    /**
     * Resolves the given HTTP request and returns structured resolution context.
     *
     * Provides comprehensive debugging information about how and why a route was selected,
     * including timing, parameters, domain matching, and resolution path.
     *
     * @param ServerRequest $request The HTTP request to resolve
     *
     * @return RouteResolutionContext Structured context with route, parameters, timing, and debug info
     *
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

        $this->trace?->log(event: 'resolve.start', context: ['path' => $path, 'method' => $method, 'host' => $host]);

        $matchResult = $this->matcher->match(routes: $this->routes, request: $request);

        if ($matchResult === null) {
            $resolutionPath[] = ['timestamp' => date(format: 'H:i:s.u'), 'description' => 'No route matched'];
            $this->trace?->log(event: 'resolve.no_match', context: ['path' => $path, 'method' => $method]);

            $allowedMethods   = $this->findAllowedMethodsForPath(path: $path);
            $resolutionPath[] = ['timestamp' => date(format: 'H:i:s.u'), 'description' => 'Checked allowed methods: ' . implode(separator: ', ', array: $allowedMethods)];
            $this->trace?->log(event: 'resolve.allowed_methods', context: ['allowed' => $allowedMethods]);

            $this->trace?->log(event: 'resolve.failed', context: [
                'path'            => $path,
                'method'          => $method,
                'allowed_methods' => $allowedMethods,
            ]);

            if ($allowedMethods !== []) {
                throw MethodNotAllowedException::for(method: $method, path: $path, allowedMethods: $allowedMethods);
            }

            throw RouteNotFoundException::for(method: $method, path: $path);
        }

        [$route, $matches] = $matchResult;
        $resolutionPath[] = ['timestamp' => date(format: 'H:i:s.u'), 'description' => "Matched route: {$route->method} {$route->path}"];
        $this->trace?->log(event: 'resolve.match_found', context: [
            'route'   => $route->name ?? $route->path,
            'matches' => count(value: $matches)
        ]);

        // Extract any parameters captured from the regex match (e.g., {id} = 123).
        $parameters       = $this->extractParameters(matches: $matches);
        $resolutionPath[] = ['timestamp' => date(format: 'H:i:s.u'), 'description' => 'Extracted parameters: ' . json_encode(value: $parameters)];
        $this->trace?->log(event: 'resolve.parameters_extracted', context: ['params' => $parameters]);

        // Apply default route parameters and merge them with extracted parameters into the request object.
        $request          = RouteRequestInjector::injectExtractedParameters(
            request   : $request,
            defaults  : $route->defaults,
            parameters: $parameters
        );
        $resolutionPath[] = ['timestamp' => date(format: 'H:i:s.u'), 'description' => 'Injected parameters into request'];
        $this->trace?->log(event: 'resolve.parameters_injected', context: ['defaults' => $route->defaults]);

        // Calls the validate method of the RouteConstraintValidator instance.
        $this->constraintValidator->validate(route: $route, request: $request);
        $resolutionPath[] = ['timestamp' => date(format: 'H:i:s.u'), 'description' => 'Route validation completed'];
        $this->trace?->log(event: 'resolve.validation_complete');

        $matchTime     = (microtime(as_float: true) - $startTime) * 1000;
        $matchedDomain = $route->domain ?? null;

        $this->trace?->log(event: 'resolve.complete', context: ['route' => $route->name ?? $route->path]);

        return RouteResolutionContext::success(
            route         : $route,
            parameters    : $parameters,
            matchedDomain : $matchedDomain,
            matchTimeMs   : $matchTime,
            resolutionPath: $resolutionPath
        );
    }

    /**
     * Finds all HTTP methods that have routes for the given path.
     *
     * Used to determine if a 404 should be 405 (method not allowed) instead.
     * Now considers pattern routes (e.g., /users/{id}) for proper 405 responses.
     *
     * @param string $path The request path to check
     *
     * @return string[] Array of allowed HTTP methods (uppercase)
     */
    private function findAllowedMethodsForPath(string $path) : array
    {
        $allowedMethods = [];

        foreach ($this->routes as $method => $pathsForMethod) {
            foreach ($pathsForMethod as $routesForPath) {
                foreach ($routesForPath as $route) {
                    if ($this->matchesIgnoringMethod(route: $route, path: $path)) {
                        $allowedMethods[] = $method;
                        break 2;
                    }
                }
            }
        }

        return arrhae(array: $allowedMethods)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Checks if a route pattern could match the given path, ignoring HTTP method.
     *
     * Used for 404/405 determination - if a pattern route exists for the path
     * but with different methods, return 405 instead of 404.
     *
     * Uses precompiled regex pattern for performance.
     *
     * @param RouteDefinition $route The route to check
     * @param string          $path  The request path
     *
     * @return bool True if the route pattern matches the path
     */
    private function matchesIgnoringMethod(RouteDefinition $route, string $path) : bool
    {
        return Pattern::of(raw: $route->compiledPathRegex)->test(subject: $path);
    }

    private function extractParameters(array $matches) : array
    {
        return arrhae(array: $matches)
            ->filter(callback: static fn (mixed $value, mixed $key) => ! is_int(value: $key))
            ->all();
    }

    /**
     * Returns all registered routes grouped by HTTP method.
     * Flattens the internal structure for backward compatibility.
     *
     * @return array<string, RouteDefinition[]>
     */
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
     * Gets the current trace instance for debugging and profiling.
     */
    public function getTrace() : RouterTrace|null
    {
        return $this->trace;
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
     * Registers a route from cache (bypasses validation).
     *
     * @param RouteDefinition $route The precompiled route to register.
     *
     * @throws DuplicateRouteException
     * @internal This method is for internal cache loading only.
     *
     */
    public function add(RouteDefinition $route) : void
    {
        $routeKey  = RouteKey::fromRoute(route: $route);
        $keyString = $routeKey->toString();

        // Check for duplicates based on configured policy
        if (isset($this->routeKeys[$keyString])) {
            $this->handleDuplicateRoute(existingKey: $routeKey, newRoute: $route);

            return; // If policy allows continuation
        }

        $this->routeKeys[$keyString] = true;

        $method = strtoupper(string: $route->method);

        // Support multiple routes per method+path for domain-aware routing
        if (! isset($this->routes[$method][$route->path])) {
            $this->routes[$method][$route->path] = [];
        }
        $this->routes[$method][$route->path][] = $route;

        // Register named routes for quick lookup
        if (! empty($route->name)) {
            $this->namedRoutes[$route->name] = $route;
        }
    }

    /**
     * Handle duplicate route registration based on configured policy.
     *
     * @throws DuplicateRouteException
     */
    private function handleDuplicateRoute(RouteKey $existingKey, RouteDefinition $newRoute) : void
    {
        // Default policy - can be made configurable in future versions
        $policy = DuplicatePolicy::THROW;

        match ($policy) {
            DuplicatePolicy::THROW   => throw new DuplicateRouteException(
                method: $newRoute->method,
                path  : $newRoute->path,
                domain: $newRoute->domain,
                name  : $newRoute->name
            ),
            DuplicatePolicy::REPLACE => $this->replaceRoute(key: $existingKey, newRoute: $newRoute),
            DuplicatePolicy::IGNORE  => null, // Do nothing, keep existing route
        };
    }

    /**
     * Replace an existing route with a new one.
     */
    private function replaceRoute(RouteKey $key, RouteDefinition $newRoute) : void
    {
        $method = strtoupper(string: $key->method);

        // Remove existing route
        if (isset($this->routes[$method][$key->path])) {
            $this->routes[$method][$key->path] = arrhae(array: $this->routes[$method][$key->path])
                ->filter(callback: static fn (RouteDefinition $route) => $route->domain !== $key->domain)
                ->all();
        }

        // Add new route
        if (! isset($this->routes[$method][$key->path])) {
            $this->routes[$method][$key->path] = [];
        }
        $this->routes[$method][$key->path][] = $newRoute;

        // Update named routes if applicable
        if (! empty($newRoute->name)) {
            $this->namedRoutes[$newRoute->name] = $newRoute;
        }
    }

    /**
     * Builds a unique key for route deduplication.
     */
    private function buildRouteKey(RouteDefinition $route) : string
    {
        return sprintf(
            '%s|%s|%s',
            strtoupper(string: $route->method),
            $route->domain ?? '',
            $route->path
        );
    }

    /**
     * Compiles a route path template into a regex pattern.
     *
     * @param string $template    The route template (e.g., "/users/{id}")
     * @param array  $constraints Parameter constraints
     *
     * @return string The compiled regex pattern
     */
    private function compileRoutePattern(string $template, array $constraints) : string
    {
        if ($template === '/') {
            return '#^/$#';
        }

        $pattern = '';
        foreach (explode(separator: '/', string: trim(string: $template, characters: '/')) as $segment) {
            $segmentMatch = Pattern::of(raw: '^\{([^}]+)\}$')->match(subject: $segment);

            if (! $segmentMatch->matched) {
                $pattern .= '/' . preg_quote(str: $segment, delimiter: '#');
                continue;
            }

            $parameter  = $segmentMatch->matches[1];
            $isOptional = str_ends_with(haystack: $parameter, needle: '?');
            $isWildcard = str_ends_with(haystack: $parameter, needle: '*');

            $name       = Pattern::of(raw: '[?*]$')->replace(subject: $parameter, replacement: '');
            $constraint = $isWildcard ? '.*' : ($constraints[$name] ?? '[^/]+');
            $group      = "(?P<{$name}>{$constraint})";

            $pattern .= $isOptional ? "(?:/{$group})?" : "/{$group}";
        }

        return "#^{$pattern}$#";
    }
}
