<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Capabilities\RouteDefinition;

/**
 * @phpstan-type RouteAction callable|array{0: class-string, 1: string}|string
 * @phpstan-type RouteMiddleware array<string|class-string>
 * @phpstan-type RouteConstraints array<string, string>
 * @phpstan-type RouteDefaults array<string, mixed>
 * @phpstan-type RouteAttributes array<string, mixed>
 * @phpstan-type RouteMetadata array<string, mixed>
 */

use Avax\Components\Router\System\Capabilities\Paths\PathNormalizer;
use Avax\Components\Router\System\Foundation\Exceptions\ReservedRouteNameException;
use Avax\Components\Router\System\PublicSurface\HttpMethod;
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
     * @throws ReservedRouteNameException
     */
    public function __construct(
        string      $method,
        string      $path,
        mixed       $action,
        array|null  $middleware = null,
        string|null $name = null,
        array|null  $constraints = null,
        array|null  $defaults = null,
        string|null $domain = null,
        array|null  $attributes = null,
        string|null $authorization = null,
        array|null  $parameters = null,
        array       $metadata = []
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
        $segmentCount   = substr_count(haystack: $path, needle: '/') - ($path === '/' ? 0 : 1);
        $parameterCount = preg_match_all(pattern: '/\{[^}]+\}/', subject: $path);
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
     * Ensures only standard HTTP methods are accepted.
     *
     * @throws InvalidArgumentException
     */
    private function validateMethod(string $method) : void
    {
        if ($method === HttpMethod::ANY->value) {
            return;
        }

        if (! HttpMethod::isValid(method: $method)) {
            throw new InvalidArgumentException(message: sprintf('Invalid HTTP method: %s', $method));
        }
    }

    /**
     * Prevents malformed paths.
     *
     * @throws InvalidArgumentException
     */
    private function validatePath(string $path) : void
    {
        if (empty($path)) {
            throw new InvalidArgumentException(message: 'Route path cannot be empty');
        }

        if (! str_starts_with(haystack: $path, needle: '/')) {
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

        if (! is_callable(value: $action) && ! is_string(value: $action) && ! is_array(value: $action)) {
            throw new InvalidArgumentException(message: 'Route action must be callable, string, or array');
        }

        if (is_array(value: $action) && count(value: $action) !== 2) {
            throw new InvalidArgumentException(message: 'Route action array must have exactly 2 elements [class, method]');
        }
    }

    /**
     * Reserves internal route name prefixes.
     *
     * @throws ReservedRouteNameException
     */
    private function validateRouteName(string $name) : void
    {
        if (! empty($name) && str_starts_with(haystack: $name, needle: '__avax.')) {
            throw new ReservedRouteNameException(name: $name);
        }
    }

    /**
     * Validates parameter constraints to prevent ReDoS attacks.
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
     * Ensures regex patterns are syntactically correct.
     *
     * @throws InvalidArgumentException
     */
    private function validateConstraintPattern(string $pattern) : void
    {
        // Basic validation - check if pattern compiles
        $testPattern = "/{$pattern}/";
        $error       = null;

        set_error_handler(callback: static function ($errno, $errstr) use (&$error) {
            $error = $errstr;
        });

        $result = preg_match(pattern: $testPattern, subject: '');

        restore_error_handler();

        if ($result === false || $error !== null) {
            throw new InvalidArgumentException(message: "Invalid constraint pattern: {$pattern}");
        }
    }

    /**
     * Compiles a route path template into a regex pattern.
     */
    private function compileRoutePattern(string $template, array $constraints) : string
    {
        if ($template === '/') {
            return '#^/$#';
        }

        $pattern = '';
        foreach (explode(separator: '/', string: trim(string: $template, characters: '/')) as $segment) {
            if (preg_match(pattern: '/^\{([^}]+)\}$/', subject: $segment, matches: $matches) !== 1) {
                $pattern .= '/' . preg_quote(str: $segment, delimiter: '#');
                continue;
            }

            $parameter  = $matches[1];
            $isOptional = str_ends_with(haystack: $parameter, needle: '?');
            $isWildcard = str_ends_with(haystack: $parameter, needle: '*');
            $name       = preg_replace(pattern: '/[?*]$/', replacement: '', subject: $parameter);
            $constraint = $isWildcard ? '.*' : ($constraints[$name] ?? '[^/]+');
            $group      = "(?P<{$name}>{$constraint})";

            $pattern .= $isOptional ? "(?:/{$group})?" : "/{$group}";
        }

        return "#^{$pattern}$#";
    }

    /**
     * @throws ReservedRouteNameException
     */
    public static function __set_state(array $properties) : self
    {
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
     * Returns a copy of the route with the action wrapped in a SerializableClosure.
     *
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
     * Returns a copy of the route with the action unwrapped.
     *
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
        return array_key_exists(key: $key, array: $this->metadata);
    }

    /**
     * Returns all metadata annotations.
     */
    public function getAllMetadata() : array
    {
        return $this->metadata;
    }

    /**
     * Export the definition into a scalar array suitable for caching.
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
        ];
    }

    public function usesClosure() : bool
    {
        return $this->action instanceof Closure;
    }
}
