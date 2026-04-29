<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\MatchRoute;

/**
 * Matches dynamic route patterns with named parameters.
 *
 * Supports patterns like:
 *   /users/{id}
 *   /posts/{slug}/comments/{commentId}
 *   /api/{version}/resources/{id?}  (optional)
 */
final class MatchDynamicRoute
{
    /**
     * Match a route pattern against a path.
     *
     * @param string $pattern Route pattern (e.g., "/users/{id}")
     * @param string $path    Request path (e.g., "/users/42")
     *
     * @return array<string, string>|null Matched parameters or null if no match
     */
    public function match(string $pattern, string $path): ?array
    {
        $regex = $this->compilePattern($pattern);
        if ($regex === null) {
            return null;
        }

        if (! preg_match($regex, $path, $matches)) {
            return null;
        }

        // Extract named parameters from matches
        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key) && $value !== '') {
                $params[$key] = $value;
            }
        }

        return $params !== [] ? $params : null;
    }

    /**
     * Compile a route pattern into a regex.
     *
     * @return string|null Regex pattern or null if invalid
     */
    private function compilePattern(string $pattern) : ?string
    {
        // Replace named parameters with named capture groups
        $regex = preg_replace_callback(
            '/\{(\w+)(\?)?\}/',
            static function (array $matches) : string {
                $name     = $matches[1];
                $optional = isset($matches[2]);

                if ($optional) {
                    return '(?P<' . $name . '>[^/]*)?';
                }

                return '(?P<' . $name . '>[^/]+)';
            },
            $pattern
        );

        if ($regex === null) {
            return null;
        }

        // Escape remaining forward slashes and anchor the pattern
        $regex = '^' . $regex . '$';

        return '#' . $regex . '#';
    }

    /**
     * Extract parameter names from a route pattern.
     *
     * @return list<string>
     */
    public function extractParameters(string $pattern) : array
    {
        preg_match_all('/\{(\w+)\??\}/', $pattern, $matches);

        return $matches[1] ?? [];
    }

    /**
     * Check if a pattern has optional parameters.
     */
    public function hasOptionalParameters(string $pattern) : bool
    {
        return preg_match('/\{\w+\?\}/', $pattern) === 1;
    }
}
