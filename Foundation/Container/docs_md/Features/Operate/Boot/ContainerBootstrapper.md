# ContainerBootstrapper

## Quick Summary
- This file assembles the “core container runtime” from an existing `DefinitionStore` and `ScopeRegistry`.
- It exists so the container kernel can be constructed consistently (Think + Actions + KernelConfig wiring).
- It removes the complexity of manual “circular dependency wiring” by doing it in one place.

### For Humans: What This Means
It’s the factory that builds the container engine room: analysis, resolution, injection, invocation, scope handling, telemetry—then wires them together.

## Terminology (MANDATORY, EXPANSIVE)
- **Bootstrapper**: A component that constructs the container runtime.
  - In this file: `bootstrap()` returns a ready-to-use `Container`.
  - Why it matters: you want one canonical assembly procedure.
- **DefinitionStore**: Registration “memory” of what services exist.
  - In this file: passed in and used by the kernel.
  - Why it matters: resolution reads from it.
- **ScopeRegistry / ScopeManager**: Runtime storage and API for scoped instances.
  - In this file: both are created/used.
  - Why it matters: scoped services must be stored and later cleaned up.
- **Think layer**: Prototype analysis/caching.
  - In this file: `ReflectionTypeAnalyzer`, `PrototypeAnalyzer`, `FilePrototypeCache`, `ServicePrototypeFactory`.
  - Why it matters: it reduces runtime reflection cost.
- **Actions layer**: Runtime behaviors that actually build/inject/invoke/resolve.
  - In this file: `DependencyResolver`, `Instantiator`, `Engine`, `InjectDependencies`, `InvokeAction`.
  - Why it matters: this is where objects are actually created.
- **Circular wiring**: Setting container references after everything is created.
  - In this file: `setContainer()` calls on several collaborators.
  - Why it matters: some components need the container to call back into it.

### For Humans: What This Means
This is “assemble the parts and plug the cables in” so the container is usable.

## Think of It
Think of it like building a PC:
- Install CPU (Think layer).
- Install RAM (cache).
- Install motherboard (kernel).
- Connect power cables (circular wiring).

### For Humans: What This Means
The parts might be great, but if you don’t connect them correctly, nothing boots.

## Story Example
`ContainerBuilder` creates a `DefinitionStore` and a `ScopeRegistry`. Then it uses this bootstrapper to build the full container runtime. After bootstrap, you can call `$container->get(Foo::class)` and the engine will analyze prototypes, resolve dependencies, and manage lifetimes.

### For Humans: What This Means
This is the step that turns “definitions and scopes” into “a working container”.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Provide definitions and scope registry.
2. Bootstrapper creates analysis, cache, and prototype factory.
3. Bootstrapper creates resolver/instantiator/engine/injector/invoker.
4. Bootstrapper creates kernel config + kernel + container.
5. Bootstrapper wires container references into collaborators.

## How It Works (Technical)
The bootstrapper builds:
- Think: type analyzer + prototype analyzer + file prototype cache + prototype factory.
- Actions: dependency resolver + instantiator + engine + injector + invoker.
- Kernel config: includes metrics, policy, timeline, terminator, and flags.
- ContainerKernel + Container.
Then it performs circular dependency wiring via `setContainer()` and registers core references/aliases into the container.

### For Humans: What This Means
It builds the container’s “operating system” and then installs it into the container instance.

## Architecture Role
- Why it lives in `Features/Operate/Boot`: it’s runtime assembly.
- What depends on it: container builders and environment-specific bootstrappers.
- What it depends on: core kernel config types and Think/Actions subsystems.
- System-level reasoning: assembly code is fragile; centralize it for correctness.

### For Humans: What This Means
You don’t want ten different ways to build the container—one way is easier to trust.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(ContainerPolicy|null $policy = null, bool $debug = false, string|null $cacheDir = null)

#### Technical Explanation
Stores bootstrap configuration flags (policy, debug, cache directory).

##### For Humans: What This Means
It configures how strict and how verbose the container should be.

##### Parameters
- `ContainerPolicy|null $policy`
- `bool $debug`
- `string|null $cacheDir`

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- When choosing between strict/policy-driven behavior vs permissive.

##### Common Mistakes
- Enabling strict mode without understanding how policies block resolution.

### Method: bootstrap(DefinitionStore $definitions, ScopeRegistry $registry)

#### Technical Explanation
Assembles and wires the full container runtime and returns a `Container`.

##### For Humans: What This Means
This is the “build me a working container” method.

##### Parameters
- `DefinitionStore $definitions`
- `ScopeRegistry $registry`

##### Returns
- `Container`

##### Throws
- Depends on filesystem access (prototype cache) and collaborators’ constructors.

##### When to Use It
- After registration store and scope registry are prepared.

##### Common Mistakes
- Using a cache directory that is not writable for file prototype caching.

## Risks, Trade-offs & Recommended Practices
- Risk: Circular wiring is easy to break.
  - Why it matters: missing `setContainer()` leads to null container usage at runtime.
  - Design stance: keep wiring in one place and test it.
  - Recommended practice: add integration tests around bootstrap (already present in `tests/`).

### For Humans: What This Means
When wiring is wrong, everything breaks in weird ways. Keep it centralized and tested.

## Related Files & Folders
- `docs_md/Core/ContainerKernel.md`: The runtime kernel created here.
- `docs_md/Features/Think/Prototype/ServicePrototypeFactory.md`: The prototype factory used here.

### For Humans: What This Means
This bootstrapper is where many subsystems first meet and start collaborating.

