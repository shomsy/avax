<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Avax\HTTP\Router\HttpMethod;
use InvalidArgumentException;

/**
 * Builds fluent-style HTTP route declarations for Avax's router.
 *
 * Supports:
 * - Route prefixing and naming
 * - Middleware stacking
 * - Domain and authorization constraints
 * - Parameter constraints, defaults, and metadata
 * - Clean Architecture-aligned route definitions
 *
 * This class acts as a DSL-style fluent builder that produces `RouteDefinition` objects.
 */
final class RouteBuilder
{
    /** @var string HTTP method (GET, POST, etc.) */
    public readonly string $method;

    /** @var string The route URI path (e.g., /users/{id}) */
    public string $path;

    /** @var string|null Optional name for the route (used for reverse routing) */
    public string|null $name = null;

    /** @var array List of middleware to apply to the route */
    public array $middleware = [];

    /** @var callable|array|string|null The route's action target (controller, callable, etc.) */
    public mixed $action = null;

    /** @var array<string, string> Regex constraints for route parameters */
    public array $constraints = [];

    /** @var array<string, string> Default values for optional parameters */
    public array $defaults = [];

    /** @var string|null Optional domain constraint (e.g., admin.{org}.com) */
    public string|null $domain = null;

    /** @var array<string, mixed> Custom metadata attached to the route */
    public array $attributes = [];

    /** @var string|null Optional authorization policy identifier */
    public string|null $authorization = null;

    /**
     * Private constructor. Use RouteBuilder::make() instead.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $path   URI path starting with /
     *
     * @throws InvalidArgumentException
     */
    private function __construct(string $method, string $path)
    {
        $this->validateMethod(method: $method);
        $this->validatePath(path: $path);

        $this->method = strtoupper(string: $method);
        $this->path   = $path;
    }

    /**
     * Validates that the HTTP method is allowed.
     *
     *
     * @throws InvalidArgumentException
     */
    private function validateMethod(string $method) : void
    {
        if (! HttpMethod::isValid(method: $method)) {
            throw new InvalidArgumentException(message: "Invalid HTTP method: {$method}");
        }
    }

    /**
     * Validates that the route path format is acceptable.
     *
     *
     * @throws InvalidArgumentException
     */
    private function validatePath(string $path) : void
    {
        route_validate_path(path: $path);
    }

    /**
     * Static factory to initialize a RouteBuilder.
     *
     * Note: Group context is applied by RouterDsl, not here.
     */
    public static function make(string $method, string $path) : self
    {
        return new self(method: $method, path: $path);
    }

    /**
     * Gets the route name.
     */
    public function getName() : string|null
    {
        return $this->name;
    }

    public function setName(string|null $name) : RouteBuilder
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the middleware stack.
     */
    public function getMiddleware() : array
    {
        return $this->middleware;
    }

    public function setMiddleware(array $middleware) : RouteBuilder
    {
        $this->middleware = $middleware;

        return $this;
    }

    /**
     * Gets the route action.
     */
    public function getAction() : callable|array|string|null
    {
        return $this->action;
    }

    public function setAction(mixed $action) : RouteBuilder
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Gets parameter constraints.
     *
     * @return array<string, string>
     */
    public function getConstraints() : array
    {
        return $this->constraints;
    }

    public function setConstraints(array $constraints) : RouteBuilder
    {
        $this->constraints = $constraints;

        return $this;
    }

    /**
     * Gets default values for parameters.
     *
     * @return array<string, string>
     */
    public function getDefaults() : array
    {
        return $this->defaults;
    }

    public function setDefaults(array $defaults) : RouteBuilder
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Gets the domain constraint, if any.
     */
    public function getDomain() : string|null
    {
        return $this->domain;
    }

    public function setDomain(string|null $domain) : RouteBuilder
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Gets custom route attributes.
     *
     * @return array<string, mixed>
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes) : RouteBuilder
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAuthorization() : string|null
    {
        return $this->authorization;
    }

    public function setAuthorization(string|null $authorization) : RouteBuilder
    {
        $this->authorization = $authorization;

        return $this;
    }

    /**
     * Sets the route name.
     */
    public function name(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    // region: Validation

    /**
     * Sets the action target of the route.
     */
    public function action(callable|array|string $action) : self
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Shortcut for setting a controller and method.
     */
    public function controller(string $controller, string $method = 'index') : self
    {
        $this->action = [$controller, $method];

        return $this;
    }

    /**
     * Attaches middleware to the route.
     */
    public function middleware(array $middleware) : self
    {
        $this->middleware = $middleware;

        return $this;
    }

    /**
     * Assigns default values for optional route parameters.
     *
     * @param array<string, string> $defaults
     */
    public function defaults(array $defaults) : self
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Assigns a domain pattern to the route.
     */
    public function withDomain(string $domain) : self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Attaches custom metadata (attributes) to the route.
     *
     * @param array<string, mixed> $attributes
     */
    public function attributes(array $attributes) : self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Specifies an authorization policy identifier.
     */
    public function withAuthorization(string $policy) : self
    {
        $this->authorization = $policy;

        return $this;
    }

    /**
     * Adds multiple route parameter constraints using regex.
     *
     * @param array<string, string> $constraints
     *
     * @throws InvalidArgumentException
     */
    public function whereIn(array $constraints) : self
    {
        foreach ($constraints as $param => $pattern) {
            $this->where(parameter: $param, pattern: $pattern);
        }

        return $this;
    }

    /**
     * Adds a single route parameter constraint using regex.
     *
     *
     * @throws InvalidArgumentException
     */
    public function where(string $parameter, string $pattern) : self
    {
        $this->validateConstraintPattern(pattern: $pattern);

        $this->constraints[$parameter] = $pattern;

        return $this;
    }

    /**
     * Ensures that the regex constraint is syntactically valid.
     *
     * @throws InvalidArgumentException
     */
    private function validateConstraintPattern(string $pattern) : void
    {
        // Test pattern compilation without @ suppression
        $testPattern = "/{$pattern}/";
        $error       = null;

        set_error_handler(static function ($errno, $errstr) use (&$error) {
            $error = $errstr;
        });

        $result = preg_match($testPattern, '');

        restore_error_handler();

        if ($result === false || $error !== null) {
            $reason = $error ?: 'invalid regex syntax';
            throw new InvalidArgumentException(message: sprintf('Invalid constraint regex "%s": %s', $pattern, $reason));
        }
    }

    /**
     * Finalizes and compiles the route definition.
     *
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function build() : RouteDefinition
    {
        return new RouteDefinition(
            method       : $this->method,
            path         : $this->path,
            action       : $this->action,
            middleware   : $this->middleware,
            name         : $this->name ?? '',
            constraints  : $this->constraints,
            defaults     : $this->defaults,
            domain       : $this->domain,
            attributes   : $this->attributes,
            authorization: $this->authorization
        );
    }

    /**
     * Specifies a policy for route-level authorization.
     *
     * @param string $policy The authorization policy identifier.
     */
    public function authorize(string $policy) : self
    {
        $this->authorization = $policy;

        return $this;
    }

    /**
     * Gets the HTTP method.
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Gets the path for the route.
     */
    public function getPath() : string
    {
        return $this->path;
    }
}