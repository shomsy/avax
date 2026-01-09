# CompilerInterface

## Quick Summary
- Defines the container’s “preparation/compilation” capabilities: analyzing, validating, warming caches, and generating artifacts.
- Exists to separate build-time container operations from runtime resolution.

### For Humans: What This Means
This interface is about getting the container ready before you run the app—checking it, analyzing it, and optionally compiling it for speed.

## Terminology
- **Compilation**: Preparing optimized artifacts for faster startup.
- **Prototype analysis**: Reflection-driven analysis of dependencies.
- **Validation**: Checking for missing services, cycles, invalid injection points.
- **Cache warming**: Precomputing expensive results.

### For Humans: What This Means
It’s the set of operations you run before production to make the container safer and faster.

## Think of It
Like pre-flight checks on an airplane: you validate systems and prepare the flight plan before takeoff.

### For Humans: What This Means
Do the checks before you serve traffic.

## Story Example
In CI, you call `validate()` to catch missing services and cycles. During deployment, you run compilation to generate a cached container file so startup is faster.

### For Humans: What This Means
You catch problems early and ship a faster container.

## For Dummies
- Use `analyzeDependenciesFor()` to inspect a class.
- Use `validate()` to verify the container.
- Use compilation/cache operations (implementation-specific) for production.

### For Humans: What This Means
It’s your build-time toolset.

## How It Works (Technical)
This is a contract only. Implementations typically reuse prototype analyzers, definition stores, and caching layers to produce optimized outputs.

### For Humans: What This Means
The interface describes what you can do; the implementation defines how.

## Architecture Role
Used by tools/console commands and build pipelines. Not required for normal runtime resolution.

### For Humans: What This Means
It’s mostly for devops, CI, and performance optimization.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: analyzeDependenciesFor(string $class): ServicePrototypeBuilder

#### Technical Explanation
Analyzes a class and returns a builder for its service prototype.

##### For Humans: What This Means
Inspect a class and get a blueprint builder.

##### Parameters
- `string $class`

##### Returns
- `ServicePrototypeBuilder`

##### Throws
- `ContainerExceptionInterface` on analysis failures.

##### When to Use It
During validation or diagnostics.

##### Common Mistakes
Analyzing non-instantiable classes.

### Method: validate(): self

#### Technical Explanation
Validates all registered service definitions.

##### For Humans: What This Means
Run the container’s health checks.

##### Parameters
- None.

##### Returns
- `self`

##### Throws
- `ContainerExceptionInterface` when validation fails.

##### When to Use It
CI, pre-deploy.

##### Common Mistakes
Ignoring warnings and shipping anyway.

### Method: getInjectionInfo(object $target): array

#### Technical Explanation
Returns detailed information about injection points.

##### For Humans: What This Means
Ask what would be injected into an object.

##### Parameters
- `object $target`

##### Returns
- `array`

##### Throws
- None (implementation dependent).

##### When to Use It
Diagnostics.

##### Common Mistakes
Assuming this performs injection.

## Risks, Trade-offs & Recommended Practices
- **Trade-off: More build steps**. Compilation improves runtime but adds build complexity.
- **Practice: Make validate part of CI**.

### For Humans: What This Means
Extra preparation steps pay off in reliability and performance.

## Related Files & Folders
- `docs_md/Tools/Console/CompileCommand.md`: Build-time compilation command.
- `docs_md/Features/Think/Prototype/ServicePrototypeBuilder.md`: Prototype builder type.

### For Humans: What This Means
Compiler interfaces are often driven by CLI tools and prototype builders.
