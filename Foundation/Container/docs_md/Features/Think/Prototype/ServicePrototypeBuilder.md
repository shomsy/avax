# ServicePrototypeBuilder

## Quick Summary
- This file provides a fluent builder for constructing `ServicePrototype` objects programmatically.
- It exists so you can create prototypes without reflection (tests, compilation, tooling).
- It removes the complexity of “manually assembling nested prototype DTOs” by offering a readable DSL.

### For Humans: What This Means
If you want to “hand-write the blueprint” instead of letting reflection discover it, this builder is your pen.

## Terminology (MANDATORY, EXPANSIVE)
- **Prototype**: A blueprint for how the container should build and inject a service.
  - In this file: the output is `ServicePrototype`.
  - Why it matters: prototypes are how runtime injection stays predictable.
- **Builder/DSL**: A fluent API that reads like configuration.
  - In this file: `for()->withConstructor()->addProperty()->addMethod()->build()`.
  - Why it matters: it’s easier to compose and test than raw arrays.
- **Constructor prototype**: The plan for constructor injection.
  - In this file: stored as `MethodPrototype|null`.
  - Why it matters: it is often the primary DI path.
- **Injected properties/methods**: Non-constructor injection points.
  - In this file: stored as arrays of `PropertyPrototype` and `MethodPrototype`.
  - Why it matters: it supports attribute-driven and setter injection patterns.
- **Instantiable flag**: Whether the service can be built.
  - In this file: `setInstantiable(false)` marks prototypes as non-instantiable.
  - Why it matters: it prevents runtime from trying to create impossible services.

### For Humans: What This Means
This builder is how you assemble “the plan” in a controlled, test-friendly way.

## Think of It
Think of it like writing a LEGO instruction booklet by hand. You decide the steps and pieces, then you hand that booklet to the builder (the container).

### For Humans: What This Means
You’re replacing “auto-generated instructions” with “manually curated instructions”.

## Story Example
You’re writing a unit test for `InjectDependencies` and you don’t want reflection. You build a `ServicePrototype` with a constructor prototype and one injected method, then feed it to your runtime injector to validate behavior deterministically.

### For Humans: What This Means
Tests become stable because they don’t depend on reflection or the filesystem.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Call `for(MyClass::class)`.
2. Optionally set `withConstructor(...)`.
3. Add properties/methods if needed.
4. Call `build()` to get a `ServicePrototype`.

## How It Works (Technical)
The builder stores mutable state while you configure it. `build()` turns that state into an immutable `ServicePrototype`. It does minimal validation (primarily that class was provided), and it keeps method/property lists in the order you added them.

### For Humans: What This Means
You write the blueprint step by step, then you “print” it at the end.

## Architecture Role
- Why it lives in `Features/Think/Prototype`: it creates Think-phase artifacts programmatically.
- What depends on it: tests, compilation/dumping workflows, and tooling.
- What it depends on: prototype model classes.
- System-level reasoning: programmatic prototypes decouple tooling from reflection.

### For Humans: What This Means
When reflection is expensive or undesirable, this builder gives you another path.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: for(string $class)

#### Technical Explanation
Sets the class name this prototype describes.

##### For Humans: What This Means
You’re choosing “what we are building a plan for”.

##### Parameters
- `string $class`: Fully qualified class name.

##### Returns
- `self`

##### Throws
- No explicit exceptions.

##### When to Use It
- Always: it’s the first step.

##### Common Mistakes
- Forgetting to set it and building an incomplete prototype.

### Method: setInstantiable(bool $state)

#### Technical Explanation
Marks whether the service is instantiable.

##### For Humans: What This Means
You’re saying “this can/can’t be constructed”.

##### Parameters
- `bool $state`

##### Returns
- `self`

##### Throws
- No explicit exceptions.

##### When to Use It
- For abstract/interface prototypes or special cases.

##### Common Mistakes
- Marking non-instantiable while expecting runtime to still resolve it.

### Method: withConstructor(MethodPrototype|null $prototype)

#### Technical Explanation
Assigns the constructor injection plan.

##### For Humans: What This Means
This is “what do we pass into `__construct`?”.

##### Parameters
- `MethodPrototype|null $prototype`

##### Returns
- `self`

##### Throws
- No explicit exceptions.

##### When to Use It
- When you want constructor injection to be explicit.

##### Common Mistakes
- Passing a method prototype that doesn’t match the real constructor signature.

### Method: addProperty(PropertyPrototype ...$prototypes)

#### Technical Explanation
Adds one or more property injection prototypes.

##### For Humans: What This Means
You’re saying “also inject these fields”.

##### Parameters
- `PropertyPrototype ...$prototypes`

##### Returns
- `self`

##### Throws
- No explicit exceptions.

##### When to Use It
- For property injection scenarios.

##### Common Mistakes
- Using property injection to hide mandatory dependencies.

### Method: addMethod(MethodPrototype ...$prototypes)

#### Technical Explanation
Adds one or more method injection prototypes.

##### For Humans: What This Means
You’re saying “after construction, call these methods with injected args”.

##### Parameters
- `MethodPrototype ...$prototypes`

##### Returns
- `self`

##### Throws
- No explicit exceptions.

##### When to Use It
- For setter/initializer injection.

##### Common Mistakes
- Depending on call order without documenting it.

### Method: makePrototype()

#### Technical Explanation
Alias for `build()` to support DSL naming.

##### For Humans: What This Means
It’s just another name for “build the blueprint”.

##### Parameters
- None.

##### Returns
- `ServicePrototype`

##### Throws
- Depends on `build()` (may throw on invalid state).

##### When to Use It
- When your calling code reads better as “make”.

##### Common Mistakes
- Thinking it creates an instance, not a prototype.

### Method: build()

#### Technical Explanation
Constructs an immutable `ServicePrototype` from the builder state.

##### For Humans: What This Means
This is where your “draft blueprint” becomes the final blueprint.

##### Parameters
- None.

##### Returns
- `ServicePrototype`

##### Throws
- `InvalidArgumentException` (as documented) if required state is missing.

##### When to Use It
- At the end of your fluent configuration chain.

##### Common Mistakes
- Building before setting the target class via `for()`.

## Risks, Trade-offs & Recommended Practices
- Risk: Programmatic prototypes can drift from real code.
  - Why it matters: if the real class changes, the hand-written prototype becomes stale.
  - Design stance: use this for tests/tooling, not as your primary source of truth.
  - Recommended practice: keep prototypes close to the test/tooling that uses them.

### For Humans: What This Means
If you hand-write instructions, you must update them when the product changes.

## Related Files & Folders
- `docs_md/Features/Think/Model/ServicePrototype.md`: The blueprint built by this class.
- `docs_md/Features/Think/Analyze/PrototypeAnalyzer.md`: Reflection-based alternative that builds prototypes automatically.

### For Humans: What This Means
Builder is manual, analyzer is automatic—both produce the same kind of blueprint.

