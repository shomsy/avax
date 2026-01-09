# ContainerKernel

## Quick Summary
ContainerKernel serves as the central orchestrator of the container system, coordinating all dependency resolution, scoping, and injection operations through specialized sub-components. It acts as the operational heart of the container, delegating complex tasks to focused subsystems while maintaining a clean, unified interface. This architecture enables high performance, extensibility, and observability while keeping the core logic maintainable and testable.

### For Humans: What This Means
Think of ContainerKernel as the conductor of a symphony orchestra. While individual musicians (sub-components) handle their specialized parts, the conductor ensures everyone plays together harmoniously. The ContainerKernel doesn't play any instrument itself—it coordinates the performance, keeps the timing, and makes sure the final result is beautiful. It's the brain that makes all the complex dependency resolution happen smoothly and efficiently.

## Terminology
**Resolution Pipeline**: A sequence of processing steps that transform a service request into a fully constructed object with all dependencies satisfied. In this file, ResolutionPipelineBuilder creates this pipeline. It matters because it defines the systematic approach to dependency injection.

**Scope Manager**: A component that handles service lifetime boundaries, allowing different instances of services to exist in different contexts. In this file, accessed via `scopes()` method. It matters because it prevents memory leaks and enables proper resource management.

**Service Prototype**: A pre-analyzed representation of a service's structure, including its dependencies and injection points. In this file, used in `resolve()` and `inspectInjection()` methods. It matters because it optimizes resolution performance by avoiding repeated analysis.

**Kernel Context**: An object containing detailed information about a resolution request, beyond just the service identifier. In this file, used in `resolveContext()` for complex resolution scenarios. It matters because it enables sophisticated dependency resolution logic.

**Telemetry Component**: A monitoring and metrics collection system that tracks container performance and usage patterns. In this file, accessed via `telemetry()` method. It matters because it enables observability and performance optimization.

**Definition Store**: A repository that holds service definitions and configuration metadata. In this file, passed to the constructor and used throughout operations. It matters because it provides the knowledge base for service resolution.

**Reflection Analysis**: The process of examining PHP classes at runtime to understand their structure and dependencies. In this file, used in `has()` method for class instantiability checks. It matters because it enables automatic resolution of unregistered concrete classes.

### For Humans: What This Means
These are the specialized tools and concepts that make the ContainerKernel work. The resolution pipeline is like an assembly line where each station adds a piece to the final product. Scope managers are like labeled containers that keep different batches separate. Prototypes are like blueprints that speed up construction. Contexts are detailed work orders. Telemetry is the performance dashboard. Definition stores are the instruction manuals. Reflection analysis is like x-ray vision that lets the container understand code structure without being told explicitly.

## Think of It
Imagine a high-tech factory where robots handle different aspects of production—some assemble parts, others paint, others package. ContainerKernel is the central control system that coordinates all these robots. It doesn't build anything itself—it tells the assembler robot when to start, signals the painter when assembly is done, and coordinates the packaging. Each robot is an expert in its domain, and the control system ensures they work together seamlessly to produce the final product.

### For Humans: What This Means
This analogy shows why ContainerKernel exists: to orchestrate complexity. Without it, each part of the container would have to know about every other part, leading to tight coupling and maintenance nightmares. The ContainerKernel creates clean separation of concerns while maintaining coordination, making the system both powerful and maintainable.

## Story Example
Before ContainerKernel existed, container logic was scattered across multiple classes with complex interdependencies. Resolving a service required navigating a maze of method calls between different components. With ContainerKernel, all operations flow through a single, well-defined interface. When a service needs resolution, the kernel coordinates the right components—checking scopes first, then consulting the pipeline, updating telemetry—ensuring consistent behavior and easy testing.

### For Humans: What This Means
This story illustrates the organizational problem ContainerKernel solves: coordination chaos. Without a central orchestrator, dependency injection became a game of telephone where each component had to know too much about the others. ContainerKernel creates a clear chain of command, making the system predictable, testable, and maintainable.

## For Dummies
Let's break this down like managing a busy restaurant kitchen:

1. **The Problem**: Cooks, servers, and suppliers all working independently, leading to confusion and delays.

2. **ContainerKernel's Job**: The head chef who coordinates everyone and ensures smooth operations.

3. **How You Use It**: You ask the kernel for a service, and it handles all the coordination internally.

4. **What Happens Inside**: The kernel delegates to specialized components for different tasks—resolution, scoping, injection.

5. **Why It's Helpful**: Keeps complex operations organized and ensures nothing gets dropped.

Common misconceptions:
- "It's just a router" - It's an intelligent orchestrator that makes decisions about which components to use when.
- "I can bypass it" - While technically possible, it breaks the container's guarantees and testing.
- "It's slow" - The orchestration overhead is minimal compared to the resolution work it coordinates.

### For Humans: What This Means
ContainerKernel isn't mysterious—it's smart organization. It takes the chaos of coordinating many moving parts and turns it into a predictable workflow. You don't need to understand every detail; you just need to know it makes your dependency injection reliable and efficient.

## How It Works (Technical)
ContainerKernel initializes with core components: KernelRuntime for resolution, KernelState for caching, KernelCompiler for optimization, and KernelFacade for high-level operations. It implements ContainerInternalInterface, delegating most operations to appropriate sub-components. Fast-path optimizations check scopes first before full resolution. Telemetry is lazily initialized to avoid overhead when not needed.

### For Humans: What This Means
Under the hood, it's a well-structured team where each member has a clear role. The runtime handles the actual work of creating objects, the state remembers what's already done, the compiler optimizes for performance, and the facade provides convenient access. The kernel knows when to use shortcuts (like checking scopes first) and when to do full processing, making it both fast and correct.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(DefinitionStore $definitions, KernelConfig  $config): void

#### Technical Explanation
Initializes the ContainerKernel with core dependencies and sets up all sub-components. Creates the resolution pipeline, runtime execution engine, state management, compiler, and facade components based on the provided configuration.

##### For Humans: What This Means
This is the kernel's setup process, where it assembles all its internal machinery. It takes the service definitions and configuration, then builds the specialized components that will handle different aspects of dependency injection.

##### Parameters
- `DefinitionStore $definitions`: The repository containing all registered service definitions.
- `KernelConfig $config`: The configuration object containing all kernel settings and collaborators.

##### Returns
- `void`: Constructor sets up the kernel but doesn't return anything.

##### Throws
- None. Initialization is designed to be safe and complete.

##### When to Use It
- When creating a new container kernel instance
- During container bootstrap and initialization
- In framework setup code

##### Common Mistakes
- Passing incomplete or invalid configuration
- Not providing required DefinitionStore
- Assuming kernel is ready for use immediately after construction (some components are lazy)

### Method: resolveContext(KernelContext $context): mixed

#### Technical Explanation
Executes advanced resolution using a full KernelContext object that contains detailed resolution state and metadata. Checks scopes first for fast-path resolution, then delegates to runtime for full pipeline execution if needed.

##### For Humans: What This Means
This is the advanced resolution method that gives you complete control over the resolution process. Instead of just a service name, you provide a detailed context object that includes metadata, overrides, and state information.

##### Parameters
- `KernelContext $context`: Complete resolution context with service ID, metadata, and execution state.

##### Returns
- `mixed`: The resolved service instance.

##### Throws
- `ResolutionException`: When resolution fails due to dependency issues.
- `Throwable`: From pipeline execution errors.

##### When to Use It
- When you need advanced resolution control
- When implementing custom resolution logic
- When working with context-aware services

##### Common Mistakes
- Not properly initializing the KernelContext
- Assuming it works like simple get() method
- Forgetting to handle exceptions from pipeline execution

### Method: has(string $id): bool

#### Technical Explanation
Performs lightweight existence checking for services by consulting definitions, scopes, and class reflection. Checks registered definitions first, then scoped instances, and finally attempts reflection-based analysis for concrete classes.

##### For Humans: What This Means
This checks if a service is available without actually creating it. It's like checking your inventory to see if you have an item before trying to use it.

##### Parameters
- `string $id`: Service identifier to check for availability.

##### Returns
- `bool`: True if the service can be resolved, false otherwise.

##### Throws
- None. Checking is designed to be safe and non-throwing.

##### When to Use It
- When implementing conditional service usage
- When validating service availability
- When building defensive code

##### Common Mistakes
- Using `has()` in performance-critical paths
- Assuming `has()` guarantees successful resolution (it doesn't perform full validation)
- Not understanding that it checks different sources (definitions, scopes, classes)

### Method: scopes(): ScopeManager

#### Technical Explanation
Provides access to the underlying scope management system for advanced scope manipulation and inspection.

##### For Humans: What This Means
This gives you direct access to the scope system for advanced operations beyond the basic beginScope/endScope methods.

##### Parameters
- None.

##### Returns
- `ScopeManager`: The scope management instance.

##### Throws
- None.

##### When to Use It
- When implementing custom scoping logic
- When debugging scope-related issues
- When building advanced container extensions

##### Common Mistakes
- Using `scopes()` when simple scope methods suffice
- Directly manipulating internal scope state
- Not understanding scope lifecycle implications

### Method: get(string $id): mixed

#### Technical Explanation
Primary service resolution method that retrieves fully constructed instances. Implements fast-path optimization by checking scopes first, then delegates to runtime for full resolution if needed.

##### For Humans: What This Means
This is the main method you use to get services from the container. Give it a service name and it returns a ready-to-use object with all dependencies satisfied.

##### Parameters
- `string $id`: Service identifier to resolve.

##### Returns
- `mixed`: The resolved service instance.

##### Throws
- `ResolutionException`: When resolution fails.
- `ServiceNotFoundException`: When service is not registered.

##### When to Use It
- When requesting services in application code
- When implementing dependency injection
- When accessing container-managed objects

##### Common Mistakes
- Calling `get()` in loops without caching
- Using `get()` for services that should be constructor-injected
- Not handling resolution exceptions

### Method: instance(string $abstract, object $instance): void

#### Technical Explanation
Registers an existing object instance with the container, making it available for future resolution. The instance is treated as a singleton and stored in the scope manager.

##### For Humans: What This Means
This lets you add an existing object to the container so other code can access it. It's like registering a pre-built component that the container can provide to other services.

##### Parameters
- `string $abstract`: Service identifier for the instance.
- `object $instance`: The object instance to register.

##### Returns
- `void`: Registration doesn't return anything.

##### Throws
- None. Registration is designed to be safe.

##### When to Use It
- When integrating external objects or services
- When registering pre-configured instances
- When migrating legacy code

##### Common Mistakes
- Registering instances with conflicting names
- Assuming registered instances are automatically injected
- Not understanding singleton treatment

### Method: make(string $id, array $parameters = []): object

#### Technical Explanation
Creates a new service instance with parameter overrides, bypassing scope caching to ensure a fresh instance. Delegates to runtime for resolution with override parameters.

##### For Humans: What This Means
This creates a brand new instance of a service every time, even if the service is normally cached. You can also override constructor parameters for customization.

##### Parameters
- `string $id`: Service identifier to instantiate.
- `array $parameters`: Parameter overrides for constructor arguments.

##### Returns
- `object`: A new service instance.

##### Throws
- `ResolutionException`: When resolution fails.
- `ServiceNotFoundException`: When service is not registered.

##### When to Use It
- When you need multiple instances of the same service
- When overriding constructor parameters
- When implementing factories

##### Common Mistakes
- Using `make()` when `get()` would be more efficient
- Passing incorrect parameter types
- Not understanding that it bypasses caching

### Method: resolve(ServicePrototype $prototype): mixed

#### Technical Explanation
Resolves a service using a pre-analyzed prototype for performance optimization. Creates a specialized context with prototype metadata and delegates to runtime.

##### For Humans: What This Means
This uses a pre-analyzed service blueprint to speed up resolution. Instead of analyzing the service from scratch, it uses optimized metadata.

##### Parameters
- `ServicePrototype $prototype`: Pre-analyzed service blueprint.

##### Returns
- `mixed`: The resolved service instance.

##### Throws
- `ResolutionException`: When prototype resolution fails.

##### When to Use It
- When implementing performance optimizations
- When working with compiled service definitions
- In advanced container scenarios

##### Common Mistakes
- Using `resolve()` when standard methods suffice
- Assuming prototypes are always faster
- Not validating prototype freshness

### Method: call(callable|string $callable, array $parameters = []): mixed

#### Technical Explanation
Executes a callable with dependency injection applied to its parameters. Delegates to runtime's invoker for resolution and execution.

##### For Humans: What This Means
This lets you execute any function or method with automatic dependency injection. The container figures out what the function needs and provides it.

##### Parameters
- `callable|string $callable`: Function or method to execute.
- `array $parameters`: Additional parameters to pass.

##### Returns
- `mixed`: The result of callable execution.

##### Throws
- `ResolutionException`: When dependencies cannot be resolved.

##### When to Use It
- When executing procedural code with dependency injection
- When implementing event handlers or middleware
- When working with legacy callables

##### Common Mistakes
- Using `call()` for simple functions without dependencies
- Not understanding parameter precedence
- Forgetting exception handling

### Method: injectInto(object $target): object

#### Technical Explanation
Performs dependency injection into an existing object instance. Creates a specialized resolution context and runs the injection pipeline.

##### For Humans: What This Means
This takes an object that was created outside the container and injects its dependencies. It's like retrofitting dependency injection onto existing objects.

##### Parameters
- `object $target`: Object to inject dependencies into.

##### Returns
- `object`: The same object with dependencies injected.

##### Throws
- `Throwable`: From pipeline execution.

##### When to Use It
- When working with objects from external sources
- When deserializing objects
- When integrating legacy code

##### Common Mistakes
- Assuming injection works like constructor injection
- Not designing objects for injection
- Using `injectInto()` when constructor injection is better

### Method: beginScope(): void

#### Technical Explanation
Initiates a new resolution scope for managing service lifetimes. Delegates to facade's scope manager.

##### For Humans: What This Means
This starts a new "scope" where services can have different lifetimes. Services resolved in this scope may behave differently than those outside.

##### Parameters
- None.

##### Returns
- `void`: Scope initiation doesn't return anything.

##### Throws
- None. Scope creation is safe.

##### When to Use It
- When implementing request-scoped services
- When creating isolated execution contexts
- When managing temporary service state

##### Common Mistakes
- Forgetting to call `endScope()`
- Nesting scopes without understanding implications
- Using scopes for singleton services

### Method: endScope(): void

#### Technical Explanation
Terminates the current resolution scope, cleaning up scoped services. Delegates to facade's scope manager.

##### For Humans: What This Means
This closes the current scope and cleans up any services that were specific to that scope.

##### Parameters
- None.

##### Returns
- `void`: Scope termination doesn't return anything.

##### Throws
- None. Scope termination is safe.

##### When to Use It
- Always paired with `beginScope()`
- In cleanup handlers or finally blocks
- When ending scoped execution contexts

##### Common Mistakes
- Calling `endScope()` without `beginScope()`
- Not calling `endScope()` causing memory leaks
- Assuming scope cleanup is automatic

### Method: canInject(object $target): bool

#### Technical Explanation
Analyzes an object to determine if it has injectable properties or methods. Checks prototype for injection points.

##### For Humans: What This Means
This checks if an object can receive dependency injection without actually performing the injection.

##### Parameters
- `object $target`: Object to analyze for injection capability.

##### Returns
- `bool`: True if object has injection points.

##### Throws
- None. Analysis is safe.

##### When to Use It
- When building injection tooling
- When implementing conditional injection
- When validating objects for injection

##### Common Mistakes
- Confusing `canInject()` with successful injection
- Using it for performance when injection is needed
- Not understanding that it checks structure, not resolvability

### Method: inspectInjection(object $target): InjectionReport

#### Technical Explanation
Provides detailed analysis of an object's injection points, returning a report with types and metadata.

##### For Humans: What This Means
This gives you a detailed breakdown of what dependencies an object can receive and what types are expected.

##### Parameters
- `object $target`: Object to inspect for injection details.

##### Returns
- `InjectionReport`: Detailed injection analysis.

##### Throws
- None. Inspection is safe.

##### When to Use It
- When debugging injection issues
- When building development tools
- When analyzing object dependencies

##### Common Mistakes
- Using `inspectInjection()` in production
- Assuming report is static
- Not understanding that inspection doesn't inject

### Method: exportMetrics(): string

#### Technical Explanation
Exports performance and usage metrics from telemetry as a serialized string.

##### For Humans: What This Means
This provides all the performance data the container has collected in a format you can analyze or send to monitoring systems.

##### Parameters
- None.

##### Returns
- `string`: Serialized metrics data.

##### Throws
- None.

##### When to Use It
- When implementing monitoring and observability
- When debugging performance issues
- When analyzing container usage patterns

##### Common Mistakes
- Calling `exportMetrics()` too frequently
- Assuming metrics are always available
- Not configuring telemetry collection

### Method: getDefinitions(): DefinitionStore

#### Technical Explanation
Provides access to the underlying service definition repository.

##### For Humans: What This Means
This gives you direct access to all the service definitions the kernel knows about.

##### Parameters
- None.

##### Returns
- `DefinitionStore`: The definition repository.

##### Throws
- None.

##### When to Use It
- When building introspection tools
- When implementing dynamic registration
- When debugging service configuration

##### Common Mistakes
- Modifying definitions directly
- Using `getDefinitions()` for performance when specific queries suffice
- Not understanding definition lifecycle

## Architecture Role
ContainerKernel sits at the center of the container architecture, implementing the core operational contract while delegating specialized work to focused subsystems. It defines the boundary between the public Container API and the internal implementation details. This layered architecture allows the kernel to evolve independently while maintaining API stability.

### For Humans: What This Means
In the container's org chart, ContainerKernel is the operations manager—the person who translates high-level requests into specific tasks for the team. It maintains the external interface that everyone relies on, while having the authority to reorganize the internal team as needed for better efficiency.

## Risks, Trade-offs & Recommended Practices
**Risk**: Tight coupling between kernel and sub-components can make testing difficult.

**Why it matters**: Complex interdependencies can lead to brittle tests and maintenance issues.

**Design stance**: Use dependency injection for sub-components to enable easy mocking.

**Recommended practice**: Test kernel operations through the public interface rather than mocking internals.

**Risk**: Performance overhead from orchestration layer.

**Why it matters**: Every operation goes through the kernel, potentially adding latency.

**Design stance**: Optimize hot paths and use fast-path optimizations.

**Recommended practice**: Profile kernel operations and optimize based on telemetry data.

**Risk**: Complex delegation can obscure where actual work happens.

**Why it matters**: Debugging becomes harder when logic is spread across multiple components.

**Design stance**: Keep kernel logic focused on coordination, not implementation.

**Recommended practice**: Use clear method naming and comprehensive logging in sub-components.

### For Humans: What This Means
Like any coordination role, the ContainerKernel has to balance control with flexibility. Too much control creates bottlenecks, too little creates chaos. The key is finding the sweet spot where coordination adds value without becoming a bottleneck itself.

## Related Files & Folders
**KernelRuntime**: Handles the actual resolution pipeline execution. You encounter it when resolution performance is critical. It contains the core logic for turning service requests into objects.

**KernelFacade**: Provides high-level operations and convenient access to subsystems. You use it indirectly through kernel methods. It simplifies complex operations into clean APIs.

**ResolutionPipelineBuilder**: Constructs the resolution pipeline based on configuration. You modify pipeline behavior through configuration. It determines the sequence of resolution steps.

**DefinitionStore**: Holds service definitions and metadata. You register services that the kernel resolves. It provides the knowledge base for all operations.

**Kernel/**: Contains all the specialized sub-components that the kernel orchestrates. You extend container functionality by modifying these components. They implement the detailed logic that the kernel coordinates.

### For Humans: What This Means
ContainerKernel works with a team of specialists, each handling different aspects of dependency injection. The runtime does the heavy lifting, the facade provides convenience, the pipeline builder sets up the process, the definition store remembers the recipes, and the Kernel folder contains all the expert subsystems. Understanding this team dynamic helps you know where to look when customizing or debugging container behavior.