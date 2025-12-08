<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Closure;
use Avax\HTTP\Router\HttpMethod;
use InvalidArgumentException;
use RuntimeException;

/**
 * Builder class for defining grouped routes in a fluent, immutable way.
 *
 * Supports:
 * - Prefix inheritance
 * - Shared middleware
 * - Domain pattern binding
 * - Route-level authorization
 * - Group composition via callback (closure nesting)
 */
final readonly class RouteGroupBuilder
{
    /**
     * @param string|null    $prefix
     * @param array<string>  $middleware
     * @param string|null    $domain
     * @param string|null    $authorization
     * @param RouteBuilder[] $routes
     */
    private function __construct(
        private string|null $prefix = null,
        private array       $middleware = [],
        private string|null $domain = null,
        private string|null $authorization = null,
        private array       $routes = []
    ) {}

    /**
     * Factory method to create a new, empty RouteGroup instance.
     *
     * @return self A new RouteGroup object representing a blank, default group.
     */
    public static function create() : self
    {
        // Create and return a new RouteGroup instance with default settings.
        return new self();
    }

    /**
     * Creates a new RouteGroup with a specified prefix.
     *
     * @param string $prefix The prefix to apply to all routes in the group.
     *
     * @return self A new RouteGroup object with the specified prefix.
     */
    public function withPrefix(string $prefix) : self
    {
        // Creates a new immutable route group with the modified prefix.
        return new self(
            prefix       : rtrim($prefix, '/'), // Ensure no trailing slash on the prefix.
            middleware   : $this->middleware, // Reuse the existing middleware.
            domain       : $this->domain, // Keep the existing domain value.
            authorization: $this->authorization, // Maintain the same authorization.
            routes       : $this->routes // Carry over the existing routes.
        );
    }

    /**
     * Adds middleware to this group's middleware stack.
     * The middleware list is merged with any existing middlewares.
     *
     * @param array<string> $middleware A list of middleware to add to the group.
     *
     * @return self A new RouteGroup object with the updated middleware stack.
     */
    public function withMiddleware(array $middleware) : self
    {
        // Create a new immutable instance with the combined middleware.
        return new self(
            prefix       : $this->prefix, // Retain the existing route prefix.
            middleware   : array_merge($this->middleware, $middleware), // Merge old and new middleware.
            domain       : $this->domain, // Keep the existing domain restriction.
            authorization: $this->authorization, // Maintain the same authorization.
            routes       : $this->routes // Preserve the existing group routes.
        );
    }

    /**
     * Defines an authorization policy for all routes in this group.
     *
     * @param string $policy The name of the authorization policy to apply.
     *
     * @return self A new RouteGroup object with the specified authorization policy.
     */
    public function withAuthorization(string $policy) : self
    {
        // Create a new immutable instance with the updated authorization policy.
        return new self(
            prefix       : $this->prefix, // Reuse the existing route prefix.
            middleware   : $this->middleware, // Retain the middleware stack.
            domain       : $this->domain, // Keep the domain restriction as is.
            authorization: $policy, // Apply the new authorization policy.
            routes       : $this->routes // Carry over the route definitions.
        );
    }

    /**
     * Adds one or more routes via closure DSL.
     *
     * Example:
     * ```
     * ->withRoutes(fn(RouteGroupBuilder $g) => $g
     *      ->addRoute(...)->addRoute(...)
     * )
     * ```
     */
    public function withRoutes(Closure $callback) : self
    {
        // Invoke the callback, passing a cloned instance of the current object ($this).
        // This allows the callback to define routes within its own scope without altering the original object.
        $nested = $callback(clone $this);

        // Check if the result of the callback is an instance of the current class (RouteGroupBuilder).
        // If not, throw a RuntimeException to ensure the callback strictly returns a valid RouteGroupBuilder instance.
        if (! $nested instanceof self) {
            throw new RuntimeException(message: 'Callback to withRoutes() must return a RouteGroupBuilder instance.');
        }

        // Return a new instance of the RouteGroupBuilder class,
        // preserving the prefix, middleware, domain, and authorization of the current instance.
        // For routes, merge the routes from the current instance ($this->routes) with those from the nested instance ($nested->routes).
        // The new instance represents the combined state of the current and nested route group builders.
        return new self(
            prefix       : $this->prefix,        // Maintain the prefix of the current route group.
            middleware   : $this->middleware,    // Maintain the middleware of the current route group.
            domain       : $this->domain,        // Maintain the domain of the current route group.
            authorization: $this->authorization, // Maintain the authorization policies of the current route group.
            routes       : [...$this->routes, ...$nested->routes] // Merge the existing and nested routes.
        );
    }

    /**
     * Adds a single route to the group.
     *
     * @param string                $method HTTP verb
     * @param string                $path   Route path
     * @param callable|array|string $action Target controller or callable
     *
     * @return self
     */
    public function addRoute(string $method, string $path, callable|array|string $action) : self
    {
        // Validate if the provided HTTP method is valid using the HttpMethod enumeration.
        // Throw an exception if the HTTP method is invalid.
        if (! HttpMethod::isValid(method: $method)) {
            throw new InvalidArgumentException(message: "Invalid HTTP method: {$method}");
        }

        // Create a new RouteBuilder instance with the validated HTTP method and the prefixed path.
        // Assign the provided action (e.g., controller method or callback) to the route.
        // Assign middleware (e.g., authentication, logging) to the route.
        $route = RouteBuilder::make(
            method: $method,
            path  : $this->applyPrefix($path)

        )
            ->action(action: $action)
            ->middleware(middleware: $this->middleware);

        // Add a domain to the route if a specific domain is defined.
        if ($this->domain !== null) {
            $route = $route->withDomain(domain: $this->domain);
        }

        // Assign an authorization policy to the route if one is provided.
        if ($this->authorization !== null) {
            $route = $route->authorize(policy: $this->authorization);
        }

        // Create a new instance of the current class, extending the existing routes
        // with the newly configured route, and preserving other properties like
        // prefix, middleware, domain, and authorization.
        return new self(
        // Maintain the current URL prefix for the routes.
            prefix       : $this->prefix,
            // Maintain the current list of middleware.
            middleware   : $this->middleware,
            // Maintain the current domain (if any).
            domain       : $this->domain,
            // Maintain the current authorization policy (if any).
            authorization: $this->authorization,
            // Append the newly configured route to the list of existing routes.
            routes       : [...$this->routes, $route]
        );
    }

    /**
     * Factory method to create a new instance of the RouteGroupBuilder class.
     *
     * This method acts as the default entry point for creating a new route group object.
     * It ensures a clean, well-defined instantiation process that allows for method chaining
     * and controlled manipulation of routes and associated properties like middleware,
     * authorization, and prefix. Ideal for managing route groups in scalable applications.
     *
     * @return self Returns a new instance of the RouteGroupBuilder class.
     */
    public static function make() : self
    {
        // Instantiate and return a new RouteGroupBuilder with default values.
        return new self();
    }


    /**
     * Applies the group prefix to a given path.
     *
     * This method is responsible for ensuring that routes within a group
     * are correctly prefixed. If no prefix is defined (i.e., `$prefix` is null),
     * it returns the provided path as-is. Otherwise, it joins the prefix and path
     * with a single forward slash (`/`) to preserve uniformity in URL structure.
     *
     * @param string $path The specific route path to which the prefix should be applied.
     *
     * @return string The modified path with the applied prefix, or the original path if no prefix is set.
     */
    private function applyPrefix(string $path) : string
    {
        // Check if the `prefix` property is null (no prefix defined).
        // If null, simply return the provided path without any modifications.
        if ($this->prefix === null) {
            return $path;
        }

        // Append the given path to the prefix while managing slash consistency.
        // - Use `rtrim` to strip any trailing slashes from the prefix.
        // - Use `ltrim` to remove any leading slashes from the provided path.
        // - This ensures a single forward slash (`/`) separates the prefix and path.
        return rtrim($this->prefix, '/') . '/' . ltrim($path, '/');
    }


    /**
     * Sets a domain constraint for all routes in the group.
     *
     * This method is part of the fluent API for configuring route groups.
     * It allows you to specify that all routes in the group should apply
     * to a specific domain. This is useful for implementing multi-tenant
     * architectures, subdomain routing, or domain-specific behavior.
     *
     * @param string $domain The domain constraint to be applied to the routes
     *                       within this group.
     *
     * @return self Returns a new instance of the `RouteGroupBuilder` class
     *              with the specified domain applied, ensuring immutability.
     */
    public function withDomain(string $domain) : self
    {
        // Create and return a new instance of the RouteGroupBuilder class
        // with the updated domain while preserving all other properties.
        return new self(
            prefix       : $this->prefix,        // Keep the current route prefix.
            middleware   : $this->middleware,    // Retain the middleware stack.
            domain       : $domain,              // Set the new domain constraint.
            authorization: $this->authorization, // Retain the authorization policy.
            routes       : $this->routes         // Retain the existing routes.
        );
    }

    /**
     * Builds all defined routes.
     *
     * Converts the routes defined within the `RouteGroupBuilder` to their
     * respective `RouteDefinition` objects by invoking their `build()`
     * methods. This allows the route definitions to be finalized and used
     * by the routing system.
     *
     * This method adheres to the principle of immutability by performing its
     * operations without modifying the internal state of the `RouteGroupBuilder`.
     * This makes the function predictable and side effect free.
     *
     * @return RouteDefinition[] An array of `RouteDefinition` instances,
     *                           representing the fully finalized routes
     *                           within this route group.
     */
    public function build() : array
    {
        // Use array_map to iterate over the list of routes and
        // invoke the `build()` method on each `RouteBuilder` instance.
        // This results in an array where every route is now a finalized
        // `RouteDefinition` object.
        return array_map(
            static fn(RouteBuilder $r) => $r->build(), // Transform RouteBuilder into RouteDefinition.
            $this->routes                // The array of RouteBuilder instances to process.
        );
    }

    /**
     * Internal helper method to apply the group’s prefix to a given path.
     *
     * This function ensures that all paths defined in the route group are
     * consistent and properly prefixed. For instance, if the group has a prefix
     * like `/admin`, each route within it will start with `/admin`.
     *
     * - If no prefix is set (`$this->prefix` is null), the method simply returns the original path.
     * - Otherwise, it concatenates the prefix to the given path string.
     *
     * This approach adheres to clean code principles by isolating this functionality
     * within a private helper, ensuring the prefix logic remains reusable and easily testable.
     *
     * @param string $path The original route path to be prefixed.
     *
     * @return string Returns the prefixed path, or the original if no prefix is defined.
     */
    private function prefixed(string $path) : string
    {
        // Check if the prefix is not set; if null, return the path as is.
        if ($this->prefix === null) {
            return $path;
        }

        // Concatenate the prefix with the provided path and return it.
        return $this->prefix . $path;
    }
}
