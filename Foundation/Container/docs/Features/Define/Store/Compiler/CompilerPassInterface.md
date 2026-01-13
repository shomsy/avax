# CompilerPassInterface

## Quick Summary

- This file declares a contract for “compiler passes” that process a `DefinitionStore`.
- It exists so you can plug in pre-processing steps without coupling them to a specific runtime.
- It removes the complexity of “where do I hook into definition processing?” by offering one consistent entry point:
  `process()`.

### For Humans: What This Means (Summary)

If you want to run a “cleanup/validation step” on your registrations, this interface is the shape of that step.

## Terminology (MANDATORY, EXPANSIVE)

- **Compiler pass**: A transformation/validation step executed before runtime resolution.
    - In this file: an object that implements `process(DefinitionStore $definitions)`.
    - Why it matters: it enables deterministic, testable preprocessing.
- **DefinitionStore**: The container’s registry of service definitions and indices.
    - In this file: the input to `process()`.
    - Why it matters: compiler passes need a single canonical target.

### For Humans: What This Means (Terms)

It’s a “hook point” for running pre-flight checks and adjustments on your container configuration.

## Think of It

Think of a compiler pass like a spell-check pass on a document: it reads the whole thing and either fixes issues or
complains.

### For Humans: What This Means (Think)

You catch mistakes early, before they turn into runtime crashes.

## Story Example

You want to enforce that every singleton service has a tag `singleton`. You write a compiler pass that scans the store
and adds that tag automatically. Or you write a pass that throws if it finds bindings missing concrete implementations
in production.

### For Humans: What This Means (Story)

You can encode your team’s rules once, and the container configuration stays consistent.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code
with confidence.

1. You implement `CompilerPassInterface`.
2. Inside `process()`, you read/modify the store.
3. You run the pass during bootstrap/compile.
4. The runtime uses the processed store.

## How It Works (Technical)

This interface defines one method: `process(DefinitionStore $definitions): void`. The container or bootstrapper is
responsible for ordering and executing passes. The pass can mutate the store, because the store is the mutable registry
for definitions.

### For Humans: What This Means (How)

You get one simple method to do “whatever you need” to the definitions before they’re used.

## Architecture Role

- Why it lives in this folder: it’s part of the definition compilation contract.
- What depends on it: any compiler runner or bootstrapper that supports passes.
- What it depends on: `DefinitionStore`.
- System-level reasoning: it enables separation of concerns between “registration” and “validation/normalization”.

### For Humans: What This Means (Role)

This keeps your container configuration sane without forcing every registration call site to be perfect.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: process(…)

#### Technical Explanation (process)

Processes and/or mutates the provided `DefinitionStore`.

##### For Humans: What This Means (process)

This is where you either fix the store or refuse to proceed.

##### Parameters (process)

- `DefinitionStore $definitions`: The registry to analyze/transform.

##### Returns (process)

- Returns nothing.

##### Throws (process)

- Depends on implementation (a pass may throw to signal invalid configuration).

##### When to Use It (process)

- During bootstrap/compile, before the container is used for runtime resolution.

##### Common Mistakes (process)

- Writing non-deterministic passes (order-dependent side effects without clear intent).

## Risks, Trade-offs & Recommended Practices

- Risk: Pass ordering can change output.
    - Why it matters: two passes can fight each other.
    - Design stance: treat passes like a pipeline with explicit ordering.
    - Recommended practice: keep passes small and single-purpose; test them.

### For Humans: What This Means (Risks)

If you stack multiple “editors”, decide who runs first—or you’ll get surprising results.

## Related Files & Folders

- `docs_md/Features/Define/Store/DefinitionStore.md`: The store being processed.
- `docs_md/Features/Define/index.md`: The overall definition layer.

### For Humans: What This Means (Related)

To understand what a compiler pass can do, you need to understand what the store contains.

