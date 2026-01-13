# ServicePrototypeFactoryInterface

## Quick Summary

- This file defines the contract for producing `ServicePrototype` instances for a given class name.
- It exists so prototype creation can be swapped, decorated, or tested behind an interface.
- It removes coupling to a specific factory implementation.

### For Humans: What This Means (Summary)

If you can create a prototype for a class, you can plug into the container through this interface.

## Terminology (MANDATORY, EXPANSIVE)

- **Factory**: A component whose job is to create objects.
    - In this file: creates `ServicePrototype` objects (directly or via cache + analyzer).
    - Why it matters: it centralizes “prototype creation policy”.
- **Prototype**: An injection blueprint (`ServicePrototype`).
    - In this file: output of `createFor()`.
    - Why it matters: runtime resolution uses it to inject correctly.
- **Cache hit**: When the prototype already exists in cache.
    - In this file: implied behavior; implementations may consult caches.
    - Why it matters: it avoids expensive reflection.
- **Analyzer**: A component that performs reflection analysis.
    - In this file: exposed via `getAnalyzer()`.
    - Why it matters: analysis is the source of truth when cache misses.

### For Humans: What This Means (Terms)

This interface is the “make me a blueprint for this class” button.

## Think of It

Think of it like a translation service: you hand it a class name, and it hands you the translated instruction manual (
the prototype).

### For Humans: What This Means (Think)

You ask once, you get a reusable plan back.

## Story Example

Your resolver needs a `ServicePrototype` for `UserRepository`. It doesn’t care whether the prototype came from disk
cache or from reflection analysis—it just calls `createFor(UserRepository::class)` on a
`ServicePrototypeFactoryInterface`.

### For Humans: What This Means (Story)

You decouple “where the blueprint comes from” from “how the blueprint is used”.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code
with confidence.

- `createFor()` returns a blueprint.
- `hasPrototype()` tells you if it’s already cached.
- `getAnalyzer()` gives access to the underlying analyzer (mostly for tooling/extensibility).

## How It Works (Technical)

This interface defines three methods:

- `createFor(string $class): ServicePrototype`
- `hasPrototype(string $class): bool`
- `getAnalyzer(): PrototypeAnalyzer`

Implementations can choose caching strategies, validation steps, and error handling policies.

### For Humans: What This Means (How)

The interface tells you *what* you can ask for; the implementation decides *how* it’s done.

## Architecture Role

- Why it lives here: it’s a contract for Think/Prototype orchestration.
- What depends on it: prototype consumers that don’t want to know cache details.
- What it depends on: prototype model and analyzer types.
- System-level reasoning: contracts prevent implementation lock-in.

### For Humans: What This Means (Role)

You keep your code flexible: swap factory implementations without rewriting the system.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: createFor(…)

#### Technical Explanation (createFor)

Returns a `ServicePrototype` for the given class, analyzing it if needed.

##### For Humans: What This Means (createFor)

“Give me the blueprint for this class.”

##### Parameters (createFor)

- `string $class`: Fully qualified class name.

##### Returns (createFor)

- `ServicePrototype`

##### Throws (createFor)

- Depends on implementation (analysis may throw if class isn’t instantiable or types are missing).

##### When to Use It (createFor)

- Whenever you need a prototype for runtime resolution or caching.

##### Common Mistakes (createFor)

- Calling it for interfaces/abstracts and expecting a valid prototype.

### Method: hasPrototype(…)

#### Technical Explanation (hasPrototype)

Checks whether a prototype exists in the backing cache for the given class.

##### For Humans: What This Means (hasPrototype)

“Have we already cached a blueprint for this?”

##### Parameters (hasPrototype)

- `string $class`

##### Returns (hasPrototype)

- `bool`

##### Throws (hasPrototype)

- Depends on implementation.

##### When to Use It (hasPrototype)

- Diagnostics, warmup flows, pre-flight checks.

##### Common Mistakes (hasPrototype)

- Treating `true` as “prototype is valid”; it only means “it exists”.

### Method: getAnalyzer(…)

#### Technical Explanation (getAnalyzer)

Returns the analyzer used to build prototypes.

##### For Humans: What This Means (getAnalyzer)

It gives you access to the “microscope” behind the factory.

##### Parameters (getAnalyzer)

- None.

##### Returns (getAnalyzer)

- `PrototypeAnalyzer`

##### Throws (getAnalyzer)

- No explicit exceptions.

##### When to Use It (getAnalyzer)

- Tooling, introspection, extension.

##### Common Mistakes (getAnalyzer)

- Mutating analyzer internals (treat it as a dependency, not configuration).

## Risks, Trade-offs & Recommended Practices

- Risk: Interface doesn’t mandate validation.
    - Why it matters: you might cache invalid prototypes if you skip verification.
    - Design stance: pair prototype creation with verification where appropriate.
    - Recommended practice: validate prototypes in dev/CI; clear caches on deploy.

### For Humans: What This Means (Risks)

An interface can’t stop you from doing something risky—your implementation and workflow must.

## Related Files & Folders

- `docs_md/Features/Think/Prototype/ServicePrototypeFactory.md`: A concrete implementation.
- `docs_md/Features/Think/Analyze/PrototypeAnalyzer.md`: The underlying analysis engine.
- `docs_md/Features/Think/Cache/PrototypeCache.md`: Where prototypes are typically stored.

### For Humans: What This Means (Related)

Factory is the orchestrator, analyzer is the thinker, cache is the memory.

