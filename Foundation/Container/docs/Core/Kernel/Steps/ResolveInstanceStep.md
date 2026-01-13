# ResolveInstanceStep

## Quick Summary

- Delegates core service resolution to the configured `EngineInterface`.
- Writes the resolved instance back into the `KernelContext` using `resolvedWith`.
- Records basic timing metadata for resolution completion.

### For Humans: What This Means (Summary)

This is the moment the container actually builds (or computes) the service instance by calling the resolution engine.

## Terminology (MANDATORY, EXPANSIVE)- **Resolution engine

**: Component that performs the actual resolution logic for a service ID.

- **EngineInterface**: Contract the kernel uses so engines can be swapped.
- **Resolved instance**: The final object/value returned for a service.
- **Resolution metadata**: Context metadata describing resolution timing.

### For Humans: What This Means

The engine is the builder; the interface is the rule; the resolved instance is the result; metadata is the timestamp
note.

## Think of It

Like pressing “Start” on a coffee machine: the earlier steps prepared everything, and now the machine actually makes the
coffee.

### For Humans: What This Means (Think)

This step is the “make it” button.

## Story Example

A service isn’t cached, so the pipeline reaches this step. The engine resolves the service by following definitions and
prototypes, returns a new instance, and the context is marked resolved. Downstream steps can now inject, extend, and
store lifecycle state.

### For Humans: What This Means (Story)

If no cache hit happened, this step builds the instance and hands it to the rest of the pipeline.

## For Dummies

1. Skip if you’re doing an injection-target operation.
2. Ask the engine to resolve using the current context.
3. Put the result into the context.
4. Record the completion timestamp.

Common misconceptions:

- “This step knows how to build services.” It doesn’t; the engine does.

### For Humans: What This Means (Dummies)

This step is just the caller; the engine is the real builder.

## How It Works (Technical)

Calls `EngineInterface::resolve(context)` and writes the returned instance into the context via `resolvedWith`. Adds
`resolution.completed_at` metadata.

### For Humans: What This Means (How)

It calls the engine and stores the returned result.

## Architecture Role

Central pipeline step that bridges kernel orchestration and the resolution engine implementation. Depends on
`EngineInterface` and the `KernelContext` API.

### For Humans: What This Means (Role)

It’s where orchestration hands control to the engine to actually produce the instance.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __construct(EngineInterface $engine)

#### Technical Explanation (__construct)

Stores the resolution engine implementation used to build instances.

##### For Humans: What This Means (__construct)

Keeps the builder component this step will call.

##### Parameters (__construct)

- `EngineInterface $engine`: Resolution engine.

##### Returns (__construct)

- `void`

##### Throws (__construct)

- None.

##### When to Use It (__construct)

Constructed during kernel assembly.

##### Common Mistakes (__construct)

Injecting an engine implementation that doesn’t match your container’s expected behavior.

### Method: __invoke(KernelContext $context)

#### Technical Explanation (__invoke)

Skips injection-target cases, calls engine resolution, marks context as resolved with the returned instance, and records
resolution timing metadata.

##### For Humans: What This Means (__invoke)

If allowed, it asks the engine to build the service and stores the result.

##### Parameters (__invoke)

- `KernelContext $context`: Holds service ID, metadata, and flags.

##### Returns (__invoke)

- `void`

##### Throws (__invoke)

- Engine-specific resolution exceptions.

##### When to Use It (__invoke)

Executed when no earlier terminal step resolved the instance.

##### Common Mistakes (__invoke)

Assuming it should run during injection-only operations (it skips those).

## Risks, Trade-offs & Recommended Practices

- **Risk: Engine complexity**. All resolution complexity lives behind the engine; keep engine observable and testable.
- **Practice: Keep step thin**. This step should remain a delegator so swapping engines is easy.

### For Humans: What This Means (Risks)

Most bugs here are engine bugs. Keep this step simple so you can change engines without rewriting the pipeline.

## Related Files & Folders

- `docs_md/Core/Kernel/Steps/index.md`: Steps overview.
- `docs_md/Features/Actions/Resolve/Engine.md`: Likely engine implementation.
- `docs_md/Features/Actions/Resolve/Contracts/EngineInterface.md`: Engine contract.

### For Humans: What This Means (Related)

Read the engine docs to see how resolution actually happens; this file just calls it.
