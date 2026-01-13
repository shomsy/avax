# KernelFacade

## Quick Summary

KernelFacade serves as the primary user interface for service registration and configuration in the container system,
implementing the facade pattern to provide a clean, intuitive API over the complex underlying registration mechanisms.
It encapsulates service binding, lifetime management, extensions, and contextual bindings into simple, chainable method
calls that developers use to configure their dependency injection container. This abstraction allows developers to focus
on declaring their services rather than understanding the internal storage and resolution mechanics.

### For Humans: What This Means (Summary)

Imagine you're at a fancy restaurant and instead of describing every ingredient and cooking technique to the chef, you
simply tell them what dish you want and any special preferences. The KernelFacade is that friendly waiter who takes your
high-level requests—"I'd like a database connection that's shared across requests"—and translates them into the detailed
instructions the kitchen (container internals) needs. It makes complex dependency configuration feel as simple as
placing an order.

## Terminology (MANDATORY, EXPANSIVE)**Facade Pattern

**: A design pattern that provides a simplified interface to a complex subsystem. In this file, the facade hides the
complexity of definition storage, scoping, and binding builders. It matters because it makes the container API
approachable for developers.

**Service Binding**: The process of associating an abstract service identifier with a concrete implementation. In this
file, `bind()`, `singleton()`, and `scoped()` methods create these associations. It matters because it establishes the
container's knowledge of how to create services.

**Service Lifetime**: The scope in which a service instance exists, determining whether it's shared or recreated. In
this file, ServiceLifetime enum controls singleton, scoped, and transient behaviors. It matters because it affects
performance, memory usage, and thread safety.

**Binding Builder**: A fluent interface for configuring complex service bindings with additional parameters. In this
file, `bind()` returns a BindingBuilder for advanced configuration. It matters because it enables sophisticated
dependency injection scenarios.

**Contextual Binding**: Service bindings that apply only in specific dependency contexts. In this file, `when()` creates
context-aware bindings. It matters because it allows different implementations for the same service in different
scenarios.

**Service Extension**: Additional behavior applied to services after creation. In this file, `extend()` and
`resolving()` add post-processing logic. It matters because it enables service decoration and initialization without
changing the core service.

### For Humans: What This Means

These are the vocabulary of service registration. The facade pattern is like having a concierge who handles all the
details so you don't have to. Service binding is telling the container "when someone asks for X, give them Y". Service
lifetime is deciding whether to reuse the same instance or create new ones. Binding builders are like advanced menus
with customization options. Contextual bindings are like having different dishes for different occasions. Service
extensions are like adding special sauces or garnishes after the main dish is ready.

## Think of It

Picture a professional event planner who handles all the details of organizing a conference—from booking venues and
arranging catering to coordinating speakers and managing registrations. The KernelFacade is that planner: you tell them
your event requirements ("we need 200 seats, vegetarian options, and a keynote speaker"), and they handle all the
complex coordination with vendors, schedules, and logistics. The result is a seamless event where you only interact with
the friendly planner, not the dozen subcontractors working behind the scenes.

### For Humans: What This Means (Think)

This analogy captures why KernelFacade exists: to coordinate complexity into simplicity. Without it, configuring a
container would require understanding multiple storage systems, scoping rules, and binding mechanisms. The facade
creates the unified, intuitive experience that makes dependency injection accessible.

## Story Example

Before KernelFacade existed, developers had to manually create ServiceDefinition objects, manage DefinitionStore
interactions, and handle scoping logic directly. Registering a simple service required multiple steps and deep knowledge
of internal APIs. With KernelFacade, service registration became declarative:
`$container->singleton('database', Database::class)` handles all the complexity internally, making container
configuration accessible to developers at all levels.

### For Humans: What This Means (Story)

This story shows the accessibility problem KernelFacade solves: API complexity. Without it, setting up dependency
injection was like having to program your own microwave—possible but requiring specialized knowledge. KernelFacade makes
it as simple as pressing " popcorn"—you get the result without understanding the electronics inside.

## For Dummies

Let's break this down like ordering food at a restaurant:

1. **The Problem**: You had to specify every ingredient, cooking method, and plating detail yourself.

2. **KernelFacade's Job**: The menu and waiter that let you order by dish name with simple modifications.

3. **How You Use It**: Call methods like `bind()` or `singleton()` with your service details.

4. **What Happens Inside**: Translates your simple requests into detailed instructions for the container.

5. **Why It's Helpful**: Makes service registration as easy as placing an order.

Common misconceptions:

- "It's just method aliases" - It's an intelligent translator that handles complex registration logic.
- "I can bypass it" - While technically possible, it breaks the container's consistency guarantees.
- "It's only for simple cases" - It handles both simple bindings and complex contextual scenarios.

### For Humans: What This Means (Dummies)

KernelFacade isn't complex—it's thoughtful. It takes the intricate details of service registration and turns them into
intuitive, memorable operations. You don't need to be a container expert; you just need to know what services you want
and how they should behave.

## How It Works (Technical)

KernelFacade holds references to DefinitionStore and ScopeManager, delegating registration calls to appropriate storage
mechanisms. Service binding methods create ServiceDefinition objects and store them, while instance registration goes
directly to the scope manager. Extension methods add behavior to the definition store, and contextual binding creates
specialized builders.

### For Humans: What This Means (How)

Under the hood, it's a traffic director with two main roads: one to the definition warehouse and one to the scope
manager. When you call bind(), it creates a service definition and stores it for later. When you register an instance,
it goes directly to the scope manager for immediate availability. Extensions and contexts get added as special routing
instructions.

## Architecture Role

KernelFacade sits at the application boundary of the kernel, defining the public registration contract while maintaining
separation from internal resolution logic. It establishes the developer experience for container configuration while
allowing the kernel internals to evolve independently.

### For Humans: What This Means (Role)

In the kernel's architecture, KernelFacade is the welcome desk—the first point of contact that shapes how developers
interact with the system. It defines the "personality" of the container's API while keeping the complex machinery
separate.

## Risks, Trade-offs & Recommended Practices

**Risk**: Facade methods can hide performance implications of different binding types.

**Why it matters**: Singleton vs transient bindings have very different performance characteristics.

**Design stance**: Document performance implications and provide guidance on binding choices.

**Recommended practice**: Use singletons for expensive resources, transients for lightweight services, scoped for
request-specific state.

**Risk**: Overuse of extensions can make service behavior unpredictable.

**Why it matters**: Multiple extensions on the same service can create complex interaction effects.

**Design stance**: Prefer explicit service decoration over runtime extensions when possible.

**Recommended practice**: Limit extensions to cross-cutting concerns and document extension order dependencies.

**Risk**: Contextual bindings can create maintenance complexity.

**Why it matters**: Context-aware bindings are harder to discover and debug than simple bindings.

**Design stance**: Use contextual bindings sparingly and document their conditions clearly.

**Recommended practice**: Prefer interface-based bindings over class-based contextual bindings for better testability.

### For Humans: What This Means (Risks)

Like any powerful interface, KernelFacade has leverage points that require care. It's excellent for its intended purpose
but needs mindful use. The key is understanding that it's a precision tool that works best when used according to its
design principles.

## Related Files & Folders

**DefinitionStore**: Stores the service definitions created by facade methods. You access it indirectly through
registration calls. It provides the persistent storage for service metadata.

**ScopeManager**: Handles instance storage for singleton and scoped services. You use it indirectly when registering
instances. It manages service lifetime and sharing.

**BindingBuilder**: Extends simple bindings with advanced configuration options. You get this returned from bind()
methods. It enables complex dependency scenarios.

**ContextBuilder**: Handles contextual binding configuration. You create this with when() calls. It manages dependency
injection based on usage context.

**ServiceLifetime**: Defines the available lifetime options for services. You use this in binding methods. It controls
instance sharing behavior.

### For Humans: What This Means (Related)

KernelFacade works with a complete service registration ecosystem. The definition store is the filing cabinet, scope
manager is the instance locker, binding builder is the customization workshop, context builder handles special cases,
and service lifetime defines the rules. Understanding this ecosystem helps you choose the right registration approach
for each service.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: bind(string $abstract, string|callable|null $concrete = null, ServiceLifetime $lifetime = ServiceLifetime::Transient): BindingBuilder

#### Technical Explanation (bind)

Creates a fundamental service binding that associates an abstract identifier with a concrete implementation,
establishing the basic relationship that enables dependency injection while allowing specification of lifetime
management behavior.

##### For Humans: What This Means (bind)

This is the core method for telling the container how to create services. You specify what you want to call the service
and how to build it, then configure how long instances should live.

##### Parameters (bind)

- `string $abstract`: The name you'll use to request this service
- `string|callable|null $concrete`: What actually gets created - a class name or a factory function
- `ServiceLifetime $lifetime`: How long instances should be reused (transient, singleton, or scoped)

##### Returns (bind)

- `BindingBuilder`: A builder object for configuring advanced binding options

##### Throws (bind)

- None. Basic binding creation is safe and doesn't throw exceptions.

##### When to Use It (bind)

- When you need full control over service binding configuration
- When you want to specify custom lifetime behavior
- As the foundation for building complex service registrations

##### Common Mistakes (bind)

- Forgetting to configure the concrete implementation
- Using incorrect lifetime settings that cause performance issues
- Not using the returned BindingBuilder for additional configuration

### Method: singleton(string $abstract, string|callable|null $concrete = null): BindingBuilder

#### Technical Explanation (singleton)

Creates a singleton binding where the container guarantees that only one instance of the service exists throughout the
application lifecycle, with all resolutions returning the same shared instance.

##### For Humans: What This Means (singleton)

Singleton bindings create services that act like global variables - everyone gets the same instance. Perfect for things
like database connections or configuration objects that should be shared.

##### Parameters (singleton)

- `string $abstract`: Service identifier for requesting the singleton
- `string|callable|null $concrete`: The class or factory that creates the singleton instance

##### Returns (singleton)

- `BindingBuilder`: Builder for additional singleton configuration

##### Throws (singleton)

- None. Singleton binding creation is safe.

##### When to Use It (singleton)

- For services that maintain state and should be shared
- For expensive resources like database connections
- For services that coordinate across the application

##### Common Mistakes (singleton)

- Using singletons for services that shouldn't be shared
- Not considering thread safety in singleton services
- Assuming singletons are always the right choice for performance

### Method: scoped(string $abstract, string|callable|null $concrete = null): BindingBuilder

#### Technical Explanation (scoped)

Creates a scoped binding where service instances are shared within a resolution scope but isolated between different
scopes, enabling per-request or per-operation service instances.

##### For Humans: What This Means (scoped)

Scoped bindings create services that are shared within a single "scope" (like a web request) but different for each
scope. It's like having separate shopping carts for different customers.

##### Parameters (scoped)

- `string $abstract`: Service identifier for scoped resolution
- `string|callable|null $concrete`: Class or factory for creating scoped instances

##### Returns (scoped)

- `BindingBuilder`: Builder for additional scoped configuration

##### Throws (scoped)

- None. Scoped binding creation is safe.

##### When to Use It (scoped)

- In web applications for per-request services
- When you need isolation between different operations
- For services that maintain state specific to a context

##### Common Mistakes (scoped)

- Confusing scoped with singleton behavior
- Not properly managing scope boundaries
- Using scoped bindings when transient would be simpler

### Method: extend(string $abstract, callable $closure): void

#### Technical Explanation (extend)

Adds post-processing behavior to an existing service binding, allowing decoration or modification of resolved instances
without altering the original binding definition.

##### For Humans: What This Means (extend)

Extensions let you add extra behavior to services after they're created, like adding logging or caching to existing
services without changing how they're originally defined.

##### Parameters (extend)

- `string $abstract`: The service to extend
- `callable $closure`: Function that receives the resolved instance and returns the modified version

##### Returns (extend)

- `void`: Extension registration doesn't return anything

##### Throws (extend)

- None. Extension registration is safe.

##### When to Use It (extend)

- When you need to add cross-cutting concerns like logging or caching
- For decorating services with additional behavior
- When you want to modify services without changing their definitions

##### Common Mistakes (extend)

- Assuming extensions run in a specific order
- Not handling exceptions in extension functions
- Using extensions when the service definition could be modified instead

### Method: resolving(string|callable $abstract, callable|null $callback = null): void

#### Technical Explanation (resolving)

Registers callbacks that execute when services are resolved, enabling post-resolution processing, validation, or
initialization of service instances.

##### For Humans: What This Means (resolving)

Resolving callbacks are like triggers that fire whenever a service gets created. You can use them to set up the service,
validate it, or perform any other initialization logic.

##### Parameters (resolving)

- `string|callable $abstract`: Service identifier or global callback for all services
- `callable|null $callback`: The function to call when the service resolves

##### Returns (resolving)

- `void`: Callback registration doesn't return anything

##### Throws (resolving)

- `InvalidArgumentException`: When parameters are malformed

##### When to Use It (resolving)

- For service initialization that requires the resolved instance
- When you need to validate services after creation
- For setting up event listeners or other post-resolution logic

##### Common Mistakes (resolving)

- Passing incorrect parameter combinations
- Assuming resolving callbacks run synchronously with resolution
- Not handling exceptions in callback functions

### Method: when(string $consumer): ContextBuilder

#### Technical Explanation (when)

Initiates contextual binding configuration, allowing different service implementations to be injected based on the
consuming class context, enabling dependency injection polymorphism.

##### For Humans: What This Means (when)

Contextual bindings let you specify that when a particular class needs a service, it should get a specific version of
that service, different from what other classes get.

##### Parameters (when)

- `string $consumer`: The class that will receive the special binding

##### Returns (when)

- `ContextBuilder`: Builder for configuring the contextual binding

##### Throws (when)

- None. Contextual binding initiation is safe.

##### When to Use It (when)

- When different parts of your application need different implementations of the same service
- For configuring services based on their usage context
- When you need dependency injection polymorphism

##### Common Mistakes (when)

- Overusing contextual bindings when interface-based design would be simpler
- Not documenting which contexts get which implementations
- Creating complex contextual binding hierarchies

### Method: instance(string $abstract, object $instance): void

#### Technical Explanation (instance)

Directly registers a pre-existing object instance as a singleton service, bypassing the normal resolution process and
ensuring the exact instance is always returned.

##### For Humans: What This Means (instance)

This lets you take an object you've already created and register it with the container so other code can get access to
it. The container treats it like a singleton.

##### Parameters (instance)

- `string $abstract`: Service identifier for accessing the instance
- `object $instance`: The pre-created object to register

##### Returns (instance)

- `void`: Instance registration doesn't return anything

##### Throws (instance)

- None. Instance registration is safe.

##### When to Use It (instance)

- When integrating with external libraries that create their own instances
- For registering configuration objects or pre-initialized services
- When you need to share existing objects through dependency injection

##### Common Mistakes (instance)

- Registering instances that shouldn't be shared
- Not ensuring the instance is properly initialized before registration
- Confusing instance registration with normal service binding

### Method: definitions(): DefinitionStore

#### Technical Explanation (definitions)

Provides direct access to the underlying service definition repository, enabling advanced introspection and manipulation
of service registrations.

##### For Humans: What This Means (definitions)

This gives you direct access to all the service definitions the container knows about. You can inspect, modify, or
analyze the registration metadata.

##### Parameters (definitions)

- None.

##### Returns (definitions)

- `DefinitionStore`: The complete repository of service definitions

##### Throws (definitions)

- None. Definition access is safe.

##### When to Use It (definitions)

- For debugging service registration issues
- When building development tools or container inspectors
- For advanced container extension scenarios

##### Common Mistakes (definitions)

- Modifying definitions directly when using the facade methods would be safer
- Assuming definition access is always fast
- Using definitions() when specific facade methods would be more appropriate

### Method: scopes(): ScopeManager

#### Technical Explanation (scopes)

Provides direct access to the scope management system, enabling advanced scope manipulation and inspection beyond the
basic scope methods.

##### For Humans: What This Means (scopes)

This gives you direct control over the scoping system, allowing you to manage service lifetimes and scope boundaries
programmatically.

##### Parameters (scopes)

- None.

##### Returns (scopes)

- `ScopeManager`: The scope management system instance

##### Throws (scopes)

- None. Scope access is safe.

##### When to Use It (scopes)

- For implementing custom scoping logic
- When building advanced container extensions
- For testing scenarios requiring scope manipulation

##### Common Mistakes (scopes)

- Using scopes() when basic scope methods would suffice
- Directly manipulating internal scope state
- Not understanding the implications of scope management

### Method: __construct(...)

#### Technical Explanation (__construct)

This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the
container’s workflow explicit and reusable.

##### For Humans: What This Means (__construct)

When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having
to manually wire the details.

##### Parameters (__construct)

- See the PHP signature in the source file for exact types and intent.

##### Returns (__construct)

- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (__construct)

- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (__construct)

- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (__construct)

- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.
