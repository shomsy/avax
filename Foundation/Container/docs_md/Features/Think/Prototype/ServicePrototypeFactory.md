# ServicePrototypeFactory

## Quick Summary
- This file implements a factory that creates `ServicePrototype` objects and caches them.
- It exists so “create prototype (cache first)” logic lives in one place.
- It removes repeated reflection cost by making caching the default path.

### For Humans: What This Means
It’s the part that says: “If we already know the blueprint, use it. If not, learn it and remember it.”

## Terminology (MANDATORY, EXPANSIVE)
- **Factory**: Creates prototypes on demand.
  - In this file: `createFor()` is the main entry point.
  - Why it matters: consumers don’t have to know about caching or analysis.
- **Cache**: Persistent storage for prototypes.
  - In this file: a `PrototypeCache` is consulted first.
  - Why it matters: cache hits avoid reflection.
- **Analyzer**: Reflection-based analysis engine.
  - In this file: `PrototypeAnalyzer` produces prototypes on cache miss.
  - Why it matters: it’s the authoritative source when the cache is empty.
- **Cache miss**: When the prototype isn’t present.
  - In this file: triggers analysis and subsequent caching.
  - Why it matters: it’s where the expensive work happens.

### For Humans: What This Means
This factory is the memory-aware assistant: “I’ll look it up before I figure it out again.”

## Think of It
Think of it like a chef who keeps recipe cards. If the card exists, cook immediately. If not, write the recipe down once, then cook.

### For Humans: What This Means
You stop reinventing the same recipe.

## Story Example
Your app resolves many services repeatedly. The first time, this factory runs analysis and caches prototypes. Every subsequent time, it returns cached prototypes, making resolution cheaper and more stable.

### For Humans: What This Means
First run pays the “learning cost”, later runs get speed.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Ask for a prototype via `createFor()`.
2. It checks cache.
3. If cache has it, return it.
4. If not, analyze the class and cache the result.

## How It Works (Technical)
The class is `final readonly` and holds:
- `PrototypeCache $cache`
- `PrototypeAnalyzer $analyzer`

`createFor()` is cache-first, then analysis, then cache write. `hasPrototype()` delegates to cache. Accessors expose the dependencies for tools and extensions.

### For Humans: What This Means
It’s a small orchestrator: cache lookup → analyze if needed → save → return.

## Architecture Role
- Why it lives here: it’s Think/Prototype orchestration.
- What depends on it: runtime resolution flows needing prototypes.
- What it depends on: `PrototypeCache` and `PrototypeAnalyzer`.
- System-level reasoning: consistent caching policy prevents scattered, inconsistent performance.

### For Humans: What This Means
One factory means everyone gets prototypes the same way, which reduces surprises.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Stores the cache and analyzer used by the factory.

##### For Humans: What This Means
You’re giving it both “memory” and “thinking power”.

##### Parameters
- `PrototypeCache $cache`
- `PrototypeAnalyzer $analyzer`

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- During container boot/wiring.

##### Common Mistakes
- Passing a cache with a directory that isn’t writable (for file cache implementations).

### Method: getCache(…)

#### Technical Explanation
Returns the cache used by this factory.

##### For Humans: What This Means
It lets you inspect or reuse the factory’s cache.

##### Parameters
- None.

##### Returns
- `PrototypeCache`

##### Throws
- No explicit exceptions.

##### When to Use It
- Diagnostics and tooling.

##### Common Mistakes
- Mutating cache config indirectly and expecting the factory to reflect it.

### Method: getAnalyzer(…)

#### Technical Explanation
Returns the analyzer used to build prototypes.

##### For Humans: What This Means
It gives you access to the same reflection logic the factory uses.

##### Parameters
- None.

##### Returns
- `PrototypeAnalyzer`

##### Throws
- No explicit exceptions.

##### When to Use It
- Tooling and extensions.

##### Common Mistakes
- Treating it as a “builder API”; it’s a dependency.

### Method: createFor(…)

#### Technical Explanation
Returns a prototype for a class, using cache-first strategy.

##### For Humans: What This Means
Get the blueprint—fast if possible, smart if needed.

##### Parameters
- `string $class`

##### Returns
- `ServicePrototype`

##### Throws
- Depends on analyzer/cache behavior (analysis may throw, cache may throw on write).

##### When to Use It
- When runtime or tooling needs a prototype for a class.

##### Common Mistakes
- Calling it with non-existent class names.

### Method: hasPrototype(…)

#### Technical Explanation
Delegates to cache existence check.

##### For Humans: What This Means
“Is the blueprint already saved?”

##### Parameters
- `string $class`

##### Returns
- `bool`

##### Throws
- Depends on cache implementation.

##### When to Use It
- Warmup and introspection.

##### Common Mistakes
- Treating “exists” as “valid and up to date”.

## Risks, Trade-offs & Recommended Practices
- Risk: Cache can become stale after refactors.
  - Why it matters: wrong prototypes cause injection issues.
  - Design stance: pair caching with invalidation.
  - Recommended practice: clear cache on deploy; validate prototypes in CI.

### For Humans: What This Means
If the blueprint is old, you’ll build the wrong thing. Refresh it when you change the building.

## Related Files & Folders
- `docs_md/Features/Think/Cache/PrototypeCache.md`: Cache contract.
- `docs_md/Features/Think/Analyze/PrototypeAnalyzer.md`: Analysis engine.
- `docs_md/Features/Think/Model/ServicePrototype.md`: Output blueprint.

### For Humans: What This Means
This class sits in the middle: cache on one side, analyzer on the other, prototype as the output.

