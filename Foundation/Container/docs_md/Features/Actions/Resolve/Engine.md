# Engine

## Quick Summary
- Resolves a service ID to an instance/value by consulting definitions, contextual bindings, and autowiring.
- Delegates nested resolutions through the container when needed, preserving `KernelContext` chains for safety.
- Uses `Instantiator` for class construction and supports closures/objects/literals as definition concretes.

### For Humans: What This Means
This is the container’s core “builder brain”: given a service ID, it figures out what to return—build a class, run a factory, return a literal, or delegate to another service.

## Terminology
- **Abstract**: The service ID being resolved (often an interface or class name).
- **Concrete**: The implementation or factory that produces the instance.
- **DefinitionStore**: Registry of service definitions and contextual matches.
- **Contextual binding**: A rule like “when X needs Y, give Z instead.”
- **Autowire**: Constructing a class by resolving constructor params via types.
- **ScopeRegistry**: Registry for scoped instances (used to decide if a concrete is already available).

### For Humans: What This Means
Abstract is what you ask for, concrete is what you get. Contextual binding is “in this situation, use that instead.” Autowire builds classes by type hints.

## Think of It
Like a receptionist with a directory and rules: when you ask for “support,” it decides whether to connect you to a person, run a special handler, or redirect you based on who’s calling.

### For Humans: What This Means
It decides the best way to satisfy your request based on rules and available registrations.

## Story Example
A service ID has no explicit definition, but it’s a concrete class. Engine autowires it using Instantiator. If the service has a definition with a closure concrete, Engine calls the closure with container and overrides. If a contextual binding exists for the parent consumer, it delegates accordingly.

### For Humans: What This Means
It can build classes automatically, run factory functions, or apply “special case” rules based on who requested the service.

## For Dummies
1. Look for a contextual binding if there’s a parent context.
2. If contextual binding exists, resolve it (closure/string/object).
3. Otherwise, fetch the definition from the store.
4. If no definition exists, autowire the class.
5. If definition exists, resolve its concrete:
   - closure: call it
   - object: return it
   - class string: build it or delegate
   - literal string: return it
6. Return the instance/value.

Common misconceptions:
- “Engine also caches instances.” It doesn’t; kernel lifecycle steps handle caching.

### For Humans: What This Means
Engine builds/returns values; the kernel decides whether to store them.

## How It Works (Technical)
`resolve` calls `resolveFromBindings`. Contextual bindings are checked first using `DefinitionStore::getContextualMatch`. Definitions are fetched via `getDefinitions()->get`. Autowiring uses `Instantiator->build` and requires a container reference. Definition concretes support closure, object, class string delegation, and literal strings.

### For Humans: What This Means
It follows a priority order: contextual rules, explicit definitions, then autowiring.

## Architecture Role
This is the implementation behind `EngineInterface`. The kernel depends on it via the contract and uses it through `ResolveInstanceStep`. It depends on definition storage, scoping, and instantiation actions.

### For Humans: What This Means
It’s the core resolver implementation used by the kernel.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: setContainer(ContainerInternalInterface $container): void

#### Technical Explanation
Stores the container reference and wires it into the instantiator when present.

##### For Humans: What This Means
It plugs the engine into the container so it can delegate and autowire.

##### Parameters
- `ContainerInternalInterface $container`

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
During boot after the container is available.

##### Common Mistakes
Forgetting to call this leads to “container reference not initialized” errors.

### Method: hasInternals(): bool

#### Technical Explanation
Reports whether the engine has required collaborators (definitions, scopes, instantiator).

##### For Humans: What This Means
Tells you if the engine is fully wired.

##### Parameters
- None.

##### Returns
- `bool`

##### Throws
- None.

##### When to Use It
Diagnostics/tests.

##### Common Mistakes
Assuming true in partial test setups.

### Method: resolve(KernelContext $context): mixed

#### Technical Explanation
Entry point for resolution; delegates to internal binding resolution.

##### For Humans: What This Means
Resolves the service described by the context.

##### Parameters
- `KernelContext $context`

##### Returns
- `mixed`

##### Throws
- Container exceptions when required collaborators are missing.

##### When to Use It
Called by kernel resolution steps.

##### Common Mistakes
Calling without definitions and container setup.

## Risks, Trade-offs & Recommended Practices
- **Risk: Implicit behavior**. Autowiring and literal returns can surprise; keep definitions explicit for important services.
- **Risk: Delegation recursion**. Delegating to other services can create loops; preserve context and rely on guards.
- **Practice: Prefer definitions for clarity**. Use autowire as a convenience, not as your only registration strategy.

### For Humans: What This Means
The engine is powerful but can hide wiring decisions. Be explicit where it matters and keep guards active.

## Related Files & Folders
- `docs_md/Features/Actions/Resolve/index.md`: Folder overview.
- `docs_md/Features/Define/Store/DefinitionStore.md`: Definitions and contextual rules.
- `docs_md/Features/Actions/Instantiate/Instantiator.md`: Autowiring constructor builder.
- `docs_md/Core/Kernel/Steps/ResolveInstanceStep.md`: Kernel step that calls the engine.

### For Humans: What This Means
If you want to understand resolution outcomes, follow the definition store, instantiator, and the kernel step that triggers this engine.
