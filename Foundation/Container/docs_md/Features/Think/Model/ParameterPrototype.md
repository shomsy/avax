# ParameterPrototype

## Quick Summary
- This file defines an immutable specification for resolving a single parameter during injection.
- It exists to capture type/default/nullability/variadic rules once, then reuse them.
- It removes the complexity of “parameter edge cases” by making them explicit data.

### For Humans: What This Means
It’s a tiny rule card that tells the container how to fill in one method argument.

## Terminology (MANDATORY, EXPANSIVE)
- **Type hint**: The declared type of a parameter (class/interface/built-in).
  - In this file: stored in `$type`.
  - Why it matters: class/interface types guide container resolution.
- **Default value**: A fallback value in the signature.
  - In this file: stored as `$hasDefault` and `$default`.
  - Why it matters: it can prevent resolution failures.
- **Nullability**: Whether null is allowed.
  - In this file: stored in `$allowsNull`.
  - Why it matters: allows graceful injection behavior.
- **Variadic**: A parameter like `...$args`.
  - In this file: stored in `$isVariadic`.
  - Why it matters: injection must collect multiple values.
- **Required**: Whether the parameter must be resolved successfully.
  - In this file: stored in `$required`.
  - Why it matters: it affects whether failure becomes an exception.

### For Humans: What This Means
This is how the container remembers “is this optional?” and “what type should I resolve?”

## Think of It
Think of it like an order ticket at a coffee shop: it lists the drink (type), whether it can be decaf (nullable), if there’s a default choice, and whether the order is mandatory.

### For Humans: What This Means
It prevents misunderstandings about what’s expected.

## Story Example
Your constructor has `__construct(LoggerInterface $logger, string $channel = 'app')`. Analysis creates a parameter prototype for `$logger` (type-based resolution) and one for `$channel` (has default). Runtime injection resolves the logger and uses the default channel unless overridden.

### For Humans: What This Means
Typed dependencies come from the container; scalars typically come from defaults or overrides.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

- Typed class/interface → container resolves it.
- Built-in/scalar type → you need a default or an override.
- Nullable → null is allowed as a fallback.

## How It Works (Technical)
This is a `readonly` data class with serialization helpers. `toArray()` flattens fields. `fromArray()` restores them. `__set_state()` supports `var_export()` hydration for file caches.

### For Humans: What This Means
It’s built to be saved and loaded quickly.

## Architecture Role
- Why it lives here: it’s the fundamental unit of injection planning.
- What depends on it: `MethodPrototype` and runtime parameter resolvers.
- What it depends on: no heavy dependencies; it’s a pure model.
- System-level reasoning: explicit parameter metadata prevents ambiguous injection behavior.

### For Humans: What This Means
When the rules are explicit, debugging becomes much easier.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Stores all parameter resolution metadata in immutable fields.

##### For Humans: What This Means
You’re writing down “how to fill this argument” once.

##### Parameters
- `string $name`
- `string|null $type`
- `bool $hasDefault`
- `mixed $default`
- `bool $isVariadic`
- `bool $allowsNull`
- `bool $required`

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- During analysis when converting reflection parameters.

##### Common Mistakes
- Treating built-in types as automatically resolvable by the container.

### Method: __set_state(…)

#### Technical Explanation
Hydrates from `var_export()` output.

##### For Humans: What This Means
Loads the saved ticket from disk.

##### Parameters
- `array $array`

##### Returns
- `self`

##### Throws
- No explicit exceptions.

##### When to Use It
- Cache file hydration.

##### Common Mistakes
- Feeding it untrusted data arrays.

### Method: fromArray(…)

#### Technical Explanation
Restores a parameter prototype from a flat array.

##### For Humans: What This Means
Turn stored data back into the object.

##### Parameters
- `array $data`

##### Returns
- `self`

##### Throws
- Potentially `InvalidArgumentException` (implementation-dependent).

##### When to Use It
- Cache hydration.

##### Common Mistakes
- Missing required keys like `name`.

### Method: toArray(…)

#### Technical Explanation
Serializes the parameter prototype to a plain array.

##### For Humans: What This Means
Pack it for storage.

##### Parameters
- None.

##### Returns
- `array`

##### Throws
- No explicit exceptions.

##### When to Use It
- Before caching/compiling.

##### Common Mistakes
- Assuming `$default` is always serializable (it might be an object in some cases).

## Risks, Trade-offs & Recommended Practices
- Risk: Scalar parameters often need overrides.
  - Why it matters: without defaults or overrides, injection fails.
  - Design stance: prefer typed dependencies; keep scalars explicit.
  - Recommended practice: use `BindingBuilder::withArgument()` for scalars.

### For Humans: What This Means
The container can’t guess your string values. Give it a default or configure it.

## Related Files & Folders
- `docs_md/Features/Think/Model/MethodPrototype.md`: Holds collections of these.
- `docs_md/Features/Define/Bind/BindingBuilder.md`: Where scalar overrides are configured.

### For Humans: What This Means
When a parameter needs a scalar, the binding configuration is usually where you provide it.

