# DefinitionStore

## Quick Summary
- This file implements the authoritative registry of service definitions, tags, extenders, and contextual rules.
- It exists so the container has one “single source of truth” for registration metadata.
- It removes the complexity of scattered registration state by centralizing lookups and indices.

### For Humans: What This Means
This is the container’s brain for “what services are registered and what rules apply to them”.

## Terminology (MANDATORY, EXPANSIVE)
- **Definition store**: A registry mapping service ids to their definitions and related indices.
  - In this file: `$definitions`, `$tags`, contextual maps, extenders, and caches.
  - Why it matters: every part of the container needs consistent answers.
- **Definition**: The blueprint (`ServiceDefinition`) for a single service.
  - In this file: stored under `$definitions[$abstract]`.
  - Why it matters: it’s what runtime resolution consumes.
- **Tag index**: Reverse mapping from tag → list of service ids.
  - In this file: `$tags`.
  - Why it matters: fast group queries without scanning all definitions.
- **Contextual rule**: An override rule scoped to a consumer + dependency pair.
  - In this file: `$contextual` and `$wildcardContextual`.
  - Why it matters: enables “special case injection” safely.
- **Wildcard pattern**: A consumer pattern like `App\\Http\\*` matched via `fnmatch`.
  - In this file: used for wildcard contextual rules.
  - Why it matters: lets you apply contextual overrides to whole categories of classes.
- **Extender**: A callback executed after a service is resolved to modify/decorate the instance.
  - In this file: stored in `$extenders`.
  - Why it matters: enables decoration without replacing the binding.
- **Memoization cache**: Saved results of expensive lookups.
  - In this file: `$resolvedCache` and `$classHierarchyCache`.
  - Why it matters: contextual matching can be expensive; caching makes it usable.

### For Humans: What This Means
This store doesn’t “build” anything. It just remembers everything you told the container, and it remembers it efficiently.

## Think of It
Think of `DefinitionStore` as a well-indexed filing cabinet: you can find a service by id, find groups by tag, and look up special exceptions without rummaging through every file.

### For Humans: What This Means
You’re not searching a messy pile—you’re using a labeled cabinet.

## Story Example
You register a handful of services, tag some as `middleware`, add a contextual override for `HttpKernel`, and add an extender for `LoggerInterface`. All of these are stored in `DefinitionStore`. Later, the resolution pipeline consults the store to decide what to instantiate, how to scope it, and which extenders to run.

### For Humans: What This Means
Everything you configure ends up in one place, so later behavior is consistent.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

- `add()` stores a definition.
- `get()` retrieves a definition.
- Tag methods let you group services.
- Context methods let you define special cases.
- Extenders let you decorate resolved instances.

Beginner FAQ:
- *Is this safe to modify at runtime?* It’s mutable, but you should treat it as “boot-time configuration” for predictable behavior.

## How It Works (Technical)
The store keeps primary data (`$definitions`) and several indices. When definitions change, it clears memoization caches. Contextual matching is resolved through a prioritized search: direct match, wildcard match, parent classes, then interfaces. To avoid repeated reflection overhead, hierarchy information is cached.

### For Humans: What This Means
It’s optimized for “read a lot, write a little”: you register once, then resolve many times.

## Architecture Role
- Why it lives in this folder: it’s the core persistence model for definitions.
- What depends on it: builders, runtime resolver, tagging, contextual injection, extenders.
- What it depends on: `ServiceDefinition` and PHP reflection helpers (`class_parents`, `class_implements`).
- System-level reasoning: central state enables deterministic container behavior.

### For Humans: What This Means
If you want to understand why the container behaves a certain way, this is the data it’s reading.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: add(…)

#### Technical Explanation
Adds or replaces a service definition and keeps tag indices consistent.

##### For Humans: What This Means
You’re saving (or updating) a service blueprint in the container’s registry.

##### Parameters
- `ServiceDefinition $definition`: The blueprint to store.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- During registration and bootstrapping.

##### Common Mistakes
- Updating definitions late after resolution has started (hard to reason about).

### Method: get(…)

#### Technical Explanation
Returns the definition for a given abstract id, or `null` if missing.

##### For Humans: What This Means
You’re asking “do we know anything about this service id?”

##### Parameters
- `string $abstract`: Service id.

##### Returns
- `ServiceDefinition|null`

##### Throws
- No explicit exceptions.

##### When to Use It
- Builders and runtime resolution use it to fetch configuration.

##### Common Mistakes
- Assuming it always exists; check for `null`.

### Method: has(…)

#### Technical Explanation
Checks existence of a definition by id.

##### For Humans: What This Means
It’s a quick “is it registered?” check.

##### Parameters
- `string $abstract`

##### Returns
- `bool`

##### Throws
- No explicit exceptions.

##### When to Use It
- Validation steps and defensive programming.

##### Common Mistakes
- Using `has()` as a guarantee of resolvability (other rules might still fail).

### Method: getTaggedIds(…)

#### Technical Explanation
Returns unique service ids associated with a given tag.

##### For Humans: What This Means
You’re asking “give me everything labeled X”.

##### Parameters
- `string $tag`

##### Returns
- `string[]`

##### Throws
- No explicit exceptions.

##### When to Use It
- Batch resolution, diagnostics, tooling.

##### Common Mistakes
- Forgetting tags are just labels; no ordering guarantee.

### Method: getContextualMatch(…)

#### Technical Explanation
Returns the best contextual match for `(consumer, needs)` using memoization.

##### For Humans: What This Means
It checks whether there’s a special rule for this situation, and caches the answer.

##### Parameters
- `string $consumer`: The consumer class being built.
- `string $needs`: The dependency id it needs.

##### Returns
- `mixed`: The configured override or `null`.

##### Throws
- No explicit exceptions.

##### When to Use It
- During resolution when injecting into a specific consumer.

##### Common Mistakes
- Assuming wildcard/context rules are applied if the consumer class doesn’t exist.

### Method: addContextual(…)

#### Technical Explanation
Stores a contextual rule either as direct match or wildcard pattern rule.

##### For Humans: What This Means
You’re saving a special-case injection rule.

##### Parameters
- `string $consumer`: Class name or wildcard pattern.
- `string $needs`: Dependency id.
- `mixed $give`: Implementation/value to provide.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- From `ContextBuilder::give()`.

##### Common Mistakes
- Overusing wildcard patterns and making behavior hard to predict.

### Method: addExtender(…)

#### Technical Explanation
Registers a post-resolution extender callback for a service id.

##### For Humans: What This Means
You’re saying “after you build this, also run this function on it”.

##### Parameters
- `string $abstract`
- `Closure $extender`

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- Decoration, instrumentation, proxying.

##### Common Mistakes
- Extenders with side effects that depend on resolution order.

### Method: getExtenders(…)

#### Technical Explanation
Returns extenders for a specific abstract plus any global wildcard extenders.

##### For Humans: What This Means
It returns “the functions that should run after this service is created”.

##### Parameters
- `string $abstract`

##### Returns
- `Closure[]`

##### Throws
- No explicit exceptions.

##### When to Use It
- During resolution just after instantiation.

##### Common Mistakes
- Assuming only specific extenders apply; wildcard extenders can also be included.

### Method: addTags(…)

#### Technical Explanation
Adds tags to an existing definition and updates the tag index.

##### For Humans: What This Means
You’re labeling a service, and the store updates its “tag lookup table”.

##### Parameters
- `string $abstract`
- `string|string[] $tags`

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- From `BindingBuilder::tag()`.

##### Common Mistakes
- Tagging unregistered services (this method silently returns).

### Method: getAllDefinitions(…)

#### Technical Explanation
Returns the full map of registered definitions.

##### For Humans: What This Means
It’s for tooling and introspection: “show me everything registered”.

##### Parameters
- None.

##### Returns
- `array<string, ServiceDefinition>`

##### Throws
- No explicit exceptions.

##### When to Use It
- Diagnostics, debugging, compilation passes.

##### Common Mistakes
- Treating the returned array as immutable; it’s a direct view of internal state.

## Risks, Trade-offs & Recommended Practices
- Risk: This store is mutable shared state.
  - Why it matters: runtime mutation can lead to inconsistent behavior.
  - Design stance: treat it as boot-time configuration.
  - Recommended practice: register once, then freeze behavior (conceptually) during runtime.
- Risk: Wildcard contextual rules can be surprising.
  - Why it matters: matching patterns may catch more consumers than intended.
  - Design stance: prefer direct matches; use wildcards for well-defined namespaces only.
  - Recommended practice: keep patterns narrow and documented.

### For Humans: What This Means
This is powerful infrastructure. Use it deliberately, and keep it predictable.

## Related Files & Folders
- `docs_md/Features/Define/Bind/BindingBuilder.md`: Writes to definitions, tags, and arguments.
- `docs_md/Features/Define/Bind/ContextBuilder.md`: Writes contextual overrides.
- `docs_md/Features/Define/Store/ServiceDefinition.md`: The per-service blueprint stored here.
- `docs_md/Features/Define/Store/Compiler/CompilerPassInterface.md`: Defines how the store can be processed.

### For Humans: What This Means
If you want to trace behavior, start with the builder call and end here: this is where the final truth is stored.

