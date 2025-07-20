# Technical Documentation for `Dependency Injector` Class

This documentation provides an in-depth technical overview of the `DependencyInjector` class within the
`Gemini\Container\Containers` namespace. The `DependencyInjector` is a robust, production-ready service container
adhering to the [PSR-11 Container Interface](https://www.php-fig.org/psr/psr-11/). It encapsulates functionalities such
as dependency injection, singleton and scoped services management, lifecycle hooks, and in-memory caching. This class is
designed following [SOLID principles](https://en.wikipedia.org/wiki/SOLID)
and [Clean Architecture](https://en.wikipedia.org/wiki/Clean_architecture), ensuring maintainability, scalability, and
testability.

---

## Table of Contents

1. [Namespace](#namespace)
2. [Description](#description)
3. [Class Declaration](#class-declaration)
4. [Properties](#properties)
5. [Methods](#methods)
6. [Usage Examples](#usage-examples)
7. [Exception Handling](#exception-handling)
8. [Lifecycle Hooks](#lifecycle-hooks)
9. [Caching Mechanism](#caching-mechanism)
10. [Dependency Graph Management](#dependency-graph-management)
11. [Conclusion](#conclusion)

---

## Namespace

`Gemini\Container\Containers`

---

## Description

The `DependencyInjector` class serves as a central component for managing service dependencies within the application.
It implements the [PSR-11 `ContainerInterface`](https://www.php-fig.org/psr/psr-11/) to ensure interoperability and
standardization. Key responsibilities of this class include:

- **Dependency Injection:** Automatically resolving and injecting dependencies required by services.
- **Singleton and Scoped Services:** Managing the lifecycle of services, ensuring single instances or scoped instances
  based on configuration.
- **Lifecycle Hooks:** Providing hooks (`INIT`, `SHUTDOWN`, `ERROR`) to execute custom logic during different phases of
  the container's lifecycle.
- **In-Memory Caching:** Caching resolved services to optimize performance.
- **Deferred Bindings:** Deferring the resolution of services until they are actually needed, enhancing efficiency.
- **Child Containers:** Supporting hierarchical containers for modular and isolated service management.
- **Dependency Graph:** Maintaining a graph of service dependencies to detect and prevent circular dependencies.

This class leverages several internal components and traits to modularize functionality, promoting clean architecture
and adherence to single responsibility.

---

## Class Declaration

```
final class DependencyInjector implements ContainerInterface
```

- **Modifiers:**
    - `final`: Indicates that this class cannot be extended.
    - `implements ContainerInterface`: Ensures compliance with the PSR-11 standard.

---

## Properties

The `DependencyInjector` class encapsulates several properties, each serving a distinct purpose in managing dependencies
and services.

### 1. `private readonly Bindings $bindings;`

- **Description:**  
  Stores all registered service bindings within the container. It maps abstract types to their concrete implementations.

- **Type:**  
  `Bindings` (assumed to be a class responsible for managing service bindings).

### 2. `private readonly Instances $instances;`

- **Description:**  
  Holds instances of singleton services that have been resolved. Ensures that only one instance of these services exists
  within the container.

- **Type:**  
  `Instances` (assumed to be a class managing singleton instances).

### 3. `private readonly ScopedInstances $scopedInstances;`

- **Description:**  
  Manages instances scoped to specific lifetimes or contexts. Allows fine-grained control over the lifecycle of certain
  services.

- **Type:**  
  `ScopedInstances` (assumed to manage scoped service instances).

### 4. `private readonly Deferred $deferred;`

- **Description:**  
  Stores deferred bindings that will be resolved lazily. Useful for optimizing performance by delaying the instantiation
  of services until they are needed.

- **Type:**  
  `Deferred` (assumed to handle deferred service bindings).

### 5. `private readonly LifecycleHooks $lifecycleHooks;`

- **Description:**  
  Tracks lifecycle hooks such as `INIT`, `SHUTDOWN`, and `ERROR`. Enables executing custom logic during specific phases
  of the container's lifecycle.

- **Type:**  
  `LifecycleHooks` (assumed to manage lifecycle event hooks).

### 6. `private readonly ChildContainers $childContainers;`

- **Description:**  
  Manages child containers to support hierarchical dependency injection. Useful for modular applications where services
  are isolated to specific contexts.

- **Type:**  
  `ChildContainers` (assumed to handle child container management).

### 7. `private DependencyGraph $dependencyGraph;`

- **Description:**  
  Represents the graph of dependencies between registered services. Enables validation and detection of circular
  dependencies.

- **Type:**  
  `DependencyGraph` (assumed to manage and analyze service dependencies).

### 8. `private readonly InMemoryCache $cache;`

- **Description:**  
  An in-memory caching mechanism for storing resolved services or data. Provides fast access to frequently used
  instances or configurations.

- **Type:**  
  `InMemoryCache` (assumed to implement caching functionalities).

### 9. `private readonly int $cacheTTL;`

- **Description:**  
  Time-to-live duration for cached items. Determines how long cached data is considered valid.

- **Type:**  
  `int`

### 10. `private array $resolutionStack = [];`

- **Description:**  
  Tracks the resolution stack during dependency resolution. Used to detect and handle circular dependencies.

- **Type:**  
  `array`

---

## Methods

The `DependencyInjector` class encompasses a variety of methods, each designed to manage and resolve service
dependencies effectively.

### 1. `__construct(int $cacheTTL = 3600)`

- **Description:**  
  Constructor initializes all necessary properties, setting up the container's internal state.

- **Parameters:**
    - `int $cacheTTL`  
      Time-to-live (TTL) for the in-memory cache, in seconds. Defaults to `3600` seconds (1 hour).

- **Usage Example:**

  ```
  $dependencyInjector = new DependencyInjector(7200); // Cache TTL set to 2 hours
  ```

### 2. `registerProviders(): void`

- **Description:**  
  Registers service providers specified in the configuration file. Service providers are responsible for binding
  services to the container.

- **Usage Example:**

  ```
  $dependencyInjector->registerProviders();
  ```

- **Implementation Details:**

    - Loads service providers from a configuration file located at `AppPath::CONFIG->get() . '/services.php'`.
    - Iterates through each provider class, instantiates it, and calls its `register()` and `boot()` methods.
    - Throws a `RuntimeException` if a critical service (e.g., `'Storage'`) is not registered.

### 3. `bind(string $abstract, Closure|string|callable $concrete, bool $singleton = false): void`

- **Description:**  
  Registers a service binding in the container.

- **Parameters:**
    - `string $abstract`  
      The abstract type or identifier of the service.

    - `Closure|string|callable $concrete`  
      The concrete implementation of the service. Can be a class name, a closure, or any callable.

    - `bool $singleton`  
      Determines if the service should be treated as a singleton. Defaults to `false`.

- **Return Value:**  
  `void`

- **Usage Example:**

  ```
  $dependencyInjector->bind('Logger', function($container) {
      return new Logger($container->get('Config'));
  }, true); // Registers Logger as a singleton
  ```

- **Exception Handling:**  
  Throws an `InvalidArgumentException` if the `$concrete` is neither callable nor a valid class.

### 4. `singleton(string $abstract, Closure|string $concrete): void`

- **Description:**  
  Registers a singleton service in the container.

- **Parameters:**
    - `string $abstract`  
      The abstract type or identifier of the service.

    - `Closure|string $concrete`  
      The concrete implementation of the service.

- **Return Value:**  
  `void`

- **Usage Example:**

  ```
  $dependencyInjector->singleton('DatabaseConnection', DatabaseConnection::class);
  ```

- **Implementation Details:**  
  Calls the `bind` method with the `$singleton` parameter set to `true`.

### 5. `get(string $id): mixed`

- **Description:**  
  Resolves and retrieves a service from the container.

- **Parameters:**
    - `string $id`  
      The identifier or abstract type of the service to retrieve.

- **Return Value:**  
  `mixed`  
  The resolved service instance.

- **Usage Example:**

  ```
  $logger = $dependencyInjector->get('Logger');
  ```

- **Exception Handling:**
    - Throws a `ServiceNotFoundException` if the service is not registered and cannot be autowired.
    - Triggers the `ERROR` lifecycle hook upon failure.

### 6. `has(string $id): bool`

- **Description:**  
  Checks if the container has a service registered under the given identifier.

- **Parameters:**
    - `string $id`  
      The identifier or abstract type of the service.

- **Return Value:**  
  `bool`  
  `true` if the service is registered; `false` otherwise.

- **Usage Example:**

  ```
  if ($dependencyInjector->has('Cache')) {
      $cache = $dependencyInjector->get('Cache');
  }
  ```

### 7. `resolve(string $abstract): mixed`

- **Description:**  
  Resolves a service binding or auto-resolves a class.

- **Parameters:**
    - `string $abstract`  
      The abstract type or identifier of the service.

- **Return Value:**  
  `mixed`  
  The resolved service instance.

- **Usage Example:**

  ```
  $database = $dependencyInjector->resolve('DatabaseConnection');
  ```

- **Exception Handling:**
    - Throws a `CircularDependencyException` if a circular dependency is detected.
    - Triggers the `ERROR` lifecycle hook upon encountering exceptions.

### 8. `instantiate(Closure|string $concrete): mixed`

- **Description:**  
  Instantiates a service using its concrete definition.

- **Parameters:**
    - `Closure|string $concrete`  
      The concrete implementation of the service.

- **Return Value:**  
  `mixed`  
  The instantiated service.

- **Usage Example:**

  ```
  $service = $dependencyInjector->instantiate(function($container) {
      return new Service($container->get('Dependency'));
  });
  ```

### 9. `autoResolve(string $class): object`

- **Description:**  
  Automatically resolves a class using reflection.

- **Parameters:**
    - `string $class`  
      The fully qualified class name to resolve.

- **Return Value:**  
  `object`  
  An instance of the resolved class.

- **Usage Example:**

  ```
  $userService = $dependencyInjector->autoResolve(UserService::class);
  ```

- **Exception Handling:**
    - Throws an `AutoResolveException` if the class cannot be instantiated.
    - Catches and rethrows `ReflectionException` as `AutoResolveException`.

### 10. `resolveBindingOrAutoResolve(string $abstract): mixed`

- **Description:**  
  Resolves a service binding or auto-resolves a class if no binding exists.

- **Parameters:**
    - `string $abstract`  
      The abstract type or identifier of the service.

- **Return Value:**  
  `mixed`  
  The resolved service instance.

- **Usage Example:**

  ```
  $mailer = $dependencyInjector->resolveBindingOrAutoResolve('Mailer');
  ```

### 11. `resolveDependency(ReflectionParameter $parameter): mixed`

- **Description:**  
  Resolves a constructor dependency parameter.

- **Parameters:**
    - `ReflectionParameter $parameter`  
      The reflection parameter representing the dependency to resolve.

- **Return Value:**  
  `mixed`  
  The resolved dependency.

- **Usage Example:**

  ```
  $dependency = $dependencyInjector->resolveDependency($parameter);
  ```

- **Exception Handling:**
    - Throws an `UnresolvableDependencyException` if the dependency cannot be resolved.

### 12. `invalidateCache(string $id): void`

- **Description:**  
  Invalidates a specific service's cache.

- **Parameters:**
    - `string $id`  
      The identifier of the service whose cache should be invalidated.

- **Return Value:**  
  `void`

- **Usage Example:**

  ```
  $dependencyInjector->invalidateCache('Logger');
  ```

### 13. `rebuildDependencyGraph(): void`

- **Description:**  
  Rebuilds the dependency graph to reflect current service bindings.

- **Return Value:**  
  `void`

- **Usage Example:**

  ```
  $dependencyInjector->rebuildDependencyGraph();
  ```

### 14. `extractDependencies(Closure|string $binding): array`

- **Description:**  
  Extracts the dependencies of a service binding.

- **Parameters:**
    - `Closure|string $binding`  
      The concrete implementation of the service.

- **Return Value:**  
  `array`  
  An array of dependency identifiers.

- **Usage Example:**

  ```
  $dependencies = $dependencyInjector->extractDependencies($binding);
  ```

- **Exception Handling:**
    - Catches `ReflectionException` and returns an empty array if dependencies cannot be extracted.

### 15. `resolveParameterType(ReflectionParameter $parameter): ?string`

- **Description:**  
  Resolves the type of a `ReflectionParameter`, supporting both named types and union types.

- **Parameters:**
    - `ReflectionParameter $parameter`  
      The reflection parameter to resolve.

- **Return Value:**  
  `?string`  
  The name of the type or `null` if unresolved.

- **Usage Example:**

  ```
  $type = $dependencyInjector->resolveParameterType($parameter);
  ```

### 16. `triggerHook(LifecycleHook $hook, array $arguments = []): void`

- **Description:**  
  Triggers a lifecycle hook with the provided arguments.

- **Parameters:**
    - `LifecycleHook $hook`  
      The lifecycle hook to trigger (e.g., `INIT`, `SHUTDOWN`, `ERROR`).

    - `array $arguments`  
      An array of arguments to pass to the hook listeners.

- **Return Value:**  
  `void`

- **Usage Example:**

  ```
  $dependencyInjector->triggerHook(LifecycleHook::INIT, [$dependencyInjector]);
  ```

### 17. `shutdown(): void`

- **Description:**  
  Shuts down the application lifecycle and triggers the `SHUTDOWN` hook.

- **Return Value:**  
  `void`

- **Usage Example:**

  ```
  $dependencyInjector->shutdown();
  ```

### 18. `register(): void`

- **Description:**  
  Registers any application services by invoking `registerProviders`.

- **Return Value:**  
  `void`

- **Usage Example:**

  ```
  $dependencyInjector->register();
  ```

### 19. `boot(): void`

- **Description:**  
  Boots the container after services are registered by triggering the `INIT` lifecycle hook.

- **Return Value:**  
  `void`

- **Usage Example:**

  ```
  $dependencyInjector->boot();
  ```

### 20. `allBindings(): array`

- **Description:**  
  Retrieves all bindings registered in the container.

- **Return Value:**  
  `array`  
  An associative array of all bindings.

- **Usage Example:**

  ```
  $bindings = $dependencyInjector->allBindings();
  ```

### 21. `allInstances(): array`

- **Description:**  
  Retrieves all resolved singleton instances.

- **Return Value:**  
  `array`  
  An associative array of all resolved singleton instances.

- **Usage Example:**

  ```
  $instances = $dependencyInjector->allInstances();
  ```

### 22. `allScopedInstances(): array`

- **Description:**  
  Retrieves all scoped instances.

- **Return Value:**  
  `array`  
  An associative array of all scoped instances.

- **Usage Example:**

  ```
  $scopedInstances = $dependencyInjector->allScopedInstances();
  ```

### 23. `everything(): array`

- **Description:**  
  Combines and returns all bindings, instances, and scoped instances.

- **Return Value:**  
  `array`  
  The combined array of all bindings, instances, and scoped instances.

- **Usage Example:**

  ```
  $allServices = $dependencyInjector->everything();
  ```

---

## Usage Examples

The following examples demonstrate how to utilize the `DependencyInjector` class to manage and resolve services within
an application.

### 1. **Initializing the Dependency Injector**

```
use Gemini\Container\Containers\DependencyInjector;
use Gemini\Container\Containers\Registry\Bindings;
use Gemini\Container\Containers\Registry\Instances;
use Gemini\Container\Containers\Registry\ScopedInstances;
use Gemini\Container\Containers\Registry\Deferred;
use Gemini\Container\Containers\Registry\LifecycleHooks;
use Gemini\Container\Containers\Registry\ChildContainers;
use Gemini\Container\Containers\Registry\DependencyGraph;
use Gemini\Cache\InMemoryCache;

// Initialize the Dependency Injector with a cache TTL of 1 hour
$dependencyInjector = new DependencyInjector(cacheTTL: 3600);
```

### 2. **Registering Service Providers**

```
$dependencyInjector->registerProviders();
```

- **Explanation:**  
  Loads and registers all service providers defined in the `services.php` configuration file. Each provider is
  responsible for binding services to the container and performing any necessary bootstrapping.

### 3. **Binding Services**

#### **Binding a Singleton Service**

```
$dependencyInjector->singleton('Logger', function($container) {
    return new Logger($container->get('Config'));
});
```

- **Explanation:**  
  Registers the `Logger` service as a singleton, ensuring only one instance exists throughout the application's
  lifecycle. The closure defines how to instantiate the `Logger`, injecting the `Config` service as a dependency.

#### **Binding a Transient Service**

```
$dependencyInjector->bind('Mailer', Mailer::class);
```

- **Explanation:**  
  Registers the `Mailer` service without singleton scope, allowing multiple instances to be created as needed.

### 4. **Resolving Services**

#### **Retrieving a Singleton Service**

```
$logger = $dependencyInjector->get('Logger');
$logger->log('Application started.');
```

- **Explanation:**  
  Retrieves the singleton instance of `Logger` from the container and uses it to log a message.

#### **Auto-Resolving a Class**

```
$userService = $dependencyInjector->get(UserService::class);
$userService->createUser('John Doe');
```

- **Explanation:**  
  Automatically resolves and instantiates the `UserService` class, injecting any dependencies it requires.

### 5. **Using Lifecycle Hooks**

```
use Gemini\Container\Containers\LifecycleHook;

// Register a custom INIT hook
$dependencyInjector->lifecycleHooks->add(LifecycleHook::INIT, function($container) {
    $container->get('Logger')->log('Container initialized.');
});

// Boot the container to trigger INIT hooks
$dependencyInjector->boot();
```

- **Explanation:**  
  Adds a custom `INIT` lifecycle hook that logs a message when the container is initialized. Invoking `boot()` triggers
  the `INIT` hook.

### 6. **Handling Transactions**

```
use Gemini\Container\Exceptions\AutoResolveException;
use Gemini\Container\Exceptions\CircularDependencyException;
use Gemini\Container\Exceptions\ServiceNotFoundException;

try {
    $dependencyInjector->transaction(function() use ($dependencyInjector) {
        $dependencyInjector->get('Database')->beginTransaction();
        
        $dependencyInjector->get('UserService')->createUser('Alice');
        $dependencyInjector->get('OrderService')->createOrder('Order123');
        
        $dependencyInjector->get('Database')->commit();
    });
    echo "BaseTransaction completed successfully.";
} catch (Exception $e) {
    $dependencyInjector->get('Database')->rollBack();
    echo "BaseTransaction failed: " . $e->getMessage();
}
```

- **Explanation:**  
  Executes multiple operations within a single transaction. If any operation fails, the transaction is rolled back to
  maintain data integrity.

### 7. **Managing Cached Services**

```
// Retrieve a cached service
$cache = $dependencyInjector->get('Cache');

// Invalidate a cached service
$dependencyInjector->invalidateCache('Cache');
```

- **Explanation:**  
  Accesses the `Cache` service from the container and demonstrates how to invalidate its cached instance.

### 8. **Working with Child Containers**

```
// Create a child container
$childContainer = $dependencyInjector->childContainers->createChild();

// Bind a service in the child container
$childContainer->bind('ReportService', ReportService::class);

// Resolve a service from the child container
$reportService = $childContainer->get('ReportService');
```

- **Explanation:**  
  Demonstrates creating a child container, binding a service within it, and resolving that service independently from
  the parent container.

---

## Exception Handling

The `DependencyInjector` class throws several custom exceptions to handle error scenarios gracefully.

### 1. `AutoResolveException`

- **Description:**  
  Thrown when the container fails to automatically resolve a class, typically due to missing dependencies or
  non-instantiable classes.

- **Usage Example:**

  ```
  try {
      $service = $dependencyInjector->get('NonExistentService');
  } catch (AutoResolveException $e) {
      echo "Error: " . $e->getMessage();
  }
  ```

### 2. `CircularDependencyException`

- **Description:**  
  Thrown when a circular dependency is detected during service resolution, preventing infinite loops.

- **Usage Example:**

  ```
  try {
      $serviceA = $dependencyInjector->get('ServiceA');
  } catch (CircularDependencyException $e) {
      echo "Circular dependency detected: " . $e->getMessage();
  }
  ```

### 3. `ServiceNotFoundException`

- **Description:**  
  Thrown when a requested service is not found in the container and cannot be autowired.

- **Usage Example:**

  ```
  try {
      $unknownService = $dependencyInjector->get('UnknownService');
  } catch (ServiceNotFoundException $e) {
      echo "Service not found: " . $e->getMessage();
  }
  ```

### 4. `UnresolvableDependencyException`

- **Description:**  
  Thrown when a dependency required by a service cannot be resolved, often due to missing type hints or default values.

- **Usage Example:**

  ```
  try {
      $incompleteService = $dependencyInjector->get('IncompleteService');
  } catch (UnresolvableDependencyException $e) {
      echo "Cannot resolve dependency: " . $e->getMessage();
  }
  ```

---

## Lifecycle Hooks

The `DependencyInjector` class supports lifecycle hooks that allow the execution of custom logic during specific phases
of the container's lifecycle.

### Supported Hooks

1. **`INIT`**

    - **Description:**  
      Triggered when the container is initialized and services are being booted.

    - **Usage Example:**

      ```
      $dependencyInjector->lifecycleHooks->add(LifecycleHook::INIT, function($container) {
          $container->get('Logger')->log('Initialization complete.');
      });
      ```

2. **`SHUTDOWN`**

    - **Description:**  
      Triggered when the application is shutting down, allowing for cleanup operations.

    - **Usage Example:**

      ```
      register_shutdown_function(function() use ($dependencyInjector) {
          $dependencyInjector->shutdown();
      });
      ```

3. **`ERROR`**

    - **Description:**  
      Triggered when an error occurs within the container, such as during service resolution.

    - **Usage Example:**

      ```
      $dependencyInjector->lifecycleHooks->add(LifecycleHook::ERROR, function($exception, $container) {
          $container->get('Logger')->error($exception->getMessage());
      });
      ```

### Adding Hooks

```
use Gemini\Container\Containers\LifecycleHook;

// Adding an INIT hook
$dependencyInjector->lifecycleHooks->add(LifecycleHook::INIT, function($container) {
    $container->get('Logger')->log('Container initialized.');
});

// Adding a SHUTDOWN hook
$dependencyInjector->lifecycleHooks->add(LifecycleHook::SHUTDOWN, function($container) {
    $container->get('Logger')->log('Container shutting down.');
});
```

### Triggering Hooks

- **`boot()` Method:**  
  Triggers the `INIT` hook after services are registered.

- **`shutdown()` Method:**  
  Triggers the `SHUTDOWN` hook, typically called during application termination.

- **Error Handling:**  
  Errors encountered during service resolution trigger the `ERROR` hook.

---

## Caching Mechanism

The `DependencyInjector` utilizes an in-memory caching mechanism to store resolved services, enhancing performance by
reducing redundant instantiations.

### Components

1. **`InMemoryCache`**

    - **Description:**  
      Manages the storage and retrieval of cached services or data.

    - **Usage Example:**

      ```
      $cache = $dependencyInjector->get('Cache');
      $cache->set('key', 'value');
      $value = $cache->get('key');
      ```

2. **`$cacheTTL` Property**

    - **Description:**  
      Defines the time-to-live (TTL) for cached items in seconds.

    - **Default Value:**  
      `3600` seconds (1 hour).

### Managing Cache

#### **Retrieving Cached Services**

```
if ($cachedService = $dependencyInjector->cache->get('ServiceIdentifier')) {
    return $cachedService;
}
```

#### **Setting Cached Services**

```
$dependencyInjector->cache->set('ServiceIdentifier', $serviceInstance, $dependencyInjector->cacheTTL);
```

#### **Invalidating Cache**

```
$dependencyInjector->invalidateCache('ServiceIdentifier');
```

- **Description:**  
  Removes a specific service from the cache, forcing it to be re-resolved upon the next request.

---

## Dependency Graph Management

To prevent issues like circular dependencies, the `DependencyInjector` maintains a dependency graph that maps out the
relationships between services.

### Components

1. **`DependencyGraph`**

    - **Description:**  
      Represents the graph structure of service dependencies, allowing for validation and detection of circular
      dependencies.

2. **`$dependencyGraph` Property**

    - **Description:**  
      Holds the current state of the dependency graph.

### Managing the Dependency Graph

#### **Rebuilding the Graph**

```
$dependencyInjector->rebuildDependencyGraph();
```

- **Description:**  
  Reconstructs the dependency graph based on current service bindings, ensuring accurate representation of service
  relationships.

#### **Extracting Dependencies**

```
$dependencies = $dependencyInjector->extractDependencies($binding);
```

- **Description:**  
  Analyzes a service binding to identify its dependencies, facilitating graph construction.

---

## Exception Handling

The `DependencyInjector` class employs a robust exception handling mechanism to manage various error scenarios
effectively.

### Custom Exceptions

1. **`AutoResolveException`**

    - **Description:**  
      Thrown when the container fails to automatically resolve a class, typically due to missing dependencies or
      non-instantiable classes.

2. **`CircularDependencyException`**

    - **Description:**  
      Thrown when a circular dependency is detected during service resolution, preventing infinite loops.

3. **`ServiceNotFoundException`**

    - **Description:**  
      Thrown when a requested service is not found in the container and cannot be autowired.

4. **`UnresolvableDependencyException`**

    - **Description:**  
      Thrown when a dependency required by a service cannot be resolved, often due to missing type hints or default
      values.

### Handling Exceptions

```
use Gemini\Container\Exceptions\ServiceNotFoundException;

try {
    $mailer = $dependencyInjector->get('Mailer');
} catch (ServiceNotFoundException $e) {
    echo "Mailer service not found: " . $e->getMessage();
}
```

- **Explanation:**  
  Attempts to retrieve the `Mailer` service and catches the `ServiceNotFoundException` if the service is not registered.

---

## Lifecycle Hooks

Lifecycle hooks provide a mechanism to execute custom logic during specific phases of the container's lifecycle,
enhancing flexibility and control over the application's behavior.

### Supported Hooks

1. **`INIT`**

    - **Description:**  
      Triggered during the initialization phase of the container, after all services have been registered but before
      they are used.

2. **`SHUTDOWN`**

    - **Description:**  
      Triggered during the shutdown phase of the application, allowing for cleanup operations.

3. **`ERROR`**

    - **Description:**  
      Triggered when an error occurs within the container, such as during service resolution or instantiation.

### Adding Hooks

```
use Gemini\Container\Containers\LifecycleHook;

// Adding an INIT hook
$dependencyInjector->lifecycleHooks->add(LifecycleHook::INIT, function($container) {
    $container->get('Logger')->log('Container initialized.');
});

// Adding a SHUTDOWN hook
$dependencyInjector->lifecycleHooks->add(LifecycleHook::SHUTDOWN, function($container) {
    $container->get('Logger')->log('Container shutting down.');
});

// Adding an ERROR hook
$dependencyInjector->lifecycleHooks->add(LifecycleHook::ERROR, function($exception, $container) {
    $container->get('Logger')->error('An error occurred: ' . $exception->getMessage());
});
```

### Triggering Hooks

- **`boot()` Method:**  
  Invokes the `INIT` lifecycle hooks.

- **`shutdown()` Method:**  
  Invokes the `SHUTDOWN` lifecycle hooks.

- **Error Handling:**  
  Errors encountered during service resolution trigger the `ERROR` lifecycle hooks.

---

## Caching Mechanism

Efficient management of service instances is critical for performance. The `DependencyInjector` employs an in-memory
caching mechanism to store resolved services, reducing the overhead of repeated instantiations.

### Components

1. **`InMemoryCache`**

    - **Description:**  
      Handles the storage and retrieval of cached items. Supports setting items with a specified TTL (Time-to-Live).

2. **`$cacheTTL` Property**

    - **Description:**  
      Defines the duration (in seconds) for which cached items remain valid.

### Managing Cached Services

#### **Setting a Cached Service**

```
$dependencyInjector->cache->set('Config', $configInstance, $dependencyInjector->cacheTTL);
```

- **Description:**  
  Caches the `Config` service instance with a TTL of 1 hour.

#### **Retrieving a Cached Service**

```
$config = $dependencyInjector->cache->get('Config');
```

- **Description:**  
  Retrieves the cached `Config` service instance if it exists and is not expired.

#### **Invalidating Cached Services**

```
$dependencyInjector->invalidateCache('Config');
```

- **Description:**  
  Removes the `Config` service instance from the cache, forcing it to be re-resolved upon the next request.

---

## Dependency Graph Management

Maintaining a dependency graph is essential for detecting and preventing circular dependencies, which can lead to
infinite loops and application crashes.

### Components

1. **`DependencyGraph`**

    - **Description:**  
      Represents the relationships between services, allowing for analysis and validation of dependencies.

2. **`$dependencyGraph` Property**

    - **Description:**  
      Holds the current state of the dependency graph, reflecting all registered service dependencies.

### Managing Dependencies

#### **Rebuilding the Dependency Graph**

```
$dependencyInjector->rebuildDependencyGraph();
```

- **Description:**  
  Reconstructs the dependency graph based on current service bindings, ensuring that all dependencies are accurately
  represented and circular dependencies are detected.

#### **Extracting Dependencies**

```
$dependencies = $dependencyInjector->extractDependencies($binding);
```

- **Description:**  
  Analyzes a service binding to identify its dependencies, facilitating accurate graph construction and validation.

---

## Conclusion

The `DependencyInjector` class within the `Gemini\Container\Containers` namespace is a comprehensive solution for
managing service dependencies in a PHP application. By adhering to industry standards like PSR-11 and embracing best
practices through SOLID principles and Clean Architecture, it ensures that applications are maintainable, scalable, and
robust.

Key features such as dependency injection, singleton and scoped service management, lifecycle hooks, in-memory caching,
and deferred bindings provide developers with the tools needed to build efficient and reliable applications.
Additionally, the inclusion of lifecycle hooks and comprehensive exception handling mechanisms enhances the flexibility
and resilience of the service container.

For further assistance or inquiries, please refer to the project's repository or contact the maintainers.

---
