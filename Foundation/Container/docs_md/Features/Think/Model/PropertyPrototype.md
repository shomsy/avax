# PropertyPrototype

## Quick Summary
- This file defines an immutable blueprint for property-based dependency injection.
- It exists so property injection can be performed without re-reflecting property metadata.
- It removes the complexity of property edge cases (defaults, nullability, required/optional) by storing them explicitly.

### For Humans: What This Means
It’s a saved description of “inject into this property, using this type, with these fallback rules”.

## Terminology (MANDATORY, EXPANSIVE)
- **Property injection**: Injecting a dependency by assigning to a property.
  - In this file: described by the prototype’s fields.
  - Why it matters: it supports DI patterns beyond constructors.
- **Resolvable type**: A class/interface id the container can resolve.
  - In this file: stored in `$type`.
  - Why it matters: without it, the container can’t fetch the dependency.
- **Default value**: A fallback value on the property declaration.
  - In this file: stored as `$hasDefault` and `$default`.
  - Why it matters: optional injection can gracefully fall back.
- **Required vs optional**: Whether resolution failure is an error.
  - In this file: stored in `$required`.
  - Why it matters: it changes error behavior.

### For Humans: What This Means
This tells the container “is this property a must-have or a nice-to-have?”

## Think of It
Think of property injection like putting a tool on a workbench slot. The prototype tells you which slot, which tool, and whether it’s okay if that tool is missing.

### For Humans: What This Means
It’s a “workbench layout plan” for injection.

## Story Example
You have a property `#[Inject] public ?LoggerInterface $logger = null;`. Analysis produces a `PropertyPrototype` marking it nullable with a default and not required. Runtime injection tries to resolve a logger; if it can’t, the default `null` is acceptable and execution continues.

### For Humans: What This Means
You can make injection optional and keep the app running when a feature isn’t wired.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

- If it’s required and can’t be resolved → error.
- If it’s optional and has default/null allowed → skip or use default.
- The prototype records these rules in simple booleans.

## How It Works (Technical)
The class is `readonly` and supports `var_export()` hydration via `__set_state()`. `toArray()` and `fromArray()` provide a stable representation for caching/compilation.

### For Humans: What This Means
It’s built to be cached: analyze once, save the result, load quickly.

## Architecture Role
- Why it lives here: it’s a Think-phase injection model.
- What depends on it: property injectors and runtime resolution steps.
- What it depends on: nothing heavy; it’s a pure model.
- System-level reasoning: explicit metadata prevents inconsistent property injection behavior.

### For Humans: What This Means
When you encode rules in data, your injector doesn’t need to guess.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Stores all property injection metadata in immutable fields.

##### For Humans: What This Means
You’re writing down the rules for one injectable property.

##### Parameters
- `string $name`
- `string|null $type`
- `bool $hasDefault`
- `mixed $default`
- `bool $allowsNull`
- `bool $required`

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- During reflection analysis.

##### Common Mistakes
- Marking a property as injectable but leaving it without type/explicit id.

### Method: __set_state(…)

#### Technical Explanation
Hydrates from `var_export()` output.

##### For Humans: What This Means
Loads the saved property blueprint from disk.

##### Parameters
- `array $array`

##### Returns
- `self`

##### Throws
- No explicit exceptions.

##### When to Use It
- Cache hydration.

##### Common Mistakes
- Feeding it malformed arrays.

### Method: fromArray(…)

#### Technical Explanation
Restores a property prototype from an array representation.

##### For Humans: What This Means
Rebuild the blueprint from saved data.

##### Parameters
- `array $data`

##### Returns
- `self`

##### Throws
- `InvalidArgumentException` if data is invalid (as documented in the code).

##### When to Use It
- Cache hydration and compilation.

##### Common Mistakes
- Missing keys like `name`.

### Method: toArray(…)

#### Technical Explanation
Serializes the property prototype to a plain array.

##### For Humans: What This Means
Pack the blueprint for storage.

##### Parameters
- None.

##### Returns
- `array`

##### Throws
- No explicit exceptions.

##### When to Use It
- Before caching.

##### Common Mistakes
- Assuming default values are always safe to serialize.

## Risks, Trade-offs & Recommended Practices
- Risk: Property injection can hide dependencies.
  - Why it matters: constructors make dependencies explicit, properties don’t.
  - Design stance: prefer constructor injection; use property injection for optional concerns.
  - Recommended practice: keep injected properties documented and few.

### For Humans: What This Means
Property injection is convenient, but it can make your class’s needs less obvious—use it thoughtfully.

## Related Files & Folders
- `docs_md/Features/Think/Analyze/PrototypeAnalyzer.md`: Creates these prototypes.
- `docs_md/Features/Actions/Inject/PropertyInjector.md`: Uses them to inject at runtime.

### For Humans: What This Means
The analyzer creates the “plan”, and the injector executes it.

