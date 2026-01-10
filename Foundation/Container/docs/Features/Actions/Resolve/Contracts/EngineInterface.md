# EngineInterface

## Quick Summary
- Defines the minimal API for a resolution engine: set a container and resolve a service described by `KernelContext`.
- Allows the kernel to depend on a stable interface instead of a concrete engine implementation.

### For Humans: What This Means (Summary)
It’s the contract that says, “Give me a context, and I’ll give you the resolved value.”

## Terminology (MANDATORY, EXPANSIVE)- **Resolution engine**: Component responsible for producing a service instance/value.
- **KernelContext**: The input describing service ID, overrides, and parent chain.
- **Internal container**: Container API used for context-aware nested resolutions.

### For Humans: What This Means
Engine is the builder; context is the request; internal container is how the engine resolves nested services safely.

## Think of It
Like a vending machine interface: you insert a request (context) and get an item (resolved value). The interface defines the buttons, not the machine internals.

### For Humans: What This Means (Think)
It standardizes what it means to “resolve a service.”

## Story Example
You replace the engine implementation in tests with a fake that returns known values. Because the kernel depends on `EngineInterface`, no pipeline code changes.

### For Humans: What This Means (Story)
You can swap engines without rewriting the container.

## For Dummies
- Implement `resolve($context)` to return a value.
- Implement `setContainer($container)` so the engine can resolve nested dependencies.

### For Humans: What This Means (Dummies)
Two methods: plug in the container, then resolve.

## How It Works (Technical)
Declares `setContainer(ContainerInternalInterface $container): void` and `resolve(KernelContext $context): mixed`.

### For Humans: What This Means (How)
The engine must accept the container and be able to resolve from a context.

## Architecture Role
This is the seam between kernel orchestration and resolution logic. Kernel steps call it via dependency injection.

### For Humans: What This Means (Role)
The kernel talks to this interface so it doesn’t care which engine implementation you use.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: setContainer(ContainerInternalInterface $container): void

#### Technical Explanation (setContainer)
Provides the engine with access to the internal container API.

##### For Humans: What This Means (setContainer)
It plugs the engine into the container.

##### Parameters (setContainer)
- `ContainerInternalInterface $container`

##### Returns (setContainer)
- `void`

##### Throws (setContainer)
- None.

##### When to Use It (setContainer)
During boot.

##### Common Mistakes (setContainer)
Not calling it before resolution, causing missing-collaborator errors.

### Method: resolve(KernelContext $context): mixed

#### Technical Explanation (resolve)
Resolves the service described by the context.

##### For Humans: What This Means (resolve)
Builds or retrieves the service value.

##### Parameters (resolve)
- `KernelContext $context`

##### Returns (resolve)
- `mixed`

##### Throws (resolve)
- Engine-specific exceptions.

##### When to Use It (resolve)
Called by kernel steps.

##### Common Mistakes (resolve)
Ignoring overrides in the context.

## Risks, Trade-offs & Recommended Practices
- **Practice: Keep interface minimal**. Don’t add extra responsibilities.

### For Humans: What This Means (Risks)
A small interface keeps the kernel decoupled.

## Related Files & Folders
- `docs_md/Features/Actions/Resolve/Contracts/index.md`: Contracts overview.
- `docs_md/Features/Actions/Resolve/Engine.md`: Default implementation.

### For Humans: What This Means (Related)
Read the implementation to see actual resolution rules.
