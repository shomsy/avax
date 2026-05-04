<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching\DomainPatternCompiler;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching\RouteMatcher;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Request\RouteRequestInjector;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;
use Psr\Log\NullLogger;

final readonly class MatchHttpRoute
{
    private RouteMatcher $routeMatcher;

    public function __construct(?RouteMatcher $routeMatcher = null)
    {
        $this->routeMatcher = $routeMatcher ?? new RouteMatcher(logger: new NullLogger());
    }

    public function match(RegisteredHttpRoutes $registeredHttpRoutes, ServerRequest $serverRequest) : MatchedHttpRoute
    {
        [$resolvedRequest, $route, $matches] = $this->resolveMatch(
            routes : $registeredHttpRoutes,
            request: $serverRequest,
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
    private function resolveMatch(RegisteredHttpRoutes $registeredHttpRoutes, ServerRequest $serverRequest) : array
    {
        $matchedRoute = $this->routeMatcher->match(
            routes : $registeredHttpRoutes->routesByMethod(),
            request: $serverRequest,
        );

        if ($matchedRoute !== null) {
            return [$serverRequest, $matchedRoute[0], $matchedRoute[1]];
        }

        if ($serverRequest->getMethod() === 'HEAD') {
            $getRequest   = $serverRequest->withMethod(method: 'GET');
            $matchedRoute = $this->routeMatcher->match(
                routes  : $registeredHttpRoutes->routesByMethod(),
                request : $getRequest,
            );

            if ($matchedRoute !== null) {
                return [$getRequest, $matchedRoute[0], $matchedRoute[1]];
            }
        }

        $allowedMethods = $this->allowedMethodsFor(
            routes : $registeredHttpRoutes,
            request: $serverRequest,
        );

        if ($allowedMethods !== []) {
            throw MethodNotAllowedException::for(
                method         : $serverRequest->getMethod(),
                path           : $serverRequest->getUri()->getPath(),
                allowedMethods : $allowedMethods,
            );
        }

        throw RouteNotFoundException::for(
            method: $serverRequest->getMethod(),
            path  : $serverRequest->getUri()->getPath(),
        );
    }

    /**
     * @return array<int, string>
     */
    private function allowedMethodsFor(RegisteredHttpRoutes $registeredHttpRoutes, ServerRequest $serverRequest) : array
    {
        $allowedMethods = [];
        $path = $serverRequest->getUri()->getPath();
        $host = $serverRequest->getUri()->getHost();

        foreach ($registeredHttpRoutes->routesByMethod() as $method => $routeDefinitions) {
            foreach ($routeDefinitions as $routeDefinition) {
                if (! $this->matchesPathAndDomain(path: $path, host: $host, route: $routeDefinition)) {
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

    private function matchesPathAndDomain(RouteDefinition $routeDefinition, string $path, string $host) : bool
    {
        if ($routeDefinition->domain !== null) {
            $compiledDomain = DomainPatternCompiler::compile(pattern: $routeDefinition->domain);

            if (! DomainPatternCompiler::match(host: $host, compiled: $compiledDomain)) {
                return false;
            }
        }

        return preg_match(pattern: $routeDefinition->compiledPathRegex, subject: $path) === 1;
    }

    /**
     * @param array<int|string, mixed> $matches
     * @return array<string, string>
     */
    private function extractParameters(array $matches): array
    {
        $parameters = [];

        foreach ($matches as $key => $value) {
            if (is_int(value: $key)) {
                continue;
            }

            if (! is_string(value: $value)) {
                continue;
            }

            $parameters[$key] = $value;
        }

        return $parameters;
    }
}
