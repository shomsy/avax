# PrototypeCache

## Quick Summary
- This file defines the interface for caching `ServicePrototype` analysis results.
- It exists so the container can swap caching backends without changing core logic.
- It removes repeated reflection cost by enabling “analyze once, reuse many times”.

### For Humans: What This Means
It’s the container’s “save and load the blueprint” contract.

## Terminology (MANDATORY, EXPANSIVE)
- **Prototype cache**: A storage layer for analyzed prototypes.
  - In this file: an interface with get/set/has/delete/clear/count operations.
  - Why it matters: caching is optional, but the container needs a stable API to use it.
- **ServicePrototype**: The stored blueprint for a service’s injection needs.
  - In this file: the value being cached.
  - Why it matters: it replaces reflection at runtime.
- **Cache key**: The identifier used to store/retrieve entries.
  - In this file: a class name string (`$class`).
  - Why it matters: it must be stable and deterministic.
- **Invalidation**: Removing stale cache entries.
  - In this file: `delete()` and `clear()`.
  - Why it matters: definitions and classes can change.

### For Humans: What This Means
If you change your classes, you need a way to clear the saved “plans” so the container doesn’t use old information.

## Think of It
Think of it like saving a route in your GPS. `PrototypeCache` is the buttons: “save route”, “load route”, “delete route”, “clear all”.

### For Humans: What This Means
It’s convenience plus performance.

## Story Example
In development, you skip caching and just reflect every time (slow but simple). In production, you use a file cache to store prototypes. Startup time includes analysis once, and then runtime resolutions avoid reflection and become faster and more predictable.

### For Humans: What This Means
It’s the difference between “think every time” and “remember what you learned”.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

- `get()` returns the blueprint if it’s cached.
- `set()` stores the blueprint.
- `has()` checks if it exists.
- `delete()` removes one.
- `clear()` removes all.

## How It Works (Technical)
This is an interface. Implementations decide serialization format, atomicity, and error handling. The container can treat them uniformly because the method set is stable.

### For Humans: What This Means
Different storage engines, same buttons.

## Architecture Role
- Why it lives here: it’s the caching contract for Think-phase outputs.
- What depends on it: prototype factories, runtime resolver, tooling.
- What it depends on: `ServicePrototype`.
- System-level reasoning: it enables production performance without coupling core code to one cache technology.

### For Humans: What This Means
Your container doesn’t care if the blueprint is saved in a file, RAM, or Redis—as long as it can load it.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: get(…)

#### Technical Explanation
Returns a cached prototype for a class name or `null` if missing.

##### For Humans: What This Means
“Do we already know the blueprint for this class?”

##### Parameters
- `string $class`: Cache key (class name).

##### Returns
- `ServicePrototype|null`

##### Throws
- Depends on implementation.

##### When to Use It
- Before running reflection analysis.

##### Common Mistakes
- Assuming a cached prototype is always valid after code changes.

### Method: set(…)

#### Technical Explanation
Stores a prototype under the class name key.

##### For Humans: What This Means
Save the blueprint so you don’t re-analyze later.

##### Parameters
- `string $class`
- `ServicePrototype $prototype`

##### Returns
- Returns nothing.

##### Throws
- Depends on implementation (I/O errors, serialization errors).

##### When to Use It
- After successfully analyzing and validating a prototype.

##### Common Mistakes
- Caching prototypes that contain non-serializable data.

### Method: has(…)

#### Technical Explanation
Returns whether a prototype exists for the key.

##### For Humans: What This Means
Quick “is it cached?” check.

##### Parameters
- `string $class`

##### Returns
- `bool`

##### Throws
- Depends on implementation.

##### When to Use It
- Fast pre-check without loading.

##### Common Mistakes
- Using `has()` as a guarantee that `get()` won’t fail; implementations may differ.

### Method: delete(…)

#### Technical Explanation
Removes a cached prototype for the given key.

##### For Humans: What This Means
Remove one saved blueprint.

##### Parameters
- `string $class`

##### Returns
- `bool`: whether it was removed.

##### Throws
- Depends on implementation.

##### When to Use It
- After detecting a stale/invalid prototype.

##### Common Mistakes
- Forgetting to delete after refactoring class names.

### Method: clear(…)

#### Technical Explanation
Clears all cached prototypes.

##### For Humans: What This Means
Delete all saved blueprints.

##### Parameters
- None.

##### Returns
- Returns nothing.

##### Throws
- Depends on implementation.

##### When to Use It
- During development, deployment, or after major refactors.

##### Common Mistakes
- Clearing in production too often, causing cold-start reflection spikes.

### Method: count(…)

#### Technical Explanation
Returns the number of cached prototypes.

##### For Humans: What This Means
It tells you how many blueprints are saved.

##### Parameters
- None.

##### Returns
- `int`

##### Throws
- Depends on implementation.

##### When to Use It
- Diagnostics and monitoring.

##### Common Mistakes
- Treating the number as “number of services”; it’s only prototypes cached.

### Method: prototypeExists(…)

#### Technical Explanation
Existence check intended to avoid deserialization overhead.

##### For Humans: What This Means
“Is there a file/entry without loading it?”

##### Parameters
- `string $class`

##### Returns
- `bool`

##### Throws
- Depends on implementation.

##### When to Use It
- Monitoring, batch checks, pre-flight validations.

##### Common Mistakes
- Duplicating logic with `has()` without understanding implementation differences.

## Risks, Trade-offs & Recommended Practices
- Risk: Stale prototypes after refactors.
  - Why it matters: injection plans can become wrong.
  - Design stance: caches must be invalidatable.
  - Recommended practice: clear cache on deploy or when class signatures change.

### For Humans: What This Means
Caching is great until it remembers yesterday’s reality. Clear it when reality changes.

## Related Files & Folders
- `docs_md/Features/Think/Cache/FilePrototypeCache.md`: A concrete implementation.
- `docs_md/Features/Think/Model/ServicePrototype.md`: The cached blueprint.

### For Humans: What This Means
To understand what’s inside the cache, look at `ServicePrototype`.

