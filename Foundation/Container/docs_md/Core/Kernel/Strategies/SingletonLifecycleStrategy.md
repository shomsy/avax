# SingletonLifecycleStrategy

## Quick Summary
Caches resolved services globally using `ScopeManager`, enabling reuse across the application. It exists to implement the singleton lifetime policy.

### For Humans: What This Means
It keeps one shared instance for a service so everyone gets the same object.

## Terminology
- **Singleton**: One shared instance for the entire app lifetime.
- **ScopeManager**: Storage mechanism used to store and retrieve instances.
- **Store/has/retrieve/clear**: Lifecycle operations for caching.

### For Humans: What This Means
Singleton means one instance; ScopeManager is the locker; store/has/retrieve/clear are the actions on that locker.

## Think of It
Like a communal water cooler: it’s filled once and everyone drinks from it. ScopeManager is the cooler, and this strategy decides to use it.

### For Humans: What This Means
One shared resource, managed centrally.

## Story Example
Without this strategy, each resolution might build a new logger. With singleton, the first resolution stores the logger in `ScopeManager`; subsequent resolutions reuse it, ensuring consistent state and lower cost.

### For Humans: What This Means
You build the logger once and reuse it everywhere automatically.

## For Dummies
- On first creation, `store` saves the instance in ScopeManager.
- `has` checks if it’s already stored.
- `retrieve` returns the stored instance.
- `clear` is a no-op here because singletons live for app lifetime.

Common misconceptions: clear doesn’t reset singletons here; ScopeManager handles global storage, not per-request scopes.

### For Humans: What This Means
It keeps things forever (until process ends); it doesn’t actively clear them.

## How It Works (Technical)
Delegates `store/has/retrieve` to `ScopeManager` global storage; `clear` does nothing. Implements `LifecycleStrategy`.

### For Humans: What This Means
It tells ScopeManager to save and fetch instances; clearing is intentionally empty.

## Architecture Role
Implements singleton caching for the kernel’s lifecycle handling. Depends on `ScopeManager`; used when service lifetime is singleton.

### For Humans: What This Means
It’s the policy the kernel uses when a service is marked singleton.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(ScopeManager $scopeManager)

#### Technical Explanation
Injects ScopeManager used for storing singleton instances.

##### For Humans: What This Means
Keeps a handle to the storage that holds singletons.

##### Parameters
- `ScopeManager $scopeManager`: Global scope storage.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Always constructed by the container when setting up singleton strategy.

##### Common Mistakes
Passing a ScopeManager not configured for global storage.

### Method: store(string $abstract, mixed $instance): void

#### Technical Explanation
Stores the instance under the service ID in ScopeManager.

##### For Humans: What This Means
Puts the singleton into storage for reuse.

##### Parameters
- `string $abstract`: Service ID.
- `mixed $instance`: Instance to cache.

##### Returns
- `void`

##### Throws
- Storage errors from ScopeManager.

##### When to Use It
Called after constructing a singleton instance.

##### Common Mistakes
Skipping store, leading to duplicate singleton creation.

### Method: has(string $abstract): bool

#### Technical Explanation
Checks ScopeManager for an existing instance.

##### For Humans: What This Means
Asks if the singleton is already stored.

##### Parameters
- `string $abstract`: Service ID.

##### Returns
- `bool`: True if cached.

##### Throws
- ScopeManager errors.

##### When to Use It
Before creating a singleton to avoid duplicates.

##### Common Mistakes
Not using this check and rebuilding unnecessarily.

### Method: retrieve(string $abstract): mixed

#### Technical Explanation
Returns stored instance from ScopeManager.

##### For Humans: What This Means
Gets the cached singleton if it exists.

##### Parameters
- `string $abstract`: Service ID.

##### Returns
- `mixed`: Cached instance or null.

##### Throws
- ScopeManager errors.

##### When to Use It
When resolving singleton services.

##### Common Mistakes
Assuming non-null without checking `has`.

### Method: clear(): void

#### Technical Explanation
No-op; singletons persist for app lifetime.

##### For Humans: What This Means
Does nothing—singletons stay until shutdown.

##### Parameters
- None.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Not typically used; provided for interface compliance.

##### Common Mistakes
Expecting this to reset singletons.

## Risks, Trade-offs & Recommended Practices
- **Risk: Stale singletons**. Changes require process restart; avoid mutable global state.
- **Trade-off: Performance vs isolation**. Singletons are fast but shared; ensure thread/process safety.
- **Practice: Keep singletons stateless or idempotent**. Minimize side effects and mutable state.

### For Humans: What This Means
Shared instances are fast but can get stale; keep them simple and restart if needed.

## Related Files & Folders
- `docs_md/Core/Kernel/Strategies/index.md`: Strategies overview.
- `docs_md/Core/Kernel/Contracts/LifecycleStrategy.md`: Contract implemented here.
- `docs_md/Features/Operate/Scope/ScopeManager.md`: Storage backing this strategy.

### For Humans: What This Means
See the overview for context, the contract for rules, and ScopeManager to understand storage.
