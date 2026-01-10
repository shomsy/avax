# Inspector

## Quick Summary
- This file performs deep introspection of container state for a specific service id: definition presence, caching status, lifetime, tags, and a prototype summary.
- It exists so you can answer “what does the container know about this service?” without manually digging through stores and scopes.
- It removes the complexity of debugging resolution problems by providing a single inspection output structure.

### For Humans: What This Means (Summary)
It’s a “tell me everything you know about this service” button.

## Terminology (MANDATORY, EXPANSIVE)
- **DefinitionStore**: The place where service definitions/bindings are stored.
  - In this file: it’s used to look up whether a service is defined.
  - Why it matters: if the definition isn’t there, resolution can’t work.
- **Scope registry**: A registry tracking whether a service instance exists in a scope (cached).
  - In this file: it’s used to check whether an instance is already cached.
  - Why it matters: cached state affects behavior and debugging.
- **Prototype factory**: A builder that analyzes class reflections to produce a prototype DTO.
  - In this file: it’s used to summarize instantiability and constructor dependencies.
  - Why it matters: it explains “why might instantiation fail?”
- **Lifetime**: Whether a service is singleton/scoped/transient.
  - In this file: lifetime value is read from the definition.
  - Why it matters: it explains caching and scope behavior.

### For Humans: What This Means (Terms)
This tool connects the dots: definition + scope + prototype analysis = a clear picture.

## Think of It
Think of it like a doctor’s chart: it shows whether the patient exists (defined), whether they’re currently in a room (cached), and what conditions they have (prototype dependencies).

### For Humans: What This Means (Think)
You don’t guess why something feels wrong—you read the chart.

## Story Example
A service fails to resolve at runtime. You inspect it and see `defined = false` (binding missing), or `defined = true` but `prototype.error` says reflection failed. You now know whether to fix registration or fix the class signature.

### For Humans: What This Means (Story)
It turns “it broke” into “here’s where it broke”.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Ask for a service id.
2. Inspector checks if it’s defined.
3. Inspector checks if an instance is cached.
4. Inspector tries to analyze the class and count constructor dependencies.
5. You get a single array report back.

## How It Works (Technical)
`inspect($id)` queries the `DefinitionStore` for the service definition and checks `ScopeRegistry` for cached instances. It then tries to run reflection analysis via `DependencyInjectionPrototypeFactory::analyzeReflectionFor()`. Errors are caught and recorded in the `prototype` portion of the report. The method returns a stable associative array with keys: `id`, `defined`, `cached`, `lifetime`, `tags`, `prototype`.

### For Humans: What This Means (How)
It’s a safe way to ask the container “what do you know?” without crashing your tooling when reflection fails.

## Architecture Role
- Why this file lives in `Observe/Inspect`: it’s observability and developer tooling, not core resolution.
- What depends on it: CLI inspect commands and diagnostics facades.
- What it depends on: definition storage, scope tracking, and prototype analysis tools.
- System-level reasoning: introspection reduces container “mystery” and makes debugging repeatable.

### For Humans: What This Means (Role)
The more you can inspect, the less you have to guess.

## Methods 


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation (__construct)
Injects the definition store, scope registry, and prototype factory.

##### For Humans: What This Means (__construct)
It needs the “where are definitions?”, “what’s cached?”, and “how would this be built?” tools.

##### Parameters (__construct)
- `$definitions`: Definition storage access.
- `$scopes`: Scope registry for cached instance presence.
- `$prototypeFactory`: Reflection-based prototype analyzer.

##### Returns (__construct)
- Nothing.

##### Throws (__construct)
- None.

##### When to Use It (__construct)
- When wiring diagnostics tooling.

##### Common Mistakes (__construct)
- Using it without having a fully populated `DefinitionStore`.

### Method: inspect(…)

#### Technical Explanation (inspect)
Builds and returns a stable inspection report for a single service id.

##### For Humans: What This Means (inspect)
It’s the one method you call to understand a service.

##### Parameters (inspect)
- `$id`: Service identifier/abstract.

##### Returns (inspect)
- An array describing definition/caching/prototype summary.

##### Throws (inspect)
- Underlying store/scope exceptions can bubble up; reflection errors are caught and returned as `prototype.error`.

##### When to Use It (inspect)
- CLI inspection and debug panels.

##### Common Mistakes (inspect)
- Assuming `prototype` is always a structured summary; it can be an error array.

## Risks, Trade-offs & Recommended Practices
- Trade-off: Reflection analysis may fail or be expensive.
  - Why it matters: missing classes or autoload issues cause reflection errors.
  - Design stance: tooling should degrade gracefully and report errors.
  - Recommended practice: run inspection in dev/admin contexts; cache prototype analysis where appropriate.

### For Humans: What This Means (Risks)
Inspection should help you debug, not create more failures—so errors must be captured and reported safely.

## Related Files & Folders
- `docs_md/Observe/Inspect/DiagnosticsManager.md`: Facade that exposes this inspector.
- `docs_md/Features/Think/Prototype/DependencyInjectionPrototypeFactory.md`: Produces the reflection analysis used here.
- `docs_md/Features/Define/Store/DefinitionStore.md`: Stores the definitions being inspected.

### For Humans: What This Means (Related)
This file is the “inspection brain” inside the diagnostics toolbox.

