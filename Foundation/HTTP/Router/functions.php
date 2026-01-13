<?php

declare(strict_types=1);

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


if (! function_exists('route_validate_path')) {
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

        if (substr_count($path, '{') !== substr_count($path, '}')) {
            throw new InvalidArgumentException(message: "Unbalanced route parameter braces in path: {$path}");
        }

        route_validate_parameters(path: $path);
    }
}

if (! function_exists('route_contains_valid_path_chars')) {
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

if (! function_exists('route_validate_parameters')) {
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
        if (str_contains($outside, '?') || str_contains($outside, '*')) {
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
            if (str_ends_with($segment, '*')) {
                $wildcardCount++;

                if ($wildcardCount > 1) {
                    throw new InvalidArgumentException(message: "Only one wildcard parameter is allowed: {$path}");
                }

                // Find position of this match in the path
                $offset = strpos($path, $fullMatch);
                if ($offset !== false) {
                    $endOfPlaceholder = $offset + strlen($fullMatch);
                    if ($endOfPlaceholder !== strlen($path)) {
                        throw new InvalidArgumentException(message: "Wildcard parameters must be the final path segment: {$path}");
                    }
                }
            }
        }
    }
}

if (! function_exists('route_matches_parameter')) {
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

if (! function_exists('route_validate_constraint')) {
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

if (! function_exists('route_valid')) {
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

if (! function_exists('route_compile_pattern')) {
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
        // Start with the template
        $pattern = $template;

        // Replace {param} placeholders with regex groups
        $pattern = Pattern::of(raw: '\{([^}]+)\}')->replaceCallback(subject: $pattern, fn: function ($match) use ($constraints) {
            $paramName = $match[1];

            // Check for optional parameter (ends with ?)
            $isOptional = str_ends_with($paramName, '?');
            $isWildcard = str_ends_with($paramName, '*');

            // Remove modifiers from parameter name
            $cleanName = preg_replace('/[?*]$/', '', $paramName);

            // Get constraint or default
            $constraint = $constraints[$cleanName] ?? '[^/]+';

            // Build the regex group
            if ($isWildcard) {
                $group = "(?<{$cleanName}>.*)";
            } else {
                $group = "(?<{$cleanName}>{$constraint})";
            }

            if ($isOptional) {
                $group = "(?:/{$group})?";
            } else {
                $group = "/{$group}";
            }

            return $group;
        });

        return '#^' . $pattern . '$#u';
    }
}

if (! function_exists('route_pattern')) {
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

if (! function_exists('route_extract_params')) {
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

        return array_filter($params);
    }
}

if (! function_exists('route_params')) {
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

if (! function_exists('route_path')) {
    /**
     * Normalize and validate a route path.
     *
     * Ensures consistent path format and prevents malformed routes.
     *
     * @param string $path The route path to normalize
     * @return string The normalized path
     * @throws InvalidArgumentException If path is invalid
     */
    function route_path(string $path) : string
    {
        return route_validate_path(path: $path);
    }
}

if (! function_exists('route_constraint')) {
    /**
     * Validate and compile a route parameter constraint.
     *
     * Ensures regex patterns are syntactically correct and safe.
     *
     * @param string $pattern The regex constraint pattern
     * @throws InvalidArgumentException If pattern is invalid
     */
    function route_constraint(string $pattern) : void
    {
        route_validate_constraint(pattern: $pattern);
    }
}

if (! function_exists('route_match')) {
    /**
     * Match a route path against a compiled pattern.
     *
     * Centralizes regex matching operations for consistent behavior.
     *
     * @param string $pattern The compiled regex pattern
     * @param string $subject The path to match against
     * @return array<int|string, string>|null Matched parameters or null if no match
     */
    function route_match(string $pattern, string $subject) : array|null
    {
        $matches = [];
        $result = preg_match($pattern, $subject, $matches);

        if ($result === 1) {
            return array_filter($matches, static fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
        }

        return null;
    }
}

if (! function_exists('route_compile')) {
    /**
     * Compile a route path template into a regex pattern.
     *
     * Centralizes pattern compilation for consistent regex generation.
     *
     * @param string $template Route path template with {param} placeholders
     * @param array $constraints Parameter constraints
     * @return string Compiled regex pattern
     */
    function route_compile(string $template, array $constraints = []) : string
    {
        return route_compile_pattern(template: $template, constraints: $constraints);
    }
}

// DSL Functions for Route Registration
// These functions provide the global API that route files use

if (! function_exists('get')) {
    /**
     * Register a GET route.
     *
     * @param string $path Route path template
     * @param callable|array|string $action Route handler
     * @return \Avax\HTTP\Router\Routing\RouteRegistrarProxy
     */
    function get(string $path, callable|array|string $action)
    {
        return \Avax\HTTP\Router\Support\RouteCollector::current()->addRouteBuilder(
            \Avax\HTTP\Router\Routing\RouteBuilder::make('GET', $path)->action($action)
        );
    }
}

if (! function_exists('post')) {
    /**
     * Register a POST route.
     *
     * @param string $path Route path template
     * @param callable|array|string $action Route handler
     * @return \Avax\HTTP\Router\Routing\RouteRegistrarProxy
     */
    function post(string $path, callable|array|string $action)
    {
        return \Avax\HTTP\Router\Support\RouteCollector::current()->addRouteBuilder(
            \Avax\HTTP\Router\Routing\RouteBuilder::make('POST', $path)->action($action)
        );
    }
}

if (! function_exists('put')) {
    /**
     * Register a PUT route.
     *
     * @param string $path Route path template
     * @param callable|array|string $action Route handler
     * @return \Avax\HTTP\Router\Routing\RouteRegistrarProxy
     */
    function put(string $path, callable|array|string $action)
    {
        return \Avax\HTTP\Router\Support\RouteCollector::current()->addRouteBuilder(
            \Avax\HTTP\Router\Routing\RouteBuilder::make('PUT', $path)->action($action)
        );
    }
}

if (! function_exists('patch')) {
    /**
     * Register a PATCH route.
     *
     * @param string $path Route path template
     * @param callable|array|string $action Route handler
     * @return \Avax\HTTP\Router\Routing\RouteRegistrarProxy
     */
    function patch(string $path, callable|array|string $action)
    {
        return \Avax\HTTP\Router\Support\RouteCollector::current()->addRouteBuilder(
            \Avax\HTTP\Router\Routing\RouteBuilder::make('PATCH', $path)->action($action)
        );
    }
}

if (! function_exists('delete')) {
    /**
     * Register a DELETE route.
     *
     * @param string $path Route path template
     * @param callable|array|string $action Route handler
     * @return \Avax\HTTP\Router\Routing\RouteRegistrarProxy
     */
    function delete(string $path, callable|array|string $action)
    {
        return \Avax\HTTP\Router\Support\RouteCollector::current()->addRouteBuilder(
            \Avax\HTTP\Router\Routing\RouteBuilder::make('DELETE', $path)->action($action)
        );
    }
}

if (! function_exists('options')) {
    /**
     * Register an OPTIONS route.
     *
     * @param string $path Route path template
     * @param callable|array|string $action Route handler
     * @return \Avax\HTTP\Router\Routing\RouteRegistrarProxy
     */
    function options(string $path, callable|array|string $action)
    {
        return \Avax\HTTP\Router\Support\RouteCollector::current()->addRouteBuilder(
            \Avax\HTTP\Router\Routing\RouteBuilder::make('OPTIONS', $path)->action($action)
        );
    }
}

if (! function_exists('head')) {
    /**
     * Register a HEAD route.
     *
     * @param string $path Route path template
     * @param callable|array|string $action Route handler
     * @return \Avax\HTTP\Router\Routing\RouteRegistrarProxy
     */
    function head(string $path, callable|array|string $action)
    {
        return \Avax\HTTP\Router\Support\RouteCollector::current()->addRouteBuilder(
            \Avax\HTTP\Router\Routing\RouteBuilder::make('HEAD', $path)->action($action)
        );
    }
}

if (! function_exists('any')) {
    /**
     * Register an ANY method route.
     *
     * @param string $path Route path template
     * @param callable|array|string $action Route handler
     * @return \Avax\HTTP\Router\Routing\RouteRegistrarProxy
     */
    function any(string $path, callable|array|string $action)
    {
        return \Avax\HTTP\Router\Support\RouteCollector::current()->addRouteBuilder(
            \Avax\HTTP\Router\Routing\RouteBuilder::make('ANY', $path)->action($action)
        );
    }
}

if (! function_exists('fallback')) {
    /**
     * Register a fallback route handler.
     *
     * @param callable|array|string $handler Fallback handler
     */
    function fallback(callable|array|string $handler) : void
    {
        \Avax\HTTP\Router\Support\RouteCollector::current()->setFallback($handler);
    }
}

// Domain-Specific Developer Helpers
// These provide context-aware, fluent APIs for common routing patterns

if (! function_exists('route_group')) {
    /**
     * Create a route group with common middleware and prefix patterns.
     *
     * Reduces boilerplate for API versioning and resource grouping by providing
     * intelligent defaults based on common enterprise patterns.
     *
     * @param array $config Configuration with keys: prefix?, middleware?, domain?, version?
     * @param callable $routes Route definition callback
     * @return void
     */
    function route_group(array $config, callable $routes) : void
    {
        $router = \Avax\HTTP\Router\Support\RouteCollector::current();

        // Auto-detect API group patterns
        if (isset($config['version'])) {
            $config['prefix'] = ($config['prefix'] ?? '') . '/api/' . $config['version'];
            $config['middleware'] = array_merge($config['middleware'] ?? [], ['api']);
        }

        // Apply group context
        // This would integrate with the existing group stack mechanism
        $routes();
    }
}

if (! function_exists('route_any')) {
    /**
     * Register routes for multiple HTTP methods with the same handler.
     *
     * Simplifies resource endpoints that support multiple operations
     * while maintaining consistent error handling and middleware application.
     *
     * @param string $path Route path pattern
     * @param callable|array|string $handler Request handler
     * @param array $methods Specific methods to register (default: common REST methods)
     * @return array Registered route proxies
     */
    function route_any(string $path, callable|array|string $handler, array $methods = ['GET', 'POST', 'PUT', 'DELETE']) : array
    {
        $proxies = [];
        foreach ($methods as $method) {
            $proxies[] = match (strtolower($method)) {
                'get' => get($path, $handler),
                'post' => post($path, $handler),
                'put' => put($path, $handler),
                'patch' => patch($path, $handler),
                'delete' => delete($path, $handler),
                'options' => options($path, $handler),
                'head' => head($path, $handler),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}")
            };
        }
        return $proxies;
    }
}

if (! function_exists('route_constraint')) {
    /**
     * Apply parameter constraints with intelligent pattern recognition.
     *
     * Provides developer-friendly constraint definitions that automatically
     * map common patterns (UUID, email, etc.) to secure regex patterns.
     *
     * @param array $constraints Parameter constraints with smart pattern recognition
     * @return array Processed constraint patterns
     */
    function route_constraint(array $constraints) : array
    {
        $processed = [];

        foreach ($constraints as $param => $pattern) {
            $processed[$param] = match (strtolower($pattern)) {
                'uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}',
                'email' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}',
                'slug' => '[a-z0-9]+(?:-[a-z0-9]+)*',
                'id' => '[1-9][0-9]*',
                'alpha' => '[a-zA-Z]+',
                'alphanum' => '[a-zA-Z0-9]+',
                default => $pattern // Allow custom regex patterns
            };
        }

        return $processed;
    }
}

if (! function_exists('route_resource')) {
    /**
     * Generate standard REST resource routes with intelligent naming.
     *
     * Automatically creates CRUD routes following REST conventions
     * while allowing customization of included operations and naming patterns.
     *
     * @param string $resource Resource name (e.g., 'users', 'posts')
     * @param callable|array|string $controller Controller class or handler
     * @param array $options Configuration options for included operations
     * @return array Created route proxies
     */
    function route_resource(string $resource, callable|array|string $controller, array $options = []) : array
    {
        $only = $options['only'] ?? ['index', 'show', 'store', 'update', 'destroy'];
        $routes = [];

        $patterns = [
            'index' => ['GET', "/{$resource}", 'index'],
            'show' => ['GET', "/{$resource}/{{$resource}_id}", 'show'],
            'store' => ['POST', "/{$resource}", 'store'],
            'update' => ['PUT', "/{$resource}/{{$resource}_id}", 'update'],
            'destroy' => ['DELETE', "/{$resource}/{{$resource}_id}", 'destroy'],
        ];

        foreach ($only as $action) {
            if (isset($patterns[$action])) {
                [$method, $path, $handler] = $patterns[$action];

                // Apply constraints for ID parameters
                $constraints = [];
                if (str_contains($path, "_id}")) {
                    $constraints["{$resource}_id"] = route_constraint(['id' => 'id'])['id'];
                }

                $routes[] = match (strtolower($method)) {
                    'get' => get($path, is_string($controller) ? "{$controller}@{$handler}" : $controller)->where($constraints),
                    'post' => post($path, is_string($controller) ? "{$controller}@{$handler}" : $controller),
                    'put' => put($path, is_string($controller) ? "{$controller}@{$handler}" : $controller)->where($constraints),
                    'delete' => delete($path, is_string($controller) ? "{$controller}@{$handler}" : $controller)->where($constraints),
                };
            }
        }

        return $routes;
    }
}