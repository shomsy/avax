# StoreLifecycleStep

## Quick Summary

- Stores the resolved instance according to its lifecycle policy (singleton, scoped, transient).
- Uses `LifecycleResolver` to choose a `LifecycleStrategy` based on the current `ServiceDefinition`.
- Writes storage metadata (`storage.*`) to the context for diagnostics.

### For Humans: What This Means (Summary)

It’s the step that decides whether the container should keep what it just built, and where to keep it, so future
resolutions can reuse it when appropriate.

## Terminology (MANDATORY, EXPANSIVE)- **Lifecycle

**: The rule about how long an instance should live (singleton/scoped/transient).

- **LifecycleResolver**: Component that maps a definition’s lifetime to a strategy implementation.
- **LifecycleStrategy**: Strategy that knows how to store/retrieve/clear instances for a lifecycle.
- **Storage metadata**: Context metadata under `storage.*` describing what happened.

### For Humans: What This Means

Lifecycle tells you if it’s reusable; resolver picks the right policy; strategy does the storing; metadata records the
outcome.

## Think of It

Like deciding whether to save leftovers: some meals you keep for later (singleton/scoped), some you don’t (transient).
This step is the “save or toss” decision.

### For Humans: What This Means (Think)

It’s the “should we keep this instance for later?” step.

## Story Example

A singleton database connection is resolved. After construction, this step resolves the singleton strategy and stores
the instance in scope storage. Next time, `RetrieveFromScopeStep` can return it instantly.

### For Humans: What This Means (Story)

It’s the write side of caching: it stores instances so future resolutions can be fast.

## For Dummies

1. If this is an injection-target operation, skip and mark storage as skipped.
2. If there’s no instance, there’s nothing to store.
3. Get the current definition from context metadata.
4. Ask `LifecycleResolver` for the right strategy.
5. Call `strategy->store(serviceId, instance)`.
6. Record lifecycle and storage location into context metadata.

Common misconceptions:

- “This decides the lifetime.” It doesn’t; the definition’s lifetime does.
- “Transient means error.” No—transient means “don’t store.”

### For Humans: What This Means (Dummies)

The lifetime comes from definitions; transient is a normal choice, not a failure.

## How It Works (Technical)

`__invoke` skips injection-target operations, returns if no instance exists, resolves the lifecycle strategy from
`LifecycleResolver`, calls `store`, infers storage location by strategy type, and writes `storage.lifecycle`,
`storage.location`, `storage.managed`, and timestamps.

### For Humans: What This Means (How)

It picks the right storage policy, stores the instance, and leaves a note about what it did.

## Architecture Role

Late pipeline step that persists resolution outcomes into caches/scopes. Depends on `LifecycleResolver` and the
lifecycle strategy implementations; complements `RetrieveFromScopeStep` (read side).

### For Humans: What This Means (Role)

This is the caching write step that makes the caching read step possible.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __construct(LifecycleResolver $lifecycleResolver)

#### Technical Explanation (__construct)

Stores the resolver used to map service definitions to lifecycle strategies.

##### For Humans: What This Means (__construct)

Keeps the policy picker used to decide where to store instances.

##### Parameters (__construct)

- `LifecycleResolver $lifecycleResolver`: Lifecycle resolver.

##### Returns (__construct)

- `void`

##### Throws (__construct)

- None.

##### When to Use It (__construct)

Constructed during kernel assembly.

##### Common Mistakes (__construct)

Using a resolver that doesn’t match your lifetime enum or definition format.

### Method: __invoke(KernelContext $context)

#### Technical Explanation (__invoke)

Skips injection-target operations, stores resolved instances via a lifecycle strategy, and records storage metadata.

##### For Humans: What This Means (__invoke)

If there’s an instance and it’s not an inject-only run, it stores the instance in the right place.

##### Parameters (__invoke)

- `KernelContext $context`: Contains instance and definition metadata.

##### Returns (__invoke)

- `void`

##### Throws (__invoke)

- Strategy-specific storage exceptions.

##### When to Use It (__invoke)

Executed after instance creation and extenders.

##### Common Mistakes (__invoke)

Assuming it stores when the instance is null; forgetting to set definition metadata earlier.

## Risks, Trade-offs & Recommended Practices

- **Risk: Stale shared state**. Storing mutable instances can leak state; choose lifetimes carefully.
- **Risk: Wrong lifetime defaults**. Auto-defined services defaulting to transient may cause extra construction; verify
  defaults.
- **Practice: Instrument storage**. Use `storage.*` metadata to monitor caching effectiveness.

### For Humans: What This Means (Risks)

Caching can be great, but shared state can bite you—pick lifetimes intentionally and monitor what’s happening.

## Related Files & Folders

- `docs_md/Core/Kernel/Steps/index.md`: Steps overview.
- `docs_md/Core/Kernel/LifecycleResolver.md`: Strategy resolver used here.
- `docs_md/Core/Kernel/Contracts/LifecycleStrategy.md`: Strategy contract.
- `docs_md/Core/Kernel/Steps/RetrieveFromScopeStep.md`: Cache read step.

### For Humans: What This Means (Related)

To understand storage, read the resolver and strategy docs, and see RetrieveFromScopeStep for the read side.
