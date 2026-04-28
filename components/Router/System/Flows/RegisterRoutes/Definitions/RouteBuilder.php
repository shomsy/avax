<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Flows\RegisterRoutes\Definitions;

use Avax\Components\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\Router\System\Foundation\Exceptions\ReservedRouteNameException;
use Avax\Components\Router\System\PublicSurface\HttpMethod;
use InvalidArgumentException;

/**
 * Builds fluent-style HTTP route declarations for Avax's router.
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
     */
    private function validateMethod(string $method) : void
    {
        if (! HttpMethod::isValid(method: $method)) {
            throw new InvalidArgumentException(message: "Invalid HTTP method: {$method}");
        }
    }

    /**
     * Validates that the route path format is acceptable.
     */
    private function validatePath(string $path) : void
    {
        RoutePathValidator::validate(path: $path);
    }

    /**
     * Static factory to initialize a RouteBuilder.
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

    public function setName(string|null $name) : self
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

    public function setMiddleware(array $middleware) : self
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

    public function setAction(mixed $action) : self
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Gets parameter constraints.
     */
    public function getConstraints() : array
    {
        return $this->constraints;
    }

    public function setConstraints(array $constraints) : self
    {
        $this->constraints = $constraints;

        return $this;
    }

    /**
     * Gets default values for parameters.
     */
    public function getDefaults() : array
    {
        return $this->defaults;
    }

    public function setDefaults(array $defaults) : self
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

    public function setDomain(string|null $domain) : self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Gets custom route attributes.
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes) : self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAuthorization() : string|null
    {
        return $this->authorization;
    }

    public function setAuthorization(string|null $authorization) : self
    {
        $this->authorization = $authorization;

        return $this;
    }

    /**
     * Sets the route name.
     */
    public function name(string $name) : self
    {
        $this->name = $this->name !== null && $this->name !== ''
            ? rtrim(string: $this->name, characters: '.') . '.' . ltrim(string: $name, characters: '.')
            : $name;

        return $this;
    }

    public function prefix(string $prefix) : self
    {
        $prefix = '/' . trim(string: $prefix, characters: '/');
        $path   = '/' . ltrim(string: $this->path, characters: '/');

        $this->path = rtrim(string: $prefix, characters: '/') . ($path === '/' ? '' : $path);

        return $this;
    }

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
     */
    public function defaults(array $defaults) : self
    {
        $this->defaults = $defaults;

        return $this;
    }

    public function domain(string $domain) : self
    {
        return $this->withDomain(domain: $domain);
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
     */
    public function where(string $parameter, string $pattern) : self
    {
        $this->validateConstraintPattern(pattern: $pattern);

        $this->constraints[$parameter] = $pattern;

        return $this;
    }

    /**
     * Ensures that the regex constraint is syntactically valid.
     */
    private function validateConstraintPattern(string $pattern) : void
    {
        $testPattern = "/{$pattern}/";
        $error       = null;

        set_error_handler(callback: static function ($errno, $errstr) use (&$error) {
            $error = $errstr;
        });

        $result = preg_match(pattern: $testPattern, subject: '');

        restore_error_handler();

        if ($result === false || $error !== null) {
            $reason = $error ?: 'invalid regex syntax';
            throw new InvalidArgumentException(message: sprintf('Invalid constraint regex "%s": %s', $pattern, $reason));
        }
    }

    /**
     * Finalizes and compiles the route definition.
     *
     * @throws ReservedRouteNameException
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
}
