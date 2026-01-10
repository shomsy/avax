# ContainerInternalInterface

## Quick Summary
- Extends `ContainerInterface` with internal capabilities required by kernel/features.
- Exposes definition store access and context-aware recursive resolution.
- Provides injection diagnostics and metric export.

### For Humans: What This Means (Summary)
It’s the “advanced mode” container API used by internal subsystems that need deeper access.

## Terminology (MANDATORY, EXPANSIVE)- **Internal contract**: API intended for container internals, not typical application code.
- **DefinitionStore**: Internal registry of definitions.
- **KernelContext resolution**: Resolving while preserving parent chain and depth.
- **Injection inspection**: Generating `InjectionReport` without guessing.

### For Humans: What This Means
This interface is for the container’s own machinery.

## Think of It
Like opening the hood of a car: you get access to internals the driver doesn’t usually touch.

### For Humans: What This Means (Think)
Use it only when you’re building container features.

## Story Example
A property injector wants to resolve a dependency while preserving the current resolution chain. If the container supports internal context resolution, it calls `resolveContext($context->child(...))` instead of `get()`.

### For Humans: What This Means (Story)
It helps internal tools resolve safely without losing the stack.

## For Dummies
- Use `getDefinitions()` when you need to read bindings.
- Use `resolveContext()` when you need recursion-safe nested resolution.
- Use `inspectInjection()` to see injection points.

### For Humans: What This Means (Dummies)
It’s the internal toolbox.

## How It Works (Technical)
Implementations expose underlying stores and provide an entry point for context-aware resolution that bypasses “fresh context” behavior.

### For Humans: What This Means (How)
It enables internals to keep the context chain intact.

## Architecture Role
Used by kernel steps, engines, injection and analysis subsystems. Application code should prefer `ContainerInterface`.

### For Humans: What This Means (Role)
It powers the container’s own features.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: getDefinitions(): DefinitionStore

#### Technical Explanation (getDefinitions)
Returns the internal definition store.

##### For Humans: What This Means (getDefinitions)
Gives you access to the container’s registry.

##### Parameters (getDefinitions)
- None.

##### Returns (getDefinitions)
- `DefinitionStore`

##### Throws (getDefinitions)
- None.

##### When to Use It (getDefinitions)
Internal engines/diagnostics.

##### Common Mistakes (getDefinitions)
Using it in application code instead of registry interfaces.

### Method: resolveContext(KernelContext $context): mixed

#### Technical Explanation (resolveContext)
Resolves within the provided kernel context.

##### For Humans: What This Means (resolveContext)
Resolve while keeping the resolution stack intact.

##### Parameters (resolveContext)
- `KernelContext $context`

##### Returns (resolveContext)
- `mixed`

##### Throws (resolveContext)
- Resolution exceptions.

##### When to Use It (resolveContext)
Nested resolutions from injectors/resolvers.

##### Common Mistakes (resolveContext)
Passing contexts with wrong parent chain.

### Method: inspectInjection(object $target): InjectionReport

#### Technical Explanation (inspectInjection)
Returns diagnostic information about injection points.

##### For Humans: What This Means (inspectInjection)
Get a report of what would be injected.

##### Parameters (inspectInjection)
- `object $target`

##### Returns (inspectInjection)
- `InjectionReport`

##### Throws (inspectInjection)
- Injection analysis exceptions.

##### When to Use It (inspectInjection)
Tooling and diagnostics.

##### Common Mistakes (inspectInjection)
Treating it as injection itself.

### Method: exportMetrics(): string

#### Technical Explanation (exportMetrics)
Exports metrics in a serialized format.

##### For Humans: What This Means (exportMetrics)
Dump the container’s metrics as text.

##### Parameters (exportMetrics)
- None.

##### Returns (exportMetrics)
- `string`

##### Throws (exportMetrics)
- None.

##### When to Use It (exportMetrics)
Diagnostics dashboards.

##### Common Mistakes (exportMetrics)
Logging raw metrics without sanitization.

## Risks, Trade-offs & Recommended Practices
- **Risk: Tight coupling**. Depending on internals makes code harder to change.
- **Practice: Keep internal usage contained**. Use it only inside container features.

### For Humans: What This Means (Risks)
Only use the hood-open API when you’re working on the container itself.

## Related Files & Folders
- `docs_md/Features/Core/Contracts/ContainerInterface.md`: Runtime contract.
- `docs_md/Features/Define/Store/DefinitionStore.md`: Definitions.
- `docs_md/Core/Kernel/Contracts/KernelContext.md`: Context semantics.

### For Humans: What This Means (Related)
Internal APIs revolve around definitions and contexts.
