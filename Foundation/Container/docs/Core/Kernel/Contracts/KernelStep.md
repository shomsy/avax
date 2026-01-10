# KernelStep

## Quick Summary

Defines the contract every kernel pipeline step must follow: an invokable step that processes a `KernelContext` during service resolution. It exists to standardize how steps mutate context and coordinate in the pipeline.

### For Humans: What This Means (Summary)

Every step in the resolution pipeline must look the same—callable with a context. This interface makes sure all steps play by that rule.

## Terminology (MANDATORY, EXPANSIVE)

- **Kernel step**: A unit of work in the resolution pipeline that inspects/modifies context.
- **Resolution pipeline**: Ordered steps that build or retrieve a service instance.
- **KernelContext**: Mutable state passed through the pipeline containing service ID, instance, metadata.
- **Invokable**: The `__invoke` magic method enabling object-as-function calls.

### For Humans: What This Means (Terms)

A step is just one action in the chain; the pipeline is the whole chain; the context is the baton they pass; being invokable means you can call the step like a function.

## Think of It

Like a relay race: each runner (step) grabs the baton (context), does their part, and hands it off. The interface defines the shape of each runner.

### For Humans: What This Means (Think)

All runners must be able to run the same way with the same baton—this contract enforces that.

## Story Example

Before `KernelStep`, steps had inconsistent signatures, causing fragile pipelines. With the interface, every new step implements `__invoke(KernelContext $context)`, making pipelines predictable and composable.

### For Humans: What This Means (Story)

You no longer wonder how to call a step—every step uses the same signature, so you can slot new ones in safely.

## For Dummies

- A step is a small class with `__invoke(KernelContext $context): void`.
- The pipeline calls each step in order, passing the context.
- Implementations update the context (instances, metadata, errors).

Common misconceptions: it doesn’t define pipeline order; it doesn’t resolve services itself; it just defines the shape of a step.

### For Humans: What This Means (Dummies)

It’s just the template: “here’s how a step should look.” The actual work and order are elsewhere.

## How It Works (Technical)

The interface declares a single `__invoke` method taking a `KernelContext`. Implementations perform step-specific logic and may mutate context state.

### For Humans: What This Means (How)

Every step class must provide one callable method that takes the context and does its job.

## Architecture Role

Lives in Contracts to enforce uniform step signatures across the kernel pipeline. Pipeline builders depend on it; all concrete steps implement it; it depends only on `KernelContext`.

### For Humans: What This Means (Role)

It’s the rule all steps must obey, allowing the pipeline to treat them uniformly.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __invoke(KernelContext $context): void

#### Technical Explanation (Invoke)

Executes the step against the provided context, potentially mutating it (resolving instances, adding metadata, handling errors).

##### For Humans: What This Means (Invoke)

When the pipeline calls the step, it gets the context, does its work, and updates the context as needed.

##### Parameters (__invoke)
- `KernelContext $context`: The current resolution state.

##### Returns (__invoke)
- `void`: Work is done via side effects on context.

##### Throws (__invoke)
- Implementation-specific exceptions if resolution work fails.

##### When to Use It (__invoke)
- Implemented by every pipeline step class; the pipeline calls it for each step.

##### Common Mistakes (__invoke)
- Treating context as immutable when it’s meant to be updated.
- Throwing without enriching context with error info where appropriate.

## Risks, Trade-offs & Recommended Practices

- **Risk: Inconsistent side effects**. Mutating context unpredictably can break downstream steps; document and standardize mutations.
- **Trade-off: Flexibility vs predictability**. Steps can do many things; keep responsibilities narrow for clarity.
- **Practice: Idempotent behavior**. Where possible, make steps idempotent to avoid double-processing.

### For Humans: What This Means (Risks)

Keep step behavior clear and consistent, and avoid surprising changes to context.

## Related Files & Folders

- `docs_md/Core/Kernel/Contracts/index.md`: Contract overview.
- `docs_md/Core/Kernel/Contracts/KernelContext.md`: The context passed into steps.
- `docs_md/Core/Kernel/Contracts/TerminalKernelStep.md`: Marker for early-return steps.

### For Humans: What This Means (Related)

Read the overview for bigger picture, see the context you’ll mutate, and check the terminal marker for steps that can short-circuit.
