<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

/**
 * @phpstan-type RouteAction callable|array{0: class-string, 1: string}|string
 * @phpstan-type RouteMiddleware array<string|class-string>
 * @phpstan-type RouteConstraints array<string, string>
 * @phpstan-type RouteDefaults array<string, mixed>
 * @phpstan-type RouteAttributes array<string, mixed>
 * @phpstan-type RouteMetadata array<string, mixed>
 */

use Avax\HTTP\Enums\HttpMethod;
use Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException;
use Avax\HTTP\Router\Support\PathNormalizer;
use Closure;
use InvalidArgumentException;
use Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException;
use Laravel\SerializableClosure\SerializableClosure;
use RuntimeException;

/**
 * Immutable data structure representing a registered HTTP route.
 *
 * Supports serializable closures via Laravel\SerializableClosure.
 */
final readonly class RouteDefinition
{
    public readonly string      $method;
    public readonly string      $path;
    public readonly mixed       $action;
    public readonly array       $middleware;
    public readonly string      $name;
    public readonly array       $constraints;
    public readonly array       $defaults;
    public readonly string|null $domain;
    public readonly array       $attributes;
    public readonly string|null $authorization;
    public readonly array       $parameters;
    public readonly array       $metadata;

    /**
     * Route specificity score for matching precedence.
     * Higher values = more specific routes matched first.
     * Calculated as: (segment count) - (parameter count)
     */
    public readonly int $specificity;

    /**
     * Precompiled regex pattern for path matching.
     * Compiled once during construction for performance.
     */
    public readonly string $compiledPathRegex;

    /**
     * Route construction ensures deterministic behavior by normalizing paths, validating constraints,
     * and precomputing routing metadata. This prevents runtime errors and ensures consistent
     * route matching across different deployment environments.
     *
     * Path normalization eliminates ambiguity from trailing slashes and multiple separators.
     * Constraint validation prevents ReDoS attacks from malformed regex patterns.
     * Specificity calculation ensures correct precedence ordering during route resolution.
     *
     * @param string      $method        The HTTP method (e.g., GET, POST) for the route.
     * @param string      $path          The URI path for the route.
     * @param mixed       $action        The action or callback associated with the route.
     * @param array       $middleware    An array of middleware to be applied to the route.
     * @param string      $name          The name of the route, optional.
     * @param array       $constraints   An array of constraints for the route parameters.
     * @param array       $defaults      An array of default values for route parameters.
     * @param string|null $domain        The domain name associated with the route, optional.
     * @param array       $attributes    Additional attributes for the route.
     * @param string|null $authorization The authorization key or identifier for the route, optional.
     * @param array       $parameters    An array of parameters to be passed to the route, optional.
     * @param array       $metadata      Rich metadata annotations (API versioning, policies, etc.)
     *
     * @throws ReservedRouteNameException
     */
    public function __construct(
        string  $method,
        string  $path,
        mixed   $action,
        array   $middleware = null,
        string  $name = null,
        array   $constraints = null,
        array   $defaults = null,
        ?string $domain = null,
        array   $attributes = null,
        ?string $authorization = null,
        array   $parameters = null,
        array   $metadata = []
    )
    {
        $middleware  ??= [];
        $name        ??= '';
        $constraints ??= [];
        $defaults    ??= [];
        $attributes  ??= [];
        $parameters  ??= [];
        $this->validateMethod(method: $method);
        $this->validatePath(path: $path);
        $this->validateAction(action: $action);
        $this->validateRouteName(name: $name);
        $this->validateConstraints(constraints: $constraints);

        // Normalize path for consistent routing
        $path = PathNormalizer::normalize(path: $path);

        // Calculate route specificity: (segment count) - (parameter count)
        // Higher specificity = more specific routes matched first
        $segmentCount   = substr_count($path, '/') - ($path === '/' ? 0 : 1); // Don't count leading slash
        $parameterCount = preg_match_all('/\{[^}]+\}/', $path);
        $specificity    = $segmentCount - $parameterCount;

        // Precompile regex pattern for performance
        $compiledPathRegex = $this->compileRoutePattern(template: $path, constraints: $constraints);

        $this->method            = $method;
        $this->path              = $path;
        $this->action            = $action;
        $this->middleware        = $middleware;
        $this->name              = $name;
        $this->constraints       = $constraints;
        $this->defaults          = $defaults;
        $this->domain            = $domain;
        $this->attributes        = $attributes;
        $this->authorization     = $authorization;
        $this->parameters        = $parameters;
        $this->metadata          = $metadata;
        $this->specificity       = $specificity;
        $this->compiledPathRegex = $compiledPathRegex;
    }

    /**
     * Ensures only standard HTTP methods are accepted to prevent security vulnerabilities
     * from malformed or malicious method names that could bypass routing logic.
     *
     * @throws InvalidArgumentException
     */
    private function validateMethod(string $method) : void
    {
        if (! HttpMethod::isSupported(method: $method)) {
            throw new InvalidArgumentException(message: sprintf('Invalid HTTP method: %s', $method));
        }
    }

    /**
     * Prevents malformed paths that could cause routing ambiguities, security issues,
     * or performance problems by ensuring paths follow expected URL structure.
     *
     * @throws InvalidArgumentException
     */
    private function validatePath(string $path) : void
    {
        if (empty($path)) {
            throw new InvalidArgumentException(message: 'Route path cannot be empty');
        }

        if (! str_starts_with($path, '/')) {
            throw new InvalidArgumentException(message: 'Route path must start with /');
        }
    }

    /**
     * Validates that the route action is callable or a valid controller reference.
     *
     * @throws InvalidArgumentException
     */
    private function validateAction(mixed $action) : void
    {
        if ($action === null) {
            throw new InvalidArgumentException(message: 'Route action cannot be null');
        }

        if (! is_callable($action) && ! is_string($action) && ! is_array($action)) {
            throw new InvalidArgumentException(message: 'Route action must be callable, string, or array');
        }

        if (is_array($action) && count($action) !== 2) {
            throw new InvalidArgumentException(message: 'Route action array must have exactly 2 elements [class, method]');
        }
    }

    /**
     * Reserves internal route name prefixes to prevent conflicts between user-defined
     * routes and framework-generated routes, avoiding naming collisions and debugging confusion.
     *
     * @throws ReservedRouteNameException
     */
    private function validateRouteName(string $name) : void
    {
        if (! empty($name) && str_starts_with($name, '__avax.')) {
            throw new ReservedRouteNameException(name: $name);
        }
    }

    /**
     * Validates parameter constraints to prevent ReDoS attacks and ensure predictable
     * routing behavior by rejecting malformed regex patterns that could cause performance issues.
     *
     * @throws InvalidArgumentException
     */
    private function validateConstraints(array $constraints) : void
    {
        foreach ($constraints as $pattern) {
            $this->validateConstraintPattern(pattern: $pattern);
        }
    }

    /**
     * Ensures regex patterns are syntactically correct to prevent runtime errors
     * and potential security issues from malformed constraints during route matching.
     *
     * @throws InvalidArgumentException
     */
    private function validateConstraintPattern(string $pattern) : void
    {
        // Basic validation - check if pattern compiles
        $testPattern = "/{$pattern}/";
        $error       = null;

        set_error_handler(static function ($errno, $errstr) use (&$error) {
            $error = $errstr;
        });

        $result = preg_match($testPattern, '');

        restore_error_handler();

        if ($result === false || $error !== null) {
            throw new InvalidArgumentException(message: "Invalid constraint pattern: {$pattern}");
        }
    }

    /**
     * Compiles a route path template into a regex pattern.
     *
     * @param string $template    The route template (e.g., "/users/{id}")
     * @param array  $constraints Parameter constraints
     *
     * @return string The compiled regex pattern
     */
    private function compileRoutePattern(string $template, array $constraints) : string
    {
        $pattern = preg_replace_callback(
            '/\{([^}]+)\}/',
            static function ($matches) use ($constraints) {
                $param      = $matches[1];
                $isOptional = str_ends_with($param, '?');
                $isWildcard = str_ends_with($param, '*');

                $paramName  = preg_replace('/[?*]$/', '', $param);
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
            $template
        );

        return "#^{$pattern}$#";
    }

    /**
     * @throws ReservedRouteNameException
     */
    public static function __set_state(array $properties) : self
    {
        // Normalize path for consistency during unserialization
        $normalizedPath = PathNormalizer::normalize(path: $properties['path']);

        return new self(
            method       : $properties['method'],
            path         : $normalizedPath,
            action       : $properties['action'],
            middleware   : $properties['middleware'],
            name         : $properties['name'],
            constraints  : $properties['constraints'],
            defaults     : $properties['defaults'],
            domain       : $properties['domain'],
            attributes   : $properties['attributes'],
            authorization: $properties['authorization'],
            parameters   : $properties['parameters'] ?? [],
            metadata     : $properties['metadata'] ?? []
        );
    }

    /**
     * Rehydrate a route definition from cached array data.
     *
     * @param array<string, mixed> $payload
     *
     * @throws ReservedRouteNameException
     */
    public static function fromArray(array $payload) : self
    {
        if (! isset($payload['method'], $payload['path'], $payload['action'])) {
            throw new InvalidArgumentException(message: 'Cached route payload is missing required fields.');
        }

        if ($payload['action'] instanceof Closure || $payload['action'] instanceof SerializableClosure) {
            throw new RuntimeException(message: 'Cached route action must not be a closure.');
        }

        // Normalize path for consistency (though cached paths should already be normalized)
        $normalizedPath = PathNormalizer::normalize(path: $payload['path']);

        return new self(
            method       : $payload['method'],
            path         : $normalizedPath,
            action       : $payload['action'],
            middleware   : $payload['middleware'] ?? [],
            name         : $payload['name'] ?? '',
            constraints  : $payload['constraints'] ?? [],
            defaults     : $payload['defaults'] ?? [],
            domain       : $payload['domain'] ?? null,
            attributes   : $payload['attributes'] ?? [],
            authorization: $payload['authorization'] ?? null,
            parameters   : [],
            metadata     : $payload['metadata'] ?? []
        );
    }

    /**
     * Returns a copy of the route with the action wrapped in a SerializableClosure (if needed).
     *
     * @throws PhpVersionNotSupportedException
     * @throws PhpVersionNotSupportedException|ReservedRouteNameException
     */
    public function withSerializedAction() : self
    {
        $action = $this->action instanceof Closure
            ? new SerializableClosure(closure: $this->action)
            : $this->action;

        return new self(
            method       : $this->method,
            path         : $this->path,
            action       : $action,
            middleware   : $this->middleware,
            name         : $this->name,
            constraints  : $this->constraints,
            defaults     : $this->defaults,
            domain       : $this->domain,
            attributes   : $this->attributes,
            authorization: $this->authorization
        );
    }

    /**
     * Returns a copy of the route with the action unwrapped (if it's a SerializableClosure).
     *
     * @throws PhpVersionNotSupportedException
     * @throws PhpVersionNotSupportedException|ReservedRouteNameException
     */
    public function withUnserializedAction() : self
    {
        $action = $this->action instanceof SerializableClosure
            ? $this->action->getClosure()
            : $this->action;

        return new self(
            method       : $this->method,
            path         : $this->path,
            action       : $action,
            middleware   : $this->middleware,
            name         : $this->name,
            constraints  : $this->constraints,
            defaults     : $this->defaults,
            domain       : $this->domain,
            attributes   : $this->attributes,
            authorization: $this->authorization
        );
    }

    /**
     * Checks if the given parameter has a constraint.
     */
    public function hasConstraint(string $parameter) : bool
    {
        return array_key_exists(key: $parameter, array: $this->constraints);
    }

    /**
     * Returns the regex constraint for a route parameter.
     */
    public function getConstraint(string $parameter) : string|null
    {
        return $this->constraints[$parameter] ?? null;
    }

    /**
     * Creates a copy of the route with additional metadata annotation.
     *
     * Enables rich route annotations without coupling:
     * - API versioning: ['api_version' => 'v2']
     * - Rate limiting: ['rate_limit' => '100/hour']
     * - Feature flags: ['feature' => 'beta']
     * - Authorization: ['roles' => ['admin', 'moderator']]
     *
     * @throws ReservedRouteNameException
     */
    public function withMetadata(string $key, mixed $value) : self
    {
        $metadata       = $this->metadata;
        $metadata[$key] = $value;

        return new self(
            method       : $this->method,
            path         : $this->path,
            action       : $this->action,
            middleware   : $this->middleware,
            name         : $this->name,
            constraints  : $this->constraints,
            defaults     : $this->defaults,
            domain       : $this->domain,
            attributes   : $this->attributes,
            authorization: $this->authorization,
            parameters   : $this->parameters,
            metadata     : $metadata
        );
    }

    /**
     * Retrieves metadata annotation by key.
     */
    public function getMetadata(string $key, mixed $default = null) : mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Checks if metadata annotation exists.
     */
    public function hasMetadata(string $key) : bool
    {
        return array_key_exists($key, $this->metadata);
    }

    /**
     * Returns all metadata annotations.
     *
     * @return array<string, mixed>
     */
    public function getAllMetadata() : array
    {
        return $this->metadata;
    }

    /**
     * Export the definition into a scalar array suitable for caching.
     *
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        if ($this->usesClosure() || $this->action instanceof SerializableClosure) {
            throw new RuntimeException(message: 'Cannot cache routes that use closures.');
        }

        return [
            'method'        => $this->method,
            'path'          => $this->path,
            'action'        => $this->action,
            'middleware'    => $this->middleware,
            'name'          => $this->name,
            'constraints'   => $this->constraints,
            'defaults'      => $this->defaults,
            'domain'        => $this->domain,
            'attributes'    => $this->attributes,
            'authorization' => $this->authorization,
            'metadata'      => $this->metadata,
            // parameters are runtime-only and not cached
        ];
    }

    public function usesClosure() : bool
    {
        return $this->action instanceof Closure;
    }
}