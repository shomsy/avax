# DepthGuardStep

## Quick Summary

- Enforces a maximum resolution depth to prevent stack overflow from deep dependency chains.
- Throws `ResolutionException` when the context depth exceeds the configured limit.
- Uses the context’s resolution path to provide a useful error message.

### For Humans: What This Means (Summary)

It stops the container from going too deep and crashing your process by throwing a clear error when dependency chains
get out of control.

## Terminology (MANDATORY, EXPANSIVE)- **Resolution depth**: How deep the container is in nested dependency resolution.

- **Max depth**: Safety limit; defaults to 64.
- **Resolution path**: String representation of the dependency chain from root to current.
- **Stack overflow**: Fatal failure caused by too deep recursion.

### For Humans: What This Means

Depth is “how many dependencies deep you are.” Max depth is the safety line. Path shows the chain. Stack overflow is the
crash this prevents.

## Think of It

Like a circuit breaker: it doesn’t prevent you from using electricity, but it stops the system before overheating burns
the house down.

### For Humans: What This Means (Think)

It’s a safety cutoff that protects your app from extreme dependency recursion.

## Story Example

A complex service graph grows over time. One day, a bug creates a long chain of nested resolutions. Instead of crashing
with a fatal error, DepthGuardStep throws a `ResolutionException` showing the path so the developer can fix the wiring.

### For Humans: What This Means (Story)

You get a readable error and a path to debug, not a mysterious crash.

## For Dummies

1. Read `KernelContext::$depth`.
2. Compare it to the configured `maxDepth`.
3. If depth is too high, throw `ResolutionException` with the current path.

Common misconceptions:

- “This detects circular dependencies.” It doesn’t; it prevents excessively deep chains (circles are handled by a
  separate step).

### For Humans: What This Means (Dummies)

It’s about “too deep,” not “loops.” It’s a backup safety net.

## How It Works (Technical)

`__invoke` checks whether `KernelContext::$depth` is greater than the configured `maxDepth`. On overflow, it throws
`ResolutionException` containing the max depth and the path.

### For Humans: What This Means (How)

If the container goes past the configured depth, it stops immediately with a descriptive error.

## Architecture Role

Early guard step that protects the pipeline from pathological dependency graphs. Depends only on context depth/path and
the `ResolutionException` type.

### For Humans: What This Means (Role)

It’s a simple safety step that keeps resolution from becoming dangerous.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __construct(int $maxDepth = 64)

#### Technical Explanation (__construct)

Stores the max depth threshold used during depth checks.

##### For Humans: What This Means (__construct)

It sets how deep the container is allowed to go.

##### Parameters (__construct)

- `int $maxDepth`: Maximum allowed depth.

##### Returns (__construct)

- `void`

##### Throws (__construct)

- None.

##### When to Use It (__construct)

Constructed by the container; override if your application legitimately needs deeper chains.

##### Common Mistakes (__construct)

Raising the limit instead of fixing a broken dependency graph.

### Method: __invoke(KernelContext $context)

#### Technical Explanation (__invoke)

Checks the context depth and throws when it exceeds `maxDepth`.

##### For Humans: What This Means (__invoke)

Stops resolution if it goes too deep.

##### Parameters (__invoke)

- `KernelContext $context`: Contains `depth` and `getPath()`.

##### Returns (__invoke)

- `void`

##### Throws (__invoke)

- `ResolutionException` when the depth limit is exceeded.

##### When to Use It (__invoke)

Executed automatically as part of the kernel pipeline.

##### Common Mistakes (__invoke)

Assuming it will catch all recursion issues; combine with circular dependency detection.

## Risks, Trade-offs & Recommended Practices

- **Risk: False alarms**. Very deep but valid graphs might hit the limit; review your design and adjust carefully.
- **Trade-off: Safety vs flexibility**. Higher limits allow deeper graphs but increase risk of stack overflow.
- **Practice: Fix roots**. Treat depth errors as a design smell—refactor dependency graphs rather than just increasing
  limits.

### For Humans: What This Means (Risks)

If you hit this, your dependency graph is probably too complex. Fix the graph first; only raise the limit if you truly
must.

## Related Files & Folders

- `docs_md/Core/Kernel/Steps/index.md`: Steps overview.
- `docs_md/Core/Kernel/Steps/CircularDependencyStep.md`: Loop detection step.
- `docs_md/Features/Core/Exceptions/ResolutionException.md`: Exception thrown here.

### For Humans: What This Means (Related)

Use circular detection for loops, and read the exception docs for how resolution failures are represented.
