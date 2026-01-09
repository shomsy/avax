# VerifyPrototype

## Quick Summary
- This file validates `ServicePrototype` instances for correctness and safety.
- It exists so you fail early with clear errors instead of failing during runtime injection.
- It removes the complexity of “hidden prototype problems” by turning them into explicit validation rules.

### For Humans: What This Means
It’s the container’s “sanity checker” for the injection plan.

## Terminology (MANDATORY, EXPANSIVE)
- **Prototype validation**: Checking that a prototype can be used safely.
  - In this file: checks instantiability and presence of resolvable types.
  - Why it matters: invalid prototypes cause runtime crashes.
- **Batch validation**: Validating multiple prototypes and collecting results.
  - In this file: `validateBatch()` returns valid/invalid lists and a summary.
  - Why it matters: tooling and CI can validate many services at once.
- **RuntimeException**: The error type used to report validation failures.
  - In this file: thrown when a prototype is invalid.
  - Why it matters: callers get a clear signal that the plan is unusable.

### For Humans: What This Means
If something is wrong with the plan, it tells you exactly what and where—before you try to build anything.

## Think of It
Think of it like a pre-flight checklist for a plane: you check critical systems before takeoff, not at 30,000 feet.

### For Humans: What This Means
You’d rather catch mistakes at startup than during a live request.

## Story Example
Someone adds a new service with an untyped constructor parameter. Analysis creates a prototype, but it contains a parameter with no resolvable type. `VerifyPrototype` catches it during CI and fails the build with a clear error, instead of letting production crash when the service is first resolved.

### For Humans: What This Means
It saves you from “it worked in dev, exploded in prod” surprises.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Call `validate($prototype)` to check one service.
2. Call `validateBatch([$p1, $p2])` to check many and get a report.
3. Fix errors before caching or using prototypes.

## How It Works (Technical)
Validation rules are straightforward:
- The prototype must be instantiable.
- Constructor parameters must have resolvable types.
- Injected properties must have resolvable types.
- Injected method parameters must have resolvable types.

`validateMethodPrototype()` enforces the “all parameters must have types” rule and throws `RuntimeException` with contextual messages.

### For Humans: What This Means
It checks the plan for missing “type information” because types are the container’s main clue.

## Architecture Role
- Why it lives here: it’s Think/Verify quality control.
- What depends on it: prototype factories/builders, caching flows, tooling.
- What it depends on: prototype model classes.
- System-level reasoning: fail-fast validation makes runtime resolution more reliable.

### For Humans: What This Means
Your container becomes more trustworthy because it refuses to run with broken instructions.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: validateBatch(…)

#### Technical Explanation
Validates an array of prototypes, returning valid prototypes, invalid ones with error messages, and summary counts.

##### For Humans: What This Means
It’s a “bulk check” that also gives you a report.

##### Parameters
- `ServicePrototype[] $prototypes`: Prototypes to validate.

##### Returns
- An array containing `valid`, `invalid`, and `summary` sections.

##### Throws
- No; it catches exceptions per prototype and records them.

##### When to Use It
- Diagnostics, CI checks, tooling, pre-cache validation.

##### Common Mistakes
- Assuming “valid” means “all dependencies exist in the store”; it only checks prototype structure/types.

### Method: validate(…)

#### Technical Explanation
Validates one prototype, throwing `RuntimeException` on the first failure.

##### For Humans: What This Means
It’s the strict version: it stops at the first issue.

##### Parameters
- `ServicePrototype $prototype`

##### Returns
- Returns nothing.

##### Throws
- `RuntimeException`: When a validation rule fails.

##### When to Use It
- When you want immediate, fail-fast behavior.

##### Common Mistakes
- Calling it in hot paths; validation is usually for build/boot/CI.

## Risks, Trade-offs & Recommended Practices
- Risk: Validation adds overhead during analysis.
  - Why it matters: building prototypes becomes slower.
  - Design stance: validate in dev/CI; optionally skip in hot production paths if you trust your build pipeline.
  - Recommended practice: validate before caching and before compiling.

### For Humans: What This Means
Checking takes time, but crashing takes more time—especially in production.

## Related Files & Folders
- `docs_md/Features/Think/Analyze/PrototypeAnalyzer.md`: Produces the prototypes being validated.
- `docs_md/Features/Think/Model/ServicePrototype.md`: The blueprint being checked.

### For Humans: What This Means
To understand what validation checks, you need to understand what a prototype contains.

