# KernelConfig

## Quick Summary

KernelConfig serves as the comprehensive configuration hub for the ContainerKernel, encapsulating all collaborators,
settings, and behavioral options in an immutable structure. It provides a fluent, builder-pattern API for configuring
kernel behavior while ensuring configuration consistency and preventing runtime modifications. This design enables
flexible kernel setup while maintaining type safety and predictability.

### For Humans: What This Means (Summary)

Think of KernelConfig as the detailed blueprint and team roster for the kernel's operation. Before the kernel starts
working, you use this config to specify exactly which tools it should use, what rules it should follow, and how it
should behave. Once set, this blueprint can't be changed, ensuring the kernel operates consistently according to the
established plan.

## Terminology (MANDATORY, EXPANSIVE)**Collaborator Injection

**: The practice of providing all dependent objects to a component through its constructor, enabling loose coupling and
testability. In this file, all kernel collaborators are injected via the constructor. It matters because it makes the
kernel's dependencies explicit and testable.

**Fluent Configuration API**: A method-chaining interface that allows readable, step-by-step configuration building. In
this file, methods like `withStrictMode()` and `withMetrics()` return new instances for chaining. It matters because it
enables expressive configuration without mutable state.

**Immutable Configuration**: Configuration that cannot be modified after creation, preventing accidental changes during
operation. In this file, the `readonly` class ensures immutability. It matters because it guarantees configuration
stability throughout the kernel's lifecycle.

**Development Mode**: A configuration flag that enables debugging and development-friendly features. In this file,
`$devMode` controls development-specific behavior. It matters because it allows different behaviors in development vs
production environments.

**Strict Mode**: A validation flag that enables additional safety checks and error detection. In this file,
`$strictMode` controls validation strictness. It matters because it provides configurable error handling for different
operational requirements.

**Auto-Definition**: Automatic service registration based on class analysis without explicit registration. In this file,
`$autoDefine` controls this feature. It matters because it reduces boilerplate for simple service registration.

### For Humans: What This Means

These are the configuration vocabulary that makes KernelConfig work. Collaborator injection is like providing all the
tools a worker needs upfront. Fluent APIs are like building a sentence word by word. Immutable configs are like plans
that can't be altered once approved. Dev mode is like having training wheels. Strict mode is like having extra safety
checks. Auto-definition is like having smart assistants who figure things out automatically.

## Think of It

Picture configuring a complex manufacturing robot before it starts production. KernelConfig is the setup interface where
you specify which tools to use, what safety protocols to follow, whether to run in training mode, and which quality
checks to perform. Once configured and activated, the robot operates according to these immutable settings, ensuring
consistent, predictable behavior.

### For Humans: What This Means (Think)

This analogy captures why KernelConfig exists: to establish unchangeable operational parameters. Just as you wouldn't
want a robot's safety settings changing mid-production, you don't want kernel configuration changing during operation.
The config locks in the behavior upfront for reliability.

## Story Example

Before KernelConfig existed, kernel collaborators were passed individually to the constructor, leading to long parameter
lists and configuration errors. With KernelConfig, all settings are encapsulated in a single object with fluent methods.
Developers can now configure the kernel with readable code like `$config->withStrictMode()->withMetrics($collector)`,
making configuration both safer and more maintainable.

### For Humans: What This Means (Story)

This story shows the practical problem KernelConfig solves: configuration complexity. Without it, setting up a kernel
was like trying to configure a complex system by passing dozens of individual wires. KernelConfig bundles everything
into a neat, fluent package that prevents mistakes and enables clear configuration.

## For Dummies

Let's break this down like setting up a new gaming PC:

1. **The Problem**: Individual components need specific settings that can't change once the PC is running.

2. **KernelConfig's Job**: A setup wizard that collects all the configuration choices into an unchangeable plan.

3. **How You Use It**: Chain method calls to specify what you want, then pass the final config to the kernel.

4. **What Happens Inside**: The config becomes the kernel's operating manual, influencing every decision it makes.

5. **Why It's Helpful**: Guarantees the kernel behaves exactly as configured, with no surprises during operation.

Common misconceptions:

- "It's just a settings bag" - It's specifically designed for immutable kernel configuration with fluent APIs.
- "I can modify it later" - The readonly nature prevents any runtime changes.
- "It's redundant with ContainerConfig" - KernelConfig is for internal kernel setup, ContainerConfig is for external
  kernel initialization.

### For Humans: What This Means (Dummies)

KernelConfig isn't complex—it's careful design. It takes the chaos of kernel setup and turns it into a predictable,
one-time process. You don't need to understand every detail; you just need to know it makes kernel behavior reliable and
configurable.

## How It Works (Technical)

KernelConfig is a readonly class with public properties for all collaborators and boolean flags for behavioral settings.
The constructor accepts all collaborators, while fluent methods like `withStrictMode()` create new instances with
modified settings using the private `cloneWith()` helper. This ensures immutability while providing a convenient
configuration API.

### For Humans: What This Means (How)

Under the hood, it's elegantly simple: a container that holds all the kernel's collaborators and settings, but can't be
changed once created. The fluent methods create new copies rather than modifying the existing one, like creating revised
blueprints instead of marking up the original.

## Architecture Role

KernelConfig sits at the boundary between kernel initialization and operation, defining the complete set of
collaborators and behavioral settings the kernel requires. It establishes the kernel's operational context while
remaining independent of the kernel's internal implementation details.

### For Humans: What This Means (Role)

In the kernel's architecture, KernelConfig is the mission briefing—the complete set of instructions, tools, and rules
that define how the kernel will operate. It sets the stage for everything the kernel does, without being part of the
actual operational logic.

## Risks, Trade-offs & Recommended Practices

**Risk**: Complex configuration can make kernel setup error-prone.

**Why it matters**: Many collaborators and settings increase the chance of misconfiguration.

**Design stance**: Use fluent API and static factory methods to guide proper configuration.

**Recommended practice**: Prefer `KernelConfig::create()` with required parameters, then chain optional settings.

**Risk**: Immutability prevents dynamic reconfiguration.

**Why it matters**: Some advanced scenarios might need runtime configuration changes.

**Design stance**: Use configuration for static setup, implement dynamic behavior within the kernel.

**Recommended practice**: Keep KernelConfig focused on initialization settings, handle dynamic behavior through kernel
methods.

**Risk**: Large number of collaborators can make testing difficult.

**Why it matters**: Mocking many dependencies increases test complexity.

**Design stance**: Group related collaborators and provide test-friendly factory methods.

**Recommended practice**: Use dependency injection containers in tests to manage complex collaborator setups.

### For Humans: What This Means (Risks)

Like any comprehensive configuration system, KernelConfig has power and complexity. It's excellent for establishing
kernel behavior but requires careful setup. Use it for what it's designed for—initial configuration—and let the kernel
handle dynamic operations.

## Related Files & Folders

**ContainerKernel**: Consumes KernelConfig during construction and uses it to configure all subsystems. You create
KernelConfig instances to customize kernel behavior. It depends on KernelConfig for its operational setup.

**ResolutionPipelineFactory**: Uses configuration settings to construct appropriate resolution pipelines. You modify
pipeline behavior through KernelConfig settings. It reads from KernelConfig to determine pipeline structure.

**Kernel/**: Other kernel components access configuration settings through KernelConfig. You modify component behavior
by changing KernelConfig properties. It serves as the configuration source for the entire kernel subsystem.

### For Humans: What This Means (Related)

KernelConfig connects to the kernel ecosystem like the central control panel. The main kernel uses it for setup,
pipeline builders read it for construction guidance, and other components reference it for behavioral settings. When you
need to customize how the kernel operates, KernelConfig is your primary interface.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: create(EngineInterface $engine, InjectDependencies $injector, InvokeAction $invoker, ScopeManager $scopes, ServicePrototypeFactory $prototypeFactory, ResolutionTimeline $timeline): self

#### Technical Explanation (create)

Factory method that creates a KernelConfig instance with only the required collaborators, allowing optional settings to
be configured through fluent methods afterward. This provides a clean entry point for configuration building while
ensuring all required dependencies are present.

##### For Humans: What This Means (create)

This is the recommended way to start building a kernel configuration. It ensures you provide all the essential
components upfront, then you can add optional settings using the fluent methods.

##### Parameters (create)

- `EngineInterface $engine`: The resolution engine that handles service instantiation.
- `InjectDependencies $injector`: The system responsible for dependency injection.
- `InvokeAction $invoker`: The component that executes callable functions with injection.
- `ScopeManager $scopes`: Manages service lifetimes and scoping.
- `ServicePrototypeFactory $prototypeFactory`: Creates optimized service blueprints.
- `ResolutionTimeline $timeline`: Tracks resolution performance and execution flow.

##### Returns (create)

- `self`: A new KernelConfig instance ready for optional configuration.

##### Throws (create)

- None. Factory method is designed to be safe.

##### When to Use It (create)

- When creating kernel configuration in application bootstrap
- When you have all required collaborators available
- When building configuration programmatically

##### Common Mistakes (create)

- Trying to pass null for required parameters
- Not chaining optional configuration methods afterward
- Using constructor directly instead of this factory method

### Method: withStrictMode(bool $strict = true): self

#### Technical Explanation (withStrictMode)

Configures whether the kernel should perform additional validation checks during operation. When enabled, provides
stricter error detection and security validation at the cost of some performance overhead.

##### For Humans: What This Means (withStrictMode)

This enables "strict mode" which makes the container more picky about what it accepts and does extra checking. It's like
turning on all the safety features—helpful for development and production safety, but might slow things down a bit.

##### Parameters (withStrictMode)

- `bool $strict`: Whether to enable strict validation mode.

##### Returns (withStrictMode)

- `self`: A new configuration instance with strict mode setting.

##### Throws (withStrictMode)

- None. Configuration is safe.

##### When to Use It (withStrictMode)

- In production environments for enhanced security
- During development for better error detection
- When you need stricter validation of service definitions

##### Common Mistakes (withStrictMode)

- Leaving strict mode disabled in production
- Assuming strict mode only affects performance (it also affects security)
- Not testing applications with strict mode enabled

### Method: withAutoDefine(bool $autoDefine = true): self

#### Technical Explanation (withAutoDefine)

Configures whether the kernel should automatically register services based on class analysis without explicit
registration. This reduces boilerplate for simple services but may have performance implications for large codebases.

##### For Humans: What This Means (withAutoDefine)

This tells the container to automatically figure out how to create services just by looking at the classes, without you
having to explicitly register them. It's convenient but might be overkill if you have a lot of classes.

##### Parameters (withAutoDefine)

- `bool $autoDefine`: Whether to enable automatic service definition.

##### Returns (withAutoDefine)

- `self`: A new configuration instance with auto-define setting.

##### Throws (withAutoDefine)

- None. Configuration is safe.

##### When to Use It (withAutoDefine)

- In small to medium applications where explicit registration is burdensome
- When you want convention over configuration
- During rapid prototyping and development

##### Common Mistakes (withAutoDefine)

- Enabling auto-define in large applications (can slow startup)
- Assuming auto-define works for all classes (has limitations)
- Not understanding performance implications

### Method: withMetrics(CollectMetrics $metrics): self

#### Technical Explanation (withMetrics)

Sets up a metrics collection system to track kernel performance, usage patterns, and operational statistics. This
enables monitoring and observability of container operations.

##### For Humans: What This Means (withMetrics)

This plugs in a metrics collector so the container can track how it's performing and what services are being used. It's
like installing a dashboard that shows you performance statistics.

##### Parameters (withMetrics)

- `CollectMetrics $metrics`: The metrics collection implementation.

##### Returns (withMetrics)

- `self`: A new configuration instance with metrics enabled.

##### Throws (withMetrics)

- None. Configuration is safe.

##### When to Use It (withMetrics)

- When implementing application monitoring
- When debugging performance issues
- When you need operational visibility into container usage

##### Common Mistakes (withMetrics)

- Not configuring metrics in production environments
- Assuming metrics collection has no performance impact
- Not using the collected metrics data

### Method: withPolicy(ContainerPolicy $policy): self

#### Technical Explanation (withPolicy)

Establishes security and validation policies for container operations, controlling what services can be resolved and how
they behave. This enables fine-grained security controls over dependency injection.

##### For Humans: What This Means (withPolicy)

This sets up security rules for the container—like deciding which services can be accessed and under what conditions.
It's like having a bouncer that checks permissions before allowing service resolution.

##### Parameters (withPolicy)

- `ContainerPolicy $policy`: The security policy implementation.

##### Returns (withPolicy)

- `self`: A new configuration instance with security policy.

##### Throws (withPolicy)

- None. Configuration is safe.

##### When to Use It (withPolicy)

- In multi-tenant applications
- When implementing security controls over service access
- In enterprise environments with strict governance requirements

##### Common Mistakes (withPolicy)

- Assuming default policy is secure enough
- Not testing policy enforcement thoroughly
- Implementing overly restrictive policies that break functionality

### Method: withTerminator(TerminateContainer $terminator): self

#### Technical Explanation (withTerminator)

Configures a shutdown handler that manages proper cleanup when the container terminates. This ensures resources are
released and finalization logic executes correctly.

##### For Humans: What This Means (withTerminator)

This sets up what happens when the container shuts down—like cleaning up resources, closing connections, or running
final cleanup tasks. It's the container's "goodbye routine."

##### Parameters (withTerminator)

- `TerminateContainer $terminator`: The shutdown handler implementation.

##### Returns (withTerminator)

- `self`: A new configuration instance with termination handling.

##### Throws (withTerminator)

- None. Configuration is safe.

##### When to Use It (withTerminator)

- When containers manage resources that need cleanup
- In long-running applications that need graceful shutdown
- When implementing proper resource management

##### Common Mistakes (withTerminator)

- Not configuring termination in applications that need it
- Assuming resources clean up automatically
- Implementing termination logic that blocks shutdown

### Method: withDevMode(bool $devMode = true): self

#### Technical Explanation (withDevMode)

Configures development-friendly features such as detailed error messages, debugging information, and relaxed validation.
This provides better developer experience at the cost of some security and performance.

##### For Humans: What This Means (withDevMode)

This enables "developer mode" which makes the container more helpful during development—better error messages, debugging
info, and less strict checking. It's like having training wheels and detailed instructions.

##### Parameters (withDevMode)

- `bool $devMode`: Whether to enable development mode features.

##### Returns (withDevMode)

- `self`: A new configuration instance with dev mode setting.

##### Throws (withDevMode)

- None. Configuration is safe.

##### When to Use It (withDevMode)

- During development and testing
- When you need detailed debugging information
- In staging environments for better error visibility

##### Common Mistakes (withDevMode)

- Leaving dev mode enabled in production
- Assuming dev mode only affects logging (it affects security too)
- Not testing production behavior with dev mode disabled

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
