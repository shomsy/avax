# LifecycleResolver

## Quick Summary
LifecycleResolver determines the appropriate lifecycle management strategy for service instances based on their definitions, enabling different sharing and cleanup behaviors. It acts as the decision point for how services are created, cached, and disposed of throughout their lifetime in the container. This resolver ensures that singleton services are shared, scoped services are properly isolated, and transient services are created fresh each time.

### For Humans: What This Means
Imagine you're managing a restaurant's inventory where some items are shared (like the salt shaker), some are per-table (like menus), and some are made fresh each time (like coffee). LifecycleResolver is the inventory manager who decides for each item whether it should be shared, kept separate, or made new every time. It ensures the right balance between efficiency and freshness for every service in your container.

## Terminology
**Lifecycle Strategy**: A defined approach for managing service instance creation, sharing, and cleanup. In this file, resolved from the registry based on service definition. It matters because it controls memory usage, performance, and service isolation.

**Service Lifetime**: The scope in which a service instance exists and is shared. In this file, extracted from ServiceDefinition and converted to string. It matters because it determines whether services are singleton, scoped, or transient.

**Backed Enum**: A PHP enum that has associated scalar values for storage and comparison. In this file, used for type-safe lifetime definitions. It matters because it provides type safety while maintaining string compatibility.

**Lifecycle Registry**: A collection of available lifecycle strategies indexed by identifier. In this file, used to retrieve the appropriate strategy. It matters because it enables extensible lifecycle management.

**Fallback Strategy**: A default behavior used when the requested strategy is unavailable. In this file, defaults to 'transient' lifecycle. It matters because it ensures the container remains functional even with unknown configurations.

### For Humans: What This Means
These are the lifecycle management vocabulary. Strategies are the rules for how items are handled. Service lifetimes define sharing rules. Backed enums provide type-safe options. The registry is the strategy catalog. Fallback ensures nothing breaks.

## Think of It
Picture a library where some books are reference-only (always the same copy), some are reserved per reader (personal copies), and some are disposable pamphlets (new each time). LifecycleResolver is the librarian who checks each book's designation and applies the right circulation rules. Whether a book is shared, personal, or disposable determines how it's managed throughout its time in the library system.

### For Humans: What This Means
This analogy shows why LifecycleResolver exists: intelligent resource management. Without it, all services would be treated the same way, leading to inefficient memory usage or unexpected sharing. LifecycleResolver applies the right management strategy for each service type.

## Story Example
Before LifecycleResolver existed, developers manually managed service lifecycles with conditional logic scattered throughout resolution code. A service intended as singleton might accidentally create multiple instances. With LifecycleResolver, lifecycle decisions are centralized and consistent. A service marked as singleton now reliably shares the same instance, while scoped services properly isolate per context.

### For Humans: What This Means
This story illustrates the consistency problem LifecycleResolver solves: scattered lifecycle logic. Without it, service management was like having different checkout rules in different library branches—confusing and error-prone. LifecycleResolver creates unified, predictable lifecycle management.

## For Dummies
Let's break this down like managing a shared workspace:

1. **The Problem**: Everything is treated the same way, leading to waste or conflicts.

2. **LifecycleResolver's Job**: The office manager who assigns the right usage rules for each resource.

3. **How You Use It**: The resolver automatically applies the right strategy based on service configuration.

4. **What Happens Inside**: Checks the service definition and selects the appropriate lifecycle management.

5. **Why It's Helpful**: Ensures services are managed efficiently and correctly.

Common misconceptions:
- "It's just a switch statement" - It provides extensible strategy pattern with registry.
- "Lifecycles are fixed" - Strategies can be extended and customized.
- "It doesn't affect performance" - Wrong lifecycles can cause memory leaks or performance issues.

### For Humans: What This Means
LifecycleResolver isn't complex—it's essential. It takes the fundamental decision of "how should this service live?" and makes it systematic and reliable. You get proper resource management without thinking about it.

## How It Works (Technical)
LifecycleResolver extracts the lifetime from ServiceDefinition, converts enum values to strings if needed, and retrieves the corresponding LifecycleStrategy from the registry. It provides fallback to transient strategy for unsupported lifecycles.

### For Humans: What This Means
Under the hood, it's a smart matcher. It reads the service's lifetime setting, makes sure it's in the right format, and finds the matching management strategy. If something's unclear, it defaults to the safest option. It's like a key that opens the right door automatically.

## Architecture Role
LifecycleResolver sits at the strategy selection boundary of the container architecture, translating declarative service configurations into executable lifecycle behaviors. It maintains separation between configuration and implementation while enabling consistent lifecycle management across the system.

### For Humans: What This Means
In the container's architecture, LifecycleResolver is the policy enforcer—the component that ensures service lifecycles match their intended behavior. It translates configuration into action without being part of the core resolution logic.

## Risks, Trade-offs & Recommended Practices
**Risk**: Incorrect lifecycle assignment can cause memory leaks or performance issues.

**Why it matters**: Singleton services creating multiple instances waste memory, transient services being shared can cause bugs.

**Design stance**: Make lifecycle defaults conservative and provide clear configuration options.

**Recommended practice**: Document lifecycle implications and use monitoring to detect lifecycle misuse.

**Risk**: Registry dependencies can make testing complex.

**Why it matters**: LifecycleResolver requires registry setup for testing.

**Design stance**: Design for easy registry mocking and provide test-friendly constructors.

**Recommended practice**: Use dependency injection for registry in tests and provide factory methods for common scenarios.

**Risk**: Enum conversion assumes backed enums, which may not always be available.

**Why it matters**: Code expects enum values but gets incompatible types.

**Design stance**: Handle type conversion gracefully with validation.

**Recommended practice**: Add type checking and provide clear error messages for invalid lifetime values.

### For Humans: What This Means
Like any resource management system, LifecycleResolver has allocation trade-offs. It's powerful for correct usage but requires understanding the implications. The key is using it mindfully with proper monitoring.

## Related Files & Folders
**LifecycleStrategyRegistry**: Provides the strategies that LifecycleResolver selects from. You register custom strategies here. It supplies the available lifecycle options.

**ServiceDefinition**: Contains the lifetime information that LifecycleResolver reads. You set lifetime during service registration. It provides the input for lifecycle decisions.

**LifecycleStrategy**: Defines the interface for lifecycle behaviors. You implement this for custom lifecycles. It establishes the contract for lifecycle management.

**ContainerKernel**: Uses LifecycleResolver during service resolution. You encounter it indirectly through resolution operations. It integrates lifecycle management into the resolution process.

### For Humans: What This Means
LifecycleResolver works with a complete lifecycle ecosystem. The registry holds the options, service definitions provide the requirements, strategy interfaces define the rules, and the kernel applies the decisions. Understanding this ecosystem helps you implement proper service lifecycles.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: resolve(ServiceDefinition|null $definition): LifecycleStrategy

#### Technical Explanation
Determines the appropriate lifecycle management strategy for a service based on its definition's lifetime configuration, converting enum values to strings and retrieving the corresponding strategy from the registry with fallback to transient behavior.

##### For Humans: What This Means
This method is the decision maker for how services should be managed. It looks at a service's configuration and decides whether it should be shared, scoped, or created fresh each time. It's like the referee who enforces the rules for each service type.

##### Parameters
- `ServiceDefinition|null $definition`: The service definition containing lifetime information, or null for default transient behavior

##### Returns
- `LifecycleStrategy`: The strategy object that defines how the service instances should be managed

##### Throws
- None. Resolution is designed to be safe with fallback to transient strategy.

##### When to Use It
- During service resolution to determine instance management behavior
- When implementing custom resolution logic that needs lifecycle decisions
- In container extensions that need to respect service lifetimes

##### Common Mistakes
- Assuming null definitions always result in transient behavior (they do, but this should be documented)
- Not handling the case where registry doesn't have the requested strategy (it falls back gracefully)
- Expecting enum conversion to work with non-backed enums (it checks instanceof BackedEnum)
