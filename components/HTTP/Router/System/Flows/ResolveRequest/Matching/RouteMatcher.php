<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\ResolveRequest\Matching;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\PublicSurface\HttpMethod;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Pure route matching logic, separated from execution.
 */
final readonly class RouteMatcher implements RouteMatcherInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function matches(RouteDefinition $route, ServerRequest $request) : bool
    {
        $method = strtoupper($request->getMethod());

        if ($route->method !== $method && $route->method !== HttpMethod::ANY->value) {
            return false;
        }

        $uriPath = $request->getUri()->getPath();
        $uriPath = filter_var($uriPath, FILTER_SANITIZE_URL);
        if ($uriPath === false || ! is_string($uriPath)) {
            return false;
        }

        $host = $request->getUri()->getHost();

        if ($route->domain !== null) {
            $compiled = DomainPatternCompiler::compile($route->domain);
            if (! DomainPatternCompiler::match($host, $compiled)) {
                return false;
            }
        }

        return preg_match($route->compiledPathRegex, $uriPath) === 1;
    }

    public function match(array $routes, ServerRequest $request) : array|null
    {
        $method = strtoupper($request->getMethod());

        if (! in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS', 'ANY'], true)) {
            throw new InvalidArgumentException("Malformed HTTP method: {$method}");
        }

        $uriPath = $request->getUri()->getPath();
        $uriPath = filter_var($uriPath, FILTER_SANITIZE_URL);
        if ($uriPath === false || ! is_string($uriPath)) {
            throw new InvalidArgumentException('Invalid URI path');
        }

        $host = $request->getUri()->getHost();

        $this->logger->debug('Matching route.', [
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
                        $compiled = DomainPatternCompiler::compile($route->domain);
                        if (! DomainPatternCompiler::match($host, $compiled)) {
                            continue;
                        }
                    }

                    $matches = [];
                    if (preg_match($route->compiledPathRegex, $uriPath, $matches)) {
                        return [$route, $this->extractParameters($matches)];
                    }
                }
            }
        }

        return null;
    }

    private function extractParameters(array $matches) : array
    {
        $params = array_filter($matches, static fn ($key) => ! is_int($key), ARRAY_FILTER_USE_KEY);
        foreach ($params as $key => $value) {
            $params[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return $params;
    }
}
