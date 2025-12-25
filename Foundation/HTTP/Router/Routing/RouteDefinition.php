<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Closure;
use Avax\HTTP\Router\HttpMethod;
use InvalidArgumentException;
use Laravel\SerializableClosure\SerializableClosure;

/**
 * Immutable data structure representing a registered HTTP route.
 *
 * Supports serializable closures via Laravel\SerializableClosure.
 */
final readonly class RouteDefinition
{
    /**
     * Constructor for initializing route details.
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
     *
     * @return void
     */
    public function __construct(
        public string      $method,
        public string      $path,
        public mixed       $action,
        public array       $middleware = [],
        public string      $name = '',
        public array       $constraints = [],
        public array       $defaults = [],
        public string|null $domain = null,
        public array       $attributes = [],
        public string|null $authorization = null,
        public array       $parameters = []
    ) {
        $this->validateMethod(method: $method);
        $this->validatePath(path: $path);
        $this->validateConstraints(constraints: $constraints);
    }

    /**
     * Validates the HTTP method against supported ones.
     *
     * @throws InvalidArgumentException
     */
    private function validateMethod(string $method) : void
    {
        if (! HttpMethod::isValid(method: $method)) {
            throw new InvalidArgumentException(message: sprintf('Invalid HTTP method: %s', $method));
        }
    }

    /**
     * Validates the route path format.
     *
     * @throws InvalidArgumentException
     */
    private function validatePath(string $path) : void
    {
        if (! preg_match(pattern: '#^/[\w\-/{}]*$#', subject: $path)) {
            throw new InvalidArgumentException(message: sprintf('Invalid route path: %s', $path));
        }
    }

    /**
     * Validates all regex constraints.
     *
     * @throws InvalidArgumentException
     */
    private function validateConstraints(array $constraints) : void
    {
        foreach ($constraints as $pattern) {
            if (@preg_match(pattern: '/' . $pattern . '/', subject: '') === false) {
                throw new InvalidArgumentException(message: sprintf('Invalid regex constraint: %s', $pattern));
            }
        }
    }

    public static function __set_state(array $properties) : self
    {
        return new self(
            method       : $properties['method'],
            path         : $properties['path'],
            action       : $properties['action'],
            middleware   : $properties['middleware'],
            name         : $properties['name'],
            constraints  : $properties['constraints'],
            defaults     : $properties['defaults'],
            domain       : $properties['domain'],
            attributes   : $properties['attributes'],
            authorization: $properties['authorization'],
            parameters   : $properties['parameters'] ?? []
        );
    }


    /**
     * Returns a copy of the route with the action wrapped in a SerializableClosure (if needed).
     *
     * @return self
     * @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException
     * @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException
     */
    public function withSerializedAction() : self
    {
        $action = $this->action instanceof Closure
            ? new SerializableClosure($this->action)
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
     * @return self
     * @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException
     * @throws \Laravel\SerializableClosure\Exceptions\PhpVersionNotSupportedException
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

    public function usesClosure() : bool
    {
        return $this->action instanceof Closure;
    }
}
