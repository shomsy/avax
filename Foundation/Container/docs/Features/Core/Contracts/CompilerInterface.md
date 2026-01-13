# CompilerInterface

## Quick Summary

- Defines the container’s “preparation/compilation” capabilities: analyzing, validating, warming caches, and generating
  artifacts.
- Exists to separate build-time container operations from runtime resolution.

### For Humans: What This Means (Summary)

This interface is about getting the container ready before you run the app—checking it, analyzing it, and optionally
compiling it for speed.

## Terminology (MANDATORY, EXPANSIVE)- **Compilation**: Preparing optimized artifacts for faster startup.

- **Prototype analysis**: Reflection-driven analysis of dependencies.
- **Validation**: Checking for missing services, cycles, invalid injection points.
- **Cache warming**: Precomputing expensive results.

### For Humans: What This Means

It’s the set of operations you run before production to make the container safer and faster.

## Think of It

Like pre-flight checks on an airplane: you validate systems and prepare the flight plan before takeoff.

### For Humans: What This Means (Think)

Do the checks before you serve traffic.

## Story Example

In CI, you call `validate()` to catch missing services and cycles. During deployment, you run compilation to generate a
cached container file so startup is faster.

### For Humans: What This Means (Story)

You catch problems early and ship a faster container.

## For Dummies

- Use `analyzeDependenciesFor()` to inspect a class.
- Use `validate()` to verify the container.
- Use compilation/cache operations (implementation-specific) for production.

### For Humans: What This Means (Dummies)

It’s your build-time toolset.

## How It Works (Technical)

This is a contract only. Implementations typically reuse prototype analyzers, definition stores, and caching layers to
produce optimized outputs.

### For Humans: What This Means (How)

The interface describes what you can do; the implementation defines how.

## Architecture Role

Used by tools/console commands and build pipelines. Not required for normal runtime resolution.

### For Humans: What This Means (Role)

It’s mostly for devops, CI, and performance optimization.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: analyzeDependenciesFor(string $class): ServicePrototypeBuilder

#### Technical Explanation (analyzeDependenciesFor)

Analyzes a class and returns a builder for its service prototype.

##### For Humans: What This Means (analyzeDependenciesFor)

Inspect a class and get a blueprint builder.

##### Parameters (analyzeDependenciesFor)

- `string $class`

##### Returns (analyzeDependenciesFor)

- `ServicePrototypeBuilder`

##### Throws (analyzeDependenciesFor)

- `ContainerExceptionInterface` on analysis failures.

##### When to Use It (analyzeDependenciesFor)

During validation or diagnostics.

##### Common Mistakes (analyzeDependenciesFor)

Analyzing non-instantiable classes.

### Method: validate(): self

#### Technical Explanation (validate)

Validates all registered service definitions.

##### For Humans: What This Means (validate)

Run the container’s health checks.

##### Parameters (validate)

- None.

##### Returns (validate)

- `self`

##### Throws (validate)

- `ContainerExceptionInterface` when validation fails.

##### When to Use It (validate)

CI, pre-deploy.

##### Common Mistakes (validate)

Ignoring warnings and shipping anyway.

### Method: getInjectionInfo(object $target): array

#### Technical Explanation (getInjectionInfo)

Returns detailed information about injection points.

##### For Humans: What This Means (getInjectionInfo)

Ask what would be injected into an object.

##### Parameters (getInjectionInfo)

- `object $target`

##### Returns (getInjectionInfo)

- `array`

##### Throws (getInjectionInfo)

- None (implementation dependent).

##### When to Use It (getInjectionInfo)

Diagnostics.

##### Common Mistakes (getInjectionInfo)

Assuming this performs injection.

## Risks, Trade-offs & Recommended Practices

- **Trade-off: More build steps**. Compilation improves runtime but adds build complexity.
- **Practice: Make validate part of CI**.

### For Humans: What This Means (Risks)

Extra preparation steps pay off in reliability and performance.

## Related Files & Folders

- `docs_md/Tools/Console/CompileCommand.md`: Build-time compilation command.
- `docs_md/Features/Think/Prototype/ServicePrototypeBuilder.md`: Prototype builder type.

### For Humans: What This Means (Related)

Compiler interfaces are often driven by CLI tools and prototype builders.

### Method: clearCache(...)

#### Technical Explanation (clearCache)

This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the
container’s workflow explicit and reusable.

##### For Humans: What This Means (clearCache)

When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having
to manually wire the details.

##### Parameters (clearCache)

- See the PHP signature in the source file for exact types and intent.

##### Returns (clearCache)

- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (clearCache)

- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (clearCache)

- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (clearCache)

- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.

### Method: compile(...)

#### Technical Explanation (compile)

This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the
container’s workflow explicit and reusable.

##### For Humans: What This Means (compile)

When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having
to manually wire the details.

##### Parameters (compile)

- See the PHP signature in the source file for exact types and intent.

##### Returns (compile)

- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (compile)

- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (compile)

- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (compile)

- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.

### Method: getCompilationStats(...)

#### Technical Explanation (getCompilationStats)

This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the
container’s workflow explicit and reusable.

##### For Humans: What This Means (getCompilationStats)

When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having
to manually wire the details.

##### Parameters (getCompilationStats)

- See the PHP signature in the source file for exact types and intent.

##### Returns (getCompilationStats)

- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (getCompilationStats)

- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (getCompilationStats)

- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (getCompilationStats)

- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.
