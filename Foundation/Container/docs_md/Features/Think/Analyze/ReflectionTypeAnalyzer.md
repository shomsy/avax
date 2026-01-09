# ReflectionTypeAnalyzer

## Quick Summary
- This file centralizes reflection and type analysis operations used by the container.
- It exists to provide consistent error handling and caching around reflection calls.
- It removes duplication and edge-case bugs by making one “official” reflection helper.

### For Humans: What This Means
It’s the container’s shared microscope: everyone uses the same tool to look at classes.

## Terminology (MANDATORY, EXPANSIVE)
- **ReflectionClass**: PHP object representing a class’s metadata.
  - In this file: cached in `$reflectionCache`.
  - Why it matters: reflection is expensive; caching helps.
- **Type formatting**: Converting `ReflectionType` objects into a readable string form.
  - In this file: `formatType()` handles union/intersection types.
  - Why it matters: the container needs stable type names to resolve dependencies.
- **Injectable property/method**: A member marked with `#[Inject]`.
  - In this file: discovered via attribute scanning helpers.
  - Why it matters: that’s how the container knows where to inject.
- **ResolutionException**: A domain exception representing resolution/reflection failures.
  - In this file: thrown when reflection fails, ensuring container-level error semantics.
  - Why it matters: consistent exception types improve diagnostics and error handling.

### For Humans: What This Means
This class makes reflection reliable and predictable across the container.

## Think of It
Think of it like a librarian who can quickly find books (classes) and knows how to handle “book doesn’t exist” errors politely.

### For Humans: What This Means
Instead of every part of the container searching on its own and failing differently, everyone asks the same librarian.

## Story Example
You build prototypes for hundreds of services. Without caching, you reflect the same class repeatedly and waste time. With `ReflectionTypeAnalyzer`, reflections are cached and reused, and errors about missing classes come out as consistent `ResolutionException`s.

### For Humans: What This Means
Your container becomes faster and your error messages become clearer.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Call `reflectClass()` to get a `ReflectionClass`.
2. Use helper methods to scan injection points and types.
3. If the class can’t be reflected, you get a container-level exception.

## How It Works (Technical)
The class stores a cache of `ReflectionClass` instances keyed by class name. Most helpers call `reflectClass()` first. Type formatting supports union and intersection types. Attribute scanning checks for the `Inject` attribute (and the explicit namespace string).

### For Humans: What This Means
It’s mostly “glue”: caching + consistent reflection behavior.

## Architecture Role
- Why it lives here: it supports Think/Analyze logic across the container.
- What depends on it: `PrototypeAnalyzer` and any reflection-based builders.
- What it depends on: PHP reflection APIs and `ResolutionException`.
- System-level reasoning: one centralized reflection helper reduces bugs and improves performance.

### For Humans: What This Means
When you centralize reflection logic, you stop reinventing the same tricky edge cases.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Initializes the analyzer with an optional pre-populated reflection cache.

##### For Humans: What This Means
You can “seed” the cache if you already have reflection objects.

##### Parameters
- `ReflectionClass[] $reflectionCache`: Optional cache.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- Typically constructed once and reused.

##### Common Mistakes
- Treating the cache as authoritative after code changes; it’s in-memory only.

### Method: isInstantiable(…)

#### Technical Explanation
Checks if a class can be instantiated, returning false on reflection failure.

##### For Humans: What This Means
It’s a safe “can I build this?” check.

##### Parameters
- `string $className`

##### Returns
- `bool`

##### Throws
- No; it catches `ResolutionException` and returns false.

##### When to Use It
- Before attempting prototype construction.

##### Common Mistakes
- Assuming instantiable means “resolvable”; dependencies can still be missing.

### Method: reflectClass(…)

#### Technical Explanation
Returns a cached `ReflectionClass` instance or creates one, throwing `ResolutionException` on failure.

##### For Humans: What This Means
It safely gives you “a reflection view” of a class.

##### Parameters
- `string $className`

##### Returns
- `ReflectionClass`

##### Throws
- `ResolutionException`: When reflection fails.

##### When to Use It
- Whenever you need to inspect class metadata.

##### Common Mistakes
- Passing non-FQCN strings or misspelled class names.

### Method: getConstructor(…)

#### Technical Explanation
Returns the class constructor method reflection or null if not available.

##### For Humans: What This Means
It tells you whether the class has a custom constructor.

##### Parameters
- `string $className`

##### Returns
- `ReflectionMethod|null`

##### Throws
- No; it catches `ResolutionException`.

##### When to Use It
- During analysis to build constructor prototypes.

##### Common Mistakes
- Treating `null` as “no dependencies”; it can be “default constructor”.

### Method: getInjectableProperties(…)

#### Technical Explanation
Scans a class’s properties and returns structured information for properties marked with `#[Inject]`.

##### For Humans: What This Means
It finds “these properties want injection” and returns a friendly list of what was found.

##### Parameters
- `string $className`: The class to inspect.

##### Returns
- `array`: A list of property records (name, type, nullability, defaults, visibility, attributes).

##### Throws
- `ResolutionException`: If the class can’t be reflected.

##### When to Use It
- When you’re building prototypes or tooling that needs property injection points.

##### Common Mistakes
- Expecting it to include properties without `#[Inject]`.

### Method: getInjectableMethods(…)

#### Technical Explanation
Scans a class’s methods and returns structured information for methods marked with `#[Inject]`, including analyzed parameter metadata.

##### For Humans: What This Means
It finds “these methods want injection calls” and summarizes what arguments they need.

##### Parameters
- `string $className`: The class to inspect.

##### Returns
- `array`: A list of method records (name, parameters, visibility, attributes).

##### Throws
- `ResolutionException`: If the class can’t be reflected.

##### When to Use It
- When building setter-injection prototypes or inspection tooling.

##### Common Mistakes
- Assuming it respects custom conventions; it only follows attributes.

### Method: analyzeMethodParameters(…)

#### Technical Explanation
Converts a `ReflectionMethod`’s parameters into an array of metadata (type, default, nullability, variadic, position).

##### For Humans: What This Means
It turns “raw reflected parameters” into a usable description.

##### Parameters
- `ReflectionMethod $method`: The method to analyze.

##### Returns
- `array`: Parameter descriptors.

##### Throws
- Depends on parameter default value extraction (runtime-dependent), but generally no explicit exceptions.

##### When to Use It
- When you need to understand “what does this method require?”.

##### Common Mistakes
- Treating returned `type` as always resolvable; built-ins still need overrides/defaults.

### Method: canResolveType(…)

#### Technical Explanation
Checks whether a type string represents something resolvable by the container (class, interface, or enum).

##### For Humans: What This Means
It answers: “Is this type a real thing the container can fetch?”

##### Parameters
- `string|null $type`: Type string to check.

##### Returns
- `bool`

##### Throws
- No explicit exceptions.

##### When to Use It
- During analysis to decide whether a type hint is actionable.

##### Common Mistakes
- Treating `?Foo` (nullable syntax) as a raw class name; normalization matters when you integrate this check into other logic.

## Risks, Trade-offs & Recommended Practices
- Risk: Reflection caching is per-process.
  - Why it matters: long-running processes will keep cached reflections until restart.
  - Design stance: cache for performance; keep it scoped to the container lifecycle.
  - Recommended practice: don’t share analyzers across unrelated container lifecycles.

### For Humans: What This Means
Caching is great, but it’s not magic persistence—restart resets it.

## Related Files & Folders
- `docs_md/Features/Think/Analyze/PrototypeAnalyzer.md`: Builds prototypes using this analyzer.
- `docs_md/Features/Think/Model/index.md`: Prototype models produced by analysis.

### For Humans: What This Means
This class is a helper; the real “output” is the prototypes it enables.
