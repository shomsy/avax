# ServiceDiscovery

## Quick Summary
- This file provides higher-level “search and recommendation” features on top of service definitions and dependency data.
- It exists so you can ask product-grade questions like “what services implement this interface?”, “what’s cacheable?”, “what alternatives exist?”.
- It removes the complexity of building discovery logic scattered across tooling and boot code.

### For Humans: What This Means
It’s your container’s search engine and recommendation engine.

## Terminology (MANDATORY, EXPANSIVE)
- **Discovery**: Finding services based on meaning (capabilities, interface, environment), not just ids.
  - In this file: methods filter `ServiceDefinitionEntity` collections in domain terms.
  - Why it matters: developers think in “capabilities”, not in “table rows”.
- **Capability**: A tag-like claim that a service can do something.
  - In this file: capabilities are derived from tags/interfaces/class patterns.
  - Why it matters: it supports “show me all cache-related services” style queries.
- **Alternative services**: Other services that could replace a given service.
  - In this file: alternatives are found via shared interfaces/traits/tags.
  - Why it matters: it helps refactors and safe migrations.
- **Recommendation**: A ranked suggestion based on similarity or shared context.
  - In this file: recommendations inspect tags, dependencies, domain proximity.
  - Why it matters: it reduces choice paralysis in large containers.
- **Conflict detection**: Finding multiple implementations for the same interface.
  - In this file: `findPotentialConflicts()` identifies ambiguous resolution situations.
  - Why it matters: ambiguity is a hidden source of production bugs.

### For Humans: What This Means
Instead of memorizing service ids, you can browse and discover services like a catalog.

## Think of It
Think of it like a music app: the repository stores all songs (definitions), but discovery builds playlists, searches, and “you might also like” recommendations.

### For Humans: What This Means
This turns container data into a developer experience feature.

## Story Example
You want to migrate from one caching implementation to another. You use discovery to find all cache-related services, detect conflicts (multiple cache implementations), and generate recommendations for safe replacements. You also compute dependency trees to see which parts of the system will be affected.

### For Humans: What This Means
It helps you change things safely because you can see the ecosystem impact.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Repositories give you raw lists of services.
2. Discovery turns those lists into “smart answers”.
3. You ask “find by interface” or “give me alternatives”, and you get meaningful results.
4. It’s mostly filtering, ranking, and summarizing.

## How It Works (Technical)
`ServiceDiscovery` composes repositories (service definitions and dependencies) and offers domain queries implemented with a mix of repository calls and in-memory filtering. Interface-based queries use `is_subclass_of()` and `class_implements()`. Recommendations combine multiple signals (shared dependencies, tags, namespace proximity) and can produce migration suggestions. Health checks detect missing dependencies, request-scoped heuristics, and potential interface conflicts.

### For Humans: What This Means
It’s a brain that reads service metadata and tells you what it means.

## Architecture Role
- Why this file lives in `Features/Define/Store`: it’s built on the stored truth of definitions and dependency edges.
- What depends on it: CLI tools, diagnostics, admin UIs, and “container introspection” workflows.
- What it depends on: `ServiceDefinitionRepository` and `ServiceDependencyRepository` to load the data.
- System-level reasoning: a container without discovery is usable only by experts; discovery makes it humane.

### For Humans: What This Means
This file is about making the container navigable for real developers.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Wires in repositories needed to perform discovery queries.

##### For Humans: What This Means
Discovery needs access to the “service list” and the “dependency map”.

##### Parameters
- Depends on signature; conceptually: a service repository and a dependency repository.

##### Returns
- Nothing.

##### Throws
- Underlying dependency injection errors (construction-time).

##### When to Use It
- When wiring diagnostic and tooling layers.

##### Common Mistakes
- Creating it without repositories initialized (no data to discover).

### Method: findCacheableServices(…)

#### Technical Explanation
Finds services that are good candidates for caching based on lifetime/tags/heuristics.

##### For Humans: What This Means
It answers: “What should probably be cached to speed things up?”

##### Parameters
- `$environment`: Optional environment filter.

##### Returns
- An `Arrhae` of recommended services.

##### Throws
- Underlying repository exceptions.

##### When to Use It
- Performance tuning and boot optimization.

##### Common Mistakes
- Assuming “cacheable” means “safe as singleton”; scope rules still apply.

### Method: findServicesByInterfaces(…)

#### Technical Explanation
Returns services that implement *all* interfaces in the provided list.

##### For Humans: What This Means
It finds services that match a full “capability checklist”.

##### Parameters
- `$interfaces`: Interface names.
- `$environment`: Optional environment filter.

##### Returns
- An `Arrhae` of matching services.

##### Throws
- Underlying repository exceptions.

##### When to Use It
- Searching for implementations with multiple constraints.

##### Common Mistakes
- Passing non-existent interfaces (autoloading).

### Method: findServicesWithCapabilities(…)

#### Technical Explanation
Finds services matching capabilities (often via tags) using `AND` / `OR` semantics.

##### For Humans: What This Means
It’s tag search, but framed as “capability” search.

##### Parameters
- `$capabilities`: List of capabilities.
- `$operator`: `AND` or `OR`.

##### Returns
- An `Arrhae` of matching services.

##### Throws
- Underlying repository exceptions.

##### When to Use It
- Discovery UI and developer tooling.

##### Common Mistakes
- Mixing “capabilities” and “tags” conventions without standardization.

### Method: findAlternativeServices(…)

#### Technical Explanation
Finds candidate replacements for a service based on shared interfaces/tags/domain proximity.

##### For Humans: What This Means
It helps you find “other services that could work here”.

##### Parameters
- `$serviceId`: The service to replace.
- `$environment`: Optional environment filter.

##### Returns
- An `Arrhae` of alternatives.

##### Throws
- Underlying repository exceptions.

##### When to Use It
- Migration planning and refactoring.

##### Common Mistakes
- Treating alternatives as guaranteed drop-in replacements; behavior can differ.

### Method: findServicesByInterface(…)

#### Technical Explanation
Returns services that implement a specific interface or extend a base class.

##### For Humans: What This Means
It answers: “Who can act like this contract?”

##### Parameters
- `$interface`: Interface or base-class name.
- `$environment`: Optional environment filter.

##### Returns
- An `Arrhae` of implementations.

##### Throws
- Underlying repository exceptions.

##### When to Use It
- Interface-based resolution audits and conflict detection.

##### Common Mistakes
- Forgetting to consider multiple implementations (ambiguity risk).

### Method: getServiceRecommendations(…)

#### Technical Explanation
Computes recommendations for related services based on shared context and similarity.

##### For Humans: What This Means
It’s “people also used these services” but for service definitions.

##### Parameters
- `$currentServiceId`: Seed service id for recommendations.

##### Returns
- An array of recommendation entries.

##### Throws
- Underlying repository exceptions.

##### When to Use It
- Admin UI and onboarding workflows.

##### Common Mistakes
- Showing recommendations without explaining the reason (“why this is recommended”).

### Method: analyzeServiceHealth(…)

#### Technical Explanation
Generates a health report for the service ecosystem (missing deps, conflicts, risky patterns).

##### For Humans: What This Means
It’s a “health check” for your service catalog.

##### Parameters
- None.

##### Returns
- An array of health issues and a score.

##### Throws
- Underlying repository exceptions.

##### When to Use It
- CI checks and boot diagnostics.

##### Common Mistakes
- Ignoring warnings until they become production incidents.

### Method: findPotentialConflicts(…)

#### Technical Explanation
Identifies interfaces with multiple implementations that could cause ambiguous resolution.

##### For Humans: What This Means
It finds places where “the container might pick the wrong one”.

##### Parameters
- None.

##### Returns
- An `Arrhae` of conflicting service groups.

##### Throws
- Underlying repository exceptions.

##### When to Use It
- Before enabling interface-based autowiring.

##### Common Mistakes
- Trying to fix conflicts by removing implementations instead of configuring explicit bindings.

### Method: getDependencyTree(…)

#### Technical Explanation
Returns a structured dependency tree for a given service id up to a depth limit.

##### For Humans: What This Means
It shows you what a service pulls in and how deep it goes.

##### Parameters
- `$serviceId`: Root id.
- `$maxDepth`: Depth limit.

##### Returns
- A nested array tree.

##### Throws
- Underlying repository exceptions.

##### When to Use It
- Debugging slow resolutions and deep dependency chains.

##### Common Mistakes
- Confusing dependency tree with “runtime instances”; this is definition-level data.

### Method: advancedSearch(…)

#### Technical Explanation
Searches services using a flexible filter array (tags, interface, namespace prefix, lifetime, etc.).

##### For Humans: What This Means
It’s a “power search” for service discovery.

##### Parameters
- `$filters`: Filter criteria.

##### Returns
- An `Arrhae` of matches.

##### Throws
- Underlying repository exceptions.

##### When to Use It
- Admin panels and advanced CLI commands.

##### Common Mistakes
- Passing filters with inconsistent key names.

### Method: suggestMigrations(…)

#### Technical Explanation
Suggests migration steps or candidates based on heuristics (e.g., request-scoped patterns, inferred tags).

##### For Humans: What This Means
It gives you “here are improvements you might consider”.

##### Parameters
- None.

##### Returns
- An array of migration suggestions.

##### Throws
- Underlying repository exceptions.

##### When to Use It
- Architecture reviews and refactor planning.

##### Common Mistakes
- Treating suggestions as strict requirements; they’re heuristics.

## Risks, Trade-offs & Recommended Practices
- Risk: Heuristics can be wrong.
  - Why it matters: naming patterns and inferred tags aren’t always reliable.
  - Design stance: discovery should explain its reasons and stay conservative.
  - Recommended practice: expose “why this matched” details in tooling.
- Trade-off: Many operations are in-memory filters.
  - Why it matters: large service catalogs can make discovery heavy.
  - Design stance: use repository pre-filtering first, then in-memory refinement.
  - Recommended practice: cache frequently-used discovery results per environment.

### For Humans: What This Means
Discovery is super helpful, but you should treat its “smartness” as guidance, not truth.

## Related Files & Folders
- `docs_md/Features/Define/Store/ServiceDefinitionRepository.md`: The source of service entities.
- `docs_md/Features/Define/Store/ServiceDependencyRepository.md`: Used to understand relationships and impact.
- `docs_md/Observe/Inspect/Inspector.md`: Uses discovery-style information for diagnostics output.

### For Humans: What This Means
Discovery is the friendly layer built on top of definitions and dependency data.

