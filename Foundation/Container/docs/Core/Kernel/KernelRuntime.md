# KernelRuntime

## Quick Summary

KernelRuntime serves as the execution engine for dependency resolution, orchestrating the resolution pipeline and
coordinating with the invocation system. It transforms service requests into fully constructed objects by executing the
resolution pipeline, handling different resolution scenarios (identifiers, prototypes, contexts), and managing the flow
between pipeline execution and result retrieval. This component acts as the bridge between high-level service requests
and the detailed mechanics of object construction and injection.

### For Humans: What This Means (Summary)

Think of KernelRuntime as the skilled operator who runs the complex machinery of dependency injection. When you ask for
a service, this operator sets up the assembly line (resolution pipeline), starts the process, monitors progress, and
hands you the finished product. It's the hands-on worker who makes sure all the sophisticated automation actually
produces results, handling different types of requests with the appropriate procedures.

## Terminology (MANDATORY, EXPANSIVE)**Resolution Pipeline

**: A structured sequence of processing steps that systematically builds objects with their dependencies. In this file,
the pipeline is executed via
`$this->pipeline->run()`. It matters because it defines the systematic approach to dependency injection, ensuring
consistent and complete object construction.

**Kernel Context**: A rich context object containing service metadata, resolution state, and execution parameters. In
this file, KernelContext instances carry resolution information through the pipeline. It matters because it provides the
communication channel between different resolution stages.

**Service Prototype**: A pre-analyzed blueprint of a service's structure and dependencies. In this file, prototypes
bypass standard analysis for optimized resolution. It matters because it enables performance optimizations for
frequently used services.

**Manual Injection**: The process of injecting dependencies into an already-created object instance. In this file, the
`injectInto()` method handles this scenario. It matters because it supports legacy objects and deserialization use
cases.

**Override Parameters**: Runtime parameter values that replace constructor or method arguments during resolution. In
this file, the `make()` method supports parameter overrides. It matters because it enables dynamic service configuration
without changing definitions.

**Pipeline Execution Path**: A string representation of the resolution steps taken during pipeline execution. In this
file, used in error messages via `$context->getPath()`. It matters because it enables debugging of complex resolution
failures.

### For Humans: What This Means

These are the operational vocabulary of service resolution. The pipeline is like the assembly line steps. Context is the
work order that travels with the product. Prototypes are like pre-cut patterns for faster sewing. Manual injection is
like adding accessories to a finished product. Override parameters are like custom options chosen at the last minute.
Execution paths are like the GPS tracking the journey.

## Think of It

Imagine a sophisticated manufacturing cell where different stations handle different aspects of production—cutting,
welding, painting, assembly. KernelRuntime is the cell controller that receives orders, sets up the workpieces with the
right specifications, starts the automated process, monitors for issues, and delivers the completed products. It
understands different order types (standard products, custom builds, repairs) and routes them through the appropriate
processes.

### For Humans: What This Means (Think)

This analogy shows why KernelRuntime exists: to execute the complex choreography of dependency injection. Without it,
each resolution request would require manual coordination of multiple systems. The runtime handles the execution details
so you can focus on using the results.

## Story Example

Before KernelRuntime existed, resolution logic was scattered across multiple methods with complex coordination.
Developers had to manually manage pipeline execution, context creation, and error handling. With KernelRuntime, all
resolution flows through a unified interface. A service request automatically gets wrapped in appropriate context, sent
through the pipeline, and the result extracted—eliminating boilerplate and ensuring consistent execution.

### For Humans: What This Means (Story)

This story illustrates the coordination problem KernelRuntime solves: scattered execution logic. Without it, resolving a
service was like conducting an orchestra where each musician had to remember their part independently. KernelRuntime
provides the conductor who ensures everyone plays together harmoniously.

## For Dummies

Let's break this down like operating a coffee vending machine:

1. **The Problem**: Complex machinery needs skilled operators to produce results.

2. **KernelRuntime's Job**: The operator who takes your order and runs the machine to deliver your coffee.

3. **How You Use It**: Ask for a service, and it handles all the internal machinery automatically.

4. **What Happens Inside**: Sets up the work order, runs the assembly process, checks for errors, delivers the result.

5. **Why It's Helpful**: Turns complex manufacturing into simple requests.

Common misconceptions:

- "It's just a wrapper" - It orchestrates complex pipeline execution and error handling.
- "I can bypass it" - While possible, it breaks the guarantees of proper resolution.
- "It's slow" - The overhead is minimal compared to the resolution work it coordinates.

### For Humans: What This Means (Dummies)

KernelRuntime isn't magic—it's skilled operation. It takes the complexity of dependency resolution machinery and turns
it into reliable, predictable results. You don't need to understand the internals; you just need to know it delivers
what you ask for.

## How It Works (Technical)

KernelRuntime holds references to ResolutionPipeline and InvokeAction. Service resolution creates a KernelContext,
executes the pipeline, and extracts the result. Different resolution methods (get, make, resolve) create appropriate
contexts with specific metadata. The injectInto method uses a special internal service ID to trigger injection-only
pipeline execution.

### For Humans: What This Means (How)

Under the hood, it's a coordinator with two main tools: the pipeline for building objects and the invoker for running
functions. When you ask for something, it creates a work ticket (context), sends it through the assembly line (
pipeline), and hands you the finished product. Different types of requests get different types of tickets to ensure
proper processing.

## Architecture Role

KernelRuntime sits at the execution layer of the kernel architecture, translating high-level resolution requests into
concrete pipeline operations. It maintains the execution contract while remaining independent of specific pipeline
implementations, allowing different resolution strategies to be plugged in.

### For Humans: What This Means (Role)

In the kernel's hierarchy, KernelRuntime is the foreman—the person who takes orders from management (higher layers) and
makes sure the workers (pipeline steps) execute them correctly. It knows how to run the process without needing to know
the details of each individual task.

## Risks, Trade-offs & Recommended Practices

**Risk**: Pipeline execution can throw various exceptions that need proper handling.

**Why it matters**: Unhandled exceptions can leave resolution in an inconsistent state.

**Design stance**: Always wrap pipeline execution in appropriate error handling.

**Recommended practice**: Use try/catch blocks around resolution calls and provide meaningful error messages.

**Risk**: Context metadata can become complex and hard to debug.

**Why it matters**: Deep context hierarchies can obscure resolution flow.

**Design stance**: Keep context metadata focused and well-documented.

**Recommended practice**: Use clear, consistent metadata keys and document their purpose.

**Risk**: Synchronous execution blocks calling thread.

**Why it matters**: Long-running resolutions can impact application responsiveness.

**Design stance**: Keep resolutions fast and consider async patterns for complex services.

**Recommended practice**: Profile resolution times and optimize slow services.

### For Humans: What This Means (Risks)

Like any execution engine, KernelRuntime has operational boundaries. It's excellent for standard dependency resolution
but requires careful handling of edge cases. Use it for its strengths—coordinated execution—and mitigate its risks
through proper error handling and monitoring.

## Related Files & Folders

**ResolutionPipeline**: Executes the actual resolution steps that KernelRuntime orchestrates. You encounter it when
resolution performance is critical. It contains the detailed logic for building objects.

**InvokeAction**: Handles callable execution that KernelRuntime delegates to. You use it indirectly through call()
method. It manages function and method invocation with dependency injection.

**KernelContext**: Carries resolution state that KernelRuntime creates and processes. You examine contexts when
debugging resolution issues. It holds all the information about a resolution request.

**ContainerKernel**: Uses KernelRuntime as its execution engine. You access runtime indirectly through kernel methods.
It provides the high-level interface that delegates to runtime.

### For Humans: What This Means (Related)

KernelRuntime works with a specialized team. The pipeline does the heavy construction work, the invoker handles function
calls, contexts carry the work information, and the main kernel provides the public interface. When you need to
understand how resolution actually happens, these related components tell the full story.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: get(string $id): mixed

#### Technical Explanation (get)

This method initiates standard service resolution by creating a basic KernelContext and delegating to the resolution
pipeline. It handles the most common use case of resolving services by identifier, ensuring all dependencies are
properly injected and the service is ready for use.

##### For Humans: What This Means (get)

When you call `get('database')`, you're asking the runtime to create and return a fully working database connection
object. The runtime sets up the work order and runs it through the assembly line to deliver exactly what you need.

##### Parameters (get)

- `string $id`: The unique identifier for the service you want to resolve.

##### Returns (get)

- `mixed`: The fully resolved and constructed service instance.

##### Throws (get)

- `ResolutionException`: When the service cannot be found or resolved due to configuration issues.

##### When to Use It (get)

- When you need the standard resolution behavior for a service
- In application code where you want dependency injection to happen automatically
- When implementing the most common container usage patterns

##### Common Mistakes (get)

- Using `get()` for services that need runtime parameter overrides (use `make()` instead)
- Calling `get()` in performance-critical code when prototypes could be faster
- Assuming the returned object is always the same instance (depends on service configuration)

### Method: resolveContext(KernelContext $context): mixed

#### Technical Explanation (resolveContext)

This method executes resolution using a pre-configured KernelContext object, allowing advanced control over the
resolution process. It runs the full pipeline execution and validates that resolution completed successfully, throwing
detailed exceptions when it fails.

##### For Humans: What This Means (resolveContext)

This is the advanced method where you provide detailed instructions about how to resolve a service. You can include
metadata, overrides, and special flags that influence how the resolution happens. It's like giving the assembly line a
detailed blueprint with special instructions.

##### Parameters (resolveContext)

- `KernelContext $context`: A fully configured context object containing service ID, metadata, overrides, and resolution
  parameters.

##### Returns (resolveContext)

- `mixed`: The resolved service instance based on the context configuration.

##### Throws (resolveContext)

- `ResolutionException`: When the pipeline fails to resolve the service, including detailed path information for
  debugging.
- `\Throwable`: Any exception thrown during pipeline execution.

##### When to Use It (resolveContext)

- When you need advanced resolution control with custom metadata
- In framework code that needs to pass additional context
- When implementing custom resolution strategies

##### Common Mistakes (resolveContext)

- Using `resolveContext()` when simpler methods like `get()` would suffice
- Not properly initializing the KernelContext with required parameters
- Ignoring the detailed error information in exceptions

### Method: make(string $id, array $parameters = []): object

#### Technical Explanation (make)

This method creates a new service instance with runtime parameter overrides, bypassing any shared instance caching. It
constructs a KernelContext with override parameters and executes resolution to ensure a fresh instance with custom
configuration.

##### For Humans: What This Means (make)

While `get()` might give you a shared instance, `make()` always creates a brand new object. It's like ordering a
custom-made item instead of getting one from the shelf. You can provide specific parameters that override the normal
configuration.

##### Parameters (make)

- `string $id`: The service identifier to resolve.
- `array $parameters`: Optional runtime parameters that override constructor or method arguments.

##### Returns (make)

- `object`: A new service instance with the specified overrides applied.

##### Throws (make)

- `ResolutionException`: When the service cannot be resolved with the given overrides.

##### When to Use It (make)

- When you need multiple instances of the same service type
- When you want to customize service configuration at runtime
- When implementing factories that create service variants

##### Common Mistakes (make)

- Using `make()` when `get()` would be more appropriate for singleton services
- Passing incorrect parameter types that don't match the service's expectations
- Forgetting that `make()` bypasses caching and may be slower

### Method: resolve(ServicePrototype $prototype): mixed

#### Technical Explanation (resolve)

This method performs optimized resolution using a pre-analyzed ServicePrototype, bypassing standard reflection and
analysis for improved performance. It creates a specialized context with prototype metadata and executes streamlined
resolution.

##### For Humans: What This Means (resolve)

For services that are used frequently, the container can pre-analyze them and create a "prototype" — a fast-track
blueprint. This method uses that blueprint to resolve services much quicker than analyzing them from scratch each time.

##### Parameters (resolve)

- `ServicePrototype $prototype`: A pre-analyzed blueprint containing optimized resolution information.

##### Returns (resolve)

- `mixed`: The resolved service instance created from the optimized prototype.

##### Throws (resolve)

- `ResolutionException`: When the prototype cannot be resolved.

##### When to Use It (resolve)

- When implementing performance optimizations for hot paths
- In caching layers that pre-analyze service structures
- When you have frequently resolved services that benefit from optimization

##### Common Mistakes (resolve)

- Using `resolve()` when standard resolution would be sufficient
- Assuming prototypes are always faster (overhead can outweigh benefits for simple services)
- Not validating that prototypes are up-to-date with current code

### Method: call(callable|string $callable, array $parameters = []): mixed

#### Technical Explanation (call)

This method executes a callable (function, method, or closure) with automatic dependency injection applied to its
parameters. It delegates to the InvokeAction system to analyze the callable signature and resolve dependencies before
execution.

##### For Humans: What This Means (call)

Normally, dependency injection works for classes, but what about regular functions? This method lets you run any
function and have the container automatically provide its dependencies. It's like having a smart assistant who reads the
recipe and gathers all ingredients before you start cooking.

##### Parameters (call)

- `callable|string $callable`: The function, method, or closure to execute.
- `array $parameters`: Optional override parameters that take precedence over dependency injection.

##### Returns (call)

- `mixed`: The result of executing the callable.

##### Throws (call)

- `\ReflectionException`: When the callable cannot be analyzed for dependency injection.

##### When to Use It (call)

- When executing procedural code that needs dependency injection
- In event handlers or middleware that require services
- When integrating legacy functions into the container ecosystem

##### Common Mistakes (call)

- Using `call()` for simple functions that don't need injection
- Forgetting that override parameters take precedence over injection
- Not handling reflection exceptions for invalid callables

### Method: injectInto(object $target): object

#### Technical Explanation (injectInto)

This method performs dependency injection into an existing object instance that was created outside the container. It
creates a specialized context for injection-only execution and runs the pipeline to inject dependencies without full
service construction.

##### For Humans: What This Means (injectInto)

Sometimes you have an object that was created elsewhere—a deserialized object, something from a factory, or legacy code.
This method lets the container "retrofit" dependency injection onto that existing object. It's like taking a car that
was built without modern electronics and adding GPS and automatic features afterward.

##### Parameters (injectInto)

- `object $target`: The existing object instance to inject dependencies into.

##### Returns (injectInto)

- `object`: The same object instance with dependencies now injected.

##### Throws (injectInto)

- `\Throwable`: Any exception from the pipeline during injection execution.

##### When to Use It (injectInto)

- When working with objects created by external libraries or frameworks
- During object deserialization from cache or storage
- When integrating legacy code that creates objects manually

##### Common Mistakes (injectInto)

- Trying to inject into objects that weren't designed for dependency injection
- Assuming injection will work the same as constructor injection
- Using `injectInto()` when constructor injection would be more appropriate

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
