# VerifyPrototype

## Quick Summary

- This file serves as the **Building Inspector** for class blueprints.
- It exists to prevent "Garbage In, Garbage Out"—it makes sure that a blueprint is complete and logically sound before the container tries to use it.
- It removes the risk of runtime "Crashing" by catching configuration errors (like missing type hints) immediately after analysis.

### For Humans: What This Means (Summary)

This is the **Final Inspector** at the end of the factory line. After the analyst (PrototypeAnalyzer) finishes drawing the blueprint, this inspector looks at it. If the analyst forgot to label a wire or left a room with no door, the inspector stamps "REJECTED" on the plan and tells you why. This ensures the construction crew (The Injectors) never get a plan that is impossible to build.

## Terminology (MANDATORY, EXPANSIVE)

- **Post-Analysis Validation**: Checking the data after it has been gathered but before it has been used or cached.
  - In this file: The entire purpose of the `validate()` method.
  - Why it matters: It catches errors *once* (during development/warmup) instead of catching them *millions of times* (during every user request).
- **Type-Hint Strictness**: The requirement that every variable being injected MUST have a clear type or ID.
  - In this file: Validated by `validateMethodPrototype()`.
  - Why it matters: If you have a variable like `public $db;` without a type, the container doesn't know what to put there. This class makes sure you fix that.
- **Instantiability Guard**: Ensuring that the class can actually be created (i.e., it’s not an Interface or an Abstract class).
  - In this file: The first check in the `validate()` method.
  - Why it matters: You can't say `new MyInterface()`. This class prevents the container from trying to perform impossible actions.
- **Batch Auditing**: Checking many plans at once to find patterns of errors.
  - In this file: Handled by `validateBatch()`.
  - Why it matters: During a "Warm Up" of your production server, this allows the system to tell you "30 of your classes are missing type hints" in one go.

### For Humans: What This Means (Terminology)

The Verifier performs **Post-Analysis Validation** (Double-checking) to enforce **Type-Hint Strictness** (Labeling) and the **Instantiability Guard** (Buildability), with support for **Batch Auditing** (Bulk checking).

## Think of It

Think of a **Passport Control Officer**:

1. **The Application**: The traveler (The Prototype) brings their paperwork.
2. **The Check**: The officer checks:
    - Is the name filled out?
    - Is the photo clear?
    - Are the dates valid?
3. **The Decision**: If anything is missing, the traveler is sent back to fix it. If everything is perfect, the traveler is allowed into the country (The Cache).

### For Humans: What This Means (Analogy)

The Verifier is the gatekeeper. It doesn't create the paperwork; it just makes sure the paperwork is 100% correct before letting it pass into the "Trusted" zone of the application.

## Story Example

You are a developer who just created a new `ReportGenerator` class. You added a new constructor argument `$tempDir` but you forgot to add the `: string` type hint to it. When you run your tests, the **VerifyPrototype** service scans your class. It sees the `$tempDir` argument has no type. Instead of letting your app start and then crashing later with a "Cannot resolve dependency" error, it immediately throws a `RuntimeException` saying: *"Parameter [tempDir] in constructor of class [ReportGenerator] has no resolvable type."* You see the error, add the type hint, and your app works perfectly on the first try.

### For Humans: What This Means (Story)

It’s like having a senior developer looking over your shoulder. It catches your "Human Mistakes" early, so you don't spend hours debugging "Silent Errors" later.

## For Dummies

Imagine you're checking a recipe before you start cooking.

1. **Check 1**: Do we have a name for every ingredient? (Parameters)
2. **Check 2**: Do we know exactly what each ingredient is? (Types)
3. **Check 3**: Can this meal actually be made? (Instantiable)
4. **Action**: If anything is missing, you go back to the store instead of starting to cook and realizing halfway through that you're missing the salt.

### For Humans: What This Means (Walkthrough)

It's a "Pre-Flight Checklist". It makes sure the plane is actually capable of flying before the pilot tries to take off.

## How It Works (Technical)

The `VerifyPrototype` is a rule-enforcement agent:

1. **Stateless Engine**: The class is `readonly` and holds no data. This makes it perfect for high-speed multi-threaded environments.
2. **Recursive Signature Scanning**: It doesn't just check the class; it drills down into every `MethodPrototype`, then every `ParameterPrototype`. It enforces a "No Null Type" policy for all active injection points.
3. **Exception-Driven**: It uses `RuntimeException`. In the `validateBatch()` method, it catches these exceptions internally and converts them into a "Report Map" of `class => error_message`.
4. **Property Inspection**: It checks all `injectedProperties` to ensure that even "Optional" properties have a type that the container can understand.

### For Humans: What This Means (Technical)

It is a "Deep Inspector". It looks at every single detail of the blueprint, searching for "Logical Holes" that would cause the container to fail later.

## Architecture Role

- **Lives in**: `Features/Think/Verify`
- **Role**: Blueprint Quality Assurance (QA).
- **Goal**: To ensure all cached/used prototypes are logically complete.

### For Humans: What This Means (Architecture)

It is the "Quality Control" station for the Intelligence Layer.

## Methods

### Method: validateBatch(array $prototypes)

#### Technical Explanation: validateBatch

Audits a collection of prototypes, returning a summary of which ones passed and which ones failed.

#### For Humans: What This Means (validateBatch)

"Give the building codes inspector a stack of 100 plans and ask for a list of which ones need fixing."

### Method: validate(ServicePrototype $prototype)

#### Technical Explanation: validate

The core atomic check. Enforces the four pillars of a valid prototype (Instantiable, Constructor Types, Property Types, Method Types).

#### For Humans: What This Means (validate)

"Check this one specific plan for errors."

## Risks & Trade-offs

- **Strictness**: This class is very "Opinionated". It requires type hints. If you use a lot of "Untyped" PHP code, this verifier will complain a lot.
- **One-Way Logic**: The verifier can tell you if a type hint is *missing*, but it can't tell you if the type hint is *wrong* (e.g. if you typoed a class name). That check happens later during the Resolution phase.

### For Humans: What This Means (Risks)

"It demands perfection". It forces you to write better, more documented code. It might feel "Annoying" at first, but it makes your application much more stable in the long run.

## Related Files & Folders

- `PrototypeAnalyzer.php`: The "Author" of the plans that this class checks.
- `ServicePrototype.php`: The "Plan" itself.
- `DesignFlow.php`: The workflow that uses this verifier to ensure a design is valid.

### For Humans: What This Means (Relationships)

The **Author** (Analyzer) writes the plan, the **Inspector** (this class) checks it, and then it’s used to build the **App**.
