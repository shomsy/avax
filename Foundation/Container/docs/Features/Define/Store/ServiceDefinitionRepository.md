# ServiceDefinitionRepository

## Quick Summary

- This file is the persistence and query gateway for `ServiceDefinitionEntity` records.
- It exists so you can find, analyze, import/export, and maintain service definitions without writing raw storage logic
  everywhere.
- It removes the complexity of “how do I ask questions about services?” by giving you domain-shaped queries (tags,
  lifetime, fuzzy search, dependency analysis).

### For Humans: What This Means (Summary)

It’s your container’s librarian: you don’t search shelves manually—you ask the librarian.

## Terminology (MANDATORY, EXPANSIVE)

- **Repository**: A data-access object that hides persistence details behind methods.
    - In this file: the class extends a base `Repository` and maps arrays to entities.
    - Why it matters: it keeps your domain code from caring about SQL/query builders.
- **Query builder**: An API for constructing queries programmatically.
    - In this file: the repository uses `$this->query()` and chained conditions.
    - Why it matters: it enables expressive filtering without hardcoding SQL.
- **Active service**: A service definition that is enabled.
    - In this file: `findActiveServices()` filters by `is_active = true`.
    - Why it matters: “defined” and “enabled” aren’t the same thing.
- **Environment filtering**: Restricting services to the current runtime environment.
    - In this file: `environment` can be `null` (global) or a specific string.
    - Why it matters: it’s how you keep prod clean from dev-only wiring.
- **Fuzzy search / similarity threshold**: Searching by approximate matches.
    - In this file: `searchServices()` uses a similarity score to filter results.
    - Why it matters: it helps humans find services when they don’t know the exact id.
- **Dependency analysis**: Building a graph of dependencies to detect cycles and hotspots.
    - In this file: `analyzeDependencies()` computes cycles/orphans/most-depended.
    - Why it matters: most container failures are dependency-shape problems.
- **Import/export**: Bulk load/store operations for service definitions.
    - In this file: `importServices()` and `exportServices()` handle batch formats.
    - Why it matters: it supports migrations, tooling, and configuration pipelines.

### For Humans: What This Means (Terms)

This is the “ask questions safely” layer; it keeps you out of storage details.

## Think of It

Think of it as a spreadsheet with smart filters and reports. You don’t rewrite formulas every time—you use built-in
“views”: active services, tag filters, lifetime filters, dependency graphs.

### For Humans: What This Means (Think)

Instead of reinventing queries, you reuse a trusted set of questions.

## Story Example

You’re debugging a circular dependency that only happens in production. You don’t want to grep through configs. You call
`findActiveServices('production')`, then `analyzeDependencies()`, and you immediately get cycles and orphan services.
You then use `searchServices('payment')` to find the services involved and export them as a bundle for review.

### For Humans: What This Means (Story)

You can debug the container like a system, not like a pile of files.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code
with confidence.

1. A repository is a class that talks to storage for you.
2. This repository speaks “service definition language”, not “database language”.
3. You call methods like “find by tags” and you get entities back.
4. It also provides reports (stats, dependency analysis) so you can reason about the whole container.

## How It Works (Technical)

The repository builds queries through the base repository/query API, retrieves raw records, and maps them to
`ServiceDefinitionEntity` via `mapToEntity()`. It provides domain queries (environment filtering, tags, type/lifetime)
and analytics (stats, dependency graph and cycle detection via DFS). Bulk operations (`importServices()`,
`exportServices()`) transform between storage arrays and entities, while maintenance operations (`cleanup()`) remove
outdated/invalid records.

### For Humans: What This Means (How)

It’s “storage + mapping + domain questions” in one place.

## Architecture Role

- Why this file lives in `Features/Define/Store`: it’s the storage-facing counterpart to defining services.
- What depends on it: discovery tools, validation flows, tooling, and any boot process that needs service lists.
- What it depends on: base `Repository`, query builder infrastructure, and the entity mapping contract.
- System-level reasoning: containers become maintainable when “service facts” are queryable and analyzable.

### For Humans: What This Means (Role)

If you can’t query your services, you can’t control them.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: findActiveServices(…)

#### Technical Explanation (findActiveServices)

Returns active services filtered by environment, including global (`null`) environment services.

##### For Humans: What This Means (findActiveServices)

It answers: “What services are actually on in this environment?”

##### Parameters (findActiveServices)

- `$environment`: The environment name, or `null` for no filtering.

##### Returns (findActiveServices)

- An `Arrhae` collection of `ServiceDefinitionEntity`.

##### Throws (findActiveServices)

- Any storage/query exceptions raised by the underlying repository.

##### When to Use It (findActiveServices)

- Bootstrapping the container for a specific environment.

##### Common Mistakes (findActiveServices)

- Forgetting that `null` environment services are always included.

### Method: findByTags(…)

#### Technical Explanation (findByTags)

Filters services by tags using an operator (`AND` / `OR`) semantics.

##### For Humans: What This Means (findByTags)

It answers: “Give me services labeled like X.”

##### Parameters (findByTags)

- `$tags`: Tags to match.
- `$operator`: How to interpret multiple tags (`AND` requires all tags, `OR` any tag).

##### Returns (findByTags)

- An `Arrhae` of matching services.

##### Throws (findByTags)

- Underlying repository exceptions.

##### When to Use It (findByTags)

- Finding infrastructure groups (database, cache, http) or domain slices.

##### Common Mistakes (findByTags)

- Passing mixed-case tags when your tagging conventions are case-sensitive.

### Method: findByType(…)

#### Technical Explanation (findByType)

Finds services that match a type (class/interface) by checking implementation/inheritance.

##### For Humans: What This Means (findByType)

It answers: “Which services can act like this interface?”

##### Parameters (findByType)

- `$type`: The class or interface name.

##### Returns (findByType)

- An `Arrhae` of matching services.

##### Throws (findByType)

- Underlying repository exceptions.

##### When to Use It (findByType)

- Finding all implementations of a contract for replacement/inspection.

##### Common Mistakes (findByType)

- Assuming type checks work without autoloading the class.

### Method: findByLifetime(…)

#### Technical Explanation (findByLifetime)

Groups services by their `ServiceLifetime`.

##### For Humans: What This Means (findByLifetime)

It answers: “Which services are singletons vs scoped vs transient?”

##### Parameters (findByLifetime)

- `$lifetime`: The lifetime enum value to match.

##### Returns (findByLifetime)

- An `Arrhae` of matching services.

##### Throws (findByLifetime)

- Underlying repository exceptions.

##### When to Use It (findByLifetime)

- Performance tuning and lifecycle debugging.

##### Common Mistakes (findByLifetime)

- Confusing “scoped” services with “singleton” caching.

### Method: getServiceStats(…)

#### Technical Explanation (getServiceStats)

Computes aggregate statistics (counts, tags, usage patterns) over stored services.

##### For Humans: What This Means (getServiceStats)

It gives you a health dashboard summary.

##### Parameters (getServiceStats)

- None.

##### Returns (getServiceStats)

- An array of statistics.

##### Throws (getServiceStats)

- Underlying repository exceptions.

##### When to Use It (getServiceStats)

- Observability dashboards and periodic audits.

##### Common Mistakes (getServiceStats)

- Running it on huge datasets too frequently without caching.

### Method: analyzeDependencies(…)

#### Technical Explanation (analyzeDependencies)

Builds a dependency graph and analyzes cycles, orphans, and high-impact nodes.

##### For Humans: What This Means (analyzeDependencies)

It tells you where the container is fragile before it breaks.

##### Parameters (analyzeDependencies)

- None.

##### Returns (analyzeDependencies)

- An array containing graph analysis results (cycles, orphans, hotspots).

##### Throws (analyzeDependencies)

- Underlying repository exceptions.

##### When to Use It (analyzeDependencies)

- Before refactors, during audits, when debugging circular dependency failures.

##### Common Mistakes (analyzeDependencies)

- Treating “no cycles found” as “no resolution problems”; runtime scopes and factories can still create cycles.

### Method: searchServices(…)

#### Technical Explanation (searchServices)

Performs fuzzy search over service metadata using a similarity threshold.

##### For Humans: What This Means (searchServices)

It helps you find the right service even if you don’t remember its exact name.

##### Parameters (searchServices)

- `$query`: The search text.
- `$threshold`: Similarity cutoff (higher means stricter).

##### Returns (searchServices)

- An `Arrhae` of matching services.

##### Throws (searchServices)

- Underlying repository exceptions.

##### When to Use It (searchServices)

- CLI tools, dev tooling, admin panels.

##### Common Mistakes (searchServices)

- Setting threshold too high and getting no results.

### Method: importServices(…)

#### Technical Explanation (importServices)

Bulk imports service definitions from an array payload into storage, returning import results.

##### For Humans: What This Means (importServices)

It’s how you load a pack of services at once (migration, bootstrap, sync).

##### Parameters (importServices)

- `$servicesData`: An array of service definition arrays.

##### Returns (importServices)

- An array with import outcomes (created/updated/errors).

##### Throws (importServices)

- Underlying repository exceptions or validation errors from entity hydration.

##### When to Use It (importServices)

- Config migrations, environment boot, syncing definitions across systems.

##### Common Mistakes (importServices)

- Supplying inconsistent keys across service payloads.

### Method: saveServiceDefinition(…)

#### Technical Explanation (saveServiceDefinition)

Persists a single `ServiceDefinitionEntity` to storage.

##### For Humans: What This Means (saveServiceDefinition)

It’s your “save” button for a service definition.

##### Parameters (saveServiceDefinition)

- `$service`: The entity to persist.

##### Returns (saveServiceDefinition)

- Nothing.

##### Throws (saveServiceDefinition)

- Underlying storage exceptions.

##### When to Use It (saveServiceDefinition)

- When a service definition is created or updated in code/tools.

##### Common Mistakes (saveServiceDefinition)

- Saving without updating dependencies, leaving inconsistent graphs.

### Method: exportServices(…)

#### Technical Explanation (exportServices)

Exports services to an array format with optional filtering criteria.

##### For Humans: What This Means (exportServices)

It’s how you dump services for backup, review, or migration.

##### Parameters (exportServices)

- `$filters`: Optional filters (tags, lifetime, environment, etc.).

##### Returns (exportServices)

- An array of serialized service definition data.

##### Throws (exportServices)

- Underlying repository exceptions.

##### When to Use It (exportServices)

- Tooling, backups, audits.

##### Common Mistakes (exportServices)

- Expecting export to include “live instances”; it exports definitions only.

### Method: cleanup(…)

#### Technical Explanation (cleanup)

Performs maintenance by removing or deactivating old/outdated records based on age.

##### For Humans: What This Means (cleanup)

It’s your “tidy up the library” job.

##### Parameters (cleanup)

- `$daysOld`: Age threshold in days (default 30).

##### Returns (cleanup)

- An array describing what was cleaned up.

##### Throws (cleanup)

- Underlying repository exceptions.

##### When to Use It (cleanup)

- Scheduled jobs, admin maintenance tasks.

##### Common Mistakes (cleanup)

- Running cleanup without understanding retention requirements.

## Risks, Trade-offs & Recommended Practices

- Risk: Heavy analytics load.
    - Why it matters: stats and dependency analysis can require loading many records.
    - Design stance: correctness first, then add caching when needed.
    - Recommended practice: cache analytics results and run them asynchronously for large datasets.
- Trade-off: Fuzzy search heuristics can surprise you.
    - Why it matters: similarity scores can be non-obvious to users.
    - Design stance: provide threshold control and show match reasons in tooling.
    - Recommended practice: use fuzzy search in dev/admin tooling, not in latency-critical request paths.

### For Humans: What This Means (Risks)

This class can answer big questions, but big questions can cost time—cache and schedule wisely.

## Related Files & Folders

- `docs_md/Features/Define/Store/ServiceDefinitionEntity.md`: The entity this repository persists and returns.
- `docs_md/Features/Define/Store/ServiceDiscovery.md`: Higher-level discovery built on top of repository access.
- `docs_md/Features/Define/Store/ServiceDependencyRepository.md`: Relationship data used for deeper dependency analysis.

### For Humans: What This Means (Related)

Entities are the “facts”, repositories are the “database brain”, discovery is the “product features”.

### Method: getEntityClass(...)

#### Technical Explanation (getEntityClass)

This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the
container’s workflow explicit and reusable.

##### For Humans: What This Means (getEntityClass)

When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having
to manually wire the details.

##### Parameters (getEntityClass)

- See the PHP signature in the source file for exact types and intent.

##### Returns (getEntityClass)

- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (getEntityClass)

- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (getEntityClass)

- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (getEntityClass)

- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.

### Method: mapToDatabase(...)

#### Technical Explanation (mapToDatabase)

This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the
container’s workflow explicit and reusable.

##### For Humans: What This Means (mapToDatabase)

When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having
to manually wire the details.

##### Parameters (mapToDatabase)

- See the PHP signature in the source file for exact types and intent.

##### Returns (mapToDatabase)

- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (mapToDatabase)

- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (mapToDatabase)

- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (mapToDatabase)

- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.

### Method: mapToEntity(...)

#### Technical Explanation (mapToEntity)

This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the
container’s workflow explicit and reusable.

##### For Humans: What This Means (mapToEntity)

When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having
to manually wire the details.

##### Parameters (mapToEntity)

- See the PHP signature in the source file for exact types and intent.

##### Returns (mapToEntity)

- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (mapToEntity)

- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (mapToEntity)

- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (mapToEntity)

- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.
