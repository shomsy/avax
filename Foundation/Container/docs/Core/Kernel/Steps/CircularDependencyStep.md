# CircularDependencyStep

## Quick Summary
- Monitors the context parent chain to spot if the current service ID appears earlier, indicating a dependency cycle.
- Throws `ResolutionException` when a cycle is detected, preventing infinite recursion.
- Runs immediately after context creation before the pipeline continues.

### For Humans: What This Means (Summary)
It acts as the guard that throws errors before the container loops endlessly when services depend on each other in circles.

## Terminology (MANDATORY, EXPANSIVE)- **Dependency cycle**: A scenario where service A depends on B and B depends on A (directly or via longer chains).
- **KernelContext parent chain**: Linked sequence of contexts representing nested resolutions.
- **ResolutionException**: Exception thrown to halt resolution when a cycle is found.

### For Humans: What This Means
Dependency cycles trap the container; the parent chain tracks the stack, and this step throws when it spots a loop.

## Think of It
It’s a spotter in a maze who checks whether you already visited the same room; if so, it raises the alarm before you keep walking in circles.

### For Humans: What This Means (Think)
It prevents you from repeating rooms indefinitely.

## Story Example
Without this step, services with mutual dependencies triggered fatal stack overflows. Now, the step sees the repeat ID, throws `ResolutionException`, and surfaces a clear circular dependency message.

### For Humans: What This Means (Story)
You get a helpful error instead of a crash when your services depend on each other.

## For Dummies
1. Look at the parent context (the previous service on the stack).
2. Ask whether that parent already contains the current service ID.
3. If yes, throw a `ResolutionException` with the full resolution path.
4. Otherwise, allow the pipeline to continue.

### For Humans: What This Means (Dummies)
It just checks the stack; if you’re already resolving the same ID, abort with a descriptive exception.

## How It Works (Technical)
`__invoke` checks if a parent exists and if it contains the current service ID via `KernelContext::contains`. Detecting a cycle triggers `ResolutionException` with the resolution path.

### For Humans: What This Means (How)
It stops the pipeline as soon as it sees a repeat.

## Architecture Role
Early pipeline step enforcing guard rails. Depends on `KernelContext` for parent tracking, throws `ResolutionException`, and prevents the pipeline from continuing into recursive loops.

### For Humans: What This Means (Role)
It’s the safety net before the container goes into recursion.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __invoke(KernelContext $context): void

#### Technical Explanation (__invoke)
Checks the parent context for the current service ID. If the parent chain already contains the ID, throws `ResolutionException` with the path; otherwise, does nothing.

##### For Humans: What This Means (__invoke)
Looks at the stack, and if it finds your service repeated, it raises an error.

##### Parameters (__invoke)
- `KernelContext $context`: The resolution state with parent references.

##### Returns (__invoke)
- `void`

##### Throws (__invoke)
- `ResolutionException` when a cycle is detected.

##### When to Use It (__invoke)
Always invoked near the start of resolution.

##### Common Mistakes (__invoke)
- Expecting it to detect cycles involving unrelated service IDs; it only follows the current resolution path.

## Risks, Trade-offs & Recommended Practices
- **Risk: False positives**. Rarely, legitimate re-entrant flows may resemble cycles; ensure your design intentionally avoids true loops.
- **Practice: Trace paths**. Use the exception message to understand where the loop formed.

### For Humans: What This Means (Risks)
Treat loops as bugs; the exception message shows the chain so you can fix the wiring.

## Related Files & Folders
- `docs_md/Core/Kernel/Steps/index.md`: Steps overview.
- `docs_md/Core/Kernel/Contracts/KernelContext.md`: Provides `contains` and path helpers.
- `docs_md/Features/Core/Exceptions/ResolutionException.md`: Exception thrown here.

### For Humans: What This Means (Related)
See the context utilities and exception docs to understand how this detection works.
