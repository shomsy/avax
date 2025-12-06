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
 * This class acts as a DSL-style immutable builder that produces `RouteDefinition` objects.
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

        $this->method = strtoupper($method);
        $this->path   = $path;
    }

    /**
     * Validates that the HTTP method is allowed.
     *
     * @param string $method
     *
     * @return void
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
     * @param string $path
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function validatePath(string $path) : void
    {
        if (! preg_match(pattern: '#^/[\w\-/{}]*$#', subject: $path)) {
            throw new InvalidArgumentException(message: "Invalid route path: {$path}");
        }
    }

    /**
     * Static factory to initialize a RouteBuilder.
     *
     * @param string $method
     * @param string $path
     *
     * @return self
     */
    public static function make(string $method, string $path) : self
    {
        $builder = new self(method: $method, path: $path);

        return RouteGroupStack::apply(builder: $builder);
    }


    /**
     * Gets the route name.
     *
     * @return string|null
     */
    public function getName() : string|null
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return RouteBuilder
     */
    public function setName(?string $name) : RouteBuilder
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the middleware stack.
     *
     * @return array
     */
    public function getMiddleware() : array
    {
        return $this->middleware;
    }

    /**
     * @param array $middleware
     *
     * @return RouteBuilder
     */
    public function setMiddleware(array $middleware) : RouteBuilder
    {
        $this->middleware = $middleware;

        return $this;
    }

    /**
     * Gets the route action.
     *
     * @return callable|array|string|null
     */
    public function getAction() : callable|array|string|null
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     *
     * @return RouteBuilder
     */
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

    /**
     * @param array $constraints
     *
     * @return RouteBuilder
     */
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

    /**
     * @param array $defaults
     *
     * @return RouteBuilder
     */
    public function setDefaults(array $defaults) : RouteBuilder
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Gets the domain constraint, if any.
     *
     * @return string|null
     */
    public function getDomain() : string|null
    {
        return $this->domain;
    }

    /**
     * @param string|null $domain
     *
     * @return RouteBuilder
     */
    public function setDomain(?string $domain) : RouteBuilder
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

    /**
     * @param array $attributes
     *
     * @return RouteBuilder
     */
    public function setAttributes(array $attributes) : RouteBuilder
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthorization() : ?string
    {
        return $this->authorization;
    }


    /**
     * @param string|null $authorization
     *
     * @return RouteBuilder
     */
    public function setAuthorization(?string $authorization) : RouteBuilder
    {
        $this->authorization = $authorization;

        return $this;
    }

    /**
     * Sets the route name.
     *
     * @param string $name
     *
     * @return self
     */
    public function name(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    // region: Validation

    /**
     * Sets the action target of the route.
     *
     * @param callable|array|string $action
     *
     * @return self
     */
    public function action(callable|array|string $action) : self
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Shortcut for setting a controller and method.
     *
     * @param string $controller
     * @param string $method
     *
     * @return self
     */
    public function controller(string $controller, string $method = 'index') : self
    {
        $this->action = [$controller, $method];

        return $this;
    }

    /**
     * Attaches middleware to the route.
     *
     * @param array $middleware
     *
     * @return self
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
     *
     * @return self
     */
    public function defaults(array $defaults) : self
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Assigns a domain pattern to the route.
     *
     * @param string $domain
     *
     * @return self
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
     *
     * @return self
     */
    public function attributes(array $attributes) : self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Specifies an authorization policy identifier.
     *
     * @param string $policy
     *
     * @return self
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
     * @return self
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
     * @param string $parameter
     * @param string $pattern
     *
     * @return self
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
     * @param string $pattern
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function validateConstraintPattern(string $pattern) : void
    {
        if (@preg_match(pattern: "/{$pattern}/", subject: '') === false) {
            throw new InvalidArgumentException(message: "Invalid constraint regex: {$pattern}");
        }
    }

    /**
     * Finalizes and compiles the route definition.
     *
     * @return RouteDefinition
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
     *
     * @return self
     */
    public function authorize(string $policy) : self
    {
        $this->authorization = $policy;

        return $this;
    }

    /**
     * Gets the HTTP method.
     *
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Gets the path for the route.
     *
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

}
