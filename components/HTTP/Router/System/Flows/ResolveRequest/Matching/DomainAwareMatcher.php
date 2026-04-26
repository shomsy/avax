<?php

declare(strict_types=1);

namespace components\HTTP\Router\System\Flows\ResolveRequest\Matching;

use components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;

/**
 * Domain-aware route matcher that considers domain constraints.
 *
 * Enables multi-tenant routing where routes can be restricted to specific domains.
 * Falls back to standard RouteMatcher for domain-agnostic routes.
 */
final class DomainAwareMatcher implements RouteMatcherInterface
{
    private RouteMatcher $baseMatcher;

    public function __construct(
        RouteMatcher $baseMatcher
    )
    {
        $this->baseMatcher = $baseMatcher;
    }

    /**
     * Finds the first matching route from the given routes array, considering domain constraints.
     * Now supports multiple routes per method+path for true domain-aware routing.
     *
     * @param array<string, array<string, RouteDefinition[]>> $routes  Routes grouped by method and path
     * @param Request                                         $request The incoming request
     *
     * @return array{0: RouteDefinition, 1: array}|null Returns [route, matches] or null if no match
     */
    public function match(array $routes, ServerRequest $request) : array|null
    {
        $method  = strtoupper(string: $request->getMethod());
        $uriPath = $request->getUri()->getPath();

        // Sort routes by specificity (most specific first) for proper precedence
        $sortedRoutes = $this->sortRoutesBySpecificity(routes: $routes);

        // Flatten routes for each method to match the base matcher interface
        $flattenedRoutes = [];
        foreach ($sortedRoutes as $methodKey => $pathsForMethod) {
            $flattenedRoutes[$methodKey] = [];
            foreach ($pathsForMethod as $pathRoutes) {
                foreach ($pathRoutes as $route) {
                    $flattenedRoutes[$methodKey][] = $route;
                }
            }
        }

        // First try to match without domain constraints
        $baseResult = $this->baseMatcher->match(routes: $flattenedRoutes, request: $request);

        if ($baseResult === null) {
            return null;
        }

        [$route, $matches] = $baseResult;

        // Now check domain constraint
        if ($this->matchesDomain(route: $route, request: $request)) {
            return [$route, $matches];
        }

        // Domain doesn't match, try to find another route that does match domain
        return $this->findDomainMatchingRoute(routes: $sortedRoutes, request: $request, method: $method, uriPath: $uriPath);
    }

    /**
     * Sorts routes by specificity in descending order (most specific first).
     * Ensures more specific routes (like /users/me) are matched before generic ones (like /users/{id}).
     *
     * @param array<string, array<string, RouteDefinition[]>> $routes
     *
     * @return array<string, array<string, RouteDefinition[]>>
     */
    private function sortRoutesBySpecificity(array $routes) : array
    {
        $sorted = [];

        foreach ($routes as $method => $pathsForMethod) {
            $sorted[$method] = [];
            foreach ($pathsForMethod as $path => $routesForPath) {
                // Sort routes by specificity (descending: higher specificity first)
                usort(array: $routesForPath, callback: static fn (RouteDefinition $a, RouteDefinition $b) => $b->specificity <=> $a->specificity);
                $sorted[$method][$path] = $routesForPath;
            }
        }

        return $sorted;
    }

    /**
     * Checks if the route's domain constraint matches the request's host.
     *
     * @param RouteDefinition $route   The route definition
     * @param Request         $request The request
     *
     * @return bool True if domain matches or no domain constraint exists
     */
    private function matchesDomain(RouteDefinition $route, ServerRequest $request) : bool
    {
        $routeDomain = $route->domain;

        // No domain constraint = matches any domain
        if ($routeDomain === null) {
            return true;
        }

        $requestHost = $this->getRequestHost(request: $request);

        // Exact domain match
        if ($routeDomain === $requestHost) {
            return true;
        }

        // Support wildcard subdomains (e.g., *.example.com)
        if (str_starts_with(haystack: $routeDomain, needle: '*.')) {
            $baseDomain = substr(string: $routeDomain, offset: 2); // Remove *. prefix

            return str_ends_with(haystack: $requestHost, needle: $baseDomain);
        }

        return false;
    }

    /**
     * Extracts the host from the request URI.
     *
     * @return string The host (lowercase)
     */
    private function getRequestHost(ServerRequest $request) : string
    {
        $host = $request->getUri()->getHost();

        // Handle port number (e.g., example.com:8080 -> example.com)
        if (str_contains(haystack: $host, needle: ':')) {
            $host = explode(separator: ':', string: $host, limit: 2)[0];
        }

        return strtolower(string: $host);
    }

    /**
     * Finds a route that matches both path/method AND domain constraints.
     * Now searches through multiple routes per path for domain-aware routing.
     */
    private function findDomainMatchingRoute(array $routes, ServerRequest $request, string $method, string $uriPath) : array|null
    {
        if (! isset($routes[$method][$uriPath])) {
            return null;
        }

        foreach ($routes[$method][$uriPath] as $route) {
            // Check if this route matches domain AND would match the base criteria
            if ($this->matchesDomain(route: $route, request: $request) && $this->baseMatcher->matches(route: $route, request: $request)) {
                // We need to get the actual matches from the base matcher
                $methodRoutes = [$method => [$route->path => [$route]]];
                $result       = $this->baseMatcher->match(routes: $methodRoutes, request: $request);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Matches a route against a request, considering domain constraints.
     *
     * @param RouteDefinition $route   The route to match
     * @param Request         $request The incoming request
     *
     * @return bool True if route matches request and domain constraints
     */
    public function matches(RouteDefinition $route, ServerRequest $request) : bool
    {
        // First check domain constraint
        if (! $this->matchesDomain(route: $route, request: $request)) {
            return false;
        }

        // Then delegate to base matcher for path/method matching
        return $this->baseMatcher->matches(route: $route, request: $request);
    }
}
