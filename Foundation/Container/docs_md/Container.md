# Container

## Quick Summary
The Container class serves as the primary public API for the dependency injection system, providing a simple facade over the complex resolution machinery beneath. It exists to give developers a clean, intuitive interface for requesting services and managing dependencies without needing to understand the intricate internals of how objects are created, wired together, and managed. This abstraction eliminates the cognitive load of manual dependency management, making applications more maintainable, testable, and flexible by centralizing object lifecycle control.

### For Humans: What This Means
Think of the Container as your application's personal assistant for handling object creation and connections. Instead of you having to remember how every object needs to be built and what it depends on, you just ask the Container for what you need, and it takes care of all the complicated wiring behind the scenes. It's like having a skilled chef who knows exactly how to prepare each dish and combine ingredients— you just order what you want, and it appears perfectly assembled, saving you from having to become an expert in every recipe.

## Terminology
**Dependency Injection**: A design pattern where objects receive their dependencies from an external source rather than creating them internally. In this file, it's the fundamental concept that the Container implements, allowing for loose coupling between components. It matters because it makes code more testable and maintainable by removing hard-coded dependencies.

**Service Identifier**: A string key used to uniquely identify a service or class that can be resolved by the container. In this file, methods like `get()`, `has()`, and `make()` use these identifiers as their primary parameters. It matters because it provides a simple, string-based way to reference complex objects without needing to know their concrete implementations.

**Resolution**: The process of creating and configuring an object with all its dependencies satisfied. In this file, this happens in methods like `get()` and `resolve()`, where the container's kernel performs the complex work of instantiating and wiring objects. It matters because it abstracts away the complexity of object creation, allowing developers to focus on using objects rather than building them.

**Scope**: A bounded context for service lifetimes, allowing different instances of services to exist in different parts of the application. In this file, `beginScope()` and `endScope()` manage these boundaries. It matters because it prevents memory leaks and allows for proper resource management in long-running applications.

**Prototype**: A pre-analyzed service definition that optimizes resolution performance. In this file, the `resolve()` method uses prototypes for advanced scenarios. It matters because it provides performance optimizations for frequently resolved services without changing the API.

**Injection Report**: A data structure containing information about what dependencies would be injected into an object. In this file, `inspectInjection()` returns this report for debugging purposes. It matters because it enables tooling and debugging without actually performing the injection.

**Scope Manager**: An object that handles the creation and management of resolution scopes. In this file, the `scopes()` method provides access to this manager. It matters because it allows advanced control over service lifetimes beyond the basic scope methods.

**Definition Store**: A repository that holds service definitions and configuration. In this file, `getDefinitions()` provides access to this store. It matters because it allows introspection and modification of how services are configured.

**Kernel Context**: An object containing additional information needed for complex resolution scenarios beyond just the service ID. In this file, `resolveContext()` uses this for advanced resolution. It matters because it enables sophisticated dependency resolution logic when simple identifiers aren't sufficient.

### For Humans: What This Means
These aren't just fancy words—they're the building blocks that make dependency injection work smoothly. Service identifiers are like names on a restaurant menu: you don't need to know how the dish is made, just what it's called. Resolution is the kitchen actually preparing and assembling your order. Scopes are like having separate tables for different parties, so their food doesn't get mixed up. Prototypes are like having pre-prepped ingredients for popular dishes. Together, they create a system where you can build complex applications without getting bogged down in the details of how everything connects.

## Think of It
Imagine you're at a high-end restaurant where the kitchen is incredibly complex—chefs specializing in different cuisines, supply chains for ingredients, quality control processes—but as a diner, you only interact with the friendly host who takes your order. The Container is that host: you tell it what you want (a service identifier), and it coordinates all the behind-the-scenes complexity to deliver exactly what you need, perfectly prepared with all its dependencies in place. The restaurant runs smoothly because each person has a clear role, and you get great food without needing to understand the entire operation.

### For Humans: What This Means
This analogy shows why the Container exists: to be your interface to complexity. Just as you trust a restaurant to handle the intricacies of food preparation, you trust the Container to handle the intricacies of object creation. It frees you to focus on your application's logic rather than its plumbing, making development faster, less error-prone, and more enjoyable.

## Story Example
Before the Container existed, developers had to manually create every object and pass all its dependencies. Imagine building a web application where your UserController needs a UserRepository, which needs a DatabaseConnection, which needs configuration settings. Without dependency injection, your controller constructor might look like: `new UserController(new UserRepository(new DatabaseConnection(new Config())))`. If any dependency changed, you had to update everywhere it was used. With the Container, you simply call `$container->get('user_controller')`, and the Container handles all the wiring. When you switch to a different database, you only change the configuration once, and the entire application automatically uses the new setup.

### For Humans: What This Means
This story illustrates the real problem the Container solves: the cascade of manual object creation that makes code brittle and hard to change. It's like the difference between having to build your own car from scratch every time you want to drive, versus having a dealership where you just pick the model you want and drive away. The Container turns a complex, error-prone process into a simple, reliable one.

## For Dummies
Let's break this down step by step like you're learning to cook for the first time:

1. **The Problem**: Objects need other objects to work, like a cake needs flour, eggs, and sugar. Without help, you'd have to gather and mix all ingredients yourself every time.

2. **The Container's Job**: It's like a smart pantry that knows recipes and automatically gathers and combines ingredients when you ask for a specific dish.

3. **How You Use It**: Instead of writing complex creation code, you just say "give me a user controller" by calling `$container->get('user_controller')`.

4. **What Happens Inside**: The Container looks up the "recipe" (service definition), gets all required "ingredients" (dependencies), and assembles everything correctly.

5. **Why It's Helpful**: If you change the recipe (like switching databases), you only update it in one place, and everything that uses it automatically gets the new version.

Common misconceptions:
- "It's just a fancy array" - No, it manages object lifecycles, dependencies, and scopes intelligently.
- "It's slow" - Modern containers are highly optimized and often faster than manual creation.
- "It's only for big applications" - Even small apps benefit from the testability and flexibility it provides.

### For Humans: What This Means
The Container isn't magic—it's a well-designed tool that follows clear rules. Think of it as a helpful assistant who remembers all the complicated setup steps so you don't have to. It makes programming feel more like describing what you want rather than micromanaging how to get it.

## How It Works (Technical)
The Container acts as a facade that delegates all operations to the ContainerKernel. When `get($id)` is called, it passes the request to the kernel, which orchestrates the resolution process through the resolution pipeline. The pipeline includes steps like definition lookup, dependency analysis, instantiation, property injection, method calls, and lifecycle management. Scopes are managed through a ScopeManager that tracks nested resolution contexts. Advanced features like prototyping and context-based resolution provide optimization paths for complex scenarios. The kernel coordinates with various subsystems like the definition store, lifecycle resolvers, and telemetry collectors to ensure robust, observable operation.

### For Humans: What This Means
Under the hood, it's like a well-choreographed dance where different specialists handle their parts: one looks up definitions, another analyzes dependencies, another creates objects, another injects values. The Container is the conductor ensuring everyone performs in the right order. You don't need to know the choreography—just that when you ask for something, it arrives complete and ready to use.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: get(string $id): mixed

#### Technical Explanation
This method serves as the primary entry point for retrieving fully constructed service instances from the container. It initiates the complete resolution process, ensuring all dependencies are properly injected and the service is ready for use. The method delegates to the kernel's resolution pipeline, which handles caching, instantiation, and dependency injection automatically.

##### For Humans: What This Means
When you call `get('database')`, you're asking the container to give you a fully working database connection object. The container doesn't just hand you a class—it creates the object, connects it to whatever it needs (like configuration settings), and makes sure it's ready to use immediately.

##### Parameters
- `string $id`: The unique identifier for the service you want. This is like the name on a menu—you ask for "database" and get a database connection.

##### Returns
- `mixed`: The fully resolved service instance. This could be any type of object, but it's guaranteed to be ready to use with all its dependencies satisfied.

##### Throws
- `ResolutionException`: When the service cannot be resolved due to dependency issues. This happens if the container can't create the object or its dependencies.
- `ServiceNotFoundException`: When the requested service identifier is not registered. This occurs when you ask for a service that was never defined.

##### When to Use It
- When you need a service instance in your application code
- When implementing dependency injection in controllers or services
- When you want the container to handle all the complex setup automatically

##### Common Mistakes
- Calling `get()` in loops without considering performance implications
- Using `get()` for services that should be injected as constructor dependencies
- Assuming the returned object is always the same instance (it depends on the service's lifetime configuration)

### Method: has(string $id): bool

#### Technical Explanation
This method performs a lightweight check to determine if a service identifier can be resolved without actually constructing the service instance. It checks both registered definitions and the scope cache, and for unregistered identifiers, it validates if the class exists and is instantiable. This allows for conditional logic and defensive programming patterns.

##### For Humans: What This Means
Before you actually ask for a service with `get()`, you can check if it's available using `has()`. It's like checking if a restaurant has your favorite dish before you order it. This is much faster than trying to get the service and catching an exception.

##### Parameters
- `string $id`: The service identifier to check for availability.

##### Returns
- `bool`: True if the service can be resolved, false otherwise. This tells you whether `get($id)` would succeed.

##### Throws
- None. This method is designed to be safe and never throw exceptions.

##### When to Use It
- When implementing optional dependencies in your code
- When you need to conditionally use a service based on availability
- When building defensive code that needs to handle missing services gracefully

##### Common Mistakes
- Using `has()` as a performance optimization when `get()` would be just as fast for single lookups
- Forgetting that `has()` doesn't guarantee the service will still be available when you call `get()` later
- Using `has()` in performance-critical code when you could avoid the check entirely

### Method: make(string $abstract, array $parameters = []): object

#### Technical Explanation
This method creates a new instance of the requested service with optional parameter overrides, bypassing any scope caching mechanisms. It performs full resolution including dependency injection, but ensures a fresh instance is created each time. This is useful for services that need unique instances or runtime parameter customization.

##### For Humans: What This Means
While `get()` might give you a shared instance (like getting the same coffee maker every time), `make()` always creates a brand new instance. It's like asking the kitchen to prepare a fresh dish just for you, even if they normally keep pre-made items ready.

##### Parameters
- `string $abstract`: The service identifier or class name to instantiate.
- `array $parameters`: Optional parameters to override constructor arguments. These replace the normal dependency injection for specific values.

##### Returns
- `object`: A new instance of the requested service with all dependencies resolved.

##### Throws
- `ResolutionException`: When dependencies cannot be resolved for the new instance.
- `ServiceNotFoundException`: When the service identifier is not registered.

##### When to Use It
- When you need multiple instances of the same service type
- When you want to override constructor parameters at runtime
- When implementing factories that create service instances with custom configuration

##### Common Mistakes
- Using `make()` when `get()` would be more appropriate for singleton-like services
- Forgetting that `make()` bypasses scope caching, potentially creating unnecessary objects
- Passing incorrect parameter types that don't match the service's constructor signature

### Method: call(callable|string $callable, array $parameters = []): mixed

#### Technical Explanation
This method executes a callable (function, method, or closure) with automatic dependency injection applied to its parameters. It analyzes the callable's signature, resolves dependencies for each parameter, and invokes the callable with the resolved dependencies. This enables dependency injection for procedural code and event handlers.

##### For Humans: What This Means
Normally, dependency injection works for classes, but what about standalone functions or methods? `call()` lets you execute any function and have the container automatically provide its dependencies. It's like having a smart assistant who reads the recipe and gathers all the ingredients before you start cooking.

##### Parameters
- `callable|string $callable`: The function, method, or closure to execute.
- `array $parameters`: Additional parameters to pass, which take precedence over dependency injection.

##### Returns
- `mixed`: The result of executing the callable.

##### Throws
- `ResolutionException`: When dependencies for the callable cannot be resolved.

##### When to Use It
- When implementing event listeners or middleware that need dependency injection
- When executing legacy procedural code that wasn't designed for dependency injection
- When building command handlers or job processors that need services

##### Common Mistakes
- Using `call()` for simple functions that don't need dependency injection
- Forgetting that parameter order matters when mixing manual parameters with injection
- Using `call()` in performance-critical code where direct instantiation would be faster

### Method: injectInto(object $target): object

#### Technical Explanation
This method performs dependency injection into an existing object instance that was created outside the container. It runs the injection pipeline on the target object, resolving and injecting properties and method dependencies. This is useful for objects created by factories, deserialization, or legacy code.

##### For Humans: What This Means
Sometimes you have an object that was created elsewhere—a deserialized object from cache, or something created by a factory. `injectInto()` lets the container "retrofit" dependency injection onto that existing object. It's like taking a car that was built without modern electronics and adding GPS, airbags, and automatic transmission after the fact.

##### Parameters
- `object $target`: The existing object to inject dependencies into.

##### Returns
- `object`: The same object instance with dependencies now injected.

##### Throws
- `ResolutionException`: When dependencies cannot be resolved for injection into the target object.

##### When to Use It
- When working with objects created by external factories or libraries
- When deserializing objects from cache or storage
- When integrating legacy code that creates objects manually

##### Common Mistakes
- Trying to inject into objects that weren't designed for dependency injection
- Assuming injection will work the same as constructor injection (it uses different mechanisms)
- Using `injectInto()` when constructor injection would be more appropriate

### Method: resolve(ServicePrototype $prototype): mixed

#### Technical Explanation
This method resolves a service using a pre-analyzed prototype, bypassing standard resolution for performance optimization. The prototype contains pre-computed dependency information, allowing faster resolution of frequently used services. This advanced API is used internally by optimization systems.

##### For Humans: What This Means
For services that are used very frequently, the container can pre-analyze them and create a "prototype" — a blueprint that speeds up creation. `resolve()` uses this optimized blueprint instead of analyzing the service from scratch each time. It's like having pre-cut patterns for sewing popular clothing items.

##### Parameters
- `ServicePrototype $prototype`: The pre-analyzed service blueprint containing dependency information.

##### Returns
- `mixed`: The resolved service instance created from the prototype.

##### Throws
- `ResolutionException`: When the prototype cannot be resolved.

##### When to Use It
- When implementing performance optimizations for frequently resolved services
- When building caching layers that pre-analyze service dependencies
- In advanced container extensions that need optimized resolution paths

##### Common Mistakes
- Using `resolve()` when standard `get()` would be sufficient
- Assuming prototypes are always faster (overhead can outweigh benefits for simple services)
- Not validating that prototypes are up-to-date with current service definitions

### Method: resolveContext(KernelContext $context): mixed

#### Technical Explanation
This method provides advanced resolution capabilities using a full KernelContext object, which contains detailed resolution state and metadata. This allows for complex resolution scenarios that require more than just a service identifier, including contextual overrides and advanced resolution strategies.

##### For Humans: What This Means
Sometimes you need more than just a service name—you need to provide context about how that service should be resolved. `resolveContext()` lets you pass a detailed "resolution request" that includes all the context the container needs to make sophisticated decisions about dependency injection.

##### Parameters
- `KernelContext $context`: A comprehensive context object containing service ID, overrides, metadata, and resolution state.

##### Returns
- `mixed`: The resolved service instance based on the provided context.

##### Throws
- `ResolutionException`: When the service cannot be resolved with the given context.

##### When to Use It
- When implementing advanced dependency injection scenarios
- When building container extensions that need fine-grained control
- When you need to pass metadata or overrides that affect resolution

##### Common Mistakes
- Using `resolveContext()` when simpler methods like `get()` would suffice
- Not properly initializing the KernelContext with required information
- Assuming context resolution works the same as standard resolution

### Method: instance(string $abstract, object $instance): void

#### Technical Explanation
This method registers an existing object instance with the container, making it available for future resolution. The instance is treated as a singleton and stored in the scope manager. If no definition exists, a singleton definition is automatically created.

##### For Humans: What This Means
Sometimes you have an object that was created elsewhere—an external service, a pre-configured connection, or something created by legacy code. `instance()` lets you "donate" that object to the container so other code can access it. It's like registering an existing family member so everyone knows where to find them.

##### Parameters
- `string $abstract`: The identifier to register the instance under.
- `object $instance`: The existing object instance to register.

##### Returns
- `void`: This method doesn't return anything; it just registers the instance.

##### Throws
- None. This method is designed to be safe and non-throwing.

##### When to Use It
- When integrating external services or libraries that create their own objects
- When you have pre-configured objects that need to be shared across the application
- When migrating legacy code that creates objects manually

##### Common Mistakes
- Using `instance()` for objects that should be created by the container
- Registering instances with generic names that conflict with service definitions
- Assuming registered instances will be automatically injected (they need to be explicitly requested)

### Method: beginScope(): void

#### Technical Explanation
This method initiates a new resolution scope, creating a boundary for service lifetimes within that scope. Services resolved within the scope may have different lifetimes than those outside. The scope must be properly closed with `endScope()` to prevent resource leaks.

##### For Humans: What This Means
Scopes are like temporary "rooms" in your application where services behave differently. `beginScope()` opens a new room, and everything that happens inside uses different rules for service sharing and cleanup. You must remember to close the room with `endScope()` when you're done.

##### Parameters
- None.

##### Returns
- `void`: This method initiates the scope but doesn't return anything.

##### Throws
- None. Scope creation is designed to be safe.

##### When to Use It
- When implementing request-scoped services in web applications
- When you need temporary service isolation for testing or background jobs
- When building middleware or decorators that need scoped behavior

##### Common Mistakes
- Forgetting to call `endScope()`, causing memory leaks
- Nesting scopes without understanding the lifetime implications
- Using scopes for services that should be application-wide singletons

### Method: endScope(): void

#### Technical Explanation
This method terminates the current resolution scope, cleaning up any scoped services and restoring the previous scope context. This ensures proper resource management and prevents memory leaks from scoped service instances.

##### For Humans: What This Means
After you've finished working in a scoped "room", `endScope()` closes the door and cleans up everything that was specific to that room. It's like checking out of a hotel room—the cleaning crew comes in and resets everything for the next guest.

##### Parameters
- None.

##### Returns
- `void`: This method closes the scope but doesn't return anything.

##### Throws
- None. Scope termination is designed to be safe.

##### When to Use It
- Always paired with `beginScope()` to ensure proper cleanup
- In finally blocks or cleanup handlers to guarantee scope closure
- When implementing request lifecycle management in web frameworks

##### Common Mistakes
- Not calling `endScope()` after `beginScope()`, causing resource leaks
- Calling `endScope()` without a corresponding `beginScope()`
- Assuming scope cleanup happens automatically (it requires explicit calls)

### Method: canInject(object $target): bool

#### Technical Explanation
This method analyzes an object to determine if it has injectable properties or methods marked for dependency injection. It performs static analysis of the object's structure without attempting actual injection, providing a lightweight way to check injectability.

##### For Humans: What This Means
Before trying to inject dependencies into an object, you can check if it even has places where injection can happen. `canInject()` looks for the "injection points" — properties or methods marked with injection attributes. It's like checking if a car has the right connections before trying to plug in accessories.

##### Parameters
- `object $target`: The object to analyze for injection points.

##### Returns
- `bool`: True if the object has injection points, false otherwise.

##### Throws
- None. This analysis is designed to be safe and non-throwing.

##### When to Use It
- When building tooling that needs to detect injectable objects
- When implementing conditional injection logic
- When debugging dependency injection issues

##### Common Mistakes
- Assuming `canInject()` means injection will succeed (it only checks for points, not resolvability)
- Using `canInject()` as a performance optimization when injection would be just as fast
- Forgetting that injection points can be added dynamically through traits or parent classes

### Method: inspectInjection(object $target): InjectionReport

#### Technical Explanation
This method provides detailed analysis of an object's injection points, returning a comprehensive report of what dependencies would be injected. It examines the object's prototype to identify injectable properties and methods, along with their expected types. This is useful for debugging, tooling, and introspection.

##### For Humans: What This Means
`inspectInjection()` gives you a detailed "blueprint" of what the container would inject into an object. It's like getting a full specification sheet that shows every property and method that can receive dependencies, along with what types are expected. Perfect for understanding why injection might be failing or for building development tools.

##### Parameters
- `object $target`: The object to inspect for detailed injection information.

##### Returns
- `InjectionReport`: A comprehensive report containing injection points, their types, and metadata.

##### Throws
- None. Inspection is designed to be safe and non-throwing.

##### When to Use It
- When debugging dependency injection issues
- When building IDE tools or container inspectors
- When you need to understand an object's injection requirements

##### Common Mistakes
- Using `inspectInjection()` in production code where performance matters
- Assuming the report is static (injection points can change with object state)
- Not understanding that inspection doesn't perform actual injection

### Method: scopes(): ScopeManager

#### Technical Explanation
This method provides access to the underlying scope management system, allowing direct manipulation of resolution scopes and service lifetimes. The ScopeManager handles nested scopes, instance storage, and cleanup coordination.

##### For Humans: What This Means
While the basic scope methods (`beginScope()`, `endScope()`) are convenient, sometimes you need direct access to the scope system for advanced operations. `scopes()` gives you the "control panel" for managing how services are shared and cleaned up across different parts of your application.

##### Parameters
- None.

##### Returns
- `ScopeManager`: The scope management system for advanced scope operations.

##### Throws
- None.

##### When to Use It
- When implementing custom scoping logic
- When building advanced container extensions
- When you need fine-grained control over service lifetimes

##### Common Mistakes
- Using `scopes()` when the simple scope methods would suffice
- Directly manipulating scopes without understanding the implications
- Not cleaning up custom scopes properly

### Method: exportMetrics(): string

#### Technical Explanation
This method exports comprehensive performance and usage metrics from the container system as a serialized string. It aggregates telemetry data from resolution operations, providing insights into performance bottlenecks and usage patterns for monitoring and optimization.

##### For Humans: What This Means
The container keeps track of how it's performing—how long resolutions take, which services are used most, where bottlenecks occur. `exportMetrics()` gives you all that data in a format you can send to monitoring systems or analyze yourself. It's like getting the black box flight recorder data from the container's operation.

##### Parameters
- None.

##### Returns
- `string`: Serialized metrics data containing performance and usage statistics.

##### Throws
- None.

##### When to Use It
- When implementing application monitoring and observability
- When debugging performance issues in dependency resolution
- When building dashboards or alerts for container health

##### Common Mistakes
- Calling `exportMetrics()` too frequently in performance-critical code
- Not configuring metrics collection when you need the data
- Assuming metrics are always available (depends on configuration)

### Method: getDefinitions(): DefinitionStore

#### Technical Explanation
This method provides direct access to the service definition registry, allowing inspection and modification of service definitions. The DefinitionStore contains all registered service configurations, bindings, and metadata used for resolution.

##### For Humans: What This Means
The container keeps a "recipe book" of all the services it knows how to create. `getDefinitions()` lets you peek at that recipe book—see what services are registered, how they're configured, and even modify them if needed. It's like having access to the restaurant's menu database for inspection or updates.

##### Parameters
- None.

##### Returns
- `DefinitionStore`: The complete registry of service definitions and configurations.

##### Throws
- None.

##### When to Use It
- When building container introspection tools
- When implementing dynamic service registration
- When debugging service configuration issues

##### Common Mistakes
- Modifying definitions directly when using the registration API would be safer
- Assuming definition access is always fast (can be expensive for large registries)
- Using `getDefinitions()` when specific query methods would be more appropriate

## Architecture Role
The Container sits at the top of the component hierarchy, serving as the main API boundary. It depends on ContainerKernel for implementation details but exposes a stable, simple interface. Other components like Features, Guard, and Observe depend on the Container for integration points. The container defines the component's public contract while delegating complex logic to specialized subsystems. This separation allows the core API to remain stable while internals evolve.

### For Humans: What This Means
In the component's "org chart," the Container is the CEO— the public face that everyone interacts with. It delegates the real work to expert managers (the kernel and its subsystems) but maintains control over the overall direction. This structure means you can trust the Container's interface to stay consistent even as the internal team gets reorganized for better efficiency.

## Risks, Trade-offs & Recommended Practices
**Risk**: Over-reliance on the container can lead to service locator anti-pattern where classes become tightly coupled to the container itself.

**Why it matters**: This defeats the purpose of dependency injection by creating a new form of tight coupling.

**Design stance**: The container should be used at application boundaries (controllers, factories) rather than deep in business logic.

**Recommended practice**: Use constructor injection for business objects and reserve container calls for composition root scenarios.

**Risk**: Scope leaks can cause memory issues in long-running applications.

**Why it matters**: Services intended for request scope might persist across requests, leading to memory bloat and potential security issues.

**Design stance**: Always pair `beginScope()` with `endScope()` using try/finally blocks.

**Recommended practice**: Use scope guards or middleware to ensure scopes are properly closed even when exceptions occur.

**Risk**: Complex resolution logic can mask performance issues.

**Why it matters**: Deep dependency graphs or circular references can cause resolution timeouts or stack overflows.

**Design stance**: Prefer shallow dependency graphs and use lazy resolution where possible.

**Recommended practice**: Use prototyping and caching for frequently resolved services, and monitor resolution metrics.

### For Humans: What This Means
Like any powerful tool, the Container can cause problems if misused. It's great for its intended purpose—managing dependencies at application edges—but becomes problematic when used as a crutch throughout your code. Think of it as a credit card: fantastic for big purchases you plan for, but dangerous for impulse buys. The key is using it strategically rather than everywhere.

## Related Files & Folders
**ContainerKernel**: The core implementation that handles all container operations. You encounter it when debugging resolution issues or extending container functionality. It exists to encapsulate the complex resolution logic away from the public API.

**DefinitionStore**: Repository for service definitions and configurations. You interact with it when registering services or inspecting what services are available. It provides the data layer for the container's knowledge of how to build objects.

**ScopeManager**: Handles scope lifecycle and nesting. You use it when implementing custom scoping logic or debugging scope-related issues. It ensures services have appropriate lifetimes in different contexts.

**ResolutionPipeline**: Orchestrates the step-by-step object creation process. You encounter it when optimizing performance or adding custom resolution steps. It defines the sequence of operations needed to fully resolve a service.

**Config/**: Contains configuration classes that define container behavior. You modify these when customizing how the container operates globally. They provide the settings that influence all container operations.

**Features/**: Houses advanced capabilities like caching, prototyping, and specialized injection. You explore this when you need capabilities beyond basic dependency injection. It extends the container with enterprise-grade features.

### For Humans: What This Means
These related pieces are like the supporting cast in a play. The Container is the star, but it couldn't perform without the kernel (director), definition store (script), scope manager (stage manager), and others. Each has a specific role that becomes important when you need to customize or debug particular aspects of dependency injection.