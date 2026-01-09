# MethodPrototype

## Quick Summary
- This file represents an analyzed method that requires dependency injection, including its parameters.
- It exists so method/constructor injection can be performed without re-reflecting.
- It removes the complexity of “how do we know what arguments to pass?” by storing parameter prototypes.

### For Humans: What This Means
It’s a saved description of “this method needs these dependencies in this order”.

## Terminology (MANDATORY, EXPANSIVE)
- **Method injection**: Injecting dependencies into a method call (constructor or setter).
  - In this file: the prototype describes method name + parameter prototypes.
  - Why it matters: it enables predictable, type-safe invocation.
- **Parameter prototype**: A description of one method parameter’s resolution needs.
  - In this file: stored as `$parameters`.
  - Why it matters: every parameter may have different type/default/required rules.
- **Serialization hydration**: Rebuilding the prototype from cached array data.
  - In this file: supported by `__set_state()` and `fromArray()`.
  - Why it matters: prototypes are commonly cached.

### For Humans: What This Means
This object is how the container remembers “what to pass into the method”.

## Think of It
Think of it like a packing list for a trip: the method is the trip, the parameters are the items you must bring, and the order matters.

### For Humans: What This Means
It’s a checklist that prevents “oops, I forgot the logger”.

## Story Example
Your class has a setter `setLogger(LoggerInterface $logger)` marked with `#[Inject]`. Analysis produces a `MethodPrototype` for `setLogger` with one `ParameterPrototype`. Runtime injection reads it and resolves the logger, then invokes the method with the correct argument.

### For Humans: What This Means
Once the container learned the setter’s needs, it can repeat it reliably.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

- The method name is stored as a string.
- The parameters are stored as an array of small prototypes.
- It can be turned into an array and back for caching.

## How It Works (Technical)
The class is `readonly` and stores `name` and `parameters`. `toArray()` serializes parameters by calling `ParameterPrototype::toArray()`. `fromArray()` rebuilds parameter objects. `__set_state()` is used for `var_export()` hydration.

### For Humans: What This Means
It’s a “data-only” object built for caching and fast reads.

## Architecture Role
- Why it lives here: it’s a core prototype model.
- What depends on it: injectors/invokers that need parameter plans.
- What it depends on: `ParameterPrototype`.
- System-level reasoning: model objects make injection predictable and cacheable.

### For Humans: What This Means
When you have a stable model, you can move fast without breaking the injection logic.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Creates a method prototype with a name and ordered parameter prototypes.

##### For Humans: What This Means
You’re describing “this method exists and needs these arguments”.

##### Parameters
- `string $name`: Method name.
- `ParameterPrototype[] $parameters`: Ordered parameter list.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- During analysis/prototype building.

##### Common Mistakes
- Passing parameters out of order; runtime injection assumes correct ordering.

### Method: __set_state(…)

#### Technical Explanation
Hydrates from `var_export()` output.

##### For Humans: What This Means
It’s how cached PHP files rebuild the object.

##### Parameters
- `array $array`

##### Returns
- `self`

##### Throws
- No explicit exceptions.

##### When to Use It
- Called by PHP during require-time hydration.

##### Common Mistakes
- Feeding it data that wasn’t produced by `toArray()`/`var_export()`.

### Method: fromArray(…)

#### Technical Explanation
Builds a `MethodPrototype` from a plain array representation.

##### For Humans: What This Means
Convert stored data into a real object again.

##### Parameters
- `array $data`

##### Returns
- `self`

##### Throws
- Potentially `InvalidArgumentException` depending on nested prototypes (implementation-dependent).

##### When to Use It
- Cache hydration and compilation.

##### Common Mistakes
- Forgetting to include `parameters` as arrays for nested prototypes.

### Method: toArray(…)

#### Technical Explanation
Serializes the method prototype (including nested parameters) to a plain array.

##### For Humans: What This Means
Pack the prototype so it can be saved.

##### Parameters
- None.

##### Returns
- `array`

##### Throws
- No explicit exceptions.

##### When to Use It
- Before storing in cache or compiled definitions.

##### Common Mistakes
- Assuming the serialized structure is stable if you change property names.

## Risks, Trade-offs & Recommended Practices
- Risk: Untyped parameters can’t be reliably injected.
  - Why it matters: the container can’t guess what to resolve.
  - Design stance: prefer typed dependencies; validate prototypes.
  - Recommended practice: run `VerifyPrototype` in dev/CI.

### For Humans: What This Means
If you don’t tell the container what you want, it can’t deliver it.

## Related Files & Folders
- `docs_md/Features/Think/Model/ParameterPrototype.md`: Parameter-level blueprint.
- `docs_md/Features/Actions/Inject/InjectDependencies.md`: Uses method prototypes to inject at runtime.

### For Humans: What This Means
This model becomes real when the injector reads it and performs the call.

