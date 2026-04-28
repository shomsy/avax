<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;

/**
 * Domain-aware route matcher that considers domain constraints.
 */
final class DomainAwareMatcher implements RouteMatcherInterface
{
    public function __construct(private RouteMatcher $baseMatcher) {}

    public function match(array $routes, ServerRequest $request) : array|null
    {
        $method  = strtoupper($request->getMethod());
        $uriPath = $request->getUri()->getPath();

        $sortedRoutes = $this->sortRoutesBySpecificity($routes);

        $flattenedRoutes = [];
        foreach ($sortedRoutes as $methodKey => $pathsForMethod) {
            $flattenedRoutes[$methodKey] = [];
            foreach ($pathsForMethod as $pathRoutes) {
                foreach ($pathRoutes as $route) {
                    $flattenedRoutes[$methodKey][] = $route;
                }
            }
        }

        $baseResult = $this->baseMatcher->match($flattenedRoutes, $request);
        if ($baseResult === null) {
            return null;
        }

        [$route, $matches] = $baseResult;

        if ($this->matchesDomain($route, $request)) {
            return [$route, $matches];
        }

        return $this->findDomainMatchingRoute($sortedRoutes, $request, $method, $uriPath);
    }

    private function sortRoutesBySpecificity(array $routes) : array
    {
        $sorted = [];
        foreach ($routes as $method => $pathsForMethod) {
            $sorted[$method] = [];
            foreach ($pathsForMethod as $path => $routesForPath) {
                usort($routesForPath, static fn (RouteDefinition $a, RouteDefinition $b) => $b->specificity <=> $a->specificity);
                $sorted[$method][$path] = $routesForPath;
            }
        }

        return $sorted;
    }

    private function matchesDomain(RouteDefinition $route, ServerRequest $request) : bool
    {
        $routeDomain = $route->domain;
        if ($routeDomain === null) {
            return true;
        }

        $requestHost = $this->getRequestHost($request);
        if ($routeDomain === $requestHost) {
            return true;
        }

        if (str_starts_with($routeDomain, '*.')) {
            $baseDomain = substr($routeDomain, 2);

            return str_ends_with($requestHost, $baseDomain);
        }

        return false;
    }

    private function getRequestHost(ServerRequest $request) : string
    {
        $host = $request->getUri()->getHost();
        if (str_contains($host, ':')) {
            $host = explode(':', $host, 2)[0];
        }

        return strtolower($host);
    }

    private function findDomainMatchingRoute(array $routes, ServerRequest $request, string $method, string $uriPath) : array|null
    {
        if (! isset($routes[$method][$uriPath])) {
            return null;
        }

        foreach ($routes[$method][$uriPath] as $route) {
            if ($this->matchesDomain($route, $request) && $this->baseMatcher->matches($route, $request)) {
                $methodRoutes = [$method => [$route->path => [$route]]];
                $result       = $this->baseMatcher->match($methodRoutes, $request);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }

    public function matches(RouteDefinition $route, ServerRequest $request) : bool
    {
        if (! $this->matchesDomain($route, $request)) {
            return false;
        }

        return $this->baseMatcher->matches($route, $request);
    }
}
