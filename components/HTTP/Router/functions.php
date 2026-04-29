<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\PublicSurface;

use Avax\Components\Application\Text\System\Foundation\Pattern;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteBuilder;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteCollector;
use Avax\Components\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteRegistrarProxy;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface;

if (! function_exists('route_validate_path')) {
    function route_validate_path(string $path): void
    {
        if ($path === '' || $path[0] !== '/') {
            throw new \InvalidArgumentException('Route path must start with a "/" and cannot be empty.');
        }

        if (!route_contains_valid_path_chars($path)) {
            throw new \InvalidArgumentException("Invalid characters in route path: {$path}");
        }

        if (substr_count($path, '{') !== substr_count($path, '}')) {
            throw new \InvalidArgumentException("Unbalanced route parameter braces in path: {$path}");
        }

        route_validate_parameters($path);
    }
}

if (! function_exists('route_contains_valid_path_chars')) {
    function route_contains_valid_path_chars(string $path): bool
    {
        return preg_match('~^[a-zA-Z0-9_./\\-{}?*]*$~', $path) === 1;
    }
}

if (! function_exists('route_validate_parameters')) {
    function route_validate_parameters(string $path): void
    {
        $outside = preg_replace('/\{[^{}]*\}/', '', $path);
        if (str_contains($outside, '?') || str_contains($outside, '*')) {
            throw new \InvalidArgumentException("Wildcard or optional markers must be inside parameters: {$path}");
        }

        preg_match_all('/\{([^{}]+)\}/', $path, $matches);
        $wildcardCount = 0;

        foreach ($matches[1] as $segment) {
            if (!route_matches_parameter($segment)) {
                throw new \InvalidArgumentException("Invalid route parameter syntax in segment {$segment}");
            }

            if (str_ends_with($segment, '*')) {
                $wildcardCount++;

                if ($wildcardCount > 1) {
                    throw new \InvalidArgumentException("Only one wildcard parameter is allowed: {$path}");
                }

                $offset = strpos($path, '{' . $segment . '}');
                if ($offset !== false) {
                    $endOfPlaceholder = $offset + strlen('{' . $segment . '}');
                    if ($endOfPlaceholder !== strlen($path)) {
                        throw new \InvalidArgumentException("Wildcard parameters must be the final path segment: {$path}");
                    }
                }
            }
        }
    }
}

if (! function_exists('route_matches_parameter')) {
    function route_matches_parameter(string $paramName): bool
    {
        return preg_match('~^[a-zA-Z_][a-zA-Z0-9_-]*(?:\?|\*)?$~', $paramName) === 1;
    }
}

if (! function_exists('route_valid')) {
    function route_valid(string $path): bool
    {
        try {
            route_validate_path($path);
            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
}

if (! function_exists('route_compile_pattern')) {
    function route_compile_pattern(string $template, array $constraints = []): string
    {
        route_validate_path($template);

        if ($template === '/') {
            return '#^/$#u';
        }

        $pattern = '';
        foreach (explode('/', trim($template, '/')) as $segment) {
            if (preg_match('/^\{([^}]+)}$/', $segment, $matches) !== 1) {
                $pattern .= '/' . preg_quote($segment, '#');
                continue;
            }

            $parameter = $matches[1];
            $isOptional = str_ends_with($parameter, '?');
            $isWildcard = str_ends_with($parameter, '*');
            $name = preg_replace('/[?*]$/', '', $parameter);
            $constraint = $isWildcard ? '.*' : ($constraints[$name] ?? '[^/]+');
            $group = "(?<{$name}>{$constraint})";

            $pattern .= $isOptional ? "(?:/{$group})?" : "/{$group}";
        }

        return '#^' . $pattern . '$#u';
    }
}

if (! function_exists('route_pattern')) {
    function route_pattern(string $template, array $constraints = []): string
    {
        return route_compile_pattern($template, $constraints);
    }
}

if (! function_exists('route_extract_params')) {
    function route_extract_params(string $path): array
    {
        preg_match_all('/\{([^{}]+)\}/', $path, $matches);
        return array_values(array_filter($matches[1]));
    }
}

if (! function_exists('route_params')) {
    function route_params(string $path): array
    {
        return route_extract_params($path);
    }
}

if (! function_exists('route_path')) {
    function route_path(string $path): string
    {
        $normalizedPath = $path === '/' ? '/' : '/' . trim($path, '/');
        route_validate_path($normalizedPath);
        return $normalizedPath;
    }
}

if (! function_exists('route_match')) {
    function route_match(string $pattern, string $subject): array|null
    {
        $result = preg_match($pattern, $subject, $matches);

        if ($result === 1) {
            return array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
        }

        return null;
    }
}

if (! function_exists('route_compile')) {
    function route_compile(string $template, array $constraints = []): string
    {
        return route_compile_pattern($template, $constraints);
    }
}

if (! function_exists('get')) {
    function get(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            RouteBuilder::make('GET', $path)->action($action)
        );
    }
}

if (! function_exists('post')) {
    function post(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            RouteBuilder::make('POST', $path)->action($action)
        );
    }
}

if (! function_exists('put')) {
    function put(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            RouteBuilder::make('PUT', $path)->action($action)
        );
    }
}

if (! function_exists('patch')) {
    function patch(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            RouteBuilder::make('PATCH', $path)->action($action)
        );
    }
}

if (! function_exists('delete')) {
    function delete(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            RouteBuilder::make('DELETE', $path)->action($action)
        );
    }
}

if (! function_exists('fallback')) {
    function fallback(callable|array|string $handler): void
    {
        RouteCollector::current()->setFallback($handler);
    }
}

if (! function_exists('route')) {
    function route(string $name, array $parameters = []): string|null
    {
        try {
            $router = \app(RouterRuntimeInterface::class);
            $route = $router->getRouteByName($name);
            $path = $route->path;

            foreach ($parameters as $key => $value) {
                $path = preg_replace("/\\{{$key}(?:[?*]?)\\}/", (string)$value, $path);
            }

            return preg_replace('/\{[^}]+\}/', '', $path);
        } catch (\Throwable) {
            return null;
        }
    }
}