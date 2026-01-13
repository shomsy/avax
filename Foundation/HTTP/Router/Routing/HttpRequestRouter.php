<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

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

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Matching\RouteMatcherInterface;
use Avax\HTTP\Router\Routing\Exceptions\DuplicateRouteException;
use Avax\HTTP\Router\Routing\Exceptions\MethodNotAllowedException;
use Avax\HTTP\Router\Routing\Exceptions\RouteNotFoundException;
use Avax\HTTP\Router\Support\RouteRequestInjector;
use Avax\HTTP\Router\Tracing\RouterTrace;
use Avax\HTTP\Router\Validation\RouteConstraintValidator;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * INTERNAL: HTTP Request Router Engine
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
    private readonly LoggerInterface $logger;
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
    private array                $namedRoutes   = [];
    /**
     * Route keys for deduplication.
     *
     * @var array<string, bool>
     */
    private array                $routeKeys     = [];
    private RouteDefinition|null $fallbackRoute = null;

    public function __construct(
        private readonly RouteConstraintValidator $constraintValidator,
        private readonly RouteMatcherInterface    $matcher,
        LoggerInterface|null                      $logger = null,
        private readonly RouterTrace|null         $trace = null
    )
    {
        $this->logger = $logger ?? new NullLogger;
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
            throw new RouteNotFoundException("Named route [{$name}] not found.", 404, [], false);
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
     * @deprecated This method is deprecated. Use FallbackManager directly.
     * Fallback handling should be unified through FallbackManager only.
     */
    public function fallback(callable|array|string $handler) : void
    {
        // Fallback is now handled exclusively through FallbackManager
        // This method is kept for backward compatibility but does nothing
        // TODO: Remove this method in a future version
    }

    /**
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    private function registerRoute(string $method, string $path, callable|array|string $action, string|null $name = null) : void
    {
        $route = new RouteDefinition(
            method       : $method,
            path         : $path,
            action       : $action,
            middleware   : [],
            name         : $name,
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
     * @internal This method is for internal cache loading only.
     *
     * @throws DuplicateRouteException
     */
    public function add(RouteDefinition $route) : void
    {
        $routeKey = RouteKey::fromRoute($route);
        $keyString = $routeKey->toString();

        // Check for duplicates based on configured policy
        if (isset($this->routeKeys[$keyString])) {
            $this->handleDuplicateRoute($routeKey, $route);
            return; // If policy allows continuation
        }

        $this->routeKeys[$keyString] = true;

        $method = strtoupper(string: $route->method);

        // Support multiple routes per method+path for domain-aware routing
        if (!isset($this->routes[$method][$route->path])) {
            $this->routes[$method][$route->path] = [];
        }
        $this->routes[$method][$route->path][] = $route;

        // Register named routes for quick lookup
        if (! empty($route->name)) {
            $this->namedRoutes[$route->name] = $route;
        }
    }

    /**
     * Builds a unique key for route deduplication.
     */
    private function buildRouteKey(RouteDefinition $route) : string
    {
        return sprintf(
            '%s|%s|%s',
            strtoupper($route->method),
            $route->domain ?? '',
            $route->path
        );
    }

    /**
     * Resolves the given HTTP request and returns structured resolution context.
     *
     * Provides comprehensive debugging information about how and why a route was selected,
     * including timing, parameters, domain matching, and resolution path.
     *
     * @param Request $request The HTTP request to resolve
     *
     * @return RouteResolutionContext Structured context with route, parameters, timing, and debug info
     *
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     * @throws \Avax\HTTP\Router\Validation\Exceptions\InvalidConstraintException
     */
    public function resolve(Request $request) : RouteResolutionContext
    {
        $startTime = microtime(true);
        $path      = $request->getUri()->getPath();
        $method    = $request->getMethod();
        $host      = $request->getUri()->getHost();

        $resolutionPath = [
            ['timestamp' => date('H:i:s.u'), 'description' => "Started resolution for {$method} {$path} from {$host}"],
        ];

        $this->trace?->log(event: 'resolve.start', context: ['path' => $path, 'method' => $method, 'host' => $host]);

        try {
            $matchResult = $this->matcher->match(routes: $this->routes, request: $request);

            if ($matchResult === null) {
                $resolutionPath[] = ['timestamp' => date('H:i:s.u'), 'description' => 'No route matched'];
                $this->trace?->log(event: 'resolve.no_match', context: ['path' => $path, 'method' => $method]);

                // No route matched - determine if it's 404 or 405
                $allowedMethods   = $this->findAllowedMethodsForPath(path: $path);
                $resolutionPath[] = ['timestamp' => date('H:i:s.u'), 'description' => 'Checked allowed methods: ' . implode(', ', $allowedMethods)];
                $this->trace?->log(event: 'resolve.allowed_methods', context: ['allowed' => $allowedMethods]);

                $failureReason = ! empty($allowedMethods)
                    ? "Method {$method} not allowed (allowed: " . implode(', ', $allowedMethods) . ')'
                    : "No route found for path {$path}";

                $matchTime = (microtime(true) - $startTime) * 1000;

                if (! empty($allowedMethods)) {
                    $this->trace?->log(event: 'resolve.method_not_allowed');
                    throw new MethodNotAllowedException(
                        $failureReason,
                        405,
                        ['allowed_methods' => $allowedMethods],
                        false
                    );
                } else {
                    $this->trace?->log(event: 'resolve.route_not_found');
                    throw new RouteNotFoundException($failureReason, 404, [], false);
                }
            }

            [$route, $matches] = $matchResult;
            $resolutionPath[] = ['timestamp' => date('H:i:s.u'), 'description' => "Matched route: {$route->method} {$route->path}"];
            $this->trace?->log(event: 'resolve.match_found', context: [
                'route'   => $route->name ?? $route->path,
                'matches' => count($matches)
            ]);

            // Extract any parameters captured from the regex match (e.g., {id} = 123).
            $parameters       = $this->extractParameters(matches: $matches);
            $resolutionPath[] = ['timestamp' => date('H:i:s.u'), 'description' => 'Extracted parameters: ' . json_encode($parameters)];
            $this->trace?->log(event: 'resolve.parameters_extracted', context: ['params' => $parameters]);

            // Apply default route parameters and merge them with extracted parameters into the request object.
            $request          = RouteRequestInjector::injectExtractedParameters(
                request   : $request,
                defaults  : $route->defaults,
                parameters: $parameters
            );
            $resolutionPath[] = ['timestamp' => date('H:i:s.u'), 'description' => 'Injected parameters into request'];
            $this->trace?->log(event: 'resolve.parameters_injected', context: ['defaults' => $route->defaults]);

            // Calls the validate method of the RouteConstraintValidator instance.
            $this->constraintValidator->validate(route: $route, request: $request);
            $resolutionPath[] = ['timestamp' => date('H:i:s.u'), 'description' => 'Route validation completed'];
            $this->trace?->log(event: 'resolve.validation_complete');

            $matchTime     = (microtime(true) - $startTime) * 1000;
            $matchedDomain = $route->domain ?? null;

            $this->trace?->log(event: 'resolve.complete', context: ['route' => $route->name ?? $route->path]);

            return RouteResolutionContext::success(
                route         : $route,
                parameters    : $parameters,
                matchedDomain : $matchedDomain,
                matchTimeMs   : $matchTime,
                resolutionPath: $resolutionPath
            );

        } catch (RouteNotFoundException|MethodNotAllowedException $e) {
            $matchTime = (microtime(true) - $startTime) * 1000;
            $this->trace?->log(event: 'resolve.failed', context: ['reason' => $e->getMessage()]);

            return RouteResolutionContext::failure(
                failureReason : $e->getMessage(),
                matchTimeMs   : $matchTime,
                resolutionPath: $resolutionPath
            );
        }
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
                    // Check if this route's pattern could match the path
                    if ($this->matchesIgnoringMethod(route: $route, path: $path)) {
                        $allowedMethods[] = $method;
                        break 2; // Found at least one route for this method
                    }
                }
            }
        }

        // Remove duplicates and sort
        $allowedMethods = array_unique($allowedMethods);
        sort($allowedMethods);

        return $allowedMethods;
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
     * @param string         $path  The request path
     *
     * @return bool True if the route pattern matches the path
     */
    private function matchesIgnoringMethod(RouteDefinition $route, string $path) : bool
    {
        // Use precompiled regex pattern for performance
        return preg_match($route->compiledPathRegex, $path) === 1;
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
        $pattern = preg_replace_callback(
            '/\{([^}]+)\}/',
            static function ($matches) use ($constraints) {
                $param      = $matches[1];
                $isOptional = str_ends_with($param, '?');
                $isWildcard = str_ends_with($param, '*');

                $paramName  = preg_replace('/[?*]$/', '', $param);
                $constraint = $constraints[$paramName] ?? '[^/]+';

                $segment = "(?P<{$paramName}>{$constraint})";

                if ($isWildcard) {
                    $segment = "(?P<{$paramName}>.*)";
                }

                if ($isOptional) {
                    $segment = "(?:/{$segment})?";
                } else {
                    $segment = "/{$segment}";
                }

                return $segment;
            },
            $template
        );

        return "#^{$pattern}$#";
    }

    private function extractParameters(array $matches) : array
    {
        return array_filter($matches, static fn($key) => ! is_int($key), ARRAY_FILTER_USE_KEY);
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
     * Handle duplicate route registration based on configured policy.
     *
     * @throws DuplicateRouteException
     */
    private function handleDuplicateRoute(RouteKey $existingKey, RouteDefinition $newRoute) : void
    {
        // Default policy - can be made configurable in future versions
        $policy = DuplicatePolicy::THROW;

        match ($policy) {
            DuplicatePolicy::THROW => throw new DuplicateRouteException(
                "Duplicate route: {$newRoute->method} {$newRoute->path}",
                409,
                [
                    'method' => $newRoute->method,
                    'path' => $newRoute->path,
                    'domain' => $newRoute->domain,
                    'name' => $newRoute->name
                ],
                false
            ),
            DuplicatePolicy::REPLACE => $this->replaceRoute($existingKey, $newRoute),
            DuplicatePolicy::IGNORE => null, // Do nothing, keep existing route
        };
    }

    /**
     * Replace an existing route with a new one.
     */
    private function replaceRoute(RouteKey $key, RouteDefinition $newRoute) : void
    {
        $method = strtoupper($key->method);

        // Remove existing route
        if (isset($this->routes[$method][$key->path])) {
            $this->routes[$method][$key->path] = array_filter(
                $this->routes[$method][$key->path],
                static fn(RouteDefinition $route) => $route->domain !== $key->domain
            );
        }

        // Add new route
        if (!isset($this->routes[$method][$key->path])) {
            $this->routes[$method][$key->path] = [];
        }
        $this->routes[$method][$key->path][] = $newRoute;

        // Update named routes if applicable
        if (! empty($newRoute->name)) {
            $this->namedRoutes[$newRoute->name] = $newRoute;
        }
    }

    /**
     * Gets the current trace instance for debugging and profiling.
     */
    public function getTrace() : RouterTrace|null
    {
        return $this->trace;
    }
}