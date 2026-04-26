<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\System\Flows\ResolveRequest\Matching;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\HTTP\Router\HttpMethod;
use Avax\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Pure route matching logic, separated from execution.
 *
 * This class encapsulates the algorithm for finding a route that matches
 * the given HTTP request based on method, path, and constraints.
 */
final readonly class RouteMatcher implements RouteMatcherInterface
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    /**
     * Checks if a single route matches the given request.
     *
     * Uses precompiled regex pattern for performance.
     *
     * @param RouteDefinition $route   The route to check
     * @param Request         $request The HTTP request
     *
     * @return bool True if the route matches
     */
    public function matches(RouteDefinition $route, ServerRequest $request) : bool
    {
        $method = strtoupper(string: $request->getMethod());

        // Check if route method matches (or is ANY)
        if ($route->method !== $method && $route->method !== HttpMethod::ANY->value) {
            return false;
        }

        $uriPath = $request->getUri()->getPath();

        // Sanitize URI path
        $uriPath = filter_var(value: $uriPath, filter: FILTER_SANITIZE_URL);
        if ($uriPath === false || ! is_string(value: $uriPath)) {
            return false;
        }

        $host = $request->getUri()->getHost();

        // Check domain constraint if present
        if ($route->domain !== null) {
            $compiled = DomainPatternCompiler::compile(pattern: $route->domain);
            if (! DomainPatternCompiler::match(host: $host, compiled: $compiled)) {
                return false;
            }
        }

        // Check path pattern using precompiled regex for performance
        return preg_match(pattern: $route->compiledPathRegex, subject: $uriPath) === 1;
    }

    /**
     * Matches the given request to a registered route.
     *
     * Routes should be in format: array<string, array<string, RouteDefinition>>
     * Where key is HTTP method, value is array<path, RouteDefinition>
     *
     * @param ServerRequest $request The HTTP request to match.
     *
     * @return array{RouteDefinition, array}|null The matched route definition and regex matches, or null.
     */
    public function match(array $routes, ServerRequest $request) : array|null
    {
        $method = strtoupper(string: $request->getMethod());

        // Validate HTTP method
        if (! in_array(needle: $method, haystack: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS', 'ANY'], strict: true)) {
            throw new InvalidArgumentException(message: "Malformed HTTP method: {$method}");
        }

        $uriPath = $request->getUri()->getPath();

        // Sanitize URI path
        $uriPath = filter_var(value: $uriPath, filter: FILTER_SANITIZE_URL);
        if ($uriPath === false || ! is_string(value: $uriPath)) {
            throw new InvalidArgumentException(message: 'Invalid URI path');
        }

        $host = $request->getUri()->getHost();

        $this->logger->debug(message: 'Matching route.', context: [
            'method' => $method,
            'path'   => $uriPath,
            'host'   => $host,
        ]);

        $methodsToTry = [$method];
        if ($method !== HttpMethod::ANY->value) {
            $methodsToTry[] = HttpMethod::ANY->value;
        }

        foreach ($methodsToTry as $methodToTry) {
            foreach ($routes[$methodToTry] ?? [] as $candidate) {
                $routeList = $candidate instanceof RouteDefinition ? [$candidate] : $candidate;

                foreach ($routeList as $route) {
                    if (! $route instanceof RouteDefinition) {
                        continue;
                    }

                    if ($route->domain !== null) {
                        $compiled = DomainPatternCompiler::compile(pattern: $route->domain);
                        if (! DomainPatternCompiler::match(host: $host, compiled: $compiled)) {
                            continue;
                        }
                    }

                    $matches = [];
                    if (preg_match(pattern: $route->compiledPathRegex, subject: $uriPath, matches: $matches)) {
                        return [$route, $matches];
                    }
                }
            }
        }

        return null;
    }

    // Helper methods from HttpRequestRouter

    private function compileRoutePattern(string $template, array $constraints) : string
    {
        if ($template === '/') {
            return '#^/$#';
        }

        $pattern = '';
        foreach (explode(separator: '/', string: trim(string: $template, characters: '/')) as $segment) {
            if (preg_match(pattern: '/^\{([^}]+)\}$/', subject: $segment, matches: $matches) !== 1) {
                $pattern .= '/' . preg_quote(str: $segment, delimiter: '#');
                continue;
            }

            $parameter  = $matches[1];
            $isOptional = str_ends_with(haystack: $parameter, needle: '?');
            $isWildcard = str_ends_with(haystack: $parameter, needle: '*');
            $name       = preg_replace(pattern: '/[?*]$/', replacement: '', subject: $parameter);
            $constraint = $isWildcard ? '.*' : ($constraints[$name] ?? '[^/]+');
            $group      = "(?P<{$name}>{$constraint})";

            $pattern .= $isOptional ? "(?:/{$group})?" : "/{$group}";
        }

        return "#^{$pattern}$#";
    }

    private function extractParameters(array $matches) : array
    {
        $params = array_filter(array: $matches, callback: static fn ($key) => ! is_int(value: $key), mode: ARRAY_FILTER_USE_KEY);

        // Sanitize parameter values
        foreach ($params as $key => $value) {
            $params[$key] = filter_var(value: $value, filter: FILTER_SANITIZE_STRING, options: FILTER_FLAG_NO_ENCODE_QUOTES);
        }

        return $params;
    }
}
