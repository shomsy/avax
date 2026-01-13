# SingletonLifecycleStrategy

## Quick Summary

Caches resolved services globally using `ScopeManager`, enabling reuse across the application. It exists to implement
the singleton lifetime policy.

### For Humans: What This Means (Summary)

It keeps one shared instance for a service so everyone gets the same object.

## Terminology (MANDATORY, EXPANSIVE)- **Singleton**: One shared instance for the entire app lifetime.

- **ScopeManager**: Storage mechanism used to store and retrieve instances.
- **Store/has/retrieve/clear**: Lifecycle operations for caching.

### For Humans: What This Means

Singleton means one instance; ScopeManager is the locker; store/has/retrieve/clear are the actions on that locker.

## Think of It

Like a communal water cooler: it’s filled once and everyone drinks from it. ScopeManager is the cooler, and this
strategy decides to use it.

### For Humans: What This Means (Think)

One shared resource, managed centrally.

## Story Example

Without this strategy, each resolution might build a new logger. With singleton, the first resolution stores the logger
in `ScopeManager`; subsequent resolutions reuse it, ensuring consistent state and lower cost.

### For Humans: What This Means (Story)

You build the logger once and reuse it everywhere automatically.

## For Dummies

- On first creation, `store` saves the instance in ScopeManager.
- `has` checks if it’s already stored.
- `retrieve` returns the stored instance.
- `clear` is a no-op here because singletons live for app lifetime.

Common misconceptions: clear doesn’t reset singletons here; ScopeManager handles global storage, not per-request scopes.

### For Humans: What This Means (Dummies)

It keeps things forever (until process ends); it doesn’t actively clear them.

## How It Works (Technical)

Delegates `store/has/retrieve` to `ScopeManager` global storage; `clear` does nothing. Implements `LifecycleStrategy`.

### For Humans: What This Means (How)

It tells ScopeManager to save and fetch instances; clearing is intentionally empty.

## Architecture Role

Implements singleton caching for the kernel’s lifecycle handling. Depends on `ScopeManager`; used when service lifetime
is singleton.

### For Humans: What This Means (Role)

It’s the policy the kernel uses when a service is marked singleton.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __construct(ScopeManager $scopeManager)

#### Technical Explanation (__construct)

Injects ScopeManager used for storing singleton instances.

##### For Humans: What This Means (__construct)

Keeps a handle to the storage that holds singletons.

##### Parameters (__construct)

- `ScopeManager $scopeManager`: Global scope storage.

##### Returns (__construct)

- `void`

##### Throws (__construct)

- None.

##### When to Use It (__construct)

Always constructed by the container when setting up singleton strategy.

##### Common Mistakes (__construct)

Passing a ScopeManager not configured for global storage.

### Method: store(string $abstract, mixed $instance): void

#### Technical Explanation (store)

Stores the instance under the service ID in ScopeManager.

##### For Humans: What This Means (store)

Puts the singleton into storage for reuse.

##### Parameters (store)

- `string $abstract`: Service ID.
- `mixed $instance`: Instance to cache.

##### Returns (store)

- `void`

##### Throws (store)

- Storage errors from ScopeManager.

##### When to Use It (store)

Called after constructing a singleton instance.

##### Common Mistakes (store)

Skipping store, leading to duplicate singleton creation.

### Method: has(string $abstract): bool

#### Technical Explanation (has)

Checks ScopeManager for an existing instance.

##### For Humans: What This Means (has)

Asks if the singleton is already stored.

##### Parameters (has)

- `string $abstract`: Service ID.

##### Returns (has)

- `bool`: True if cached.

##### Throws (has)

- ScopeManager errors.

##### When to Use It (has)

Before creating a singleton to avoid duplicates.

##### Common Mistakes (has)

Not using this check and rebuilding unnecessarily.

### Method: retrieve(string $abstract): mixed

#### Technical Explanation (retrieve)

Returns stored instance from ScopeManager.

##### For Humans: What This Means (retrieve)

Gets the cached singleton if it exists.

##### Parameters (retrieve)

- `string $abstract`: Service ID.

##### Returns (retrieve)

- `mixed`: Cached instance or null.

##### Throws (retrieve)

- ScopeManager errors.

##### When to Use It (retrieve)

When resolving singleton services.

##### Common Mistakes (retrieve)

Assuming non-null without checking `has`.

### Method: clear(): void

#### Technical Explanation (clear)

No-op; singletons persist for app lifetime.

##### For Humans: What This Means (clear)

Does nothing—singletons stay until shutdown.

##### Parameters (clear)

- None.

##### Returns (clear)

- `void`

##### Throws (clear)

- None.

##### When to Use It (clear)

Not typically used; provided for interface compliance.

##### Common Mistakes (clear)

Expecting this to reset singletons.

## Risks, Trade-offs & Recommended Practices

- **Risk: Stale singletons**. Changes require process restart; avoid mutable global state.
- **Trade-off: Performance vs isolation**. Singletons are fast but shared; ensure thread/process safety.
- **Practice: Keep singletons stateless or idempotent**. Minimize side effects and mutable state.

### For Humans: What This Means (Risks)

Shared instances are fast but can get stale; keep them simple and restart if needed.

## Related Files & Folders

- `docs_md/Core/Kernel/Strategies/index.md`: Strategies overview.
- `docs_md/Core/Kernel/Contracts/LifecycleStrategy.md`: Contract implemented here.
- `docs_md/Features/Operate/Scope/ScopeManager.md`: Storage backing this strategy.

### For Humans: What This Means (Related)

See the overview for context, the contract for rules, and ScopeManager to understand storage.
