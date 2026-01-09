# PrototypeAnalyzer

## Quick Summary
- This file builds a `ServicePrototype` by analyzing a class with reflection.
- It exists so the container can precompute injection requirements (constructor, properties, methods).
- It removes runtime reflection complexity by converting “class structure” into “injectable blueprint”.

### For Humans: What This Means
It’s the container’s “class inspector” that turns a class into a practical plan for dependency injection.

## Terminology (MANDATORY, EXPANSIVE)
- **Prototype**: A blueprint describing how to build and inject a service.
  - In this file: `ServicePrototype`, `MethodPrototype`, `ParameterPrototype`, `PropertyPrototype`.
  - Why it matters: the resolver can follow the blueprint instead of reflecting repeatedly.
- **Reflection**: PHP’s runtime API for inspecting classes, methods, parameters, types.
  - In this file: used indirectly via `ReflectionTypeAnalyzer` and directly for granular steps.
  - Why it matters: injection points are discovered through reflection.
- **Inject attribute**: Metadata marker (`#[Inject]`) that indicates an injection point.
  - In this file: used to find injectable properties and methods.
  - Why it matters: it controls what the container should inject.
- **Instantiable**: A class that can be constructed (not abstract, not interface, etc.).
  - In this file: checked before building a prototype.
  - Why it matters: a prototype for a non-instantiable class is a dead end.
- **Service id**: The identifier to resolve (often a class/interface name).
  - In this file: derived from attribute configuration or type hints.
  - Why it matters: it’s what the container will later use to resolve dependencies.

### For Humans: What This Means
This analyzer’s job is to answer: “If I had to build this class, what would I need, and where would I inject it?”

## Think of It
Think of `PrototypeAnalyzer` like a customs officer inspecting a suitcase: it opens the class, lists what’s inside (dependencies), and records what’s required.

### For Humans: What This Means
It’s not building the suitcase contents. It’s making a checklist.

## Story Example
You have a class with a constructor that takes `LoggerInterface`, a property marked with `#[Inject]`, and a setter method also marked with `#[Inject]`. `PrototypeAnalyzer` runs once, discovers all injection points, and produces a `ServicePrototype`. Later, runtime injection uses that prototype to inject everything correctly.

### For Humans: What This Means
You don’t pay the reflection cost every time; you “learn it once” and reuse it.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. You give it a class name.
2. It reflects the class.
3. It finds constructor dependencies.
4. It finds properties and methods marked for injection.
5. It returns a `ServicePrototype` that describes all of that.

Beginner FAQ:
- *Does it analyze every method?* It scans methods, but only records ones marked with `#[Inject]`.
- *What if a property has `#[Inject]` but no type?* It throws, because it can’t reliably resolve.

## How It Works (Technical)
`analyze()` reflects a class and requires it to be instantiable. It then calls granular analysis methods:
- `analyzeConstructor()` builds a `MethodPrototype` for the constructor.
- `analyzeProperties()` builds `PropertyPrototype` entries for injectable properties (skipping readonly).
- `analyzeMethods()` builds `MethodPrototype` entries for injectable methods (excluding constructor).

Type resolution prefers non-built-in `ReflectionNamedType` and selects the first non-built-in from union types.

### For Humans: What This Means
It’s a structured scan: constructor first, then injectable properties, then injectable methods.

## Architecture Role
- Why it lives in this folder: it’s a Think/Analyze component that produces models.
- What depends on it: prototype factories/builders and runtime that consumes cached prototypes.
- What it depends on: `ReflectionTypeAnalyzer` and the prototype model classes.
- System-level reasoning: it decouples “reflection” from “resolution”, enabling caching and stability.

### For Humans: What This Means
The container stops guessing at runtime because it already prepared a plan.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Stores a `ReflectionTypeAnalyzer` used to reflect classes consistently.

##### For Humans: What This Means
It plugs the analyzer into a shared reflection helper.

##### Parameters
- `ReflectionTypeAnalyzer $typeAnalyzer`: Reflection helper/cacher.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- Created during container boot or prototype factory setup.

##### Common Mistakes
- Creating multiple analyzers without caching, increasing reflection cost.

### Method: getTypeAnalyzer(…)

#### Technical Explanation
Exposes the underlying type analyzer.

##### For Humans: What This Means
It lets other parts reuse the same reflection helper.

##### Parameters
- None.

##### Returns
- `ReflectionTypeAnalyzer`

##### Throws
- No explicit exceptions.

##### When to Use It
- When building tools or higher-level factories that need the same analyzer.

##### Common Mistakes
- Treating it as mutable configuration; it’s a dependency, not settings.

### Method: analyze(…)

#### Technical Explanation
Analyzes a class and returns a `ServicePrototype`. Throws if the class isn’t instantiable or if required injection information can’t be derived.

##### For Humans: What This Means
It’s the “build the blueprint” method.

##### Parameters
- `string $class`: Fully qualified class name.

##### Returns
- `ServicePrototype`: The injection blueprint.

##### Throws
- `RuntimeException`: If the class can’t be prototyped (not instantiable, missing resolvable injection type).

##### When to Use It
- During the Think phase, before runtime resolution.

##### Common Mistakes
- Trying to prototype interfaces/abstract classes.

### Method: analyzeConstructor(…)

#### Technical Explanation
Builds a `MethodPrototype` for the constructor (or returns `null` when none exists).

##### For Humans: What This Means
It figures out what constructor arguments the class needs.

##### Parameters
- `ReflectionClass $reflector`: Reflected class.

##### Returns
- `MethodPrototype|null`

##### Throws
- No explicit exceptions.

##### When to Use It
- Used internally by `analyze()`.

##### Common Mistakes
- Assuming null means “no dependencies”; it can also mean “default constructor”.

### Method: analyzeProperties(…)

#### Technical Explanation
Scans properties, selects those with `#[Inject]`, and creates `PropertyPrototype` entries. Skips readonly properties.

##### For Humans: What This Means
It finds “inject into this property” points.

##### Parameters
- `ReflectionClass $reflector`

##### Returns
- `array`: Map of property name → `PropertyPrototype`.

##### Throws
- `RuntimeException`: If an injectable property has no resolvable type.

##### When to Use It
- Used internally by `analyze()`.

##### Common Mistakes
- Marking a property with `#[Inject]` but not giving a type or explicit abstract id.

### Method: analyzeMethods(…)

#### Technical Explanation
Scans methods, selects those with `#[Inject]`, and creates `MethodPrototype` entries (excluding the constructor).

##### For Humans: What This Means
It finds “call this method and inject arguments” points.

##### Parameters
- `ReflectionClass $reflector`

##### Returns
- `MethodPrototype[]`

##### Throws
- No explicit exceptions.

##### When to Use It
- Used internally by `analyze()`.

##### Common Mistakes
- Expecting un-annotated methods to be injected.

### Method: analyzeParameter(…)

#### Technical Explanation
Converts a `ReflectionParameter` into a `ParameterPrototype` with type/default/variadic/nullability flags.

##### For Humans: What This Means
It turns one parameter into a detailed “how to resolve me” instruction.

##### Parameters
- `ReflectionParameter $param`

##### Returns
- `ParameterPrototype`

##### Throws
- No explicit exceptions.

##### When to Use It
- Used internally when building method prototypes.

##### Common Mistakes
- Leaving parameters untyped and expecting DI to guess.

## Risks, Trade-offs & Recommended Practices
- Risk: Reflection is expensive.
  - Why it matters: repeated reflection per request is slow.
  - Design stance: analyze once, cache prototypes, reuse.
  - Recommended practice: use `PrototypeCache` implementations in production.
- Risk: Attribute/type misuse leads to runtime failures.
  - Why it matters: missing types create unresolvable injection points.
  - Design stance: treat prototype validation as a first-class step.
  - Recommended practice: run `VerifyPrototype` before caching or compiling.

### For Humans: What This Means
Reflection is like reading the whole manual every time you press a button. Cache the manual’s conclusions instead.

## Related Files & Folders
- `docs_md/Features/Think/Analyze/ReflectionTypeAnalyzer.md`: Reflection helper used here.
- `docs_md/Features/Think/Model/ServicePrototype.md`: The output blueprint.
- `docs_md/Features/Think/Verify/VerifyPrototype.md`: Validation layer to catch mistakes early.

### For Humans: What This Means
If you want to understand “input → blueprint → validation”, follow those three files.

