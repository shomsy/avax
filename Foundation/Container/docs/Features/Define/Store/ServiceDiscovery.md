# ServiceDiscovery

## Quick Summary
- This file provides higher-level “search and recommendation” features on top of service definitions and dependency data.
- It exists so you can ask product-grade questions like “what services implement this interface?”, “what’s cacheable?”, “what alternatives exist?”.
- It removes the complexity of building discovery logic scattered across tooling and boot code.

### For Humans: What This Means (Summary)
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

### For Humans: What This Means (Terms)
Instead of memorizing service ids, you can browse and discover services like a catalog.

## Think of It
Think of it like a music app: the repository stores all songs (definitions), but discovery builds playlists, searches, and “you might also like” recommendations.

### For Humans: What This Means (Think)
This turns container data into a developer experience feature.

## Story Example
You want to migrate from one caching implementation to another. You use discovery to find all cache-related services, detect conflicts (multiple cache implementations), and generate recommendations for safe replacements. You also compute dependency trees to see which parts of the system will be affected.

### For Humans: What This Means (Story)
It helps you change things safely because you can see the ecosystem impact.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Repositories give you raw lists of services.
2. Discovery turns those lists into “smart answers”.
3. You ask “find by interface” or “give me alternatives”, and you get meaningful results.
4. It’s mostly filtering, ranking, and summarizing.

## How It Works (Technical)
`ServiceDiscovery` composes repositories (service definitions and dependencies) and offers domain queries implemented with a mix of repository calls and in-memory filtering. Interface-based queries use `is_subclass_of()` and `class_implements()`. Recommendations combine multiple signals (shared dependencies, tags, namespace proximity) and can produce migration suggestions. Health checks detect missing dependencies, request-scoped heuristics, and potential interface conflicts.

### For Humans: What This Means (How)
It’s a brain that reads service metadata and tells you what it means.

## Architecture Role
- Why this file lives in `Features/Define/Store`: it’s built on the stored truth of definitions and dependency edges.
- What depends on it: CLI tools, diagnostics, admin UIs, and “container introspection” workflows.
- What it depends on: `ServiceDefinitionRepository` and `ServiceDependencyRepository` to load the data.
- System-level reasoning: a container without discovery is usable only by experts; discovery makes it humane.

### For Humans: What This Means (Role)
This file is about making the container navigable for real developers.

## Methods 


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation (__construct)
Wires in repositories needed to perform discovery queries.

##### For Humans: What This Means (__construct)
Discovery needs access to the “service list” and the “dependency map”.

##### Parameters (__construct)
- Depends on signature; conceptually: a service repository and a dependency repository.

##### Returns (__construct)
- Nothing.

##### Throws (__construct)
- Underlying dependency injection errors (construction-time).

##### When to Use It (__construct)
- When wiring diagnostic and tooling layers.

##### Common Mistakes (__construct)
- Creating it without repositories initialized (no data to discover).

### Method: findCacheableServices(…)

#### Technical Explanation (findCacheableServices)
Finds services that are good candidates for caching based on lifetime/tags/heuristics.

##### For Humans: What This Means (findCacheableServices)
It answers: “What should probably be cached to speed things up?”

##### Parameters (findCacheableServices)
- `$environment`: Optional environment filter.

##### Returns (findCacheableServices)
- An `Arrhae` of recommended services.

##### Throws (findCacheableServices)
- Underlying repository exceptions.

##### When to Use It (findCacheableServices)
- Performance tuning and boot optimization.

##### Common Mistakes (findCacheableServices)
- Assuming “cacheable” means “safe as singleton”; scope rules still apply.

### Method: findServicesByInterfaces(…)

#### Technical Explanation (findServicesByInterfaces)
Returns services that implement *all* interfaces in the provided list.

##### For Humans: What This Means (findServicesByInterfaces)
It finds services that match a full “capability checklist”.

##### Parameters (findServicesByInterfaces)
- `$interfaces`: Interface names.
- `$environment`: Optional environment filter.

##### Returns (findServicesByInterfaces)
- An `Arrhae` of matching services.

##### Throws (findServicesByInterfaces)
- Underlying repository exceptions.

##### When to Use It (findServicesByInterfaces)
- Searching for implementations with multiple constraints.

##### Common Mistakes (findServicesByInterfaces)
- Passing non-existent interfaces (autoloading).

### Method: findServicesWithCapabilities(…)

#### Technical Explanation (findServicesWithCapabilities)
Finds services matching capabilities (often via tags) using `AND` / `OR` semantics.

##### For Humans: What This Means (findServicesWithCapabilities)
It’s tag search, but framed as “capability” search.

##### Parameters (findServicesWithCapabilities)
- `$capabilities`: List of capabilities.
- `$operator`: `AND` or `OR`.

##### Returns (findServicesWithCapabilities)
- An `Arrhae` of matching services.

##### Throws (findServicesWithCapabilities)
- Underlying repository exceptions.

##### When to Use It (findServicesWithCapabilities)
- Discovery UI and developer tooling.

##### Common Mistakes (findServicesWithCapabilities)
- Mixing “capabilities” and “tags” conventions without standardization.

### Method: findAlternativeServices(…)

#### Technical Explanation (findAlternativeServices)
Finds candidate replacements for a service based on shared interfaces/tags/domain proximity.

##### For Humans: What This Means (findAlternativeServices)
It helps you find “other services that could work here”.

##### Parameters (findAlternativeServices)
- `$serviceId`: The service to replace.
- `$environment`: Optional environment filter.

##### Returns (findAlternativeServices)
- An `Arrhae` of alternatives.

##### Throws (findAlternativeServices)
- Underlying repository exceptions.

##### When to Use It (findAlternativeServices)
- Migration planning and refactoring.

##### Common Mistakes (findAlternativeServices)
- Treating alternatives as guaranteed drop-in replacements; behavior can differ.

### Method: findServicesByInterface(…)

#### Technical Explanation (findServicesByInterface)
Returns services that implement a specific interface or extend a base class.

##### For Humans: What This Means (findServicesByInterface)
It answers: “Who can act like this contract?”

##### Parameters (findServicesByInterface)
- `$interface`: Interface or base-class name.
- `$environment`: Optional environment filter.

##### Returns (findServicesByInterface)
- An `Arrhae` of implementations.

##### Throws (findServicesByInterface)
- Underlying repository exceptions.

##### When to Use It (findServicesByInterface)
- Interface-based resolution audits and conflict detection.

##### Common Mistakes (findServicesByInterface)
- Forgetting to consider multiple implementations (ambiguity risk).

### Method: getServiceRecommendations(…)

#### Technical Explanation (getServiceRecommendations)
Computes recommendations for related services based on shared context and similarity.

##### For Humans: What This Means (getServiceRecommendations)
It’s “people also used these services” but for service definitions.

##### Parameters (getServiceRecommendations)
- `$currentServiceId`: Seed service id for recommendations.

##### Returns (getServiceRecommendations)
- An array of recommendation entries.

##### Throws (getServiceRecommendations)
- Underlying repository exceptions.

##### When to Use It (getServiceRecommendations)
- Admin UI and onboarding workflows.

##### Common Mistakes (getServiceRecommendations)
- Showing recommendations without explaining the reason (“why this is recommended”).

### Method: analyzeServiceHealth(…)

#### Technical Explanation (analyzeServiceHealth)
Generates a health report for the service ecosystem (missing deps, conflicts, risky patterns).

##### For Humans: What This Means (analyzeServiceHealth)
It’s a “health check” for your service catalog.

##### Parameters (analyzeServiceHealth)
- None.

##### Returns (analyzeServiceHealth)
- An array of health issues and a score.

##### Throws (analyzeServiceHealth)
- Underlying repository exceptions.

##### When to Use It (analyzeServiceHealth)
- CI checks and boot diagnostics.

##### Common Mistakes (analyzeServiceHealth)
- Ignoring warnings until they become production incidents.

### Method: findPotentialConflicts(…)

#### Technical Explanation (findPotentialConflicts)
Identifies interfaces with multiple implementations that could cause ambiguous resolution.

##### For Humans: What This Means (findPotentialConflicts)
It finds places where “the container might pick the wrong one”.

##### Parameters (findPotentialConflicts)
- None.

##### Returns (findPotentialConflicts)
- An `Arrhae` of conflicting service groups.

##### Throws (findPotentialConflicts)
- Underlying repository exceptions.

##### When to Use It (findPotentialConflicts)
- Before enabling interface-based autowiring.

##### Common Mistakes (findPotentialConflicts)
- Trying to fix conflicts by removing implementations instead of configuring explicit bindings.

### Method: getDependencyTree(…)

#### Technical Explanation (getDependencyTree)
Returns a structured dependency tree for a given service id up to a depth limit.

##### For Humans: What This Means (getDependencyTree)
It shows you what a service pulls in and how deep it goes.

##### Parameters (getDependencyTree)
- `$serviceId`: Root id.
- `$maxDepth`: Depth limit.

##### Returns (getDependencyTree)
- A nested array tree.

##### Throws (getDependencyTree)
- Underlying repository exceptions.

##### When to Use It (getDependencyTree)
- Debugging slow resolutions and deep dependency chains.

##### Common Mistakes (getDependencyTree)
- Confusing dependency tree with “runtime instances”; this is definition-level data.

### Method: advancedSearch(…)

#### Technical Explanation (advancedSearch)
Searches services using a flexible filter array (tags, interface, namespace prefix, lifetime, etc.).

##### For Humans: What This Means (advancedSearch)
It’s a “power search” for service discovery.

##### Parameters (advancedSearch)
- `$filters`: Filter criteria.

##### Returns (advancedSearch)
- An `Arrhae` of matches.

##### Throws (advancedSearch)
- Underlying repository exceptions.

##### When to Use It (advancedSearch)
- Admin panels and advanced CLI commands.

##### Common Mistakes (advancedSearch)
- Passing filters with inconsistent key names.

### Method: suggestMigrations(…)

#### Technical Explanation (suggestMigrations)
Suggests migration steps or candidates based on heuristics (e.g., request-scoped patterns, inferred tags).

##### For Humans: What This Means (suggestMigrations)
It gives you “here are improvements you might consider”.

##### Parameters (suggestMigrations)
- None.

##### Returns (suggestMigrations)
- An array of migration suggestions.

##### Throws (suggestMigrations)
- Underlying repository exceptions.

##### When to Use It (suggestMigrations)
- Architecture reviews and refactor planning.

##### Common Mistakes (suggestMigrations)
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

### For Humans: What This Means (Risks)
Discovery is super helpful, but you should treat its “smartness” as guidance, not truth.

## Related Files & Folders
- `docs_md/Features/Define/Store/ServiceDefinitionRepository.md`: The source of service entities.
- `docs_md/Features/Define/Store/ServiceDependencyRepository.md`: Used to understand relationships and impact.
- `docs_md/Observe/Inspect/Inspector.md`: Uses discovery-style information for diagnostics output.

### For Humans: What This Means (Related)
Discovery is the friendly layer built on top of definitions and dependency data.

