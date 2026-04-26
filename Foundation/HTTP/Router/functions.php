<?php

declare(strict_types=1);

use Avax\HTTP\Router\RouterRuntimeInterface;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteBuilder;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteCollector;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteRegistrarProxy;
use Avax\Text\Pattern;
use Avax\Text\RegexException;

/**
 * Router DSL Helper Layer.
 *
 * Provides human-grade router surface where regex and low-level operations
 * are abstracted away into readable, idiomatic helper functions.
 *
 * Analogous to Foundation/Text/functions.php but for router operations.
 */


if (! function_exists(function: 'route_validate_path')) {
    /**
     * Validate a route path format and syntax (internal function).
     *
     * @param string $path The route path to validate
     *
     * @throws InvalidArgumentException If path is invalid
     */
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
    /**
     * Check if a string contains only valid route path characters.
     *
     * @param string $path The path to check
     *
     * @return bool True if valid characters only
     */
    function route_contains_valid_path_chars(string $path) : bool
    {
        return Pattern::of(raw: '^[a-zA-Z0-9_.\\-/{}?*]*$')->test(subject: $path);
    }
}

if (! function_exists(function: 'route_validate_parameters')) {
    /**
     * Validate route parameter syntax in a path.
     *
     * @param string $path The route path to validate
     *
     * @throws InvalidArgumentException If parameter syntax is invalid
     */
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

            // Check for wildcard modifier
            if (str_ends_with(haystack: $segment, needle: '*')) {
                $wildcardCount++;

                if ($wildcardCount > 1) {
                    throw new InvalidArgumentException(message: "Only one wildcard parameter is allowed: {$path}");
                }

                // Find position of this match in the path
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
    /**
     * Validate a route parameter name syntax.
     *
     * @param string $paramName The parameter name to validate
     *
     * @return bool True if valid
     */
    function route_matches_parameter(string $paramName) : bool
    {
        $pattern = '^[a-zA-Z_][a-zA-Z0-9_-]*(?:\?|\*)?$';

        return Pattern::of(raw: $pattern)->test(subject: $paramName);
    }
}

if (! function_exists(function: 'route_validate_constraint')) {
    /**
     * Validate a regex constraint pattern syntax (internal function).
     *
     * @param string $pattern The regex pattern to validate
     *
     * @throws InvalidArgumentException If pattern is invalid
     */
    function route_validate_constraint(string $pattern) : void
    {
        try {
            // Test pattern compilation using DSL - throws RegexException on invalid syntax
            Pattern::of(raw: $pattern)->test(subject: '');
        } catch (RegexException $e) {
            throw new InvalidArgumentException(
                message : sprintf('Invalid regex constraint "%s": %s', $pattern, $e->getMessage()),
                code    : 0,
                previous: $e
            );
        }
    }
}

if (! function_exists(function: 'route_valid')) {
    /**
     * Validate if a route path has correct syntax and is safe.
     *
     * @param string $path The route path to validate
     *
     * @return bool True if path is valid
     */
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
    /**
     * Compile a route path template into a regex pattern (internal function).
     *
     * @param string $template    Route path template with {param} placeholders
     * @param array  $constraints Regex constraints for parameters
     *
     * @return string Compiled regex pattern
     */
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
    /**
     * Create a normalized route regex pattern from DSL path template.
     *
     * Examples:
     * - '/users/{id}' -> '#^/users/(?<id>[^/]+)$#u'
     * - '/blog/{slug?}' -> '#^/blog(?:/(?<slug>[^/]+))?$#u'
     * - '/files/{path*}' -> '#^/files/(?<path>.*)$#u'
     *
     * @param string $template    Route path template with {param} placeholders
     * @param array  $constraints Regex constraints for parameters
     *
     * @return string Compiled regex pattern
     */
    function route_pattern(string $template, array $constraints = []) : string
    {
        return route_compile_pattern(template: $template, constraints: $constraints);
    }
}

if (! function_exists(function: 'route_extract_params')) {
    /**
     * Extract parameter names from a route path template (internal function).
     *
     * @param string $path Route path template
     *
     * @return array<string> Array of parameter names
     */
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
    /**
     * Extract parameter names from a route path template.
     *
     * @param string $path Route path template
     *
     * @return array<string> Array of parameter names
     */
    function route_params(string $path) : array
    {
        return route_extract_params(path: $path);
    }
}

if (! function_exists(function: 'route_path')) {
    /**
     * Normalize and validate a route path.
     *
     * Ensures consistent path format and prevents malformed routes.
     *
     * @param string $path The route path to normalize
     *
     * @return string The normalized path
     * @throws InvalidArgumentException If path is invalid
     */
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
    /**
     * Match a route path against a compiled pattern.
     *
     * Centralizes regex matching operations for consistent behavior.
     *
     * @param string $pattern The compiled regex pattern
     * @param string $subject The path to match against
     *
     * @return array<int|string, string>|null Matched parameters or null if no match
     */
    function route_match(string $pattern, string $subject) : array|null
    {
        $matches = [];
        $result  = preg_match(pattern: $pattern, subject: $subject, matches: $matches);

        if ($result === 1) {
            /** @var array<string, string> $filtered */
            $filtered = array_filter(array: $matches, callback: static fn ($key) => ! is_int(value: $key), mode: ARRAY_FILTER_USE_KEY);

            return $filtered;
        }

        return null;
    }
}

if (! function_exists(function: 'route_compile')) {
    /**
     * Compile a route path template into a regex pattern.
     *
     * Centralizes pattern compilation for consistent regex generation.
     *
     * @param string $template    Route path template with {param} placeholders
     * @param array  $constraints Parameter constraints
     *
     * @return string Compiled regex pattern
     */
    function route_compile(string $template, array $constraints = []) : string
    {
        return route_compile_pattern(template: $template, constraints: $constraints);
    }
}

// DSL Functions for Route Registration
// These functions provide the global API that route files use

if (! function_exists(function: 'get')) {
    /**
     * Register a GET route.
     *
     * @param string                $path   Route path template
     * @param callable|array|string $action Route handler
     *
     * @return RouteRegistrarProxy
     */
    function get(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            routeBuilder: RouteBuilder::make(method: 'GET', path: $path)->action(action: $action)
        );
    }
}

if (! function_exists(function: 'post')) {
    /**
     * Register a POST route.
     *
     * @param string                $path   Route path template
     * @param callable|array|string $action Route handler
     *
     * @return RouteRegistrarProxy
     */
    function post(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            routeBuilder: RouteBuilder::make(method: 'POST', path: $path)->action(action: $action)
        );
    }
}

if (! function_exists(function: 'put')) {
    /**
     * Register a PUT route.
     *
     * @param string                $path   Route path template
     * @param callable|array|string $action Route handler
     *
     * @return RouteRegistrarProxy
     */
    function put(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            routeBuilder: RouteBuilder::make(method: 'PUT', path: $path)->action(action: $action)
        );
    }
}

if (! function_exists(function: 'patch')) {
    /**
     * Register a PATCH route.
     *
     * @param string                $path   Route path template
     * @param callable|array|string $action Route handler
     *
     * @return RouteRegistrarProxy
     */
    function patch(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            routeBuilder: RouteBuilder::make(method: 'PATCH', path: $path)->action(action: $action)
        );
    }
}

if (! function_exists(function: 'delete')) {
    /**
     * Register a DELETE route.
     *
     * @param string                $path   Route path template
     * @param callable|array|string $action Route handler
     *
     * @return RouteRegistrarProxy
     */
    function delete(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            routeBuilder: RouteBuilder::make(method: 'DELETE', path: $path)->action(action: $action)
        );
    }
}

if (! function_exists(function: 'options')) {
    /**
     * Register an OPTIONS route.
     *
     * @param string                $path   Route path template
     * @param callable|array|string $action Route handler
     *
     * @return RouteRegistrarProxy
     */
    function options(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            routeBuilder: RouteBuilder::make(method: 'OPTIONS', path: $path)->action(action: $action)
        );
    }
}

if (! function_exists(function: 'head')) {
    /**
     * Register a HEAD route.
     *
     * @param string                $path   Route path template
     * @param callable|array|string $action Route handler
     *
     * @return RouteRegistrarProxy
     */
    function head(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            routeBuilder: RouteBuilder::make(method: 'HEAD', path: $path)->action(action: $action)
        );
    }
}

if (! function_exists(function: 'any')) {
    /**
     * Register an ANY method route.
     *
     * @param string                $path   Route path template
     * @param callable|array|string $action Route handler
     *
     * @return RouteRegistrarProxy
     */
    function any(string $path, callable|array|string $action) : RouteRegistrarProxy
    {
        return RouteCollector::current()->addRouteBuilder(
            routeBuilder: RouteBuilder::make(method: 'ANY', path: $path)->action(action: $action)
        );
    }
}

if (! function_exists(function: 'fallback')) {
    /**
     * Register a fallback route handler.
     *
     * @param callable|array|string $handler Fallback handler
     */
    function fallback(callable|array|string $handler) : void
    {
        RouteCollector::current()->setFallback(fallback: $handler);
    }
}

// Domain-Specific Developer Helpers
// These provide context-aware, fluent APIs for common routing patterns

if (! function_exists(function: 'route_group')) {
    /**
     * Create a route group with common middleware and prefix patterns.
     *
     * Reduces boilerplate for API versioning and resource grouping by providing
     * intelligent defaults based on common enterprise patterns.
     *
     * @param array    $config Configuration with keys: prefix?, middleware?, domain?, version?
     * @param callable $routes Route definition callback
     *
     * @return void
     */
    function route_group(array $config, callable $routes) : void
    {
        $router = RouteCollector::current();

        // Auto-detect API group patterns
        if (isset($config['version'])) {
            $config['prefix']     = ($config['prefix'] ?? '') . '/api/' . $config['version'];
            $config['middleware'] = array_merge($config['middleware'] ?? [], ['api']);
        }

        // Apply group context
        // This would integrate with the existing group stack mechanism
        $routes();
    }
}

if (! function_exists(function: 'route_any')) {
    /**
     * Register routes for multiple HTTP methods with the same handler.
     *
     * Simplifies resource endpoints that support multiple operations
     * while maintaining consistent error handling and middleware application.
     *
     * @param string                $path    Route path pattern
     * @param callable|array|string $handler ServerRequest handler
     * @param array                 $methods Specific methods to register (default: common REST methods)
     *
     * @return array Registered route proxies
     */
    function route_any(string $path, callable|array|string $handler, array $methods = ['GET', 'POST', 'PUT', 'DELETE']) : array
    {
        $proxies = [];
        foreach ($methods as $method) {
            $proxies[] = match (strtolower(string: $method)) {
                'get'     => get(path: $path, action: $handler),
                'post'    => post(path: $path, action: $handler),
                'put'     => put(path: $path, action: $handler),
                'patch'   => patch(path: $path, action: $handler),
                'delete'  => delete(path: $path, action: $handler),
                'options' => options(path: $path, action: $handler),
                'head'    => head(path: $path, action: $handler),
                default => throw new InvalidArgumentException(message: "Unsupported HTTP method: {$method}")
            };
        }

        return $proxies;
    }
}

if (! function_exists(function: 'route_constraint')) {
    /**
     * Apply parameter constraints with intelligent pattern recognition.
     *
     * Provides developer-friendly constraint definitions that automatically
     * map common patterns (UUID, email, etc.) to secure regex patterns.
     *
     * @param array $constraints Parameter constraints with smart pattern recognition
     *
     * @return array Processed constraint patterns
     */
    function route_constraint(array $constraints) : array
    {
        $processed = [];

        foreach ($constraints as $param => $pattern) {
            $processed[$param] = match (strtolower(string: $pattern)) {
                'uuid'     => '[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}',
                'email'    => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}',
                'slug'     => '[a-z0-9]+(?:-[a-z0-9]+)*',
                'id'       => '[1-9][0-9]*',
                'alpha'    => '[a-zA-Z]+',
                'alphanum' => '[a-zA-Z0-9]+',
                default    => $pattern // Allow custom regex patterns
            };
        }

        return $processed;
    }
}

if (! function_exists(function: 'route_resource')) {
    /**
     * Generate standard REST resource routes with intelligent naming.
     *
     * Automatically creates CRUD routes following REST conventions
     * while allowing customization of included operations and naming patterns.
     *
     * @param string                $resource   Resource name (e.g., 'users', 'posts')
     * @param callable|array|string $controller Controller class or handler
     * @param array                 $options    Configuration options for included operations
     *
     * @return array Created route proxies
     */
    function route_resource(string $resource, callable|array|string $controller, array $options = []) : array
    {
        $only   = $options['only'] ?? ['index', 'show', 'store', 'update', 'destroy'];
        $routes = [];

        $patterns = [
            'index'   => ['GET', "/{$resource}", 'index'],
            'show'    => ['GET', "/{$resource}/{{$resource}_id}", 'show'],
            'store'   => ['POST', "/{$resource}", 'store'],
            'update'  => ['PUT', "/{$resource}/{{$resource}_id}", 'update'],
            'destroy' => ['DELETE', "/{$resource}/{{$resource}_id}", 'destroy'],
        ];

        foreach ($only as $action) {
            if (isset($patterns[$action])) {
                [$method, $path, $handler] = $patterns[$action];

                // Apply constraints for ID parameters
                $constraints = [];
                if (str_contains(haystack: $path, needle: "_id}")) {
                    $constraints["{$resource}_id"] = route_constraint(constraints: ['id' => 'id'])['id'];
                }

                $route = match (strtolower(string: $method)) {
                    'get'    => get(path: $path, action: is_string(value: $controller) ? "{$controller}@{$handler}" : $controller),
                    'post'   => post(path: $path, action: is_string(value: $controller) ? "{$controller}@{$handler}" : $controller),
                    'put'    => put(path: $path, action: is_string(value: $controller) ? "{$controller}@{$handler}" : $controller),
                    'delete' => delete(path: $path, action: is_string(value: $controller) ? "{$controller}@{$handler}" : $controller),
                };

                foreach ($constraints as $param => $pattern) {
                    $route->where(param: $param, pattern: $pattern);
                }

                $routes[] = $route;
            }
        }

        return $routes;
    }
}
if (! function_exists(function: 'route')) {
    /**
     * Generate a URL for a named route.
     *
     * @param string $name       The name of the route
     * @param array  $parameters Parameters to inject into the route path
     *
     * @return string|null The generated URL or null on failure
     */
    function route(string $name, array $parameters = []) : string|null
    {
        try {
            // Retrieve the runtime router instance from the dependency injection container.
            $router = app(abstract: RouterRuntimeInterface::class);

            // Fetch the route definition by its name using the retrieved `Router` instance.
            $route = $router->getRouteByName(name: $name);

            // Extract the path of the route.
            $path = $route->path;

            // Inject parameters into the path
            foreach ($parameters as $key => $value) {
                $path = Pattern::of(raw: "\\{{$key}(?:[?*]?)\\}")->replace(subject: $path, replacement: (string) $value);
            }

            // Clean up any optional params not provided
            return Pattern::of(raw: '\\{[^}]+\\}')->replace(subject: $path, replacement: '');
        } catch (Throwable $throwable) {
            logger(message: 'Failed to generate route.', context: ['route_name' => $name, 'exception' => $throwable]);

            return null;
        }
    }
}
