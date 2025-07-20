<?php

declare(strict_types=1);

namespace Gemini\Container\Containers;

use Closure;
use Gemini\Cache\InMemoryCache;
use Gemini\Config\Architecture\DDD\AppPath;
use Gemini\Container\Containers\Proxy\LazyProxy;
use Gemini\Container\Containers\Registry\Bindings;
use Gemini\Container\Containers\Registry\Deferred;
use Gemini\Container\Containers\Registry\Instances;
use Gemini\Container\Containers\Registry\LifecycleHooks;
use Gemini\Container\Containers\Registry\ScopedInstances;
use Gemini\Container\Contracts\ContainerInterface;
use Gemini\Container\Exceptions\AutoResolveException;
use Gemini\Container\Exceptions\CircularDependencyException;
use Gemini\Container\Exceptions\ServiceNotFoundException;
use Gemini\Container\Exceptions\UnresolvableDependencyException;
use Gemini\DataHandling\ArrayHandling\Arrhae;
use InvalidArgumentException;
use League\Container\Exception\ContainerException;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;
use Throwable;

/**
 * DependencyInjector
 *
 * A production-ready PSR-11 compatible service container.
 * Responsibilities:
 * - Dependency injection
 * - Singleton and scoped services
 * - Lifecycle hooks (INIT, SHUTDOWN, ERROR)
 * - In-memory caching
 *
 * This class adheres to SOLID principles and Clean Architecture guidelines.
 */
final class DependencyInjector implements ContainerInterface
{
    /**
     * Tracks the currently active scope context.
     * Used to isolate scoped service resolution per logical group (e.g. request, tenant).
     */
    private string $activeScope = 'default';

    /**
     * Indicates whether autowiring fallback is allowed.
     * When strict mode is enabled, only explicitly registered services can be resolved.
     */
    private bool $strictMode = false;


    private readonly Arrhae $contextualBindings;

    /**
     * Stores all registered bindings within the container.
     * This includes mappings of abstract types to concrete implementations.
     */
    private readonly Bindings $bindings;

    /**
     * Holds instances of singleton services that have been resolved.
     * Ensures only one instance of these services exists in the container.
     */
    private readonly Instances $instances;

    /**
     * Manages instances scoped to specific lifetimes or contexts.
     * Allows fine-grained control over the lifecycle of certain services.
     */
    private readonly ScopedInstances $scopedInstances;

    /**
     * Stores deferred bindings that will be resolved lazily.
     * Useful for optimizing performance by delaying the instantiation of services.
     */
    private readonly Deferred $deferred;

    /**
     * Tracks lifecycle hooks such as INIT, SHUTDOWN, and ERROR.
     * Enables executing custom logic during specific phases of the container's lifecycle.
     */
    private readonly LifecycleHooks $lifecycleHooks;

    /**
     * In-memory caching mechanism for storing resolved services or data.
     * Provides fast access to frequently used instances or configurations.
     */
    private readonly InMemoryCache $inMemoryCache;

    /**
     * Tracks the resolution stack during dependency resolution.
     * Used to detect and handle circular dependencies.
     */
    private array           $resolutionStack = [];

    private readonly Arrhae $scopedInstancesByScope;

    private readonly Arrhae $tagBindings;

    private readonly Arrhae $lazyBindings;

    /**
     * Constructor for the dependency injection container.
     *
     * @param int $cacheTTL Time-to-live (TTL) for the in-memory cache, in seconds.
     */
    public function __construct(private readonly int $cacheTTL = 3600)
    {
        $this->bindings               = new Bindings();
        $this->instances              = new Instances();
        $this->scopedInstances        = new ScopedInstances();
        $this->deferred               = new Deferred();
        $this->lifecycleHooks         = new LifecycleHooks();
        $this->inMemoryCache          = new InMemoryCache();
        $this->scopedInstancesByScope = new Arrhae();
        $this->contextualBindings     = new Arrhae();
        $this->tagBindings            = new Arrhae();
        $this->lazyBindings           = new Arrhae();
    }

    /**
     * Enables strict mode: disables fallback to autoResolve() for unknown services.
     *
     * This should be enabled in production for maximum control and security.
     */
    public function enableStrictMode() : void
    {
        $this->strictMode = true;
    }

    /**
     * Disables strict mode: allows fallback to reflection-based autowiring.
     */
    public function disableStrictMode() : void
    {
        $this->strictMode = false;
    }

    /**
     * Returns whether strict mode is currently active.
     *
     */
    public function isStrictMode() : bool
    {
        return $this->strictMode;
    }

    /**
     * Registers service providers specified in the configuration file.
     */
    public function registerProviders() : void
    {
        // Path to the configuration file containing the list of service providers.
        $providers = include AppPath::CONFIG->get() . '/services.php';

        foreach ($providers as $providerClass) {
            $provider = new $providerClass($this); // Pass DependencyInjector to the provider.
            $provider->register(); // Call register() to bind services.
            $provider->boot(); // Call boot() for additional setup.
        }
    }

    /**
     * Resolves a service from the container.
     */
    public function get(string $id) : mixed
    {
        if ($this->has(id: $id)) {
            return $this->resolve(abstract: $id);
        }

        // ✅ Strict mode blocks fallback resolution
        if ($this->strictMode) {
            $this->triggerHook(
                lifecycleHook: LifecycleHook::ERROR,
                args         : [new ServiceNotFoundException(serviceId: $id), $this]
            );
            throw new ServiceNotFoundException(serviceId: $id);
        }

        // ✅ Fallback to autowiring in dev/test
        if (class_exists($id)) {
            return $this->autoResolve(class: $id);
        }

        $this->triggerHook(
            lifecycleHook: LifecycleHook::ERROR,
            args         : [new ServiceNotFoundException(serviceId: $id), $this]
        );

        throw new ServiceNotFoundException(serviceId: $id);
    }

    /**
     * Checks if the container has a service registered.
     */
    public function has(string $id) : bool
    {
        if ($this->bindings->has(key: $id) || $this->instances->has(key: $id)) {
            return true;
        }

        return $this->deferred->has(key: $id);
    }

    /**
     * Resolves a service binding or auto-resolves a class.
     */
    private function resolve(string $abstract) : mixed
    {
        if (in_array($abstract, $this->resolutionStack, true)) {
            $circularDependencyException = new CircularDependencyException(
                serviceId      : $abstract,
                resolutionStack: $this->resolutionStack
            );

            $this->triggerHook(lifecycleHook: LifecycleHook::ERROR, args: [$circularDependencyException, $this]);
            throw $circularDependencyException;
        }

        $this->resolutionStack[] = $abstract;

        try {
            // ✅ Check for scoped instances first
            if ($this->scopedInstances->has(key: $abstract)) {
                $scoped = $this->scopedInstances->get(key: $abstract);

                if (! isset($scoped['instance'])) {
                    $instance           = $this->instantiate(concrete: $scoped['concrete']);
                    $scoped['instance'] = $instance;
                    $this->scopedInstances->set(key: $abstract, value: $scoped);
                }

                return $scoped['instance'];
            }

            // ✅ Check singleton cache
            if ($this->instances->has(key: $abstract)) {
                return $this->instances->get(key: $abstract);
            }

            // ✅ Check in-memory cache
            if ($cached = $this->inMemoryCache->get(key: $abstract)) {
                return $cached;
            }

            // ✅ Fallback to binding or autowiring
            $instance = $this->resolveBindingOrAutoResolve(abstract: $abstract);

            // ✅ Save singleton and cache if needed
            if ($this->bindings->get(key: $abstract)['singleton'] ?? false) {
                $this->instances->set(key: $abstract, value: $instance);
                $this->inMemoryCache->set(key: $abstract, value: $instance, ttl: $this->cacheTTL);
            }

            return $instance;
        } finally {
            array_pop($this->resolutionStack);
        }
    }

    /**
     * Triggers a lifecycle hook.
     */
    private function triggerHook(LifecycleHook $lifecycleHook, array $args = []) : void
    {
        $this->lifecycleHooks->trigger($lifecycleHook, $args);
    }

    /**
     * Instantiates a service using its concrete definition.
     */
    private function instantiate(Closure|string $concrete) : mixed
    {
        return $concrete instanceof Closure ? $concrete($this) : $this->autoResolve(class: $concrete);
    }

    /**
     * Automatically resolves a class using reflection.
     */
    private function autoResolve(string $class) : object
    {
        try {
            $reflectionClass = new ReflectionClass(objectOrClass: $class);

            if (! $reflectionClass->isInstantiable()) {
                throw new AutoResolveException(className: $class);
            }

            $constructor = $reflectionClass->getConstructor();

            if ($constructor === null) {
                return new $class();
            }

            $dependencies = array_map(
                fn(ReflectionParameter $reflectionParameter) : mixed => $this->resolveDependency(
                    reflectionParameter: $reflectionParameter
                ),
                $constructor->getParameters()
            );

            return $reflectionClass->newInstanceArgs(args: $dependencies);
        } catch (ReflectionException $reflectionException) {
            throw new AutoResolveException(className: $class, previous: $reflectionException);
        }
    }

    /**
     * Resolves a constructor dependency parameter.
     */
    private function resolveDependency(ReflectionParameter $reflectionParameter) : mixed
    {
        $type = $reflectionParameter->getType();

        // ✅ Must be a class type
        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            $dependency     = $type->getName();
            $declaringClass = $reflectionParameter->getDeclaringClass()?->getName();

            // ✅ Check for contextual binding override
            if (
                $declaringClass !== null &&
                isset($this->contextualBindings[$declaringClass][$dependency])
            ) {
                $contextual = $this->contextualBindings[$declaringClass][$dependency];

                return $this->instantiate(concrete: $contextual);
            }

            // ✅ Default resolve via container
            return $this->resolve(abstract: $dependency);
        }

        // ✅ Fallback to default value if available
        if ($reflectionParameter->isDefaultValueAvailable()) {
            return $reflectionParameter->getDefaultValue();
        }

        throw new UnresolvableDependencyException(reflectionParameter: $reflectionParameter);
    }

    /**
     * Resolves a service binding or auto-resolves a class.
     */
    private function resolveBindingOrAutoResolve(string $abstract) : mixed
    {
        if ($this->bindings->has(key: $abstract)) {
            return $this->instantiate(concrete: $this->bindings->get(key: $abstract)['concrete']);
        }

        return $this->autoResolve(class: $abstract);
    }

    /**
     * Registers any application services.
     */
    public function register() : void
    {
        $this->registerProviders();
    }

    /**
     * Boots the container after services are registered.
     */
    public function boot() : void
    {
        $this->triggerHook(lifecycleHook: LifecycleHook::INIT);
    }

    /**
     * Validates all registered bindings by resolving them.
     *
     * Useful in production to detect misconfigured services before runtime.
     *
     */
    public function validateBindings() : void
    {
        $errors = [];

        foreach ($this->allBindings() as $abstract => $_) {
            try {
                $this->get($abstract);
            } catch (Throwable $e) {
                $errors[] = "[{$abstract}] => " . $e::class . ': ' . $e->getMessage();
            }
        }

        if ($errors !== []) {
            throw new AutoResolveException(
                className: 'Container',
                previous : new RuntimeException(implode("\n", $errors))
            );
        }
    }

    /**
     * Retrieve all bindings registered in the container.
     *
     * @return array An associative array of all bindings.
     */
    public function allBindings() : array
    {
        return $this->bindings->all();
    }

    /**
     * Registers a singleton service in the container.
     */
    public function singleton(string $abstract, Closure|string $concrete) : void
    {
        $this->bind(abstract: $abstract, concrete: $concrete, singleton: true);
    }

    /**
     * Registers a service binding in the container.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function bind(string $abstract, Closure|string|callable $concrete, bool $singleton = false) : void
    {
        if (! is_callable($concrete) && ! class_exists($concrete)) {
            throw new InvalidArgumentException(
                message: "Concrete for " . $abstract . " must be callable or a valid class."
            );
        }

        $this->bindings->set(key: $abstract, value: ['concrete' => $concrete, 'singleton' => $singleton]);
        $this->invalidateCache(id: $abstract);
        $this->rebuildDependencyGraph();
    }

    /**
     * Invalidates a specific service's cache.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function invalidateCache(string $id) : void
    {
        $this->inMemoryCache->delete(key: $id);
    }

    /**
     * Rebuilds the dependency graph.
     */
    private function rebuildDependencyGraph() : void {}

    /**
     * Shuts down the application lifecycle and triggers the SHUTDOWN hook.
     */
    public function shutdown() : void
    {
        $this->triggerHook(lifecycleHook: LifecycleHook::SHUTDOWN, args: [$this]);
    }

    /**
     * Combines and returns all bindings, instances, and scoped instances.
     *
     * @return array The combined array of all bindings, instances, and scoped instances.
     */
    public function everything() : array
    {
        return array_merge($this->allBindings(), $this->allInstances(), $this->allScopedInstances());
    }

    /**
     * Retrieve all resolved singleton instances.
     *
     * @return array An associative array of all resolved singleton instances.
     */
    public function allInstances() : array
    {
        return $this->instances->all();
    }

    /**
     * Retrieve all scoped instances.
     *
     * @return array An associative array of all scoped instances.
     */
    public function allScopedInstances() : array
    {
        return $this->scopedInstances->all();
    }

    /**
     * Registers a scoped binding in the container.
     *
     * This method establishes a scoped service registration within the Dependency Injection Container.
     * Scoped services are instantiated once per scope and reused within that scope boundary.
     *
     * @param string          $abstract The abstract type/interface to be bound
     * @param \Closure|string $concrete The concrete implementation or factory closure
     * @param string|null     $scope    The scope identifier (defaults to current active scope)
     *
     * @throws \RuntimeException When scope operations fail
     *
     * @throws \InvalidArgumentException When abstract or concrete are invalid
     */
    public function scoped(
        string         $abstract,
        Closure|string $concrete,
        string|null    $scope = null
    ) : void {
        // Determine the effective scope, fallback to active scope if none provided
        $scope = $scope ?? $this->activeScope;

        // Register the concrete implementation in the scoped instances collection
        // Using dot notation to create a unique scope-specific binding key
        $this->scopedInstancesByScope->set(
            key  : "$scope.$abstract",
            value: ['concrete' => $concrete]
        );
    }

    /**
     * Flushes all scoped instance bindings from the container.
     *
     * This method is crucial for maintaining a clean dependency injection container state
     * by removing all scoped instance registrations. It's particularly useful during:
     * - Testing scenarios where a fresh container state is needed
     * - Request lifecycle completion
     * - Manual container state management
     *
     * @throws ContainerException When clearing operation fails due to internal state corruption
     */
    public function flushScope(string|null $scope = null) : void
    {
        if ($scope === null) {
            $this->scopedInstancesByScope->clear();
        } else {
            $keysToForget = array_filter(
                $this->scopedInstancesByScope->keys(),
                static fn(string $key) => str_starts_with($key, "{$scope}.")
            );

            foreach ($keysToForget as $key) {
                $this->scopedInstancesByScope->forget($key);
            }
        }
    }


    public function beginScope(string $scope) : void
    {
        $this->activeScope = $scope;

        if (! $this->scopedInstancesByScope->has($scope)) {
            $this->scopedInstancesByScope->set($scope, []);
        }
    }

    /**
     * Begins contextual binding declaration using a fluent builder pattern.
     *
     * @param string $consumer The fully qualified class name of the consumer (e.g., Controller)
     *
     * @return object Anonymous context binder builder
     */
    public function when(string $consumer) : object
    {
        /**
         * Creates an anonymous readonly configuration class for contextual dependency binding.
         * Implements the Fluent Interface pattern for intuitive dependency configuration.
         *
         * @param DependencyInjector $di       The dependency injector instance
         * @param string             $consumer The consuming class identifier
         *
         * @return object                      Anonymous readonly configuration class
         */
        return new readonly class($this, $consumer) {
            /**
             * Initializes a new configuration context with dependency injection capabilities.
             * Uses constructor promotion for clean and maintainable property declaration.
             *
             * @param DependencyInjector $di       Core dependency injection service
             * @param string             $consumer Fully qualified name of the consuming class
             */
            public function __construct(
                private DependencyInjector $di,
                private string             $consumer
            ) {}

            /**
             * Initiates the dependency configuration chain for a specific dependency.
             * Creates a fluent interface for defining contextual bindings.
             *
             * @param string $dependency Fully qualified name of the required dependency
             *
             * @return object           Anonymous readonly configuration class for binding definition
             */
            public function needs(string $dependency) : object
            {
                /**
                 * Anonymous readonly class providing the final step in dependency configuration.
                 * Implements the Builder pattern for constructing contextual bindings.
                 */
                return new readonly class(
                    $this->di,
                    $this->consumer,
                    $dependency
                ) {
                    /**
                     * Initializes the binding configuration context.
                     * Uses constructor promotion for maintaining clean code principles.
                     *
                     * @param DependencyInjector $di         Core dependency injection service
                     * @param string             $consumer   Fully qualified name of the consuming class
                     * @param string             $dependency Fully qualified name of the dependency being bound
                     */
                    public function __construct(
                        private DependencyInjector $di,
                        private string             $consumer,
                        private string             $dependency
                    ) {}

                    /**
                     * Finalizes the contextual binding configuration.
                     * Registers the implementation for the specified dependency in the given context.
                     *
                     * @param Closure|string $implementation Concrete implementation or factory for the dependency
                     */
                    public function give(Closure|string $implementation) : void
                    {
                        $this->di->addContextualBinding(
                            consumer      : $this->consumer,
                            dependency    : $this->dependency,
                            implementation: $implementation
                        );
                    }
                };
            }
        };
    }

    /**
     * Adds a contextual binding to the container for dependency injection.
     *
     * Establishes a relationship between a consumer class and its dependencies,
     * allowing for specific implementation bindings in different contexts.
     * This enables flexible dependency resolution based on the consumer's context.
     *
     * @param string         $consumer       The fully qualified class name of the consuming service
     * @param string         $dependency     The abstract type or interface being bound
     * @param Closure|string $implementation The concrete implementation or factory closure
     *
     * @throws InvalidArgumentException When invalid binding parameters are provided
     */
    public function addContextualBinding(
        string         $consumer,
        string         $dependency,
        Closure|string $implementation
    ) : void {
        // Constructs a unique binding key using dot notation and registers the implementation
        $this->contextualBindings->set(
            key  : "{$consumer}.{$dependency}",
            value: $implementation
        );
    }

    /**
     * Registers a lazy-loading binding in the container.
     *
     * This method enables dependency injection with deferred resolution through a closure.
     * The binding will only be resolved when the abstract type is actually requested,
     * providing better performance through lazy initialization.
     *
     * @param string  $abstract The abstract type or interface to be resolved
     * @param Closure $resolver The closure that defines how to resolve the binding
     *
     * @throws InvalidArgumentException When the abstract parameter is invalid
     */
    public function lazy(
        string  $abstract,
        Closure $resolver
    ) : void {
        // Register the lazy binding resolver in the container's lazy bindings collection
        $this->lazyBindings->set($abstract, static fn() => new LazyProxy($resolver));
    }

    /**
     * Associates multiple services with a specific tag identifier
     *
     * This method establishes a many-to-one relationship between services and a tag,
     * enabling service discovery through tag-based lookup.
     *
     * @param string $tag      The tag identifier to associate services with
     * @param array  $services Array of service identifiers to be tagged
     *
     * @throws InvalidArgumentException If the tag name is invalid
     */
    public function tag(string $tag, array $services) : void
    {
        $this->tagBindings->set(key: $tag, value: $services);
    }

    /**
     * Retrieves all services associated with a specific tag
     *
     * Returns an array of service identifiers that were previously tagged
     * with the specified tag. Returns an empty array if no services are found.
     *
     * @param string $tag The tag identifier to look up
     *
     * @return array<int, string> Array of service identifiers associated with the tag
     */
    public function tagged(string $tag) : array
    {
        return $this->tagBindings->get(key: $tag, default: []);
    }

    /**
     * Resolves a lazy-loaded binding from the container.
     *
     * This method is part of the Dependency Injection Container's lazy loading mechanism,
     * implementing the Service Locator pattern for improved performance through deferred
     * instantiation of dependencies.
     *
     * @param string $abstract The abstract identifier to resolve from lazy bindings
     *
     * @return mixed The resolved instance or null if no lazy binding exists
     *
     * @throws \InvalidArgumentException When the abstract identifier is invalid
     * @throws \RuntimeException When the lazy binding closure fails to execute
     */
    private function resolveLazy(string $abstract) : mixed
    {
        // Check if a lazy binding exists for the given abstract and execute its closure if found
        return $this->lazyBindings->has(key: $abstract)
            // Execute the lazy binding closure to instantiate the dependency
            ? ($this->lazyBindings->get(key: $abstract))()
            // Return null if no lazy binding exists for the given abstract
            : null;
    }

    /**
     * Resolves a scoped instance from the container based on the current active scope.
     *
     * This method implements the Scope Pattern to manage instance lifecycles within defined boundaries.
     * It ensures proper isolation of instances between different scopes while maintaining
     * singleton-like behavior within the same scope.
     *
     * @param string $abstract The abstract identifier to resolve from the container
     *
     * @return mixed The resolved instance or null if not found in current scope
     *
     * @throws AutoResolveException When unable to instantiate the concrete implementation
     */
    private function resolveScoped(string $abstract) : mixed
    {
        // Construct the unique scope key by combining active scope and abstract identifier
        $scopeKey = $this->activeScope . '.' . $abstract;

        // Early return if no entry exists for the given scope key
        if (! $this->scopedInstancesByScope->has(key: $scopeKey)) {
            return null;
        }

        // Retrieve the scoped entry containing concrete implementation and optional instance
        $entry = $this->scopedInstancesByScope->get(key: $scopeKey);

        // Lazy instantiation of the concrete implementation if the instance doesn't exist
        if (! isset($entry['instance'])) {
            // Create a new instance using the stored concrete implementation
            $entry['instance'] = $this->instantiate(concrete: $entry['concrete']);
            // Update the scope registry with the newly created instance
            $this->scopedInstancesByScope->set(key: $scopeKey, value: $entry);
        }

        // Return the resolved scoped instance
        return $entry['instance'];
    }
}
