<?php

declare(strict_types=1);

namespace Avax\HTTP\Router;

use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Kernel\RouterKernel;
use Avax\HTTP\Router\Routing\Exceptions\RouteNotFoundException;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteBuilder;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Routing\RouteGroupAttributesConfigurator;
use Avax\HTTP\Router\Routing\RouteGroupContext;
use Avax\HTTP\Router\Routing\RouteGroupStack;
use Avax\HTTP\Router\Routing\RouteRegistrarProxy;
use Avax\HTTP\Router\Support\RouteCollector;
use Closure;
use LogicException;
use Psr\Http\Message\ResponseInterface;

/**
 * Central Router facade.
 *
 * Acts as the primary route registration and resolution interface.
 * Delegates resolution to RouterKernel and registration via RouteBuilder DSL.
 */
final class Router implements RouterInterface
{
    /**
     * The fallback handler to be used if no other handler is available.
     *
     * @var mixed|null This can be null or any object that implements the necessary handler interface.
     */
    private Closure|null $fallbackHandler = null;

    /**
     * Initializes a new instance of the class with the provided dependencies.
     *
     * @param HttpRequestRouter $httpRequestRouter The HTTP request router instance to be injected.
     * @param RouterKernel      $kernel            The kernel instance to manage routing logic.
     */
    public function __construct(
        private readonly HttpRequestRouter $httpRequestRouter,
        private readonly RouterKernel      $kernel,
    ) {}

    /**
     * Registers a GET route.
     */
    public function get(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(
            method: HttpMethod::GET->value,
            path: $path,
            action: $action
        );
    }

    /**
     * Internal route registration via RouteBuilder.
     */
    private function register(string $method, string $path, callable|array|string $action): RouteRegistrarProxy
    {
        // Define the route using a builder pattern.
        // `RouteBuilder::make` creates a new instance of the RouteBuilder class
        // by specifying the HTTP method and the URI path.
        $builder = RouteBuilder::make(
            method: $method, // The HTTP method (e.g., GET, POST, PUT).
            path: $path      // The URI path defining the route (e.g., `/users`, `/posts/{id}`).
        );

        // Define the action (e.g., controller or callable) to handle the route's behavior.
        // The action can be a callable, array-based controller reference, or string representation.
        $builder->action(
            action: $action // The action that will be invoked when the route is matched.
        );

        // 🔁 Collect the defined route for later usage.
        // Adds the `RouteBuilder` instance to the `RouteCollector` for later bootstrapping,
        // middleware applications, or cache compilation for performance optimization.
        RouteCollector::add(builder: $builder);

        // Return a proxy to handle registration for the associated HTTP request router.
        // The `RouteRegistrarProxy` will be used to facilitate the registration of
        // the newly defined route and allow for advanced handling or configurations.
        return new RouteRegistrarProxy(
            router: $this->httpRequestRouter,
            // The HTTP router responsible for routing requests to corresponding actions.
            builder: $builder                  // The route builder containing the route's definition and metadata.
        );
    }

    /**
     * Registers a POST route.
     */
    public function post(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(
            method: HttpMethod::POST->value,
            path: $path,
            action: $action
        );
    }

    /**
     * Registers a PUT route.
     */
    public function put(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(
            method: HttpMethod::PUT->value,
            path: $path,
            action: $action
        );
    }

    /**
     * Registers a PATCH route.
     */
    public function patch(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(
            method: HttpMethod::PATCH->value,
            path: $path,
            action: $action
        );
    }

    /**
     * Registers a DELETE route.
     */
    public function delete(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(
            method: HttpMethod::DELETE->value,
            path: $path,
            action: $action
        );
    }

    /**
     * Registers a HEAD route.
     */
    public function head(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(
            method: HttpMethod::HEAD->value,
            path: $path,
            action: $action
        );
    }

    /**
     * Registers an OPTIONS route.
     */
    public function options(string $path, callable|array|string $action): RouteRegistrarProxy
    {
        return $this->register(
            method: HttpMethod::OPTIONS->value,
            path: $path,
            action: $action
        );
    }

    /**
     * Registers the same action for all HTTP methods.
     *
     * @return RouteRegistrarProxy[]
     */
    public function any(string $path, callable|array|string $action): array
    {
        $proxies = [];

        foreach (HttpMethod::cases() as $method) {
            $proxies[] = $this->register(
                method: $method->value,
                path: $path,
                action: $action
            );
        }

        return $proxies;
    }

    /**
     * Resolves the given request and returns the appropriate response.
     *
     * @param Request $request The incoming request to be handled.
     *
     * @return ResponseInterface The response generated by the kernel or the fallback handler.
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function resolve(Request $request): ResponseInterface
    {
        /**
         * Handles the incoming HTTP request using the primary kernel.
         * If no route is found, and a fallback handler is defined, it delegates to the fallback.
         * Otherwise, it throws an exception indicating no route match.
         *
         * @param Request $request The incoming HTTP request object to be processed.
         *
         * @return ResponseInterface The HTTP response generated by either the kernel or the fallback handler.
         * @throws RouteNotFoundException If no route is matched and no fallback handler exists.
         *
         */
        try {
            // Use the kernel's responsibility to handle the incoming HTTP request by delegating it to
            // its `handle` method. This internally resolves the matched route, applies middleware,
            // dispatches the associated action, and returns an appropriate response.
            return $this->kernel->handle(request: $request);
        } catch (RouteNotFoundException) {
            // Check if a fallback handler is defined within the current context. This determines
            // whether an alternate mechanism to process unmatched requests is available.
            if ($this->fallbackHandler !== null) {
                // Invoke the fallback handler with the incoming request to return an alternative
                // response. This can allow the system to gracefully degrade behavior where routes
                // are not available.
                return call_user_func($this->fallbackHandler, $request);
            }

            // If no fallback handler is defined, explicitly throw a `RouteNotFoundException` with
            // a clear message to indicate the failure in routing and lack of fallback resolution.
            throw new RouteNotFoundException(
                message: sprintf(
                    "❌ No route matched for [%s] %s and no fallback defined.",
                    $request->getMethod(),
                    $request->getUri()->getPath()
                )
            );
        }
    }

    /**
     * Defines a fallback route.
     *
     * @param callable|array|string $handler
     */
    public function fallback(callable|array|string $handler): void
    {
        if (is_callable(value: $handler)) {
            $this->fallbackHandler = $handler;
        } elseif (is_array(value: $handler) || is_string(value: $handler)) {
            $this->fallbackHandler = static function (Request $request) use ($handler): ResponseInterface {
                /** @var ControllerDispatcher $dispatcher */
                $dispatcher = app(abstract: ControllerDispatcher::class);

                return $dispatcher->dispatch(
                    action: $handler,
                    request: $request
                );
            };
        }
    }

    /**
     * Registers a route using the given route definition from the cache.
     *
     * @param RouteDefinition $definition The route definition to be registered.
     *
     * @return void
     */
    public function registerRouteFromCache(RouteDefinition $definition): void
    {
        // Adds the provided route definition to the HTTP request router.
        $this->httpRequestRouter->add(route: $definition);
    }

    /**
     * Retrieves a route by its name from the HTTP request router.
     *
     * @param string $name The name of the route to retrieve.
     *
     * @return RouteDefinition The route definition associated with the specified name.
     */
    public function getRouteByName(string $name): RouteDefinition
    {
        // Returns the route with the specified name from the HTTP request router.
        return $this->httpRequestRouter->getByName(name: $name);
    }

    /**
     * Retrieves the currently injected HTTP request router object.
     *
     * @return HttpRequestRouter The injected HTTP request router instance.
     */
    public function getHttpRouter(): HttpRequestRouter
    {
        // Returns the currently injected HTTP request router object.
        return $this->httpRequestRouter;
    }

    /**
     * Returns all registered route definitions.
     *
     * @return array<string, RouteDefinition[]>
     */
    public function allRoutes(): array
    {
        return $this->httpRequestRouter->allRoutes();
    }

    /**
     * Assigns a name prefix to the current route group.
     *
     * @param string $prefix The prefix to be added to the name of routes in the current group.
     *
     * @return self Provides fluent chaining of methods.
     * @throws LogicException If called outside the scope of a route group.
     *
     */
    public function name(string $prefix): self
    {
        // Retrieve the current route group context from the route group stack.
        $context = RouteGroupStack::current();

        // Throw an exception if the method is called outside of a group context.
        if ($context === null) {
            throw new LogicException(
                message: 'Cannot call ->name() outside of a route group context.'
            );
        }

        // Set the provided name prefix on the current route group context.
        $context->setNamePrefix(prefix: $prefix);

        // Return the current instance for method chaining.
        return $this;
    }

    /**
     * Assigns a domain constraint to the routes within the current group.
     *
     * @param string $domain The domain constraint to associate with the current route group.
     *
     * @return self Provides fluent chaining of methods.
     */
    public function domain(string $domain): self
    {
        // Set the domain configuration for the current route group if available.
        RouteGroupStack::current()?->setDomain(domain: $domain);

        // Return the current instance for method chaining.
        return $this;
    }

    /**
     * Configures an authorization policy for routes in the current group.
     *
     * @param string $policy The policy to enforce on the current route group.
     *
     * @return self Provides fluent chaining of methods.
     */
    public function authorize(string $policy): self
    {
        // Set the authorization policy for the current route group if available.
        RouteGroupStack::current()?->setAuthorization(authorization: $policy);

        // Return the current instance for method chaining.
        return $this;
    }

    /**
     * Defines a group of routes with shared attributes and configurations.
     *
     * This method allows developers to group routing logic together under a common context,
     * such as a shared URL prefix, middleware, authorization, or domain. The grouping
     * is facilitated by a `RouteGroupContext` instance that encapsulates these shared attributes.
     *
     * It uses a stack to manage nested route groups, enabling hierarchical routing setups.
     *
     * @param array   $attributes  An associative array of attributes that define the route group's configuration.
     *                             Allowed attributes are:
     *                             - `prefix` (string): Prepends a common URI segment to all routes in the group.
     *                             - `middleware` (array): Adds an array of middleware to all routes in the group.
     *                             - `domain` (string): Defines a domain scope for the routes in the group.
     *                             - `name` (string): Adds a name prefix for all route names in this group.
     *                             - `authorize` (string): Defines an authorization scope for the group.
     * @param Closure $callback    A closure responsible for defining the grouped routes.
     *
     * @return void
     */
    public function group(array $attributes, Closure $callback): void
    {
        $context = new RouteGroupContext();

        /**
         * Create a new instance of RouteGroupAttributesConfigurator to configure routing attributes for a group.
         *
         * An associative array of group-level attributes and configurations.
         *
         * Expected attributes can include:
         * - `prefix`: URI prefix for the route group. (string)
         * - `name`: Name prefix for the route group. (string)
         * - `domain`: The domain constraint for the group. (string)
         * - `authorize`: Authorization-related information for the group. (string)
         * - `middleware`: Middleware(s) applicable to the entire group. (string|array<string>)
         *
         * This array acts as the primary source of group-level contextual information.
         *
         * @var array<string, mixed> $attributes
         *
         *
         * The routing context that will be configured with the provided attributes.
         *
         * This encapsulated group-level configuration for routes, ensuring each group
         * inherits consistent behavior in a decoupled, reusable manner (aligned with DDD style).
         *
         * @var RouteGroupContext    $context
         *
         */
        (new RouteGroupAttributesConfigurator())->apply(
            attributes: $attributes,
            context: $context
        );

        // Push the created context onto the routing stack, indicating the start of a new route group.
        RouteGroupStack::push(group: $context);

        try {
            // Invoke the provided callback, passing the current instance to define the group's routes.
            $callback($this);
        } finally {
            // Pop the context from the stack, signaling the end of the current route group.
            RouteGroupStack::pop();
        }
    }

    /**
     * Sets a prefix for all routes defined within the current route group.
     *
     * @param string $prefix The prefix to be prepended to the URI of all routes in the group.
     *
     * @return self Provides fluent chaining of methods.
     */
    public function prefix(string $prefix): self
    {
        // Assign the URI prefix to the current route group if the context is active.
        RouteGroupStack::current()?->setPrefix(prefix: $prefix);

        // Return the current instance for method chaining.
        return $this;
    }

    /**
     * Registers a new array of middleware to the current route group stack.
     *
     * Leverages RouteGroupStack to add the provided middleware collection for
     * the currently active route group if it exists.
     *
     * @param array $middleware An array of middleware classes or callables to be added.
     *
     * @return self Allows method chaining by returning the same instance of MiddlewareManager.
     */
    public function middleware(array $middleware): self
    {
        // Retrieve the current route group stack if available, and add the middleware to it.
        RouteGroupStack::current()?->addMiddleware(middleware: $middleware);

        // Enable method chaining by returning the current object instance.
        return $this;
    }

    /**
     * Adds parameter constraints (regex patterns) to the current route group context.
     * These constraints act as validation rules for route parameters within the group.
     *
     * Example:
     * ```php
     * $routeGroup->where(['id' => '\d+', 'slug' => '[a-z\-]+']);
     * ```
     *
     * @param array<string, string> $constraints A key-value array where the key is the parameter name,
     *                                           and the value is a regex pattern to validate the parameter.
     *
     * @return self Returns the current instance for fluent method chaining.
     */
    public function where(array $constraints): self
    {
        // Retrieve the current route group from the stack and
        // add the specified parameter constraints.
        RouteGroupStack::current()?->addConstraints(constraints: $constraints);

        // Return the current instance for further modifications.
        return $this;
    }

    /**
     * Defines default parameter values for the current route group context.
     * These defaults will be applied if a parameter is not explicitly provided in the URL.
     *
     * Example:
     * ```
     * $routeGroup->defaults(['locale' => 'en', 'timezone' => 'UTC']);
     * ```
     *
     * @param array<string, mixed> $defaults A key-value array where the key is the parameter name,
     *                                       and the value is the default value for the parameter.
     *
     * @return self Returns the current instance for fluent method chaining.
     */
    public function defaults(array $defaults): self
    {
        // Retrieve the current route group from the stack and
        // add the specified default parameter values.
        RouteGroupStack::current()?->addDefaults(defaults: $defaults);

        // Return the current instance for further modifications.
        return $this;
    }

    /**
     * Attaches arbitrary route-level metadata to the current route group context.
     * Metadata can be informational or directive, to influence routing behavior or store extra data.
     *
     * Example:
     * ```
     * $routeGroup->attributes(['middleware' => 'auth', 'role' => 'admin']);
     * ```
     *
     * @param array<string, mixed> $attributes A key-value array of metadata attributes.
     *
     * @return self Returns the current instance for fluent method chaining.
     */
    public function attributes(array $attributes): self
    {
        // Retrieve the current route group from the stack and
        // add the specified metadata attributes.
        RouteGroupStack::current()?->addAttributes(attributes: $attributes);

        // Return the current instance for further modifications.
        return $this;
    }
}
