<?php

declare(strict_types=1);

use Avax\Components\Router\System\Flows\RegisterRoutes\Definitions\RouteBuilder;
use Avax\Components\Router\System\Flows\RegisterRoutes\Files\RouteCollector;
use Avax\Components\Router\System\Flows\RegisterRoutes\Files\RouteRegistrarProxy;
use Avax\Components\Router\System\PublicSurface\RouterRuntimeInterface;
use Avax\Components\Text\System\PublicSurface\Pattern;

/**
 * Router DSL Helper Layer.
 */

if (! function_exists(function: 'route_validate_path')) {
    function route_validate_path(string $path) : void
    {
        if ($path === '' || $path[0] !== '/') {
            throw new InvalidArgumentException(message: 'Route path must start with a "/" and cannot be empty.');
        }

        if (! route_contains_valid_path_chars(path: $path)) {
            throw new InvalidArgumentException(message: "Invalid characters in route path: {$path}");
        }

        if (substr_count(haystack: $path, needle: '{') !== substr_count(haystack: $path, needle: '}')) {
            throw new InvalidArgumentException(message: "Unbalanced route parameter braces in path: {$path}");
        }

        route_validate_parameters(path: $path);
    }
}

if (! function_exists(function: 'route_contains_valid_path_chars')) {
    function route_contains_valid_path_chars(string $path) : bool
    {
        return Pattern::of(raw: '^[a-zA-Z0-9_.\\-/{}?*]*$')->test(subject: $path);
    }
}

if (! function_exists(function: 'route_validate_parameters')) {
    function route_validate_parameters(string $path) : void
    {
        $outside = Pattern::of(raw: '\\{[^{}]*\\}')->replace(subject: $path, replacement: '');
        if (str_contains(haystack: $outside, needle: '?') || str_contains(haystack: $outside, needle: '*')) {
            throw new InvalidArgumentException(message: "Wildcard or optional markers must be inside parameters: {$path}");
        }

        $matches       = Pattern::of(raw: '\\{([^{}]+)\\}')->matchAll(subject: $path);
        $wildcardCount = 0;

        foreach ($matches as $match) {
            $fullMatch = $match[0] ?? '';
            $segment   = $match[1] ?? '';

            if (! route_matches_parameter(paramName: $segment)) {
                throw new InvalidArgumentException(message: "Invalid route parameter syntax in segment {$fullMatch}");
            }

            if (str_ends_with(haystack: $segment, needle: '*')) {
                $wildcardCount++;

                if ($wildcardCount > 1) {
                    throw new InvalidArgumentException(message: "Only one wildcard parameter is allowed: {$path}");
                }

                $offset = strpos(haystack: $path, needle: $fullMatch);
                if ($offset !== false) {
                    $endOfPlaceholder = $offset + strlen(string: $fullMatch);
                    if ($endOfPlaceholder !== strlen(string: $path)) {
                        throw new InvalidArgumentException(message: "Wildcard parameters must be the final path segment: {$path}");
                    }
                }
            }
        }
    }
}

if (! function_exists(function: 'route_matches_parameter')) {
    function route_matches_parameter(string $paramName) : bool
    {
        $pattern = '^[a-zA-Z_][a-zA-Z0-9_-]*(?:\?|\*)?$';

        return Pattern::of(raw: $pattern)->test(subject: $paramName);
    }
}

if (! function_exists(function: 'route_valid')) {
    function route_valid(string $path) : bool
    {
        try {
            route_validate_path(path: $path);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}

if (! function_exists(function: 'route_compile_pattern')) {
    function route_compile_pattern(string $template, array $constraints = []) : string
    {
        route_validate_path(path: $template);

        if ($template === '/') {
            return '#^/$#u';
        }

        $pattern = '';
        foreach (explode(separator: '/', string: trim(string: $template, characters: '/')) as $segment) {
            if (preg_match(pattern: '/^\{([^}]+)}$/', subject: $segment, matches: $matches) !== 1) {
                $pattern .= '/' . preg_quote(str: $segment, delimiter: '#');
                continue;
            }

            $parameter  = $matches[1];
            $isOptional = str_ends_with(haystack: $parameter, needle: '?');
            $isWildcard = str_ends_with(haystack: $parameter, needle: '*');
            $name       = preg_replace(pattern: '/[?*]$/', replacement: '', subject: $parameter);
            $constraint = $isWildcard ? '.*' : ($constraints[$name] ?? '[^/]+');
            $group      = "(?<{$name}>{$constraint})";

            $pattern .= $isOptional ? "(?:/{$group})?" : "/{$group}";
        }

        return '#^' . $pattern . '$#u';
    }
}

if (! function_exists(function: 'route_pattern')) {
    function route_pattern(string $template, array $constraints = []) : string
    {
        return route_compile_pattern(template: $template, constraints: $constraints);
    }
}

if (! function_exists(function: 'route_extract_params')) {
    function route_extract_params(string $path) : array
    {
        $matches = Pattern::of(raw: '\{([^{}]+)\}')->matchAll(subject: $path);
        $params  = [];
        foreach ($matches as $match) {
            $params[] = $match[1] ?? '';
        }

        return array_values(array: array_filter(array: $params));
    }
}

if (! function_exists(function: 'route_params')) {
    function route_params(string $path) : array
    {
        return route_extract_params(path: $path);
    }
}

if (! function_exists(function: 'route_path')) {
    function route_path(string $path) : string
    {
        $normalizedPath = $path === '/'
            ? '/'
            : '/' . trim(string: $path, characters: '/');

        route_validate_path(path: $normalizedPath);

        return $normalizedPath;
    }
}

if (! function_exists(function: 'route_match')) {
    function route_match(string $pattern, string $subject) : array|null
    {
        $matches = [];
        $result  = preg_match(pattern: $pattern, subject: $subject, matches: $matches);

        if ($result === 1) {
            return array_filter(array: $matches, callback: static fn ($key) => ! is_int(value: $key), mode: ARRAY_FILTER_USE_KEY);
        }

        return null;
    }
}

if (! function_exists(function: 'route_compile')) {
    function route_compile(string $template, array $constraints = []) : string
    {
        return route_compile_pattern(template: $template, constraints: $constraints);
    }
}

if (! function_exists(function: 'get')) {
    function get(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            routeBuilder: RouteBuilder::make(method: 'GET', path: $path)->action(action: $action)
        );
    }
}

if (! function_exists(function: 'post')) {
    function post(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            routeBuilder: RouteBuilder::make(method: 'POST', path: $path)->action(action: $action)
        );
    }
}

if (! function_exists(function: 'put')) {
    function put(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            routeBuilder: RouteBuilder::make(method: 'PUT', path: $path)->action(action: $action)
        );
    }
}

if (! function_exists(function: 'patch')) {
    function patch(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            routeBuilder: RouteBuilder::make(method: 'PATCH', path: $path)->action(action: $action)
        );
    }
}

if (! function_exists(function: 'delete')) {
    function delete(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            routeBuilder: RouteBuilder::make(method: 'DELETE', path: $path)->action(action: $action)
        );
    }
}

if (! function_exists(function: 'fallback')) {
    function fallback(callable|array|string $handler) : void
    {
        RouteCollector::current()->setFallback(fallback: $handler);
    }
}

if (! function_exists(function: 'route')) {
    function route(string $name, array $parameters = []) : string|null
    {
        try {
            $router = app(abstract: RouterRuntimeInterface::class);
            $route  = $router->getRouteByName(name: $name);
            $path   = $route->path;

            foreach ($parameters as $key => $value) {
                $path = Pattern::of(raw: "\\{{$key}(?:[?*]?)\\}")->replace(subject: $path, replacement: (string) $value);
            }

            return Pattern::of(raw: '\\{[^}]+\\}')->replace(subject: $path, replacement: '');
        } catch (Throwable) {
            return null;
        }
    }
}
