# ServiceDependencyRepository

## Quick Summary
- This file stores and analyzes `ServiceDependency` relationships: it builds graphs, detects cycles, finds orphans, and can auto-discover dependencies using reflection.
- It exists so dependency health becomes observable and enforceable, not an afterthought.
- It removes the complexity of dependency graph tooling by centralizing algorithms and queries.

### For Humans: What This Means (Summary)
It’s your container’s “dependency detective”: it finds suspicious relationship patterns before they blow up at runtime.

## Terminology (MANDATORY, EXPANSIVE)
- **Dependency graph**: Nodes are services; edges are dependencies.
  - In this file: methods build adjacency lists and traverse them.
  - Why it matters: cycle detection and health reports come from graphs.
- **Cycle detection (DFS)**: Finding circular dependencies using depth-first search.
  - In this file: DFS-based algorithms collect cycles.
  - Why it matters: circular dependencies are a common reason containers fail to resolve.
- **Orphan services**: Services that are defined but not connected meaningfully.
  - In this file: methods identify services with missing or unused relationships.
  - Why it matters: orphans are often dead code or misconfigurations.
- **Auto-discovery**: Deriving dependencies by reflecting on class constructors/properties.
  - In this file: reflection inspects service classes to infer dependencies.
  - Why it matters: it reduces manual dependency bookkeeping (with trade-offs).
- **Health score**: A summary judgement of dependency quality.
  - In this file: health analysis reports issues and a score.
  - Why it matters: it’s easier to act on a “status” than raw details.

### For Humans: What This Means (Terms)
This repository turns a messy web of service relationships into clear reports you can act on.

## Think of It
Think of it like checking your city’s road map for roundabouts that never end (cycles), streets that go nowhere (orphans), and highways everyone relies on (hotspots).

### For Humans: What This Means (Think)
It helps you avoid “traffic jams” in the container before users hit them.

## Story Example
Your production container boot time slowly grows over months. You run dependency analysis and discover a handful of services that almost everything depends on, plus a few cycles introduced by a new feature. You fix the cycle and split a hotspot service into two, and the system becomes easier to reason about.

### For Humans: What This Means (Story)
You can improve container architecture with evidence, not intuition.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. This repository stores edges (“A depends on B”).
2. It can answer “who depends on X?”, “what does X depend on?”, and “are there circles?”.
3. It can build a big map (graph) and run graph algorithms on it.
4. It can also auto-discover edges by inspecting class signatures (when configured).

## How It Works (Technical)
The repository queries persisted dependency rows and maps them to entities. Graph methods build adjacency lists keyed by service id. Cycle detection uses DFS with a recursion stack; orphan detection compares services/edges; impact methods reverse-lookup dependents. Auto-discovery uses reflection to inspect constructors and type hints, then records inferred dependency edges. Maintenance operations validate referential integrity against service definitions and remove orphaned edges.

### For Humans: What This Means (How)
It’s “store edges + build graphs + run algorithms + keep data clean”.

## Architecture Role
- Why this file lives in `Features/Define/Store`: dependency relationships are stored facts, not runtime guesses.
- What depends on it: guard validators, discovery tools, diagnostics, and boot-time health checks.
- What it depends on: `ServiceDefinitionRepository` for service existence and reflection for inference.
- System-level reasoning: dependency problems are systemic; centralizing analysis prevents “everyone implements their own half-broken detector”.

### For Humans: What This Means (Role)
One trusted dependency analyzer beats ten half-trusted scripts.

## Methods 


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: getDependentServices(…)

#### Technical Explanation (getDependentServices)
Returns services that depend on the given service id (reverse lookup).

##### For Humans: What This Means (getDependentServices)
It answers: “If I change X, who might break?”

##### Parameters (getDependentServices)
- `$serviceId`: The service being depended on.

##### Returns (getDependentServices)
- An `Arrhae` of dependency records/entities.

##### Throws (getDependentServices)
- Underlying repository exceptions.

##### When to Use It (getDependentServices)
- Impact analysis and safe refactoring.

##### Common Mistakes (getDependentServices)
- Confusing dependents with dependencies (direction matters).

### Method: getDependencyChains(…)

#### Technical Explanation (getDependencyChains)
Builds transitive dependency chains for a service up to a maximum depth.

##### For Humans: What This Means (getDependencyChains)
It shows you the “dependency tree” a service pulls in.

##### Parameters (getDependencyChains)
- `$serviceId`: The root service.
- `$maxDepth`: Limit to avoid infinite recursion.

##### Returns (getDependencyChains)
- A nested array representing dependency chains.

##### Throws (getDependencyChains)
- Underlying repository exceptions.

##### When to Use It (getDependencyChains)
- Debugging “why is this service slow?” and understanding transitive coupling.

##### Common Mistakes (getDependencyChains)
- Setting depth too low and missing deeper issues.

### Method: getServiceDependencies(…)

#### Technical Explanation (getServiceDependencies)
Returns direct dependency edges for a service.

##### For Humans: What This Means (getServiceDependencies)
It answers: “What does this service need right away?”

##### Parameters (getServiceDependencies)
- `$serviceId`: The service to inspect.

##### Returns (getServiceDependencies)
- An `Arrhae` of dependencies.

##### Throws (getServiceDependencies)
- Underlying repository exceptions.

##### When to Use It (getServiceDependencies)
- Building graphs or presenting dependencies in tooling.

##### Common Mistakes (getServiceDependencies)
- Assuming this includes transitive dependencies; it’s direct only.

### Method: analyzeDependencyHealth(…)

#### Technical Explanation (analyzeDependencyHealth)
Produces a health report based on cycles, missing services, orphans, and complexity.

##### For Humans: What This Means (analyzeDependencyHealth)
It’s a “doctor report” for your container’s dependency structure.

##### Parameters (analyzeDependencyHealth)
- None.

##### Returns (analyzeDependencyHealth)
- An array describing issues and an overall health score.

##### Throws (analyzeDependencyHealth)
- Underlying repository exceptions.

##### When to Use It (analyzeDependencyHealth)
- CI validation, boot diagnostics, periodic audits.

##### Common Mistakes (analyzeDependencyHealth)
- Treating warnings as ignorable forever; they accumulate into outages.

### Method: detectCircularDependencies(…)

#### Technical Explanation (detectCircularDependencies)
Detects cycles in the dependency graph using DFS traversal.

##### For Humans: What This Means (detectCircularDependencies)
It finds “A needs B needs C needs A” loops.

##### Parameters (detectCircularDependencies)
- None.

##### Returns (detectCircularDependencies)
- An array of detected cycles.

##### Throws (detectCircularDependencies)
- Underlying repository exceptions.

##### When to Use It (detectCircularDependencies)
- Debugging resolution failures or preventing regressions.

##### Common Mistakes (detectCircularDependencies)
- Assuming “no cycles” means “no runtime loops”; factories and runtime conditions can still create cycles.

### Method: getDependencyGraph(…)

#### Technical Explanation (getDependencyGraph)
Builds and returns an adjacency list representation of all dependencies.

##### For Humans: What This Means (getDependencyGraph)
It gives you the raw map you can visualize or analyze.

##### Parameters (getDependencyGraph)
- None.

##### Returns (getDependencyGraph)
- An array keyed by service id containing dependency lists.

##### Throws (getDependencyGraph)
- Underlying repository exceptions.

##### When to Use It (getDependencyGraph)
- Building graphs for UI/CLI tooling.

##### Common Mistakes (getDependencyGraph)
- Using it as-is for critical decisions without considering optional deps.

### Method: findOrphanedServices(…)

#### Technical Explanation (findOrphanedServices)
Identifies services that are disconnected or otherwise “dangling” in the dependency dataset.

##### For Humans: What This Means (findOrphanedServices)
It finds services nobody uses or dependencies that point nowhere.

##### Parameters (findOrphanedServices)
- None.

##### Returns (findOrphanedServices)
- An array of orphan findings.

##### Throws (findOrphanedServices)
- Underlying repository exceptions.

##### When to Use It (findOrphanedServices)
- Cleanup, maintenance, and reducing container clutter.

##### Common Mistakes (findOrphanedServices)
- Deleting orphans without checking if they’re entry points (some services are intentionally standalone).

### Method: getMostDependedServices(…)

#### Technical Explanation (getMostDependedServices)
Returns the top N services with the most dependents (hotspots).

##### For Humans: What This Means (getMostDependedServices)
It finds “everything depends on this” services.

##### Parameters (getMostDependedServices)
- `$limit`: How many results to return.

##### Returns (getMostDependedServices)
- An array of hotspot services.

##### Throws (getMostDependedServices)
- Underlying repository exceptions.

##### When to Use It (getMostDependedServices)
- Performance monitoring, refactor planning, resilience improvements.

##### Common Mistakes (getMostDependedServices)
- Treating hotspots as “bad”; some are legitimate core infrastructure.

### Method: getDependencyStats(…)

#### Technical Explanation (getDependencyStats)
Computes summary statistics about dependency count, types, and patterns.

##### For Humans: What This Means (getDependencyStats)
It gives you a quick “state of dependencies” snapshot.

##### Parameters (getDependencyStats)
- None.

##### Returns (getDependencyStats)
- An array of statistics.

##### Throws (getDependencyStats)
- Underlying repository exceptions.

##### When to Use It (getDependencyStats)
- Dashboarding and audits.

##### Common Mistakes (getDependencyStats)
- Using stats without context; always pair with actual graph inspection.

### Method: autoDiscoverDependencies(…)

#### Technical Explanation (autoDiscoverDependencies)
Inspects service classes (reflection) to infer dependencies and store them as edges.

##### For Humans: What This Means (autoDiscoverDependencies)
It tries to build the map automatically by reading class signatures.

##### Parameters (autoDiscoverDependencies)
- `$serviceRepo`: Repository to supply service definitions/classes to analyze.

##### Returns (autoDiscoverDependencies)
- An array of discovered relationships and outcomes.

##### Throws (autoDiscoverDependencies)
- `ReflectionException` when reflection fails for a class.

##### When to Use It (autoDiscoverDependencies)
- Dev tooling and audits where you want to compare declared vs actual dependencies.

##### Common Mistakes (autoDiscoverDependencies)
- Treating reflection inference as perfectly accurate; dynamic factories and runtime config won’t be visible.

### Method: trackDependency(…)

#### Technical Explanation (trackDependency)
Records a dependency relationship (with type/optionality) to persistence.

##### For Humans: What This Means (trackDependency)
It’s the “add edge to the graph” operation.

##### Parameters (trackDependency)
- Depends on signature; conceptually: dependent id, dependency id, type, optional flag, timestamp.

##### Returns (trackDependency)
- Depends on implementation; typically void or a status array.

##### Throws (trackDependency)
- Underlying repository exceptions.

##### When to Use It (trackDependency)
- During service registration or analysis ingestion.

##### Common Mistakes (trackDependency)
- Tracking duplicates without de-duplication strategy.

### Method: validateDependencies(…)

#### Technical Explanation (validateDependencies)
Checks that dependency edges refer to existing service definitions and reports inconsistencies.

##### For Humans: What This Means (validateDependencies)
It finds “edges pointing to missing services”.

##### Parameters (validateDependencies)
- `$serviceRepo`: Source of truth for defined services.

##### Returns (validateDependencies)
- An array describing validation issues.

##### Throws (validateDependencies)
- Underlying repository exceptions.

##### When to Use It (validateDependencies)
- CI checks and boot-time validation.

##### Common Mistakes (validateDependencies)
- Running it before definitions are fully loaded.

### Method: cleanupOrphanedDependencies(…)

#### Technical Explanation (cleanupOrphanedDependencies)
Removes dependency edges that reference missing services or otherwise violate integrity rules.

##### For Humans: What This Means (cleanupOrphanedDependencies)
It deletes broken arrows in the map.

##### Parameters (cleanupOrphanedDependencies)
- None.

##### Returns (cleanupOrphanedDependencies)
- An array describing what was cleaned.

##### Throws (cleanupOrphanedDependencies)
- Underlying repository exceptions.

##### When to Use It (cleanupOrphanedDependencies)
- Maintenance jobs after migrations or big refactors.

##### Common Mistakes (cleanupOrphanedDependencies)
- Cleaning too aggressively without auditing the results.

## Risks, Trade-offs & Recommended Practices
- Risk: Reflection-based auto-discovery lies by omission.
  - Why it matters: factories, dynamic config, and runtime conditionals won’t show up.
  - Design stance: treat auto-discovery as advisory, not authoritative.
  - Recommended practice: compare inferred deps with declared deps; flag mismatches for review.
- Trade-off: Graph analysis can be expensive on large datasets.
  - Why it matters: cycle detection and stats require loading and iterating edges.
  - Design stance: correctness first; optimize with caching and incremental updates.
  - Recommended practice: run deep analysis asynchronously or in CI/admin tooling.

### For Humans: What This Means (Risks)
Use this repository like a diagnostic tool: powerful, but not something you run on every request.

## Related Files & Folders
- `docs_md/Features/Define/Store/ServiceDependency.md`: The edge entity this repository stores.
- `docs_md/Features/Define/Store/ServiceDefinitionRepository.md`: The source of truth for services being connected.
- `docs_md/Features/Define/Store/ServiceDiscovery.md`: Uses relationship insights for recommendations and searches.

### For Humans: What This Means (Related)
Definitions say “what exists”, dependencies say “what relies on what”, discovery says “what should you do about it”.


### Method: getEntityClass(...)

#### Technical Explanation (getEntityClass)
This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the container’s workflow explicit and reusable.

##### For Humans: What This Means (getEntityClass)
When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having to manually wire the details.

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
This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the container’s workflow explicit and reusable.

##### For Humans: What This Means (mapToDatabase)
When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having to manually wire the details.

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
This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the container’s workflow explicit and reusable.

##### For Humans: What This Means (mapToEntity)
When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having to manually wire the details.

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
