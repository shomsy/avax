<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Support;

use InvalidArgumentException;

/**
 * Validates route path patterns for wildcards and optional parameters.
 *
 * Ensures route paths follow strict rules:
 * - Wildcard (*) appears only once and only at path end
 * - Optional (?) parameters stay within segment bounds
 * - Allowed characters: [a-zA-Z0-9_-]
 * - No nested wildcards or invalid combinations
 */
final class RoutePathValidator
{
    /**
     * Regular expression for valid parameter names.
     */
    private const string VALID_PARAM_PATTERN = '/^[a-zA-Z_][a-zA-Z0-9_-]*$/';

    /**
     * Validate a complete route path.
     *
     * @param string $path The route path to validate
     *
     * @throws InvalidArgumentException If path contains invalid patterns
     */
    public static function validate(string $path) : void
    {
        if (empty($path)) {
            throw new InvalidArgumentException(message: 'Route path cannot be empty');
        }

        if ($path[0] !== '/') {
            throw new InvalidArgumentException(message: 'Route path must start with "/"');
        }

        self::validateParameters(path: $path);
        self::validateWildcards(path: $path);
        self::validateOptionalParameters(path: $path);
    }

    /**
     * Validate parameter names and syntax.
     */
    private static function validateParameters(string $path) : void
    {
        // Find all {param} patterns
        preg_match_all('/\{([^}]+)\}/', $path, $matches);

        foreach ($matches[1] as $param) {
            // Remove optional (?) and wildcard (*) markers for validation
            $cleanParam = preg_replace('/[?*]$/', '', $param);

            if (! preg_match(self::VALID_PARAM_PATTERN, $cleanParam)) {
                throw new InvalidArgumentException(
                    message: "Invalid parameter name '{$cleanParam}' in path '{$path}'. " .
                    'Parameter names must match: ' . self::VALID_PARAM_PATTERN
                );
            }

            // Check for nested modifiers
            if (substr_count($param, '?') > 1 || substr_count($param, '*') > 1) {
                throw new InvalidArgumentException(
                    message: "Invalid parameter '{$param}' in path '{$path}': cannot have multiple ? or * modifiers"
                );
            }

            // Check for invalid modifier combinations
            if (str_contains($param, '?') && str_contains($param, '*')) {
                throw new InvalidArgumentException(
                    message: "Invalid parameter '{$param}' in path '{$path}': cannot combine ? and * modifiers"
                );
            }
        }
    }

    /**
     * Validate wildcard (*) usage.
     */
    private static function validateWildcards(string $path) : void
    {
        // Find all wildcard parameters
        preg_match_all('/\{([^}]*\*[^{}]*)\}/', $path, $matches);

        if (count($matches[0]) > 1) {
            throw new InvalidArgumentException(
                message: "Multiple wildcard parameters found in path '{$path}'. Only one wildcard (*) allowed per route."
            );
        }

        if (! empty($matches[0])) {
            $wildcardParam = $matches[0][0];

            // Find position of wildcard in path
            $wildcardPos       = strpos($path, $wildcardParam);
            $pathAfterWildcard = substr($path, $wildcardPos + strlen($wildcardParam));

            // Check if there's anything after the wildcard parameter
            if (! empty(trim($pathAfterWildcard, '/'))) {
                throw new InvalidArgumentException(
                    message: "Wildcard parameter '{$wildcardParam}' must be at the end of the path in '{$path}'"
                );
            }
        }
    }

    /**
     * Validate optional (?) parameter usage.
     */
    private static function validateOptionalParameters(string $path) : void
    {
        // Find all optional parameters
        preg_match_all('/\{([^}]*\?[^{}]*)\}/', $path, $matches);

        foreach ($matches[0] as $optionalParam) {
            // Check if optional parameter is preceded by wildcard
            $paramPos = strpos($path, $optionalParam);

            // Look backwards for wildcard in same path segment
            $segmentStart = strrpos(substr($path, 0, $paramPos), '/');
            $segmentStart = $segmentStart === false ? 0 : $segmentStart;

            $segmentBefore = substr($path, $segmentStart, $paramPos - $segmentStart);

            if (str_contains($segmentBefore, '*')) {
                throw new InvalidArgumentException(
                    message: "Optional parameter '{$optionalParam}' cannot appear after wildcard in path '{$path}'"
                );
            }
        }
    }

    /**
     * Check if a path segment contains parameters.
     */
    public static function hasParameters(string $segment) : bool
    {
        return str_contains($segment, '{') && str_contains($segment, '}');
    }

    /**
     * Extract parameter names from a path.
     *
     * @return string[] Array of parameter names without modifiers
     */
    public static function extractParameterNames(string $path) : array
    {
        preg_match_all('/\{([^}]+)\}/', $path, $matches);

        return array_map(static function ($param) {
            return preg_replace('/[?*]$/', '', $param);
        }, $matches[1]);
    }

    /**
     * Check if path contains wildcard parameters.
     */
    public static function hasWildcard(string $path) : bool
    {
        return str_contains($path, '*');
    }

    /**
     * Check if path contains optional parameters.
     */
    public static function hasOptional(string $path) : bool
    {
        return str_contains($path, '?');
    }
}
