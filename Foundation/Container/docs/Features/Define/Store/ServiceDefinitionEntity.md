# ServiceDefinitionEntity

## Quick Summary

- This file defines the immutable “record” of a service your container knows about: its id, class, lifetime, tags,
  dependencies, and environment.
- It exists so you can treat service definitions like reliable facts, not ad-hoc arrays flying around your code.
- It removes the complexity of “what does this service mean?” by giving you one validated, serializable shape.

### For Humans: What This Means (Summary)

It’s the container’s business card for a service: one place where the truth about that service lives.

## Terminology (MANDATORY, EXPANSIVE)

- **Service definition**: A description of how a service should exist in the container.
    - In this file: the definition is a single immutable entity instance.
    - Why it matters: the container can only behave predictably if the definition is stable.
- **Service id**: A unique identifier (string) used to reference a service.
    - In this file: `id` is the primary key for lookup and relationships.
    - Why it matters: every other system (scopes, repositories, diagnostics) needs a stable name.
- **Lifetime**: A policy that defines how long a resolved instance should live.
    - In this file: `ServiceLifetime` is stored and serialized via `$lifetime->value`.
    - Why it matters: lifetime changes how caching and scoping work.
- **Tags**: Free-form labels used for grouping and discovery.
    - In this file: `tags` are used by filtering methods like `hasTag()`.
    - Why it matters: tags make discovery queries “human friendly”.
- **Dependencies**: Other service ids this service needs to function.
    - In this file: `dependencies` are used for graph building and analysis.
    - Why it matters: dependency data is what lets you detect cycles before runtime.
- **Environment**: A runtime “mode” (prod, dev, etc.) that can restrict availability.
    - In this file: `environment` is optional and checked via `isAvailableInEnvironment()`.
    - Why it matters: it prevents accidentally enabling dev-only services in production.
- **Immutability**: The object can’t be mutated after creation.
    - In this file: constructor uses `public readonly` properties and validates immediately.
    - Why it matters: once created, you can safely share it without wondering who changed it.
- **Serialization / hydration**: Converting an object to array and back.
    - In this file: `toArray()` and `fromArray()` define the storage contract.
    - Why it matters: repositories and caches need a stable interchange format.

### For Humans: What This Means (Terms)

These words are just different ways of saying: “this entity is the container’s memory of what a service is”.

## Think of It

Think of it like a product label in a warehouse: it tells you the product’s name (id), what it is (class), how it should
be handled (lifetime), what shelves it belongs on (tags), and what other parts it needs (dependencies).

### For Humans: What This Means (Think)

If you’ve ever had a box with no label, you know how much time you lose. This file prevents that.

## Story Example

You’re onboarding a project and you need to answer: “Which services are prod-only?”, “Which services are singletons?”,
“What depends on the database?”. Without a stable entity, you end up hunting through config files and arrays. With
`ServiceDefinitionEntity`, you ask the repository for entities and query them safely (tags, lifetime, environment,
dependencies) with predictable rules.

### For Humans: What This Means (Story)

Instead of detective work, you get straightforward questions with straightforward answers.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code
with confidence.

1. A service definition is “how the container should know a service”.
2. This class stores that definition as a single immutable object.
3. It validates important rules immediately (no empty id, class must exist).
4. It can turn itself into a storage-friendly array, and be rebuilt from it.
5. If you want a “modified” version, you don’t mutate it—you build a new one with `withUpdates()`.

## How It Works (Technical)

The constructor receives the full definition shape (id, class, lifetime, config, tags, dependencies, environment,
description, active flag, timestamps) and calls a private `validate()` that enforces core invariants. The entity offers
convenience queries (`hasTag()`, `dependsOn()`, `getComplexityScore()`, `isAvailableInEnvironment()`) and a persistence
boundary (`toArray()` / `fromArray()`). `withUpdates()` implements “immutable patching” by serializing to array,
applying allowed keys, updating `updated_at`, and rehydrating through `fromArray()`.

### For Humans: What This Means (How)

It’s strict on creation, easy to query later, and easy to store/restore.

## Architecture Role

- Why this file lives in `Features/Define/Store`: it’s part of “defining” services and “storing” those definitions in a
  consistent shape.
- What depends on it: repositories, discovery tools, validation rules, diagnostics, and anything that needs to talk
  about services without instantiating them.
- What it depends on: `ServiceLifetime` for lifetime semantics and basic PHP primitives for validation/serialization.
- System-level reasoning: containers are hard to debug when definitions are fuzzy; a strong entity makes the rest of the
  system simpler and safer.

### For Humans: What This Means (Role)

If you make “what a service is” solid, everything else (searching, validating, debugging) stops being painful.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation (__construct)

Creates an immutable service definition and validates core invariants immediately.

##### For Humans: What This Means (__construct)

If this object exists, you can trust it’s not garbage data.

##### Parameters (__construct)

- `$id`: The name you’ll use to refer to this service later.
- `$class`: The class or interface this service represents.
- `$lifetime`: How long resolved instances should live.
- `$config`: Extra configuration the container might use when building the service.
- `$tags`: Labels you’ll use to group or search services.
- `$dependencies`: Service ids this service needs.
- `$environment`: Optional “only available in this environment” flag.
- `$description`: Optional human description for discovery/UX.
- `$isActive`: Whether this definition should be considered enabled.
- `$createdAt`, `$updatedAt`: Optional timestamps for auditing.

##### Returns (__construct)

- Returns nothing.

##### Throws (__construct)

- `InvalidArgumentException` when required fields are invalid (empty id/class, missing class/interface, invalid
  dependency strings).

##### When to Use It (__construct)

- When you’re creating service definitions from configuration, code registration, or imports.

##### Common Mistakes (__construct)

- Passing a class string that doesn’t exist at runtime (autoloading not configured).

### Method: getTableName(…)

#### Technical Explanation (getTableName)

Returns the canonical persistence table name for service definitions.

##### For Humans: What This Means (getTableName)

It’s the “where do we store these?” label used by the repository layer.

##### Parameters (getTableName)

- None.

##### Returns (getTableName)

- The table name string.

##### Throws (getTableName)

- None.

##### When to Use It (getTableName)

- Inside repository implementations or migration tooling.

##### Common Mistakes (getTableName)

- Treating it as a dynamic value; it’s intentionally stable.

### Method: hasTag(…)

#### Technical Explanation (hasTag)

Checks whether the service definition includes a given tag.

##### For Humans: What This Means (hasTag)

It answers: “Is this service in the ‘database’ bucket?”

##### Parameters (hasTag)

- `$tag`: The tag you’re looking for.

##### Returns (hasTag)

- `true` if the tag is present; otherwise `false`.

##### Throws (hasTag)

- None.

##### When to Use It (hasTag)

- Tag-based filtering and discovery.

##### Common Mistakes (hasTag)

- Expecting partial matching; it’s exact matching.

### Method: dependsOn(…)

#### Technical Explanation (dependsOn)

Checks whether the service declares a dependency on another service id.

##### For Humans: What This Means (dependsOn)

It answers: “Does A need B to exist?”

##### Parameters (dependsOn)

- `$serviceId`: The dependency id you want to check.

##### Returns (dependsOn)

- `true` if it’s listed in `$dependencies`.

##### Throws (dependsOn)

- None.

##### When to Use It (dependsOn)

- Building graphs, impact analysis, cycle detection.

##### Common Mistakes (dependsOn)

- Confusing “depends on” with “is depended on”.

### Method: getComplexityScore(…)

#### Technical Explanation (getComplexityScore)

Computes a heuristic complexity score from dependencies, lifetime, and config size.

##### For Humans: What This Means (getComplexityScore)

It’s a rough “this service is expensive to deal with” number.

##### Parameters (getComplexityScore)

- None.

##### Returns (getComplexityScore)

- An integer score where higher means more complex.

##### Throws (getComplexityScore)

- None.

##### When to Use It (getComplexityScore)

- Prioritizing caching, optimizing resolution pipelines, spotting “heavy” services.

##### Common Mistakes (getComplexityScore)

- Treating it as an absolute truth; it’s a heuristic.

### Method: isAvailableInEnvironment(…)

#### Technical Explanation (isAvailableInEnvironment)

Evaluates environment constraints to decide if the service should be considered available.

##### For Humans: What This Means (isAvailableInEnvironment)

It prevents accidentally using a dev-only service in production.

##### Parameters (isAvailableInEnvironment)

- `$environment`: The environment you’re running in (or `null`).

##### Returns (isAvailableInEnvironment)

- `true` if it’s available in that environment.

##### Throws (isAvailableInEnvironment)

- None.

##### When to Use It (isAvailableInEnvironment)

- Filtering service lists during boot or discovery.

##### Common Mistakes (isAvailableInEnvironment)

- Passing the wrong environment string (typos cause “not available”).

### Method: withUpdates(…)

#### Technical Explanation (withUpdates)

Creates a new entity instance by applying allowed field updates to the serialized form.

##### For Humans: What This Means (withUpdates)

It’s how you “edit” an immutable object: you make a new copy.

##### Parameters (withUpdates)

- `$updates`: An associative array of allowed keys to new values.

##### Returns (withUpdates)

- A new `ServiceDefinitionEntity`.

##### Throws (withUpdates)

- `InvalidArgumentException` if the resulting entity is invalid.
- `DateMalformedStringException` if timestamp parsing fails during rehydration.

##### When to Use It (withUpdates)

- Updating service metadata (description, active flag, tags) while keeping immutability.

##### Common Mistakes (withUpdates)

- Providing keys that don’t exist in the storage array; they’re ignored.

### Method: toArray(…)

#### Technical Explanation (toArray)

Serializes the entity into a flat, storage-friendly array (including JSON encoding for arrays).

##### For Humans: What This Means (toArray)

It turns the object into something you can store in a database or cache.

##### Parameters (toArray)

- None.

##### Returns (toArray)

- An associative array representing the entity.

##### Throws (toArray)

- None.

##### When to Use It (toArray)

- Persisting entities or exporting definitions.

##### Common Mistakes (toArray)

- Expecting arrays to stay arrays; some are JSON strings by design.

### Method: fromArray(…)

#### Technical Explanation (fromArray)

Hydrates a new entity instance from the serialized array representation.

##### For Humans: What This Means (fromArray)

It rebuilds the object from stored data.

##### Parameters (fromArray)

- `$data`: The storage array.

##### Returns (fromArray)

- A new `ServiceDefinitionEntity`.

##### Throws (fromArray)

- `DateMalformedStringException` if timestamps are malformed.

##### When to Use It (fromArray)

- Mapping database records to entities inside repositories.

##### Common Mistakes (fromArray)

- Supplying invalid JSON strings for `config`, `tags`, or `dependencies`.

## Risks, Trade-offs & Recommended Practices

- Risk: `class_exists()` / `interface_exists()` validation depends on autoloading.
    - Why it matters: in some environments (build steps, partial autoload), the class might not be loadable even though
      it’s “valid”.
    - Design stance: strong validation is worth it, but you need correct runtime wiring.
    - Recommended practice: validate after composer/autoload is ready; consider environment-aware validation for build
      tooling.
- Trade-off: JSON-encoding arrays for storage convenience.
    - Why it matters: it simplifies storage but makes ad-hoc querying harder.
    - Design stance: keep storage format stable; add repository methods for querying instead of raw DB queries.
    - Recommended practice: treat `toArray()` as a persistence boundary; don’t “poke” inside JSON fields from outside.

### For Humans: What This Means (Risks)

Strong rules save you later, but you must run them in the right runtime conditions.

## Related Files & Folders

- `docs_md/Features/Define/Store/ServiceDefinitionRepository.md`: Saves and queries these entities.
- `docs_md/Features/Define/Store/ServiceDiscovery.md`: Uses entities to answer higher-level questions.
- `docs_md/Features/Core/Enum/ServiceLifetime.md`: Defines lifetime meanings.

### For Humans: What This Means (Related)

This file is the “what”; repositories are the “where”; discovery is the “so what?”.

