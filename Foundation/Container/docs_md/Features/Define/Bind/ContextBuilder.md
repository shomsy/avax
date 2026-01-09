# ContextBuilder

## Quick Summary
- This file implements a two-step builder for contextual bindings: `needs()` then `give()`.
- It exists so you can override a dependency *only for a specific consumer*.
- It removes the complexity of storing “consumer + dependency → implementation” rules by writing into `DefinitionStore`.

### For Humans: What This Means
You can say “when class A needs interface X, give it implementation Y” without changing the global binding.

## Terminology (MANDATORY, EXPANSIVE)
- **Contextual binding**: A binding that applies only in a specific situation.
  - In this file: it’s a `(consumer, needs) -> give` rule.
  - Why it matters: it solves “same interface, different implementations per consumer”.
- **Consumer**: The class currently being built (the one that “needs” something).
  - In this file: provided in the constructor and stored as `$consumer`.
  - Why it matters: it scopes the override.
- **Needs**: The dependency identifier required by the consumer.
  - In this file: stored temporarily in `$needs` after calling `needs()`.
  - Why it matters: it’s the key for the override.
- **Give**: The implementation/value the container should provide.
  - In this file: written into the store with `addContextual()`.
  - Why it matters: it’s the answer to the consumer’s “need”.
- **Two-step builder**: A builder that requires ordering to build a complete rule.
  - In this file: `give()` throws if you didn’t call `needs()` first.
  - Why it matters: it prevents incomplete rules.

### For Humans: What This Means
It’s like writing a sentence with blanks: “When ___ needs ___, give ___.” You fill the blanks in order.

## Think of It
Think of contextual binding as a “VIP exception list”: normally the rules apply to everyone, but for a specific person (consumer), you override what they get.

### For Humans: What This Means
You’re not changing the whole system—just making an exception for one class.

## Story Example
You have two loggers: `FileLogger` for background jobs and `HttpLogger` for web requests. Globally, you bind `LoggerInterface` to `FileLogger`. But your `HttpKernel` must receive `HttpLogger`. With `ContextBuilder`, you define that exception without touching other consumers.

### For Humans: What This Means
You get the right logger in the right place, without breaking everything else.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. You pick the consumer: `$container->when(SomeClass::class)`.
2. You pick what it needs: `->needs(Interface::class)`.
3. You choose what to give: `->give(Implementation::class)`.
4. The rule is saved in the store.

Beginner FAQ:
- *Why doesn’t `give()` return `$this`?* Because the rule is “completed” and the builder resets its temporary state.

## How It Works (Technical)
`ContextBuilder` holds a reference to `DefinitionStore` and a consumer id. `needs()` stores the dependency id in `$needs`. `give()` validates that `$needs` is set, writes the contextual rule to the store, then resets `$needs` to ensure the builder doesn’t accidentally reuse state across unrelated calls.

### For Humans: What This Means
It’s a small state machine: first you say what’s needed, then you say what to provide.

## Architecture Role
- Why it lives in this folder: it’s part of the “Define” DSL for registration time.
- What depends on it: `Registrar::when()` and user bootstrapping code.
- What it depends on: `DefinitionStore` for persistence.
- System-level reasoning: it provides a safe way to express exceptions without polluting global bindings.

### For Humans: What This Means
This is the “exception mechanism” that keeps the rest of your bindings clean.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Creates a builder for a specific consumer and store.

##### For Humans: What This Means
You’re saying “I’m about to define rules for this consumer class.”

##### Parameters
- `DefinitionStore $store`: Where the rule will be saved.
- `string $consumer`: Which class the rule applies to.

##### Returns
- No return value.

##### Throws
- No explicit exceptions.

##### When to Use It
- You usually get it from `Registrar::when()`.

##### Common Mistakes
- Passing an unrelated identifier that doesn’t match your actual consumer type.

### Method: needs(…)

#### Technical Explanation
Sets the dependency identifier that the consumer needs, as the first step of the rule.

##### For Humans: What This Means
You’re choosing “the thing we’re overriding”.

##### Parameters
- `string $abstract`: The dependency id (often an interface/class).

##### Returns
- Returns `$this` so you can call `give()` next.

##### Throws
- No explicit exceptions.

##### When to Use It
- Immediately after `when(...)`.

##### Common Mistakes
- Calling `needs()` twice and expecting multiple rules without calling `give()`.

### Method: give(…)

#### Technical Explanation
Writes the contextual override to the store and resets internal builder state. Throws if `needs()` was not called.

##### For Humans: What This Means
This is the moment the exception rule becomes real and is saved.

##### Parameters
- `mixed $implementation`: What to provide for that need (class name, closure, or instance).

##### Returns
- Returns nothing; the rule is committed.

##### Throws
- `LogicException`: If you call it before `needs()`.

##### When to Use It
- Right after `needs()`.

##### Common Mistakes
- Forgetting to call `needs()` first.

## Risks, Trade-offs & Recommended Practices
- Risk: Overusing contextual rules can make behavior hard to predict.
  - Why it matters: debugging becomes “why did this consumer get something else?”
  - Design stance: use contextual bindings for clear, justified exceptions.
  - Recommended practice: document exceptions and keep them close to bootstrapping code.

### For Humans: What This Means
Exceptions are useful, but too many exceptions turns your “rules” into chaos.

## Related Files & Folders
- `docs_md/Features/Define/Bind/Registrar.md`: Entry point that gives you a `ContextBuilder`.
- `docs_md/Features/Define/Store/DefinitionStore.md`: Stores contextual rules.

### For Humans: What This Means
When you want to understand “where my contextual rule ended up”, look at the store.

