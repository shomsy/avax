# ContainerConfig

## Quick Summary

ContainerConfig serves as the configuration hub for the container system, providing a clean, immutable way to pass
settings and options to the ContainerKernel during initialization. It acts as a configuration transfer object,
encapsulating all setup parameters in a single, thread-safe structure that prevents configuration drift after kernel
creation. This design ensures that container behavior is determined at construction time and remains consistent
throughout the container's lifecycle.

### For Humans: What This Means (Summary)

Imagine setting up a complex machine before turning it on. ContainerConfig is like the control panel where you set all
the dials and switches before hitting "start." Once the container is running, you can't change these fundamental
settings—they're locked in for safety and predictability. It's the blueprint that tells the container how to behave from
the moment it's created.

## Terminology (MANDATORY, EXPANSIVE)

**Immutable Configuration**: A configuration object that cannot be modified after creation, ensuring that settings
remain consistent and preventing accidental changes during runtime. In this file, the `readonly` class property
guarantees immutability. It matters because it prevents configuration-related bugs and makes the system more
predictable.

**Configuration Builder Pattern**: A design pattern where configuration is built through method chaining, creating new
instances rather than modifying existing ones. In this file, `withSettings()` follows this pattern by returning a new
instance. It matters because it enables fluent configuration APIs while maintaining immutability.

**Settings Array**: A simple associative array containing configuration key-value pairs, providing basic configuration
storage without advanced features like dot notation. In this file, the `$settings` property holds this array. It matters
because it keeps the configuration simple and focused on core container needs.

**Configuration Transfer Object**: An object designed specifically to carry configuration data between components, with
no business logic of its own. In this file, ContainerConfig serves this role for kernel initialization. It matters
because it clearly separates configuration concerns from operational logic.

**Kernel Initialization**: The process of setting up the container's core components with their configuration parameters
before operational use. In this file, ContainerConfig is consumed during ContainerKernel construction. It matters
because proper initialization ensures the container behaves consistently from the first resolution.

### For Humans: What This Means (Terms)

These concepts are the vocabulary of configuration management. Immutable configuration is like writing instructions on a
checklist that can't be erased—once set, they stay set. The builder pattern is like adding ingredients to a recipe one
by one, creating a new version each time. Settings arrays are simple labeled boxes. Transfer objects are like delivery
packages that just carry information without doing work themselves. Kernel initialization is like programming your
coffee machine before brewing your first cup.

## Think of It

Picture a restaurant kitchen where the chef sets up all the stations, tools, and ingredients before service begins.
ContainerConfig is like the prep checklist and station assignments that get locked in once the kitchen opens. You can't
suddenly decide to rearrange the kitchen mid-service—that would cause chaos. Instead, you prepare everything carefully
beforehand, and then the kitchen runs smoothly according to the established plan.

### For Humans: What This Means (Think)

This analogy captures why ContainerConfig exists: to establish clear, unchangeable ground rules before the complex work
begins. Just as a kitchen needs its setup to function properly, the container needs its configuration to resolve
dependencies correctly. The immutability ensures that once "service" starts, there are no surprises or mid-operation
changes.

## Story Example

Before ContainerConfig existed, developers passed configuration parameters individually to the ContainerKernel
constructor, leading to long parameter lists and potential mistakes. If you wanted to change a setting, you had to
modify constructor calls everywhere. With ContainerConfig, you create a single configuration object with all settings,
pass it once, and get immutable behavior. When deploying to different environments, you simply create different
ContainerConfig instances with appropriate settings for each context.

### For Humans: What This Means (Story)

This story shows the practical problem ContainerConfig solves: configuration complexity and mutability. Without it,
setting up a container was like trying to configure a complex system by passing individual wires through a small
hole—one at a time, easy to mix up, hard to change. ContainerConfig bundles everything into a neat package that you pass
once, clearly labeled and impossible to accidentally modify later.

## For Dummies

Let's break this down like setting up a new computer:

1. **The Problem**: Settings scattered everywhere, changeable anytime, causing unpredictable behavior.

2. **ContainerConfig's Job**: A sealed envelope containing all the setup instructions that gets opened once at startup.

3. **How You Use It**: Create a config object with your settings, pass it to the kernel, and that's it—no more changes
   allowed.

4. **What Happens Inside**: The config becomes part of the kernel's foundation, influencing all its behavior.

5. **Why It's Helpful**: Guarantees consistent behavior and prevents runtime configuration errors.

Common misconceptions:

- "It's just a settings wrapper" - It's specifically designed for immutable kernel initialization.
- "I can modify it after creation" - The readonly nature prevents any changes.
- "It's redundant with Settings class" - ContainerConfig is for kernel setup, Settings is for runtime configuration.

### For Humans: What This Means (Dummies)

ContainerConfig isn't complicated—it's just smart design. It takes the chaos of configuration and turns it into a
predictable, one-time setup process. You don't need to understand the internals; you just need to know that it makes
your container reliable and consistent.

## How It Works (Technical)

ContainerConfig is a readonly class that stores configuration in a public `$settings` array. The constructor accepts
initial settings, while `withSettings()` creates new instances with merged configuration using array_merge. The `get()`
and `has()` methods provide access to individual settings with optional defaults. This design ensures configuration
cannot be modified after creation and provides a clean API for kernel initialization.

### For Humans: What This Means (How)

Under the hood, it's elegantly simple: a container for settings that can't be changed once created. The `withSettings`
method creates new copies rather than modifying the existing one, like making a photocopy with additions instead of
writing on the original. This approach guarantees that once the container starts, its fundamental behavior is set in
stone.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __construct(array $settings = []): void

#### Technical Explanation (Construct)

Initializes a new ContainerConfig instance with the provided settings array, storing it as a readonly property to ensure
immutability.

##### For Humans: What This Means (Construct)

This creates the configuration package that gets passed to the container kernel. Once created, this configuration cannot
be changed, ensuring the kernel starts with a consistent setup.

##### Parameters (__construct)

- `array $settings`: Initial configuration values as a flat associative array.

##### Returns (__construct)

- `void`: Constructor doesn't return anything; it just sets up the instance.

##### Throws (__construct)

- None. Constructor accepts any array and doesn't validate content.

##### When to Use It (__construct)

- When creating configuration for container initialization
- When you have known settings to pass to the kernel
- In bootstrap or setup code

##### Common Mistakes (__construct)

- Trying to modify the settings after construction (readonly property)
- Assuming the constructor validates settings (it just stores them)
- Using complex nested arrays when the config expects flat structure

### Method: withSettings(array $settings): self

#### Technical Explanation (WithSettings)

Creates a new ContainerConfig instance with additional settings merged into the existing configuration using
array_merge. The original instance remains unchanged, following immutable design principles.

##### For Humans: What This Means (WithSettings)

This lets you add more configuration settings to an existing config, creating a new config object rather than modifying
the old one. It's like making a revised copy of your shopping list with additional items.

##### Parameters (withSettings)

- `array $settings`: Additional settings to merge into the configuration.

##### Returns (withSettings)

- `self`: A new ContainerConfig instance with merged settings.

##### Throws (withSettings)

- None. Merging is designed to be safe and non-destructive.

##### When to Use It (withSettings)

- When building configuration incrementally
- When combining base configuration with environment-specific settings
- When creating configuration variants

##### Common Mistakes (withSettings)

- Expecting the original instance to be modified (it creates a new instance)
- Assuming merge overwrites nested arrays (uses array_merge which may not deeply merge)
- Not capturing the returned new instance

### Method: get(string $key, mixed $default = null): mixed

#### Technical Explanation (Get)

Retrieves a configuration value by key from the internal settings array, returning the default value if the key doesn't
exist.

##### For Humans: What This Means (Get)

This is how you read individual configuration values from the config. Give it a key like "debug" and it returns the
value, or your fallback if it wasn't set.

##### Parameters (get)

- `string $key`: The configuration key to look up.
- `mixed $default`: Value to return if the key doesn't exist.

##### Returns (get)

- `mixed`: The configuration value or the default if not found.

##### Throws (get)

- None. Missing keys are handled gracefully with defaults.

##### When to Use It (get)

- When the kernel needs to read specific configuration values
- When implementing conditional behavior based on settings
- When providing fallback values for optional configuration

##### Common Mistakes (get)

- Not providing sensible defaults for required settings
- Assuming all keys exist without defaults
- Using this for bulk access when you need multiple values

### Method: has(string $key): bool

#### Technical Explanation (Has)

Checks whether a configuration key exists in the internal settings array using array_key_exists.

##### For Humans: What This Means (Has)

This lets you check if a particular configuration setting has been provided before trying to use it. Like checking if an
ingredient is in your pantry before starting a recipe.

##### Parameters (has)

- `string $key`: The configuration key to check for existence.

##### Returns (has)

- `bool`: True if the key exists, false otherwise.

##### Throws (has)

- None. Checking existence is always safe.

##### When to Use It (has)

- When you need to conditionally apply configuration
- When validating that required settings are present
- When implementing optional configuration features

##### Common Mistakes (has)

- Using `has()` when you actually need the value (just use `get()` with default)
- Assuming `has()` checks for truthy values (it only checks existence)
- Not understanding that it uses exact key matching

## Architecture Role

ContainerConfig sits at the boundary between container setup and runtime operation, serving as the configuration
injection point for the ContainerKernel. It defines the contract for what settings the kernel accepts while remaining
independent of the kernel's internal implementation. This separation allows the configuration API to evolve
independently of the core resolution logic.

### For Humans: What This Means (Role)

In the container's architecture, ContainerConfig is the handshake between setup and operation. It's the agreed-upon
format for passing instructions to the kernel, like a standardized briefing document that the operations team (kernel)
expects. This clear interface means you can change how you prepare the briefing without affecting how the team uses it.

## Risks, Trade-offs & Recommended Practices

**Risk**: Over-reliance on configuration can make the container behavior unpredictable or hard to debug.

**Why it matters**: Complex configuration hierarchies can mask issues and make testing difficult.

**Design stance**: Keep configuration focused on essential kernel behaviors.

**Recommended practice**: Prefer convention over configuration where possible, and document all configuration options
clearly.

**Risk**: Immutable configuration prevents dynamic reconfiguration in long-running applications.

**Why it matters**: Some applications need to adjust behavior based on runtime conditions.

**Design stance**: Use ContainerConfig for static setup, and Settings class for dynamic configuration.

**Recommended practice**: Distinguish between initialization config (ContainerConfig) and runtime config (Settings).

**Risk**: Large configuration arrays can slow initialization.

**Why it matters**: Complex setup can delay application startup.

**Design stance**: Load configuration lazily where appropriate.

**Recommended practice**: Use configuration builders or factories to prepare ContainerConfig instances efficiently.

### For Humans: What This Means (Risks)

Like any configuration system, ContainerConfig has sweet spots and boundaries. It's perfect for establishing the
container's personality at startup, but not for changing its mind mid-operation. Use it where it excels—initial
setup—and combine it with other tools for dynamic needs. It's a specialized tool, not a general-purpose configuration
hammer.

## Related Files & Folders

**ContainerKernel**: Receives ContainerConfig during construction and uses it to configure kernel behavior. You
encounter ContainerConfig when setting up kernel instances. It provides the configuration that influences kernel
operation.

**Settings**: Complements ContainerConfig by handling dynamic, runtime configuration needs. You use Settings for
configuration that can change during operation. It differs from ContainerConfig by being mutable and supporting dot
notation.

**Core/**: Other core components may reference configuration through ContainerConfig during initialization. You modify
ContainerConfig when customizing core container behavior. It serves as the configuration source for the entire core
system.

### For Humans: What This Means (Related)

ContainerConfig works alongside related components like teammates on a project. ContainerKernel is the main consumer,
Settings handles the flexible parts, and other core components use it as their reference. When you need to configure the
container's core behavior, ContainerConfig is usually your starting point, with the others filling in the details.
