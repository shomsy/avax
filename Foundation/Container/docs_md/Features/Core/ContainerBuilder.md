# ContainerBuilder

## Quick Summary
ContainerBuilder serves as the primary construction interface for the dependency injection container, implementing a fluent builder pattern that enables expressive service registration and configuration. It orchestrates the complete container assembly process—from service definitions through compilation and bootstrapping—providing a clean, chainable API for setting up complex dependency injection scenarios. This builder pattern encapsulates the complexity of container construction while offering powerful customization capabilities.

### For Humans: What This Means
Think of ContainerBuilder as the master craftsman who takes your specifications and builds the container exactly how you want it. Instead of manually wiring everything together, you describe what you need through a fluent interface, and the builder handles all the intricate assembly work. It's like having an expert carpenter who speaks your language and turns your blueprints into reality.

## Terminology
**Fluent Builder Pattern**: A design pattern that allows method chaining to configure objects step-by-step, enabling readable and expressive construction code. In this file, methods like `bind()`, `singleton()`, and `withProfile()` return `$this` for chaining. It matters because it creates intuitive APIs for complex configuration.

**Service Registration**: The process of defining how services should be created and managed by the container. In this file, methods like `bind()` and `singleton()` register service definitions. It matters because it establishes the container's knowledge of available services.

**Compiler Pass**: A processing step that can modify service definitions before container construction. In this file, `addCompilerPass()` registers passes for execution. It matters because it enables automated service optimization and validation.

**Bootstrap Profile**: A predefined configuration template that sets up common container behaviors. In this file, `BootstrapProfile` defines debug modes and optimization settings. It matters because it provides sensible defaults for different deployment scenarios.

**Definition Store**: A repository that holds all service definitions and their configuration. In this file, `$definitions` stores the service registry. It matters because it provides the data foundation for container operation.

**Scope Registry**: A system for managing service lifetime boundaries and scoping rules. In this file, `$registry` handles scope definitions. It matters because it controls how services are shared or isolated.

### For Humans: What This Means
These are the building blocks of container construction. Fluent builders make configuration conversational, service registration teaches the container your services, compiler passes allow automated improvements, bootstrap profiles provide starting templates, definition stores remember everything, and scope registries manage service lifecycles. Together they create a comprehensive system for defining exactly how your container should work.

## Think of It
Imagine you're directing the construction of a custom kitchen. The ContainerBuilder is your general contractor who understands your requirements and coordinates all the subcontractors. You specify what appliances you want (services), how they should be installed (bindings), what quality standards to meet (policies), and what timeline to follow (profiles). The builder then orchestrates plumbers, electricians, and carpenters (bootstrapper, compiler passes, registrars) to create your perfect kitchen.

### For Humans: What This Means
This analogy captures why ContainerBuilder exists: to orchestrate complexity. Without it, building a container would require coordinating dozens of components manually. The ContainerBuilder provides the unified interface that makes sophisticated container construction feel natural and manageable.

## Story Example
Before ContainerBuilder existed, developers had to manually instantiate and wire all container components, leading to verbose setup code and easy mistakes. With ContainerBuilder, complex container configurations became declarative and readable. A full enterprise container setup that once required hundreds of lines now fits in a clean, fluent chain of method calls.

### For Humans: What This Means
This story illustrates the practical problem ContainerBuilder solves: construction complexity. Without it, setting up a container was like assembling furniture without instructions—possible but error-prone and time-consuming. ContainerBuilder provides the clear instructions that make container construction reliable and pleasant.

## For Dummies
Let's break this down like building a custom PC:

1. **The Problem**: Individual components need specific configuration that can't change once assembled.

2. **ContainerBuilder's Job**: A guided assembly process that collects all your choices and builds the system.

3. **How You Use It**: Chain configuration methods to specify what you want, then call `build()`.

4. **What Happens Inside**: Registers services, runs optimizations, applies policies, and creates the final container.

5. **Why It's Helpful**: Turns complex assembly into simple, readable specifications.

Common misconceptions:
- "It's just a wrapper" - It orchestrates sophisticated compilation and bootstrapping processes.
- "I can modify it after build" - The container is immutable once built.
- "It's only for advanced users" - The fluent API makes it accessible while remaining powerful.

### For Humans: What This Means
ContainerBuilder isn't complex—it's organized. It takes the chaos of container construction and turns it into a predictable, step-by-step process. You don't need to understand every detail; you just need to know it makes building containers reliable and expressive.

## How It Works (Technical)
ContainerBuilder maintains internal state for definitions, registry, and configuration options. Service registration methods delegate to Registrar, configuration methods modify internal state, and `build()` executes compiler passes then delegates to ContainerBootstrapper. The fluent interface ensures all configuration happens before the immutable build phase.

### For Humans: What This Means
Under the hood, it's a stateful collector that gathers your instructions and then executes them in the right order. Service methods teach it what to build, configuration methods tell it how to build, and build() triggers the actual construction. It's like a shopping list that becomes a perfectly stocked kitchen.

## Architecture Role
ContainerBuilder sits at the application boundary of the container system, providing the construction API while delegating to specialized subsystems. It defines the container's public construction contract while maintaining independence from internal implementation details.

### For Humans: What This Means
In the container's architecture, ContainerBuilder is the front door—the welcoming interface that leads to the complex machinery inside. It presents a simple, powerful API while hiding the sophisticated coordination required to build a working container.

## Risks, Trade-offs & Recommended Practices
**Risk**: Complex builder chains can become hard to debug and maintain.

**Why it matters**: Long fluent chains obscure the final configuration.

**Design stance**: Prefer named configuration methods and break complex setups into logical groups.

**Recommended practice**: Extract common configurations into factory methods or configuration classes.

**Risk**: Compiler pass failures can prevent container construction.

**Why it matters**: Failed passes stop the entire build process.

**Design stance**: Handle compiler pass exceptions gracefully with meaningful error messages.

**Recommended practice**: Log compiler pass execution and provide recovery options.

**Risk**: Builder state can become inconsistent if methods are called in wrong order.

**Why it matters**: Some configurations depend on others being set first.

**Design stance**: Make builder methods order-independent where possible.

**Recommended practice**: Document method dependencies and provide validation in build().

### For Humans: What This Means
Like any powerful construction tool, ContainerBuilder has edges that require care. It's excellent for its intended purpose but needs thoughtful use. The key is balancing its power with clarity and error handling.

## Related Files & Folders
**ContainerBootstrapper**: Executes the final container assembly after builder configuration. You encounter it when build() is called. It handles the runtime container creation.

**Registrar**: Handles the detailed service registration logic that builder methods delegate to. You use it indirectly through bind() and singleton() methods. It manages the service definition storage.

**DefinitionStore**: Stores all service definitions that the builder collects. You modify it through registration methods. It provides the service knowledge base.

**BootstrapProfile**: Defines configuration templates that the builder can use. You select profiles with withProfile(). It provides sensible defaults for different scenarios.

**CompilerPassInterface**: Defines the contract for compilation extensions. You implement it for custom build-time processing. It enables automated service optimization.

### For Humans: What This Means
ContainerBuilder works with a team of specialists. The bootstrapper does the final assembly, the registrar handles registration details, the definition store remembers everything, profiles provide templates, and compiler passes allow customization. Understanding this team helps you know when to use each tool.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: create(): self

#### Technical Explanation
Factory method that creates a new ContainerBuilder instance initialized with production defaults, providing a clean starting point for container configuration.

##### For Humans: What This Means
This is the standard way to begin building a container. It gives you a fresh builder ready to configure with your services and settings.

##### Parameters
- None.

##### Returns
- `self`: New builder instance with production defaults.

##### Throws
- None. Creation is always safe.

##### When to Use It
- At the beginning of container setup in your application bootstrap.

##### Common Mistakes
- Trying to instantiate ContainerBuilder directly (use create() instead).

### Method: bind(string $abstract, mixed $concrete = null): BindingBuilder

#### Technical Explanation
Registers a service binding with transient lifetime, allowing new instances to be created for each resolution request.

##### For Humans: What This Means
This tells the container how to create a service. Each time someone asks for this service, the container will create a new instance.

##### Parameters
- `string $abstract`: Service name to register.
- `mixed $concrete`: What to create (class name, factory function, etc.).

##### Returns
- `BindingBuilder`: Builder for advanced configuration.

##### Throws
- None.

##### When to Use It
- When you want a fresh instance each time the service is requested.

##### Common Mistakes
- Forgetting that this creates new instances every time (use singleton() if you want sharing).

### Method: singleton(string $abstract, mixed $concrete = null): BindingBuilder

#### Technical Explanation
Registers a service that returns the same shared instance for all resolution requests throughout the application lifetime.

##### For Humans: What This Means
This creates a service that everyone shares. The container will create it once and then give the same instance to everyone who asks.

##### Parameters
- `string $abstract`: Service name.
- `mixed $concrete`: What to create for the singleton.

##### Returns
- `BindingBuilder`: Builder for advanced configuration.

##### Throws
- None.

##### When to Use It
- For services that maintain state or are expensive to create (database connections, caches, etc.).

##### Common Mistakes
- Using singleton() for services that shouldn't be shared between requests.

### Method: scoped(string $abstract, mixed $concrete = null): BindingBuilder

#### Technical Explanation
Registers a service with scoped lifetime, sharing instances within a resolution scope but creating new instances for different scopes.

##### For Humans: What This Means
This creates services that are shared within a single "scope" (like a web request) but separate between different scopes.

##### Parameters
- `string $abstract`: Service name.
- `mixed $concrete`: What to create for scoped instances.

##### Returns
- `BindingBuilder`: Builder for advanced configuration.

##### Throws
- None.

##### When to Use It
- For per-request services in web applications.

##### Common Mistakes
- Confusing scoped with singleton behavior.

### Method: instance(string $abstract, object $instance): void

#### Technical Explanation
Registers a pre-existing object instance as a singleton service, making it available for dependency injection.

##### For Humans: What This Means
This takes an object you've already created and tells the container to share it as a service.

##### Parameters
- `string $abstract`: Service name.
- `object $instance`: The existing object to register.

##### Returns
- `void`.

##### Throws
- None.

##### When to Use It
- When you have an object created elsewhere that needs to be injectable.

##### Common Mistakes
- Registering instances that shouldn't be shared.

### Method: extend(string $abstract, callable $closure): void

#### Technical Explanation
Adds a post-processing extension to modify resolved service instances, enabling decoration and enhancement.

##### For Humans: What This Means
This lets you add extra behavior to services after they're created, like adding logging or caching.

##### Parameters
- `string $abstract`: Service to extend.
- `callable $closure`: Function that receives and modifies the service instance.

##### Returns
- `void`.

##### Throws
- None.

##### When to Use It
- For cross-cutting concerns like logging, caching, or validation.

##### Common Mistakes
- Assuming extensions run in a predictable order.

### Method: when(string $consumer): ContextBuilderInterface

#### Technical Explanation
Initiates contextual binding configuration where different service implementations can be provided based on the consuming class.

##### For Humans: What This Means
This allows you to specify that when a particular class needs a service, it should get a special version of that service.

##### Parameters
- `string $consumer`: Class that will receive contextual binding.

##### Returns
- `ContextBuilderInterface`: Builder for contextual configuration.

##### Throws
- None.

##### When to Use It
- When different parts of your app need different implementations of the same service.

##### Common Mistakes
- Overusing contextual bindings when simpler solutions exist.

### Method: security(ContainerPolicy $policy = null): self|Security

#### Technical Explanation
Configures the container's security policy or provides access to advanced security configuration.

##### For Humans: What This Means
This sets up security rules for what the container is allowed to do, or gives you a builder for complex security settings.

##### Parameters
- `ContainerPolicy|null $policy`: Policy to set, or null to get security builder.

##### Returns
- `self|Security`: Builder for chaining or security configuration object.

##### Throws
- None.

##### When to Use It
- When you need to restrict what services can be resolved or how.

##### Common Mistakes
- Not configuring security in production environments.

### Method: addCompilerPass(CompilerPassInterface $pass): self

#### Technical Explanation
Registers a compiler pass that will process service definitions during the build phase, enabling optimizations and validations.

##### For Humans: What This Means
This adds a processing step that runs before the container is built, allowing automated improvements to your service definitions.

##### Parameters
- `CompilerPassInterface $pass`: The compiler pass to execute.

##### Returns
- `self`: Builder for chaining.

##### Throws
- None.

##### When to Use It
- For build-time optimizations, validations, or service transformations.

##### Common Mistakes
- Assuming compiler passes run in a specific order.

### Method: withProfile(BootstrapProfile $profile): self

#### Technical Explanation
Applies a bootstrap profile that defines container initialization behavior and optimization settings.

##### For Humans: What This Means
This applies a pre-configured set of settings that define how the container should behave in different environments.

##### Parameters
- `BootstrapProfile $profile`: Profile to apply.

##### Returns
- `self`: Builder for chaining.

##### Throws
- None.

##### When to Use It
- To quickly configure the container for development, testing, or production.

##### Common Mistakes
- Overriding profile settings manually instead of using the profile.

### Method: debug(bool $debug = true): self

#### Technical Explanation
Enables or disables debug mode, affecting error reporting and development features.

##### For Humans: What This Means
This turns on detailed error messages and debugging features for development.

##### Parameters
- `bool $debug`: Whether to enable debug mode.

##### Returns
- `self`: Builder for chaining.

##### Throws
- None.

##### When to Use It
- In development environments for better error reporting.

##### Common Mistakes
- Leaving debug mode enabled in production.

### Method: cacheDir(string $dir): self

#### Technical Explanation
Sets the directory where compiled container artifacts and cached prototypes will be stored.

##### For Humans: What This Means
This tells the container where to save its performance optimizations and cached data.

##### Parameters
- `string $dir`: Path to cache directory.

##### Returns
- `self`: Builder for chaining.

##### Throws
- None.

##### When to Use It
- To specify where performance optimizations should be stored.

##### Common Mistakes
- Using non-writable directories for cache.

### Method: build(): Container

#### Technical Explanation
Finalizes the container configuration by executing compiler passes and bootstrapping the runtime container.

##### For Humans: What This Means
This is the final step that takes all your configuration and creates the actual working container.

##### Parameters
- None.

##### Returns
- `Container`: The fully constructed container ready for use.

##### Throws
- `ContainerException`: If compilation or bootstrap fails.

##### When to Use It
- After configuring all services and settings to create the container.

##### Common Mistakes
- Trying to modify the builder after calling build() (container is immutable).
- Not handling build exceptions properly.
