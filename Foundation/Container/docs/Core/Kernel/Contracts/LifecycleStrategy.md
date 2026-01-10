# LifecycleStrategy

## Quick Summary
Defines how service instances are stored, checked, retrieved, and cleared according to their lifecycle (singleton, scoped, transient). It exists to standardize lifecycle handling across strategies.

### For Humans: What This Means (Summary)
It’s the rulebook for storing and fetching services based on lifetime—one place to say how singletons, scoped, or transient instances behave.

## Terminology (MANDATORY, EXPANSIVE)- **Lifecycle strategy**: Policy for caching or not caching instances (singleton/scoped/transient).
- **Store**: Persisting an instance under a service ID.
- **Has**: Checking if an instance is available for reuse.
- **Retrieve**: Getting a stored instance.
- **Clear**: Removing stored instances (e.g., end of scope).

### For Humans: What This Means
Strategy decides whether to keep an instance; store puts it away, has checks if it’s there, retrieve gets it, clear wipes it.

## Think of It
Like different locker policies at a gym: permanent lockers (singleton), day-use lockers (scoped), and no locker (transient). This interface defines the rules each policy must support.

### For Humans: What This Means (Think)
You pick the locker policy, but every policy must offer the same basic actions: put away, check, get, and empty.

## Story Example
Before this interface, lifecycle handling varied and caches were inconsistent. With it, singleton/scoped/transient strategies implement the same methods, so the kernel can swap strategies without changing pipeline logic.

### For Humans: What This Means (Story)
The kernel can treat all lifecycles the same way, just plugging in the right policy.

## For Dummies
- Implementations decide whether to cache instances.
- `store` saves an instance (or no-op for transient).
- `has` checks availability.
- `retrieve` returns the cached instance (or null).
- `clear` empties stored instances (useful for scopes).

Common misconceptions: it doesn’t choose the strategy; it just defines what any strategy must do.

### For Humans: What This Means (Dummies)
This is the required behavior; you still pick which strategy to use for each service.

## How It Works (Technical)
Declares four methods: `store`, `has`, `retrieve`, and `clear`. Implementations provide caching or no-op behavior according to lifecycle semantics.

### For Humans: What This Means (How)
Every lifecycle policy must implement these four actions; how they behave depends on the policy.

## Architecture Role
Part of Contracts to abstract lifecycle behavior. Resolution pipeline depends on it to manage caching uniformly; concrete strategies (singleton, scoped, transient) implement it.

### For Humans: What This Means (Role)
It lets the pipeline handle lifecycles generically while plugging in specific policies.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: store(string $abstract, mixed $instance): void

#### Technical Explanation (store)
Stores an instance keyed by service identifier according to lifecycle rules (cache or no-op).

##### For Humans: What This Means (store)
Puts the instance away for later if the policy allows caching.

##### Parameters (store)
- `string $abstract`: Service ID.
- `mixed $instance`: Instance to store.

##### Returns (store)
- `void`

##### Throws (store)
- Implementation-specific exceptions on storage errors.

##### When to Use It (store)
Called by the pipeline after creating an instance.

##### Common Mistakes (store)
Assuming transient strategies store anything—they don’t.

### Method: has(string $abstract): bool

#### Technical Explanation (has)
Checks whether an instance is available under the given service ID per strategy rules.

##### For Humans: What This Means (has)
Asks, “Do we already have this service stored?”

##### Parameters (has)
- `string $abstract`: Service ID.

##### Returns (has)
- `bool`: True if instance can be reused.

##### Throws (has)
- Implementation-specific exceptions on state errors.

##### When to Use It (has)
Before resolving/constructing to see if reuse is possible.

##### Common Mistakes (has)
Not aligning with scoped boundaries, leading to false positives/negatives.

### Method: retrieve(string $abstract): mixed

#### Technical Explanation (retrieve)
Returns the stored instance or null based on lifecycle policy.

##### For Humans: What This Means (retrieve)
Gets the cached instance if it exists; otherwise returns nothing.

##### Parameters (retrieve)
- `string $abstract`: Service ID.

##### Returns (retrieve)
- `mixed`: Cached instance or null.

##### Throws (retrieve)
- Implementation-specific exceptions on retrieval errors.

##### When to Use It (retrieve)
After `has` indicates availability, or optimistically before construction.

##### Common Mistakes (retrieve)
Expecting non-null for transient strategies; ignoring potential stale instances in mis-scoped caches.

### Method: clear(): void

#### Technical Explanation (clear)
Removes stored instances according to lifecycle rules; often used at scope end or cache reset.

##### For Humans: What This Means (clear)
Empties the cache for this lifecycle.

##### Parameters (clear)
- None.

##### Returns (clear)
- `void`

##### Throws (clear)
- Implementation-specific exceptions on cleanup failures.

##### When to Use It (clear)
Scope teardown, cache resets, or shutdown.

##### Common Mistakes (clear)
Forgetting to clear scoped caches, leading to leaks.

## Risks, Trade-offs & Recommended Practices
- **Risk: Stale instances**. Wrong strategy choice can leak outdated objects; choose lifecycle carefully.
- **Risk: Scope leaks**. Not clearing scoped caches causes cross-request bleed; ensure proper clear calls.
- **Practice: Keep strategies simple**. Avoid mixing concerns; strategies should focus on lifecycle storage only.
- **Practice: Instrument caches**. Add metrics/logging to detect misuse.

### For Humans: What This Means (Risks)
Pick the right lifecycle, clean up scopes, and keep strategy code focused so you don’t leak objects.

## Related Files & Folders
- `docs_md/Core/Kernel/Contracts/index.md`: Contract overview.
- `docs_md/Core/Kernel/Strategies/SingletonLifecycleStrategy.md`: Example implementation.
- `docs_md/Core/Kernel/Strategies/ScopedLifecycleStrategy.md`: Scoped implementation.
- `docs_md/Core/Kernel/Strategies/TransientLifecycleStrategy.md`: Transient implementation.

### For Humans: What This Means (Related)
See the implementations to understand how each policy behaves in practice.
