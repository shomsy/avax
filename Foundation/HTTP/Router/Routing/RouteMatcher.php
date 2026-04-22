<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\HttpMethod;
use Avax\HTTP\Router\Matching\RouteMatcherInterface;
use Avax\HTTP\Router\Support\DomainPatternCompiler;
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
    public function matches(RouteDefinition $route, Request $request) : bool
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
     * @param Request $request The HTTP request to match.
     *
     * @return array{RouteDefinition, array}|null The matched route definition and regex matches, or null.
     */
    public function match(array $routes, Request $request) : array|null
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
            foreach ($routes[$methodToTry] ?? [] as $route) {
                if ($route->domain !== null) {
                    $compiled = DomainPatternCompiler::compile(pattern: $route->domain);
                    if (! DomainPatternCompiler::match(host: $host, compiled: $compiled)) {
                        continue;
                    }
                }

                // Use precompiled regex pattern for performance
                $matches = [];

                if (preg_match(pattern: $route->compiledPathRegex, subject: $uriPath, matches: $matches)) {
                    return [$route, $matches];
                }
            }
        }

        return null;
    }

    // Helper methods from HttpRequestRouter

    private function compileRoutePattern(string $template, array $constraints) : string
    {
        // PERFORMANCE: Compile route pattern from template and constraints
        $pattern = preg_replace_callback(
            pattern : '/\{([^}]+)\}/',
            callback: static function ($matches) use ($constraints) {
                $param      = $matches[1];
                $isOptional = str_ends_with(haystack: $param, needle: '?');
                $isWildcard = str_ends_with(haystack: $param, needle: '*');

                $paramName  = preg_replace(pattern: '/[?*]$/', replacement: '', subject: $param);
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
            subject : $template
        );

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