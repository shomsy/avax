# PrototypeRegistry

## Quick Summary
- This file implements an in-memory registry for `ServicePrototype` instances.
- It exists to provide a fast “L1 cache” above persistent prototype caches.
- It removes the complexity of repeated prototype loading by keeping hot prototypes in memory.

### For Humans: What This Means
It’s the container’s “short-term memory” for prototypes: faster than disk, but not permanent.

## Terminology (MANDATORY, EXPANSIVE)
- **L1 cache**: The fastest cache layer, usually memory.
  - In this file: `$prototypes` is the in-memory map.
  - Why it matters: hot paths become cheaper.
- **L2 cache**: A slower but persistent cache layer.
  - In this file: referenced conceptually (it can bulk-load from persistent storage).
  - Why it matters: you can reload after restart.
- **LRU (Least Recently Used)**: Eviction policy that removes items not used recently.
  - In this file: access timestamps are tracked in `$accessTimes`.
  - Why it matters: prevents unbounded memory growth.
- **Eviction**: Removing items to enforce size limits.
  - In this file: `enforceMemoryLimit()` is responsible for that.
  - Why it matters: protects long-running processes from memory bloat.

### For Humans: What This Means
It keeps the “most-used blueprints” nearby and throws away old ones when memory gets crowded.

## Think of It
Think of it like your desk vs your filing cabinet. The desk (registry) holds what you’re actively using. The cabinet (persistent cache) holds everything else.

### For Humans: What This Means
Desk is fast but small. Cabinet is slower but bigger.

## Story Example
In a long-running worker, the same services are resolved repeatedly. The registry keeps their prototypes in memory, making repeated operations faster. When too many different classes are used, the registry evicts older ones to keep memory stable.

### For Humans: What This Means
It speeds up the common case without letting memory explode.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. `set()` stores a prototype in memory.
2. `get()` retrieves it and marks it as recently used.
3. If you store too many, old ones get evicted.
4. `bulkLoad()` can load many prototypes from another source.

## How It Works (Technical)
Prototypes are stored in an associative array by class name. Each access updates a monotonic timestamp in `$accessTimes`. When adding entries, the registry enforces a maximum size by evicting least recently used entries.

### For Humans: What This Means
Every time you use a prototype, it gets a “recently used” mark.

## Architecture Role
- Why it lives in Think/Model: it’s part of managing prototype data as a first-class artifact.
- What depends on it: runtime flows and tooling that want fast prototype access.
- What it depends on: `ServicePrototype` as the stored data.
- System-level reasoning: memory caches complement persistent caches in performance-sensitive environments.

### For Humans: What This Means
If your app is long-running, memory caching matters a lot.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Initializes the registry with a maximum size.

##### For Humans: What This Means
You decide how big the “desk” is.

##### Parameters
- `int $maxSize`: Maximum prototypes to keep in memory.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- When building a long-running container runtime.

##### Common Mistakes
- Setting max size too high and wasting memory.

### Method: get(…)

#### Technical Explanation
Retrieves a prototype and updates its access time.

##### For Humans: What This Means
Get the blueprint and mark it as “recently used”.

##### Parameters
- `string $class`

##### Returns
- `ServicePrototype|null`

##### Throws
- No explicit exceptions.

##### When to Use It
- When resolving services repeatedly.

##### Common Mistakes
- Forgetting that `null` means “not in memory”; you may need to load from L2 cache.

### Method: has(…)

#### Technical Explanation
Returns whether the prototype exists in memory.

##### For Humans: What This Means
Quick “is it on the desk?” check.

##### Parameters
- `string $class`

##### Returns
- `bool`

##### Throws
- No explicit exceptions.

##### When to Use It
- Optimistic fast-path checks.

##### Common Mistakes
- Treating `has()` as a guarantee of correctness; prototypes can still be stale.

### Method: remove(…)

#### Technical Explanation
Removes a prototype and its access tracking.

##### For Humans: What This Means
Take one blueprint off the desk.

##### Parameters
- `string $class`

##### Returns
- `bool`

##### Throws
- No explicit exceptions.

##### When to Use It
- Manual invalidation for one class.

##### Common Mistakes
- Removing but forgetting to clear persistent cache too (if needed).

### Method: clear(…)

#### Technical Explanation
Clears all prototypes and resets internal counters.

##### For Humans: What This Means
Empty the desk completely.

##### Parameters
- None.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- Memory cleanup, global invalidation.

##### Common Mistakes
- Clearing too often and losing performance benefits.

### Method: getAllClasses(…)

#### Technical Explanation
Returns all class keys currently stored.

##### For Humans: What This Means
List what’s on the desk.

##### Parameters
- None.

##### Returns
- `string[]`

##### Throws
- No explicit exceptions.

##### When to Use It
- Debugging and reporting.

##### Common Mistakes
- Assuming order implies anything; it’s just array keys.

### Method: getAllPrototypes(…)

#### Technical Explanation
Returns the internal prototypes map.

##### For Humans: What This Means
Get all blueprints currently in memory.

##### Parameters
- None.

##### Returns
- `array<string, ServicePrototype>`

##### Throws
- No explicit exceptions.

##### When to Use It
- Reporting/debugging.

##### Common Mistakes
- Mutating the returned array and expecting it to update internal state safely.

### Method: getStats(…)

#### Technical Explanation
Returns simple stats (count, max size, memory usage, utilization).

##### For Humans: What This Means
How full is the desk?

##### Parameters
- None.

##### Returns
- `array`

##### Throws
- No explicit exceptions.

##### When to Use It
- Monitoring.

##### Common Mistakes
- Treating memory usage serialization as exact; it’s an approximation.

### Method: count(…)

#### Technical Explanation
Returns how many prototypes are stored.

##### For Humans: What This Means
How many blueprints are in memory right now?

##### Parameters
- None.

##### Returns
- `int`

##### Throws
- No explicit exceptions.

##### When to Use It
- Monitoring and sanity checks.

##### Common Mistakes
- Confusing it with “services registered”; it’s only in-memory prototypes.

### Method: bulkLoad(…)

#### Technical Explanation
Loads prototypes for a set of classes using a provided loader callback and stores them in memory.

##### For Humans: What This Means
It’s a warmup helper: “load these blueprints now”.

##### Parameters
- `iterable $classes`: Class names to load.
- `callable $loader`: Function that loads a prototype for a class.

##### Returns
- `int`: Number loaded.

##### Throws
- Depends on loader; registry itself does not throw explicitly.

##### When to Use It
- Boot warmup and bulk operations.

##### Common Mistakes
- Using a loader that throws on missing prototypes instead of returning null.

### Method: set(…)

#### Technical Explanation
Stores a prototype, updates access time, and enforces the memory limit.

##### For Humans: What This Means
Put a blueprint on the desk and make room if needed.

##### Parameters
- `string $class`
- `ServicePrototype $prototype`

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- After loading/creating prototypes you expect to reuse.

##### Common Mistakes
- Storing too many cold prototypes and evicting hot ones; tune max size.

## Risks, Trade-offs & Recommended Practices
- Risk: Memory caches can hide staleness.
  - Why it matters: long-running processes might keep prototypes after code changes.
  - Design stance: treat process restarts and cache invalidation as part of deployment.
  - Recommended practice: clear registry on deploy/reload; tie it to your lifecycle.

### For Humans: What This Means
If you change the code, restart or clear caches—or you’ll keep using old blueprints.

## Related Files & Folders
- `docs_md/Features/Think/Cache/PrototypeCache.md`: Persistent cache layer.
- `docs_md/Features/Think/Model/ServicePrototype.md`: The stored blueprint.

### For Humans: What This Means
Registry is memory. PrototypeCache is persistence. Both store the same kind of blueprint.

