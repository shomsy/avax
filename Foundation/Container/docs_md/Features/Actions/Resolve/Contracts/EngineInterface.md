# EngineInterface

## Quick Summary
- Defines the minimal API for a resolution engine: set a container and resolve a service described by `KernelContext`.
- Allows the kernel to depend on a stable interface instead of a concrete engine implementation.

### For Humans: What This Means
It’s the contract that says, “Give me a context, and I’ll give you the resolved value.”

## Terminology
- **Resolution engine**: Component responsible for producing a service instance/value.
- **KernelContext**: The input describing service ID, overrides, and parent chain.
- **Internal container**: Container API used for context-aware nested resolutions.

### For Humans: What This Means
Engine is the builder; context is the request; internal container is how the engine resolves nested services safely.

## Think of It
Like a vending machine interface: you insert a request (context) and get an item (resolved value). The interface defines the buttons, not the machine internals.

### For Humans: What This Means
It standardizes what it means to “resolve a service.”

## Story Example
You replace the engine implementation in tests with a fake that returns known values. Because the kernel depends on `EngineInterface`, no pipeline code changes.

### For Humans: What This Means
You can swap engines without rewriting the container.

## For Dummies
- Implement `resolve($context)` to return a value.
- Implement `setContainer($container)` so the engine can resolve nested dependencies.

### For Humans: What This Means
Two methods: plug in the container, then resolve.

## How It Works (Technical)
Declares `setContainer(ContainerInternalInterface $container): void` and `resolve(KernelContext $context): mixed`.

### For Humans: What This Means
The engine must accept the container and be able to resolve from a context.

## Architecture Role
This is the seam between kernel orchestration and resolution logic. Kernel steps call it via dependency injection.

### For Humans: What This Means
The kernel talks to this interface so it doesn’t care which engine implementation you use.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: setContainer(ContainerInternalInterface $container): void

#### Technical Explanation
Provides the engine with access to the internal container API.

##### For Humans: What This Means
It plugs the engine into the container.

##### Parameters
- `ContainerInternalInterface $container`

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
During boot.

##### Common Mistakes
Not calling it before resolution, causing missing-collaborator errors.

### Method: resolve(KernelContext $context): mixed

#### Technical Explanation
Resolves the service described by the context.

##### For Humans: What This Means
Builds or retrieves the service value.

##### Parameters
- `KernelContext $context`

##### Returns
- `mixed`

##### Throws
- Engine-specific exceptions.

##### When to Use It
Called by kernel steps.

##### Common Mistakes
Ignoring overrides in the context.

## Risks, Trade-offs & Recommended Practices
- **Practice: Keep interface minimal**. Don’t add extra responsibilities.

### For Humans: What This Means
A small interface keeps the kernel decoupled.

## Related Files & Folders
- `docs_md/Features/Actions/Resolve/Contracts/index.md`: Contracts overview.
- `docs_md/Features/Actions/Resolve/Engine.md`: Default implementation.

### For Humans: What This Means
Read the implementation to see actual resolution rules.
