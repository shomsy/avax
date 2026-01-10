# Features/Think/Verify

## What This Folder Represents

This folder serves as the **Quality Assurance (QA) Department** for the container's intelligence system. It validates that every blueprint (Prototype) is logically sound and follows the system's rules before it is allowed to be used or cached.

Technically, `Features/Think/Verify` contains stateless verification engines that perform secondary checks on `ServicePrototype` objects. While the `Analyze` phase discovers "What is there", the `Verify` phase decides "Is this allowed?". It enforces strict rules about type hints, instantiability, and circular safety, ensuring that clear, actionable exceptions are thrown during the development phase rather than cryptic errors during a production request.

### For Humans: What This Means (Summary)

This is the **Safety Inspector**. After the analyst finishes drawing the blueprints for a house, the safety inspector looks at them. If the analyst forgot to put stairs to the second floor, the inspector catches the mistake while it's still just a drawing. This saves you from the disaster of trying to build the house and realizing the problem when it's too late.

## Terminology (MANDATORY, EXPANSIVE)

- **Post-Discovery Validation**: Checking data *after* it has been found but *before* it has been used.
  - In this folder: The primary workflow for all verifiers.
  - Why it matters: It ensures that once a blueprint is in the cache, it is 100% "Trusted."
- **Strict-Mode Enforcement**: Requiring that every injection point has a clear, resolvable type.
  - In this folder: Handled by checking for missing type-hints in constructors.
  - Why it matters: Prevents "Silent Failures" where the container doesn't know what to give you.
- **Instantiability Guard**: Confirming that a class can actually be created (not an interface).
  - In this folder: The first check performed on every service.
  - Why it matters: You can't say `new MyInterface()`. This folder stops the container from even trying.
- **Fail-Fast Principle**: Crashing immediately when an error is found, rather than trying to "guess" and failing later.
  - In this folder: Every method throws an exception if a rule is broken.
  - Why it matters: Saves developers hours of debugging by pointing directly to the broken code.

### For Humans: What This Means (Terminology)

**Post-Discovery Validation** is "The Second Look". **Strict-Mode** is "No Guessing Allowed". **Instantiability Gard** is "Can we actually build this?", and **Fail-Fast** is "Stop early if there's a problem."

## Think of It

Think of a **Pre-Flight Checklist for a Pilot**:

1. **The Plan**: The flight path is drawn (Prototype).
2. **The Checklist (This Folder)**: The pilot checks: "Is there enough fuel? Is the weather clear? Are the engines checked?"
3. **The GO/NO-GO Decision**: If everything is checked, they take off. If one thing is wrong, they stay on the ground until it's fixed.

### For Humans: What This Means (Analogy)

It’s the checklist that keeps the plane from taking off if it’s missing a wing.

## Story Example

You are a new developer on a team. You create a service but forget to add the `: Logger` type hint to your constructor. Without this folder, your app would start, but would crash with a weird error the first time a user visits your site. With the **Verify** folder, the container sees the missing hint during the first second of your tests and tells you exactly which file and which line you forgot to fix.

### For Humans: What This Means (Story)

It gives you "Peace of Mind". It acts as a safety net that catches your smallest mistakes so they never reach your customers.

## For Dummies

Imagine you're checking a list of ingredients for a recipe.

1. **Analysis**: You see "1 cup of stuff".
2. **Verification**: You say "Wait, what is 'stuff'? Is it flour? Is it sugar? I can't cook without knowing exactly what it is."
3. **Error**: You stop and ask for the specific name.

### For Humans: What This Means (Walkthrough)

It’s the "Wait a minute..." department.

## How It Works (Technical)

The "Verify" folder operates as an auditing layer:

1. **Rule Set**: It maintains a list of "Cardinal Sins" (like untyped parameters).
2. **Atomic Logic**: Each checker is focused on one specific part of the blueprint (Method, Property, etc.).
3. **Report Generation**: It can audit single classes or entire batches, producing a "Health Report" of your application's architecture.

### For Humans: What This Means (Technical)

It is the "Auditor". It looks at the "Accounting" of your classes to make sure all the "Numbers" (Types) add up correctly.

## Architecture Role

- **Lives in**: `Features/Think/Verify`
- **Role**: Quality Assurance and Error Reporting.
- **Goal**: To ensure all resolution-bound metadata is perfect.

### For Humans: What This Means (Architecture)

It is the "Inspector's Office" for the Intelligence Layer.

## What Belongs Here

- Classes that check Prototypes for logical errors.
- Exception classes specific to configuration/blueprint errors.
- Batch auditing tools for system warm-ups.

### For Humans: What This Means (Belongs)

If it says "This blueprint is wrong because...", it belongs here.

## What Does NOT Belong Here

- **Finding info**: (lives in `Think/Analyze`).
- **Storing info**: (lives in `Think/Cache`).
- **User-side rules**: (lives in `Features/Define`).

### For Humans: What This Means (Not Belongs)

It only **Inspects**. it doesn't **Write** or **Store**.

## How Files Collaboration

The `VerifyPrototype` service receives a model from `Model/ServicePrototype`. It uses the `ReflectionTypeAnalyzer` from `Analyze/` to double-check its work, and if everything passes, the `Cache/` folder is finally allowed to save the result.

### For Humans: What This Means (Collaboration)

The **Inspector** (this folder) reads the **Blueprint** (Model) and gives the **Green Light** to the **Librarian** (Cache).
