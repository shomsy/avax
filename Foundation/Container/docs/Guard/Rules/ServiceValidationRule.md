# ServiceValidationRule

## Quick Summary
- This file defines an attribute-based rule that validates service class names (existence, abstract/interface allowances, required interfaces, forbidden classes).
- It exists so service registration fails early when a class is missing or violates constraints.
- It removes the complexity of “is this class safe to register as a service?” by making it a consistent rule.

### For Humans: What This Means (Summary)
It’s a background check for service classes.

## Terminology (MANDATORY, EXPANSIVE)
- **Service class**: The class you want the container to instantiate.
  - In this file: `$value` is expected to be a class name string.
  - Why it matters: registering a non-existent class is one of the easiest ways to break a container.
- **Instantiability**: Whether a class can actually be instantiated (not abstract).
  - In this file: reflection checks `isAbstract()` depending on config.
  - Why it matters: abstract services need special handling.
- **Required interfaces**: Interfaces that must be implemented.
  - In this file: `$requiredInterfaces` must all be satisfied.
  - Why it matters: it enforces contracts and prevents “wrong type registered”.
- **Forbidden classes**: A denylist of classes that must never be registered.
  - In this file: `$forbiddenClasses` is checked strictly.
  - Why it matters: it’s a safety net against risky or debug-only components.

### For Humans: What This Means (Terms)
This rule helps you avoid “it compiles, but it explodes at runtime” service registrations.

## Think of It
Think of it like hiring rules: the applicant must exist, must not be “in training only” (abstract), must have required certifications (interfaces), and must not be on the banned list.

### For Humans: What This Means (Think)
It prevents bad hires from joining your container workforce.

## Story Example
You refactor a namespace and forget to update the service definition. In production, the container would crash later when it tries to instantiate the missing class. With `ServiceValidationRule`, validation fails immediately during registration with a clear “class doesn’t exist” error and you fix it before deployment.

### For Humans: What This Means (Story)
It catches “broken wiring” early, when it’s cheap to fix.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. You pass a class name string.
2. This rule checks it exists.
3. It checks if interfaces/abstract classes are allowed.
4. It checks required interfaces and forbidden classes.
5. It returns true/false.

## How It Works (Technical)
The rule reads its constructor configuration (`allowAbstract`, `allowInterface`, `requiredInterfaces`, `forbiddenClasses`). `validate()` verifies the input is a non-empty string, checks class/interface existence, optionally denies interfaces, denies forbidden classes, checks abstract instantiability via reflection, and verifies required interface implementation. `getErrorMessage()` describes constraints in human-readable form.

### For Humans: What This Means (How)
It’s a configurable “is this class acceptable?” gate.

## Architecture Role
- Why this file lives in `Guard/Rules`: it’s input validation, not resolution.
- What depends on it: service definition ingestion and overall validation orchestration.
- What it depends on: reflection and the validation framework.
- System-level reasoning: you want service definition errors to surface before the container is used.

### For Humans: What This Means (Role)
If service definitions are wrong, the container shouldn’t pretend everything is okay.

## Methods 


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation (__construct)
Configures which classes are acceptable: interfaces/abstract allowed or not, required interfaces, forbidden classes.

##### For Humans: What This Means (__construct)
It sets the rules for what “valid service class” means in your system.

##### Parameters (__construct)
- `$allowAbstract`: Whether abstract classes are permitted.
- `$allowInterface`: Whether interfaces are permitted.
- `$requiredInterfaces`: List of interfaces that must be implemented.
- `$forbiddenClasses`: Denylist of classes that must be rejected.

##### Returns (__construct)
- Nothing.

##### Throws (__construct)
- None.

##### When to Use It (__construct)
- When defining validation behavior for service class properties.

##### Common Mistakes (__construct)
- Allowing interfaces without having a binding strategy to pick an implementation.

### Method: validate(…)

#### Technical Explanation (validate)
Validates a class name string against configured constraints and reflection checks.

##### For Humans: What This Means (validate)
It answers: “Can this class safely be a service?”

##### Parameters (validate)
- `$value`: Expected class name string.

##### Returns (validate)
- `true` if valid; otherwise `false`.

##### Throws (validate)
- `ReflectionException` if reflection fails.

##### When to Use It (validate)
- During registration validation, before storing definitions.

##### Common Mistakes (validate)
- Passing values that are not strings (e.g., objects or arrays from config parsing).

### Method: getErrorMessage(…)

#### Technical Explanation (getErrorMessage)
Builds a descriptive failure message based on configured constraints.

##### For Humans: What This Means (getErrorMessage)
It tells you which rules you violated.

##### Parameters (getErrorMessage)
- None.

##### Returns (getErrorMessage)
- A string message.

##### Throws (getErrorMessage)
- None.

##### When to Use It (getErrorMessage)
- When producing developer-facing validation output.

##### Common Mistakes (getErrorMessage)
- Treating the message as complete context; you still want to include the failing class name.

## Risks, Trade-offs & Recommended Practices
- Risk: Allowing interfaces without disambiguation.
  - Why it matters: multiple implementations can cause ambiguous resolution.
  - Design stance: only allow interfaces when you have explicit bindings or conflict detection.
  - Recommended practice: pair interface allowance with explicit bindings and conflict checks (`ServiceDiscovery`).

### For Humans: What This Means (Risks)
Interfaces are great, but only if you also decide which implementation should be used.

## Related Files & Folders
- `docs_md/Guard/Rules/ServiceValidator.md`: Orchestrates validation and uses this rule.
- `docs_md/Guard/Rules/DependencyValidationRule.md`: Validates dependency lists.
- `docs_md/Features/Define/Store/ServiceDefinitionEntity.md`: The entity whose `class` property is validated.

### For Humans: What This Means (Related)
This rule guards the “service class” field so the whole system remains predictable.

