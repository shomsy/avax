<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching\DomainPatternCompiler;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching\RouteMatcher;
use components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use components\HTTP\Router\System\Flows\ResolveRequest\Request\RouteRequestInjector;
use components\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use components\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;
use Psr\Log\NullLogger;

final class MatchHttpRoute
{
    private RouteMatcher $routeMatcher;

    public function __construct(RouteMatcher $routeMatcher = null)
    {
        $this->routeMatcher = $routeMatcher ?? new RouteMatcher(logger: new NullLogger());
    }

    public function match(RegisteredHttpRoutes $routes, ServerRequest $request) : MatchedHttpRoute
    {
        [$resolvedRequest, $route, $matches] = $this->resolveMatch(
            routes  : $routes,
            request : $request,
        );

        $parameters = $this->extractParameters(matches: $matches);

        return new MatchedHttpRoute(
            route   : $route,
            request : RouteRequestInjector::injectWithContext(
                request    : $resolvedRequest,
                route      : $route,
                parameters : $parameters,
            ),
        );
    }

    /**
     * @return array{0: ServerRequest, 1: RouteDefinition, 2: array<string, string>}
     */
    private function resolveMatch(RegisteredHttpRoutes $routes, ServerRequest $request) : array
    {
        $matchedRoute = $this->routeMatcher->match(
            routes  : $routes->routesByMethod(),
            request : $request,
        );

        if ($matchedRoute !== null) {
            return [$request, $matchedRoute[0], $matchedRoute[1]];
        }

        if ($request->getMethod() === 'HEAD') {
            $getRequest = $request->withMethod(method: 'GET');
            $matchedRoute = $this->routeMatcher->match(
                routes  : $routes->routesByMethod(),
                request : $getRequest,
            );

            if ($matchedRoute !== null) {
                return [$getRequest, $matchedRoute[0], $matchedRoute[1]];
            }
        }

        $allowedMethods = $this->allowedMethodsFor(
            routes  : $routes,
            request : $request,
        );

        if ($allowedMethods !== []) {
            throw MethodNotAllowedException::for(
                method         : $request->getMethod(),
                path           : $request->getUri()->getPath(),
                allowedMethods : $allowedMethods,
            );
        }

        throw RouteNotFoundException::for(
            method : $request->getMethod(),
            path   : $request->getUri()->getPath(),
        );
    }

    /**
     * @return array<int, string>
     */
    private function allowedMethodsFor(RegisteredHttpRoutes $routes, ServerRequest $request) : array
    {
        $allowedMethods = [];
        $path = $request->getUri()->getPath();
        $host = $request->getUri()->getHost();

        foreach ($routes->routesByMethod() as $method => $routeDefinitions) {
            foreach ($routeDefinitions as $route) {
                if (! $this->matchesPathAndDomain(route: $route, path: $path, host: $host)) {
                    continue;
                }

                $allowedMethods[] = $method;

                break;
            }
        }

        $allowedMethods = array_values(array_unique(array: $allowedMethods));
        sort($allowedMethods);

        return $allowedMethods;
    }

    private function matchesPathAndDomain(RouteDefinition $route, string $path, string $host) : bool
    {
        if ($route->domain !== null) {
            $compiledDomain = DomainPatternCompiler::compile(pattern: $route->domain);

            if (! DomainPatternCompiler::match(host: $host, compiled: $compiledDomain)) {
                return false;
            }
        }

        return preg_match(pattern: $route->compiledPathRegex, subject: $path) === 1;
    }

    /**
     * @param array<int|string, mixed> $matches
     * @return array<string, string>
     */
    private function extractParameters(array $matches): array
    {
        $parameters = [];

        foreach ($matches as $key => $value) {
            if (is_int(value: $key) || ! is_string(value: $value)) {
                continue;
            }

            $parameters[$key] = $value;
        }

        return $parameters;
    }
}
