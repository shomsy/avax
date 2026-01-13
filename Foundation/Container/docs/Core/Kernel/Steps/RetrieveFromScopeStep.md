# RetrieveFromScopeStep

## Quick Summary

- Checks `ScopeManager` for an already-resolved instance of the requested service.
- If found, marks the context as resolved and effectively short-circuits the pipeline (terminal step).
- Records resolution strategy metadata (`scope`, cached) for telemetry.

### For Humans: What This Means (Summary)

It’s the “cache hit” step: if the container already has the instance, it returns it immediately and skips expensive
work.

## Terminology (MANDATORY, EXPANSIVE)- **ScopeManager**: Stores instances for singleton/scoped lifetimes.

- **Terminal step**: A pipeline step that can finish resolution early.
- **Cached resolution**: Returning an existing instance rather than constructing a new one.
- **Resolution strategy metadata**: Context metadata describing how the instance was obtained.

### For Humans: What This Means

ScopeManager is your cache; terminal step means “we can stop now”; cached resolution means reuse; metadata records the
decision.

## Think of It

Like checking your fridge before cooking: if the meal is already prepared, you don’t start cooking from scratch.

### For Humans: What This Means (Think)

If it’s already there, don’t rebuild it.

## Story Example

A singleton logger is resolved many times. On the first call, it’s built and stored. On the next call,
`RetrieveFromScopeStep` finds it in `ScopeManager`, sets it on the context, and the pipeline stops early.

### For Humans: What This Means (Story)

Once built, repeated resolutions become fast and consistent.

## For Dummies

1. If you’re doing an injection-target operation, skip (you already have an instance).
2. Ask `ScopeManager` whether the service ID exists.
3. If it exists, fetch it and mark the context as resolved.
4. Record that resolution came from scope and was cached.

Common misconceptions:

- “This caches instances.” It doesn’t; it only retrieves.

### For Humans: What This Means (Dummies)

This step only reads from the cache; another step is responsible for storing.

## How It Works (Technical)

Implements `TerminalKernelStep`. `__invoke` checks scope presence and, if present, calls `resolvedWith` on context and
writes `resolution.strategy` and `resolution.cached` metadata.

### For Humans: What This Means (How)

If it finds the instance, it puts it in the context and flags the resolution as a cache hit.

## Architecture Role

Early performance step for singleton/scoped services. Depends on `ScopeManager` and the context’s resolved markers;
allows the pipeline to short-circuit before construction and injection.

### For Humans: What This Means (Role)

It’s the fast path that avoids work when caching applies.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __construct(ScopeManager $scopeManager)

#### Technical Explanation (__construct)

Stores the scope manager used to check and retrieve cached instances.

##### For Humans: What This Means (__construct)

Keeps the cache storage handle.

##### Parameters (__construct)

- `ScopeManager $scopeManager`: Instance storage.

##### Returns (__construct)

- `void`

##### Throws (__construct)

- None.

##### When to Use It (__construct)

Constructed by the container.

##### Common Mistakes (__construct)

Using a scope manager that doesn’t separate scopes correctly.

### Method: __invoke(KernelContext $context)

#### Technical Explanation (__invoke)

Skips injection-target operations, checks `ScopeManager` for an instance, resolves context with that instance when
present, and sets resolution metadata.

##### For Humans: What This Means (__invoke)

If the instance exists, it returns it immediately.

##### Parameters (__invoke)

- `KernelContext $context`: Contains service ID and metadata.

##### Returns (__invoke)

- `void`

##### Throws (__invoke)

- None.

##### When to Use It (__invoke)

Executed at the start of resolution when caches should be checked first.

##### Common Mistakes (__invoke)

Expecting it to cache results; that happens in lifecycle store steps.

## Risks, Trade-offs & Recommended Practices

- **Risk: Stale instances**. Cached objects can become stale if they hold mutable state; choose lifetimes carefully.
- **Trade-off: Speed vs freshness**. Cache hits are fast but reuse state; transient services avoid this.
- **Practice: Keep cached services stable**. Prefer caching for stateless or intentionally shared services.

### For Humans: What This Means (Risks)

Caching is great for performance, but shared objects can leak state—only cache what you truly want shared.

## Related Files & Folders

- `docs_md/Core/Kernel/Steps/index.md`: Steps overview.
- `docs_md/Core/Kernel/Steps/StoreLifecycleStep.md`: Stores instances into lifecycle caches.
- `docs_md/Features/Operate/Scope/ScopeManager.md`: Storage used here.
- `docs_md/Core/Kernel/Contracts/TerminalKernelStep.md`: Terminal marker.

### For Humans: What This Means (Related)

This is the read-side of caching; StoreLifecycle is the write-side; ScopeManager is the storage; TerminalKernelStep
explains the early-stop behavior.
