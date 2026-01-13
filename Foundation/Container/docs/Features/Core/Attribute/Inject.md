# Inject

## Quick Summary

- Marks a property, method, or parameter as an explicit injection point.
- Optionally overrides the service ID (`$abstract`) that should be resolved.
- Exists to make injection intent explicit and discoverable via reflection.

### For Humans: What This Means (Summary)

When you put `#[Inject]` somewhere, you’re telling the container: “Please fill this dependency for me.”

## Terminology (MANDATORY, EXPANSIVE)- **Attribute**: A PHP metadata marker discovered via reflection.

- **Injection point**: A location where the container should provide a dependency (property/method/parameter).
- **Abstract (service ID)**: Identifier the container resolves; if provided here, it overrides type-based inference.

### For Humans: What This Means

This attribute is a label the container reads to know where to inject, and what exactly to inject.

## Think of It

Like placing a sticky note on a socket that says “plug power here.”

### For Humans: What This Means (Think)

It removes guesswork: you explicitly mark where wiring should happen.

## Story Example

A class has a property `private LoggerInterface $logger;`. You add `#[Inject]` and the injector sees it during prototype
analysis. During injection, the container resolves `LoggerInterface` (or the provided `$abstract`) and sets the
property.

### For Humans: What This Means (Story)

You mark the spot, and the container does the wiring.

## For Dummies

- Add `#[Inject]` to a property/method/parameter.
- Optionally pass an explicit abstract ID.
- The container’s injector will detect it and inject the value.

Common misconceptions:

- It doesn’t *resolve* anything by itself; it’s only metadata.

### For Humans: What This Means (Dummies)

This attribute is a signpost, not an engine.

## How It Works (Technical)

The attribute is defined with targets for properties, methods, and parameters. It stores an optional `$abstract` string.
Prototype analyzers and injectors read it to decide injection behavior.

### For Humans: What This Means (How)

It’s just a small data holder that other parts of the container interpret.

## Architecture Role

Lives in `Features/Core/Attribute` as a shared primitive used by analysis, injection, and validation layers. It does not
depend on the container runtime.

### For Humans: What This Means (Role)

It’s a foundational marker used everywhere injection is supported.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __construct(?string $abstract = null)

#### Technical Explanation (__construct)

Stores an optional explicit service ID to inject.

##### For Humans: What This Means (__construct)

Lets you say “inject this exact service,” not just “inject by type.”

##### Parameters (__construct)

- `?string $abstract`: Service ID override.

##### Returns (__construct)

- `void`

##### Throws (__construct)

- None.

##### When to Use It (__construct)

Use when type hints aren’t enough or you want a specific binding.

##### Common Mistakes (__construct)

Using an abstract that isn’t registered.

## Risks, Trade-offs & Recommended Practices

- **Risk: Hidden dependencies**. Attribute injection can hide what a class needs; document injection points.
- **Practice: Prefer constructors for core requirements**. Use `#[Inject]` for optional wiring or cross-cutting
  concerns.

### For Humans: What This Means (Risks)

Attributes are convenient, but constructors make dependencies obvious. Use both intentionally.

## Related Files & Folders

- `docs_md/Features/Core/Attribute/index.md`: Attribute overview.
- `docs_md/Features/Actions/Inject/InjectDependencies.md`: Performs injection using prototypes.
- `docs_md/Features/Think/Analyze/PrototypeAnalyzer.md`: Likely detects injection attributes.

### For Humans: What This Means (Related)

Read the injection action to see how injection is applied, and the analyzers to see how injection points are discovered.
