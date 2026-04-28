<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions;

use InvalidArgumentException;

/**
 * Validates route path patterns for wildcards and optional parameters.
 */
final class RoutePathValidator
{
    private const string VALID_PARAM_PATTERN = '/^[a-zA-Z_][a-zA-Z0-9_-]*$/';

    public static function validate(string $path) : void
    {
        if (empty($path)) {
            throw new InvalidArgumentException('Route path cannot be empty');
        }
        if ($path[0] !== '/') {
            throw new InvalidArgumentException('Route path must start with "/"');
        }

        self::validateParameters($path);
        self::validateWildcards($path);
        self::validateOptionalParameters($path);
    }

    private static function validateParameters(string $path) : void
    {
        preg_match_all('/\{([^}]+)\}/', $path, $matches);
        foreach ($matches[1] as $param) {
            $cleanParam = preg_replace('/[?*]$/', '', $param);
            if (! preg_match(self::VALID_PARAM_PATTERN, $cleanParam)) {
                throw new InvalidArgumentException("Invalid parameter name '{$cleanParam}' in path '{$path}'");
            }
            if (substr_count($param, '?') > 1 || substr_count($param, '*') > 1) {
                throw new InvalidArgumentException("Invalid parameter '{$param}' in path '{$path}': cannot have multiple ? or * modifiers");
            }
            if (str_contains($param, '?') && str_contains($param, '*')) {
                throw new InvalidArgumentException("Invalid parameter '{$param}' in path '{$path}': cannot combine ? and * modifiers");
            }
        }
    }

    private static function validateWildcards(string $path) : void
    {
        preg_match_all('/\{([^}]*\*[^{}]*)\}/', $path, $matches);
        if (count($matches[0]) > 1) {
            throw new InvalidArgumentException("Multiple wildcard parameters found in path '{$path}'");
        }
        if (! empty($matches[0])) {
            $wildcardParam     = $matches[0][0];
            $wildcardPos       = strpos($path, $wildcardParam);
            $pathAfterWildcard = substr($path, $wildcardPos + strlen($wildcardParam));
            if (! empty(trim($pathAfterWildcard, '/'))) {
                throw new InvalidArgumentException("Wildcard parameter '{$wildcardParam}' must be at the end of the path in '{$path}'");
            }
        }
    }

    private static function validateOptionalParameters(string $path) : void
    {
        preg_match_all('/\{([^}]*\?[^{}]*)\}/', $path, $matches);
        foreach ($matches[0] as $optionalParam) {
            $paramPos      = strpos($path, $optionalParam);
            $segmentStart  = strrpos(substr($path, 0, $paramPos), '/');
            $segmentStart  = $segmentStart === false ? 0 : $segmentStart;
            $segmentBefore = substr($path, $segmentStart, $paramPos - $segmentStart);
            if (str_contains($segmentBefore, '*')) {
                throw new InvalidArgumentException("Optional parameter '{$optionalParam}' cannot appear after wildcard in path '{$path}'");
            }
        }
    }

    public static function extractParameterNames(string $path) : array
    {
        preg_match_all('/\{([^}]+)\}/', $path, $matches);

        return array_map(static function ($param) {
            return preg_replace('/[?*]$/', '', $param);
        }, $matches[1]);
    }
}
