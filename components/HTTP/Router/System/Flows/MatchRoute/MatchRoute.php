<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\MatchRoute;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;

/**
 * Result of a route match attempt.
 */
final readonly class MatchResult
{
    public function __construct(
        public ?RouteDefinition $route = null,
        /** @var list<RouteMethod> */
        public array            $allowedMethods = [],
        /** @var array<string, string> */
        public ?array           $parameters = null,
    ) {}

    public function isMatch() : bool
    {
        return $this->route !== null;
    }

    public function isMethodNotAllowed() : bool
    {
        return $this->route === null && $this->allowedMethods !== [];
    }

    public function isNotFound() : bool
    {
        return $this->route === null && $this->allowedMethods === [];
    }
}

final class MatchRoute
{
    /** @var array<string, array{pattern: string, regex: string, paramNames: list<string>}> */
    private array $compiledPatterns = [];

    public function execute(RouteCollection $routeCollection, RequestInterface $request) : MatchResult
    {
        $method    = $request->getMethod();
        $path      = $request->getUri()->getPath();
        $foundPath = false;
        $methods   = [];

        foreach ($routeCollection->all() as $route) {
            $routeUri = $route->uri();

            if (! $this->uriMatches($routeUri, $path, $params)) {
                continue;
            }

            $foundPath = true;

            if ($route->method()->value === $method) {
                return new MatchResult(
                    route     : $route,
                    parameters: $params,
                );
            }

            if (! in_array($route->method(), $methods, true)) {
                $methods[] = $route->method();
            }
        }

        if ($foundPath) {
            return new MatchResult(
                allowedMethods: $methods,
            );
        }

        if ($routeCollection->hasFallback()) {
            $fallback = $routeCollection->getFallback();
            if ($fallback !== null) {
                return new MatchResult(
                    route: $fallback,
                );
            }
        }

        return new MatchResult();
    }

    /**
     * Check if a route URI pattern matches the request path.
     * Populates $params with extracted route parameters on match.
     *
     * @param array<string, string> $params
     */
    private function uriMatches(string $pattern, string $path, ?array &$params) : bool
    {
        if ($pattern === $path) {
            $params = [];

            return true;
        }

        if (! str_contains($pattern, '{')) {
            $params = null;

            return false;
        }

        $compiled = $this->compilePattern($pattern);
        if ($compiled === null) {
            $params = null;

            return false;
        }

        if (! preg_match($compiled['regex'], $path, $matches)) {
            $params = null;

            return false;
        }

        $params = [];
        foreach ($compiled['paramNames'] as $index => $name) {
            $params[$name] = $matches[$index + 1];
        }

        return true;
    }

    /**
     * Compile a route pattern into a regex.
     *
     * @return array{pattern: string, regex: string, paramNames: list<string>}|null
     */
    private function compilePattern(string $pattern) : array|null
    {
        if (isset($this->compiledPatterns[$pattern])) {
            return $this->compiledPatterns[$pattern];
        }

        $paramNames = [];
        $regex      = preg_replace_callback(
            '/\{(\w+)\}/',
            static function (array $matches) use (&$paramNames) : string {
                $paramNames[] = $matches[1];

                return '([^/]+)';
            },
            $pattern,
        );

        if ($regex === null) {
            return null;
        }

        $compiled = [
            'pattern'    => $pattern,
            'regex'      => '#^' . $regex . '$#u',
            'paramNames' => $paramNames,
        ];

        $this->compiledPatterns[$pattern] = $compiled;

        return $compiled;
    }
}
