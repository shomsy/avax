# ServiceDependency

## Quick Summary
- This file defines an immutable edge in your service dependency graph: “A depends on B”, including how (constructor/property/method/etc.) and whether it’s optional.
- It exists so dependency relationships are first-class data, not implicit guesses.
- It removes the complexity of dependency analysis by giving you a consistent, serializable relationship object.

### For Humans: What This Means (Summary)
It’s a single arrow in your container’s dependency map.

## Terminology (MANDATORY, EXPANSIVE)
- **Dependency edge**: A directed relationship between two nodes in a graph.
  - In this file: `serviceId -> dependsOnId` is the edge.
  - Why it matters: graphs let you detect cycles and understand impact.
- **Dependent vs dependency**: The service that needs something vs the thing it needs.
  - In this file: `serviceId` is the dependent; `dependsOnId` is the dependency.
  - Why it matters: mixing these up breaks analysis.
- **Dependency type**: How the dependency is injected.
  - In this file: `constructor`, `property`, `method`, `setter`, `interface` (and sometimes inverse types).
  - Why it matters: injection timing affects strength and lazy-loading options.
- **Optional dependency**: A dependency that can fail without breaking the service.
  - In this file: `isOptional` influences `canBeLazyLoaded()`.
  - Why it matters: optional dependencies reduce coupling but increase “maybe” behavior.
- **Strength score**: A heuristic that ranks coupling.
  - In this file: `getStrengthScore()` maps types to 1–10.
  - Why it matters: it helps you prioritize refactors and monitoring.
- **Inverse relationship**: The reverse “who depends on me?” edge.
  - In this file: `getInverse()` flips direction for analysis.
  - Why it matters: some graph queries are easier with reversed edges.

### For Humans: What This Means (Terms)
This file is about making “service relationships” explicit so you can reason about them.

## Think of It
Think of it like a “requires” link in a recipe: the dish (service) requires an ingredient (dependency). The type tells you when you need it (before cooking vs as garnish), and optional means “nice to have”.

### For Humans: What This Means (Think)
It turns vague coupling into something you can see, measure, and fix.

## Story Example
Your container starts failing with circular dependencies, but only sometimes. You store dependencies as `ServiceDependency` edges during registration and later ask the repository to detect cycles. You also discover that many dependencies are “optional property injections” and can be made lazy, reducing startup coupling.

### For Humans: What This Means (Story)
You stop guessing about dependencies and start using a real map.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. This object says “Service A needs Service B.”
2. It also says *how* A gets B (constructor/property/etc.).
3. It can tell you if the dependency is strong or weak.
4. It can tell you if it can be lazy-loaded.
5. It can serialize to an array for storage and be rebuilt from that array.

## How It Works (Technical)
The constructor stores readonly fields and validates invariants (non-empty ids, no self-dependency, allowed type). Static helpers (`fromArray()`, `getTableName()`) define the persistence boundary. Instance helpers (`toArray()`, `isPropertyDependency()`, `isMethodDependency()`, `isConstructorDependency()`, `getStrengthScore()`, `canBeLazyLoaded()`, `getInverse()`, `getDescription()`) provide analysis-friendly semantics without exposing storage concerns.

### For Humans: What This Means (How)
It’s strict when created, and helpful when you analyze and debug.

## Architecture Role
- Why this file lives in `Features/Define/Store`: it’s part of storing the “relationship facts” of your service ecosystem.
- What depends on it: dependency repositories, health analyzers, discovery tools, cycle detectors.
- What it depends on: basic validation and timestamps; it stays lightweight by design.
- System-level reasoning: containers scale when dependency shape is visible and enforceable.

### For Humans: What This Means (Role)
If you can’t see relationships, you can’t control complexity.

## Methods 


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation (__construct)
Creates a dependency edge and validates that it’s a valid relationship.

##### For Humans: What This Means (__construct)
It prevents broken graph data from ever being created.

##### Parameters (__construct)
- `$serviceId`: The service that depends on something.
- `$dependsOnId`: The service that is depended upon.
- `$dependencyType`: The injection mechanism label.
- `$isOptional`: Whether resolution should tolerate failure.
- `$createdAt`: Optional timestamp for audit trails.

##### Returns (__construct)
- Returns nothing.

##### Throws (__construct)
- `InvalidArgumentException` when ids are empty, self-dependency exists, or the type is invalid.

##### When to Use It (__construct)
- When you register services or auto-discover relationships.

##### Common Mistakes (__construct)
- Using inconsistent dependency type strings across the codebase.

### Method: fromArray(…)

#### Technical Explanation (fromArray)
Hydrates an entity from persisted array data.

##### For Humans: What This Means (fromArray)
It rebuilds the relationship from storage.

##### Parameters (fromArray)
- `$data`: Storage array with keys like `service_id` and `depends_on_id`.

##### Returns (fromArray)
- A new `ServiceDependency`.

##### Throws (fromArray)
- `DateMalformedStringException` if `created_at` parsing fails.

##### When to Use It (fromArray)
- Mapping database rows to domain objects.

##### Common Mistakes (fromArray)
- Missing required keys in the array.

### Method: getTableName(…)

#### Technical Explanation (getTableName)
Returns the canonical table name for persisted dependency edges.

##### For Humans: What This Means (getTableName)
It’s the stable “storage location name” for dependency edges.

##### Parameters (getTableName)
- None.

##### Returns (getTableName)
- The table name string.

##### Throws (getTableName)
- None.

##### When to Use It (getTableName)
- Repositories and migrations.

##### Common Mistakes (getTableName)
- Treating it as a configurable value.

### Method: toArray(…)

#### Technical Explanation (toArray)
Serializes the dependency edge to an associative array.

##### For Humans: What This Means (toArray)
It makes the relationship storable and transferable.

##### Parameters (toArray)
- None.

##### Returns (toArray)
- A storage-friendly array.

##### Throws (toArray)
- None.

##### When to Use It (toArray)
- Persistence, export, diagnostics.

##### Common Mistakes (toArray)
- Assuming `created_at` is always set; it’s nullable.

### Method: isPropertyDependency(…)

#### Technical Explanation (isPropertyDependency)
Checks whether the dependency type indicates property injection.

##### For Humans: What This Means (isPropertyDependency)
It answers: “Is this injected into a property later?”

##### Parameters (isPropertyDependency)
- None.

##### Returns (isPropertyDependency)
- `true` when type is `property`.

##### Throws (isPropertyDependency)
- None.

##### When to Use It (isPropertyDependency)
- Injection strategy and analysis.

##### Common Mistakes (isPropertyDependency)
- Assuming “property” means “public”; it’s just a label here.

### Method: isMethodDependency(…)

#### Technical Explanation (isMethodDependency)
Checks whether the dependency is injected via a method/setter style.

##### For Humans: What This Means (isMethodDependency)
It answers: “Does something call a method to inject this?”

##### Parameters (isMethodDependency)
- None.

##### Returns (isMethodDependency)
- `true` for `method` or `setter`.

##### Throws (isMethodDependency)
- None.

##### When to Use It (isMethodDependency)
- Determining lazy-loading viability or coupling strength.

##### Common Mistakes (isMethodDependency)
- Forgetting that setters are treated as a method dependency.

### Method: getStrengthScore(…)

#### Technical Explanation (getStrengthScore)
Returns a heuristic coupling strength score based on dependency type.

##### For Humans: What This Means (getStrengthScore)
It’s a quick “how tight is this relationship?” rating.

##### Parameters (getStrengthScore)
- None.

##### Returns (getStrengthScore)
- Integer score 1–10.

##### Throws (getStrengthScore)
- None.

##### When to Use It (getStrengthScore)
- Sorting dependencies and prioritizing refactors.

##### Common Mistakes (getStrengthScore)
- Treating the score as a formal architecture metric.

### Method: canBeLazyLoaded(…)

#### Technical Explanation (canBeLazyLoaded)
Determines whether the dependency could be resolved lazily based on type and optionality.

##### For Humans: What This Means (canBeLazyLoaded)
It tells you if you can postpone resolving this dependency.

##### Parameters (canBeLazyLoaded)
- None.

##### Returns (canBeLazyLoaded)
- `true` if optional or non-constructor.

##### Throws (canBeLazyLoaded)
- None.

##### When to Use It (canBeLazyLoaded)
- Startup optimization and circular dependency mitigation.

##### Common Mistakes (canBeLazyLoaded)
- Marking critical dependencies as optional just to make them “lazy”.

### Method: isConstructorDependency(…)

#### Technical Explanation (isConstructorDependency)
Checks whether the dependency is required at instantiation time.

##### For Humans: What This Means (isConstructorDependency)
Constructor deps are the “must have now” kind.

##### Parameters (isConstructorDependency)
- None.

##### Returns (isConstructorDependency)
- `true` if type is `constructor`.

##### Throws (isConstructorDependency)
- None.

##### When to Use It (isConstructorDependency)
- Determining strictness and “must resolve to build object” constraints.

##### Common Mistakes (isConstructorDependency)
- Modeling all dependencies as constructor dependencies and creating rigid graphs.

### Method: getInverse(…)

#### Technical Explanation (getInverse)
Creates the reverse relationship (swap dependent/dependency) for analysis workflows.

##### For Humans: What This Means (getInverse)
It answers: “Who depends on me?” by flipping the arrow.

##### Parameters (getInverse)
- None.

##### Returns (getInverse)
- A new `ServiceDependency` representing the inverse.

##### Throws (getInverse)
- `InvalidArgumentException` if the generated type is considered invalid by validation rules.

##### When to Use It (getInverse)
- Impact analysis and reverse-graph queries.

##### Common Mistakes (getInverse)
- Persisting inverse edges without a clear strategy (you can derive them on demand).

### Method: getDescription(…)

#### Technical Explanation (getDescription)
Produces a human-readable string for logs, debugging, and UI.

##### For Humans: What This Means (getDescription)
It’s a friendly sentence explaining the relationship.

##### Parameters (getDescription)
- None.

##### Returns (getDescription)
- A formatted description string.

##### Throws (getDescription)
- None.

##### When to Use It (getDescription)
- CLI tools, diagnostics output, logs.

##### Common Mistakes (getDescription)
- Parsing this string as data; it’s for humans, not machines.

## Risks, Trade-offs & Recommended Practices
- Risk: Dependency type is a string, not a strict enum.
  - Why it matters: inconsistent spelling creates analysis bugs.
  - Design stance: keep the entity lightweight but validate against a known set.
  - Recommended practice: centralize allowed types and reuse them across creation points.
- Trade-off: Inverse edges can confuse your data model.
  - Why it matters: storing both forward and inverse edges can double data and drift.
  - Design stance: derive inverse edges for analysis instead of persisting them blindly.
  - Recommended practice: persist only forward edges and compute inverse when needed.

### For Humans: What This Means (Risks)
Keep the “arrow language” consistent, and don’t store extra arrows unless you have to.

## Related Files & Folders
- `docs_md/Features/Define/Store/ServiceDependencyRepository.md`: Stores and analyzes these edges.
- `docs_md/Features/Define/Store/ServiceDefinitionEntity.md`: The services being connected by these edges.
- `docs_md/Guard/Rules/DependencyValidationRule.md`: Validates dependency declarations before they become edges.

### For Humans: What This Means (Related)
Edges connect services; repositories analyze edges; guard rules stop bad edges from happening.

