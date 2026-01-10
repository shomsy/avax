# LifecycleStrategyRegistry

## Quick Summary
LifecycleStrategyRegistry provides a centralized registry for managing different service lifecycle strategies, enabling pluggable and extensible lifecycle management. It acts as the catalog of available strategies that the LifecycleResolver uses to determine how services should be created, shared, and disposed of. This registry pattern allows the container to support various lifecycle behaviors while maintaining clean separation between strategy definitions and their usage.

### For Humans: What This Means (Summary)
Imagine you're a chef with a recipe book containing different cooking methods—some dishes are pre-made and served from the warmer, some are cooked to order, some are prepared fresh for each customer. LifecycleStrategyRegistry is your recipe catalog where you store all the different preparation methods. When someone orders a dish, you look up the right cooking method in your catalog and apply it. It keeps all your cooking techniques organized and easily accessible.

## Terminology (MANDATORY, EXPANSIVE)**Strategy Pattern**: A design pattern that defines a family of algorithms, encapsulates each one, and makes them interchangeable. In this file, lifecycle strategies are the encapsulated algorithms. It matters because it enables runtime selection of different behaviors.

**Registry Pattern**: A pattern for storing and retrieving objects by name or key. In this file, strategies are stored and accessed by string identifiers. It matters because it provides centralized management of related objects.

**Lifecycle Strategy**: An object that implements a specific service lifecycle management approach. In this file, registered and retrieved by name. It matters because it defines how services are instantiated and managed.

**Pluggable Architecture**: A system design that allows components to be added or replaced without modifying core code. In this file, new strategies can be registered dynamically. It matters because it enables extensibility and customization.

**Strategy Name**: A string identifier used to register and retrieve lifecycle strategies. In this file, used as array keys for storage. It matters because it provides human-readable strategy identification.

### For Humans: What This Means
These are the registry management vocabulary. Strategy pattern is choosing different approaches. Registry pattern is organized storage. Lifecycle strategies are the specific methods. Pluggable architecture means easy swapping. Strategy names are the labels on your storage bins.

## Think of It
Picture a toolbox where each drawer contains tools for a different type of work—screwdrivers for fastening, hammers for pounding, pliers for gripping. LifecycleStrategyRegistry is the toolbox organizer that labels each drawer and makes sure you can quickly find the right tool for the job. When you need to work on a project, you consult the toolbox to get the appropriate tools for that specific task.

### For Humans: What This Means (Think)
This analogy shows why LifecycleStrategyRegistry exists: organized access to specialized tools. Without it, you'd have tools scattered everywhere, making it hard to find what you need when you need it. The registry creates a systematic way to store and retrieve the right lifecycle management approach for each situation.

## Story Example
Before LifecycleStrategyRegistry existed, lifecycle strategies were hardcoded with conditional logic throughout the codebase. Adding a new lifecycle required modifying multiple files. With the registry, strategies became pluggable components that could be registered at runtime. A custom caching strategy could now be added without touching core container code, making the system highly extensible.

### For Humans: What This Means (Story)
This story illustrates the extensibility problem LifecycleStrategyRegistry solves: hardcoded limitations. Without it, adding new lifecycle behaviors was like trying to add new tools to a fixed toolbox—you'd have to rebuild the whole thing. The registry creates a modular system where new strategies can be added like sliding new drawers into the toolbox.

## For Dummies
Let's break this down like organizing a spice rack:

1. **The Problem**: Spices are scattered, hard to find, can't add new ones easily.

2. **LifecycleStrategyRegistry's Job**: A labeled spice rack where everything has its place and you can add new spices.

3. **How You Use It**: Register strategies when setting up, retrieve them by name when needed.

4. **What Happens Inside**: Stores strategy objects by name for quick lookup.

5. **Why It's Helpful**: Makes lifecycle management modular and extensible.

Common misconceptions:
- "It's just an array" - It provides type-safe registration with validation.
- "Strategies are built-in" - New strategies can be registered dynamically.
- "It's for internal use only" - It enables user-defined custom lifecycles.

### For Humans: What This Means (Dummies)
LifecycleStrategyRegistry isn't fancy—it's practical organization. It takes the problem of managing different lifecycle approaches and solves it with simple, reliable storage and retrieval. You get a clean way to organize and access lifecycle strategies.

## How It Works (Technical)
LifecycleStrategyRegistry maintains an array of LifecycleStrategy objects indexed by string names. The register() method adds strategies, has() checks existence, get() retrieves with validation, and all() returns the complete collection. Constructor accepts default strategies for initialization.

### For Humans: What This Means (How)
Under the hood, it's an organized shelf system. You put strategies on labeled shelves (register), check if something's there (has), pull it off the shelf when needed (get), or see everything available (all). It's like a well-organized pantry where everything has its place and is easy to find.

## Architecture Role
LifecycleStrategyRegistry sits at the extensibility boundary of the lifecycle system, providing the mechanism for strategy registration while maintaining type safety and validation. It enables the Strategy pattern implementation for lifecycle management without coupling to specific strategies.

### For Humans: What This Means (Role)
In the container's architecture, LifecycleStrategyRegistry is the extension port—the place where new capabilities can be plugged in. It defines how new lifecycle behaviors can be added without changing the core system.

## Risks, Trade-offs & Recommended Practices
**Risk**: Registry can become a dumping ground for poorly designed strategies.

**Why it matters**: Bad strategies can cause performance issues or bugs.

**Design stance**: Validate strategies during registration and provide clear documentation.

**Recommended practice**: Review and test custom strategies before registration.

**Risk**: Strategy name collisions can cause unexpected behavior.

**Why it matters**: Two strategies with the same name will overwrite each other.

**Design stance**: Use namespaced naming conventions for strategy identifiers.

**Recommended practice**: Use prefixes like 'custom.' or 'vendor.' for custom strategies.

**Risk**: Large number of strategies can impact lookup performance.

**Why it matters**: Array searches scale linearly with registry size.

**Design stance**: Keep strategy count reasonable and consider caching.

**Recommended practice**: Profile registry operations and optimize for common strategies.

### For Humans: What This Means (Risks)
This registry is a labeled shelf of “lifetime behaviors”. If you put random stuff on the shelf (bad strategies) or reuse labels (name collisions), the container will behave in surprising ways. Keep the shelf curated, keep names unique, and test any custom strategy like you’d test core infrastructure.

## Related Files & Folders
**LifecycleResolver**: Uses the registry to select appropriate strategies for service definitions. You register strategies that the resolver selects. It provides the decision logic for strategy selection.

**LifecycleStrategy**: Defines the interface that all registered strategies must implement. You implement this interface for custom strategies. It establishes the contract for lifecycle behavior.

**ContainerKernel**: Integrates lifecycle management through the resolver and registry. You configure the registry during kernel setup. It provides the runtime context for lifecycle execution.

**Strategies/**: Contains concrete strategy implementations that can be registered. You examine these for built-in lifecycle behaviors. It provides the actual strategy implementations.

### For Humans: What This Means (Related)
LifecycleStrategyRegistry works with a complete lifecycle ecosystem. The resolver makes decisions, the strategy interface defines rules, the kernel provides context, and the strategies folder contains the implementations. Understanding this ecosystem helps you implement and register custom lifecycle behaviors.

## Methods 


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: register(string $name, LifecycleStrategy $strategy): void

#### Technical Explanation (register)
Adds a lifecycle strategy implementation to the registry under a unique name, making it available for resolution by the LifecycleResolver. This enables extensible lifecycle management beyond built-in strategies.

##### For Humans: What This Means (register)
This method lets you add a new lifecycle strategy to the container's collection. You give it a name and the strategy object, and from then on the container can use this strategy for managing services. It's like adding a new tool to your toolbox with a label.

##### Parameters (register)
- `string $name`: Unique identifier for the strategy (used for lookup)
- `LifecycleStrategy $strategy`: The strategy implementation to register

##### Returns (register)
- `void`: Registration doesn't return anything

##### Throws (register)
- None. Registration is designed to be safe and always succeeds.

##### When to Use It (register)
- When implementing custom lifecycle behaviors for services
- During container configuration to add specialized strategies
- In container extensions that provide new lifecycle options

##### Common Mistakes (register)
- Using duplicate names (will overwrite existing strategies)
- Not implementing the LifecycleStrategy interface correctly
- Registering strategies after services have been resolved

### Method: has(string $name): bool

#### Technical Explanation (has)
Performs a safe existence check for a strategy by name, returning true if the strategy is registered without throwing exceptions. This enables conditional logic based on strategy availability.

##### For Humans: What This Means (has)
This method lets you check if a particular lifecycle strategy has been registered without causing an error. It's like checking if you have a specific tool in your toolbox before trying to use it.

##### Parameters (has)
- `string $name`: Strategy name to check for existence

##### Returns (has)
- `bool`: True if strategy exists, false otherwise

##### Throws (has)
- None. Checking is always safe.

##### When to Use It (has)
- Before attempting to retrieve a strategy that might not exist
- In conditional configuration logic
- When building defensive code around strategy usage

##### Common Mistakes (has)
- Using has() when you actually need the strategy (just call get() and handle the exception)
- Not understanding that has() only checks registration, not validity
- Using has() in performance-critical code when you know the strategy exists

### Method: get(string $name): LifecycleStrategy

#### Technical Explanation (get)
Retrieves a registered lifecycle strategy by name with validation, throwing an exception if the strategy is not found. This provides type-safe access to strategy implementations.

##### For Humans: What This Means (get)
This method gives you the actual strategy object for a given name. If the strategy doesn't exist, it tells you clearly with an error. It's like asking for a specific tool from your toolbox—you get it if it's there, or an error if it's not.

##### Parameters (get)
- `string $name`: Name of the strategy to retrieve

##### Returns (get)
- `LifecycleStrategy`: The registered strategy implementation

##### Throws (get)
- `InvalidArgumentException`: When the requested strategy is not registered

##### When to Use It (get)
- When you need to use a specific lifecycle strategy
- In resolution logic that requires strategy objects
- When implementing custom lifecycle selection

##### Common Mistakes (get)
- Not handling the InvalidArgumentException
- Calling get() without first checking has() (better to handle the exception)
- Assuming get() returns a valid strategy (it does, but only if registered)

### Method: all(): array

#### Technical Explanation (all)
Returns a complete map of all registered lifecycle strategies indexed by their names, enabling inspection and iteration over available lifecycle options for debugging or configuration purposes.

##### For Humans: What This Means (all)
This method gives you a complete list of all the lifecycle strategies that have been registered. It's like getting an inventory of everything in your toolbox, organized by name.

##### Parameters (all)
- None.

##### Returns (all)
- `array<string, LifecycleStrategy>`: Map of strategy names to their implementations

##### Throws (all)
- None. Retrieving all strategies is always safe.

##### When to Use It (all)
- For debugging and inspecting registered strategies
- When building administrative interfaces
- For logging or monitoring strategy availability

##### Common Mistakes (all)
- Modifying the returned array (it should be treated as read-only)
- Assuming the array is ordered (it's indexed by registration order)
- Using all() in performance-critical code when you only need one strategy

### Method: __construct(...)

#### Technical Explanation (__construct)
This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the container’s workflow explicit and reusable.

##### For Humans: What This Means (__construct)
When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having to manually wire the details.

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
