# ServicePrototype

## Quick Summary
- This file defines the container’s full blueprint for constructing and injecting a service.
- It exists to bridge Think-phase reflection analysis and runtime resolution.
- It removes the complexity of runtime reflection by providing a precomputed injection plan.

### For Humans: What This Means
It’s the master blueprint for building one service: constructor, injected properties, injected methods, and whether it can be built.

## Terminology (MANDATORY, EXPANSIVE)
- **Service prototype**: A complete plan for building a service.
  - In this file: includes constructor method prototype, property prototypes, method prototypes, and instantiability flag.
  - Why it matters: the resolver can execute a plan instead of discovering it.
- **Constructor prototype**: A `MethodPrototype` for `__construct`.
  - In this file: stored in `$constructor`.
  - Why it matters: constructor injection is usually the primary DI mechanism.
- **Injected properties**: Properties the container should assign values to.
  - In this file: stored in `$injectedProperties`.
  - Why it matters: enables optional concerns and attribute-based injection.
- **Injected methods**: Methods the container should call with injected arguments.
  - In this file: stored in `$injectedMethods`.
  - Why it matters: supports setter injection patterns.
- **Instantiable flag**: Whether the class can be constructed.
  - In this file: stored in `$isInstantiable`.
  - Why it matters: prevents runtime attempts to build impossible targets.

### For Humans: What This Means
This is the container’s “complete build plan” for one class.

## Think of It
Think of `ServicePrototype` like a LEGO instruction booklet: it tells you what pieces you need and which steps to do (constructor, then inject properties, then call injection methods).

### For Humans: What This Means
You follow the plan and you get a correct object every time.

## Story Example
A class has constructor dependencies plus a couple of `#[Inject]` setters. Analysis produces a `ServicePrototype` containing those method prototypes. Runtime instantiation uses the constructor prototype to resolve arguments, builds the object, then runs property and method injections as described.

### For Humans: What This Means
It ensures the container injects everything consistently without rediscovering it each time.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

- `class` tells what you’re building.
- `constructor` tells how to call `__construct`.
- `injectedProperties` tells what to assign.
- `injectedMethods` tells what extra methods to call.
- `toArray()` / `fromArray()` lets you cache the plan.

## How It Works (Technical)
This `readonly` model supports `var_export()` hydration via `__set_state()`. `fromArray()` reconstructs nested `MethodPrototype` and `PropertyPrototype` objects. `toArray()` flattens the structure for caching.

### For Humans: What This Means
It’s built to be stored and loaded fast, because it’s used a lot.

## Architecture Role
- Why it lives here: it’s the central output of Think-phase analysis.
- What depends on it: runtime resolution engines and injectors.
- What it depends on: method/property prototype models.
- System-level reasoning: a stable blueprint makes resolution faster and more predictable.

### For Humans: What This Means
When the plan is explicit, the container becomes less “magical” and more “mechanical”.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Stores all service injection analysis results in immutable fields.

##### For Humans: What This Means
You’re writing down the entire build plan for one service.

##### Parameters
- `string $class`
- `MethodPrototype|null $constructor`
- `PropertyPrototype[] $injectedProperties`
- `MethodPrototype[] $injectedMethods`
- `bool $isInstantiable`

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- When a prototype analyzer finishes scanning a class.

##### Common Mistakes
- Treating non-instantiable prototypes as usable.

### Method: __set_state(…)

#### Technical Explanation
Hydrates from `var_export()` output.

##### For Humans: What This Means
Loads the saved instruction booklet from disk.

##### Parameters
- `array $array`

##### Returns
- `self`

##### Throws
- No explicit exceptions.

##### When to Use It
- Cache file hydration.

##### Common Mistakes
- Hydrating from stale exported data after refactors.

### Method: fromArray(…)

#### Technical Explanation
Restores a `ServicePrototype` from a plain array representation, reconstructing nested prototypes.

##### For Humans: What This Means
Rebuild the plan from stored data.

##### Parameters
- `array $data`

##### Returns
- `self`

##### Throws
- `InvalidArgumentException` if required fields are missing (as documented in code).

##### When to Use It
- Cache hydration and compilation.

##### Common Mistakes
- Feeding it arrays with wrong nested shapes.

### Method: toArray(…)

#### Technical Explanation
Serializes the full prototype (including nested prototypes) to a plain array.

##### For Humans: What This Means
Pack the whole plan so it can be saved.

##### Parameters
- None.

##### Returns
- `array`

##### Throws
- No explicit exceptions.

##### When to Use It
- Before caching.

##### Common Mistakes
- Assuming closures or non-exportable values are present; prototypes should be pure data.

## Risks, Trade-offs & Recommended Practices
- Risk: Prototype plans can become stale when code changes.
  - Why it matters: injection plans might point to parameters that no longer exist.
  - Design stance: pair caching with invalidation and validation.
  - Recommended practice: clear prototype cache on deploy; validate prototypes in CI.

### For Humans: What This Means
If you change the LEGO set, throw away the old instructions and print new ones.

## Related Files & Folders
- `docs_md/Features/Think/Analyze/PrototypeAnalyzer.md`: Produces service prototypes.
- `docs_md/Features/Think/Cache/PrototypeCache.md`: Stores them.
- `docs_md/Features/Think/Verify/VerifyPrototype.md`: Validates them.

### For Humans: What This Means
PrototypeAnalyzer makes the plan, VerifyPrototype checks the plan, PrototypeCache saves the plan.

