# DependencyValidationRule

## Quick Summary

- This file defines an attribute-based validation rule that checks whether dependency arrays in service definitions are
  well-formed and allowed.
- It exists so you can validate dependency declarations early, before they become runtime resolution failures.
- It removes the complexity of “did we register bad dependency data?” by turning it into a predictable validation step.

### For Humans: What This Means (Summary)

It’s a spell-checker for your “this service depends on…” lists.

## Terminology (MANDATORY, EXPANSIVE)

- **Attribute-based validation**: Validation rules attached to properties via PHP attributes.
    - In this file: the class is used as `#[DependencyValidationRule(...)]`.
    - Why it matters: rules live close to the data they validate.
- **Whitelist**: A list of allowed values.
    - In this file: `availableServices` restricts which dependency ids are allowed.
    - Why it matters: it prevents accidental or malicious identifiers.
- **Service identifier format**: The allowed shape of dependency strings.
    - In this file: a regex similar to PHP identifier rules is used.
    - Why it matters: consistent naming reduces errors and prevents “weird” ids.
- **Short-circuit validation**: Stop at the first failure.
    - In this file: validation returns `false` as soon as a dependency is invalid.
    - Why it matters: it’s fast and deterministic.

### For Humans: What This Means (Terms)

It makes sure dependencies look sane and, optionally, that they’re on your approved list.

## Think of It

Think of it like a guest list at a private event. If you provide a whitelist, only names on that list get in.

### For Humans: What This Means (Think)

When a dependency is wrong, you want to catch it at registration time, not in production.

## Story Example

Your system allows developers to register services via configuration. One config accidentally declares a dependency
`"databse"` instead of `"database"`. Without validation, resolution fails later in a confusing way. With
`DependencyValidationRule`, the registration step fails immediately with a clear error message and you fix it quickly.

### For Humans: What This Means (Story)

It turns a late, confusing failure into an early, obvious one.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code
with confidence.

1. Your service says it depends on `['db', 'cache']`.
2. This rule checks that’s an array of non-empty strings.
3. If you provided a whitelist, it checks the ids exist in that list.
4. It returns true/false, and provides an error message if needed.

## How It Works (Technical)

The rule stores optional constraints (allowed services list, self-reference allowance flags, depth limit). `validate()`
ensures the input is an array, then checks each entry for being a non-empty string, optionally checks whitelist
membership, and validates format via regex. `getErrorMessage()` builds a human-readable message describing the active
constraints.

### For Humans: What This Means (How)

It’s a small and fast gate that blocks bad dependency data.

## Architecture Role

- Why this file lives in `Guard/Rules`: it’s about validating inputs, not resolving services.
- What depends on it: service definition ingestion, configuration loaders, and validators/orchestrators.
- What it depends on: the validation framework base rule type.
- System-level reasoning: if you allow bad dependency declarations into the system, everything downstream becomes harder
  and noisier.

### For Humans: What This Means (Role)

Better input validation means less debugging and fewer runtime surprises.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation (__construct)

Configures the rule with optional constraints (whitelist, self-reference behavior, depth limit).

##### For Humans: What This Means (__construct)

It lets you decide how strict you want to be.

##### Parameters (__construct)

- `$availableServices`: Allowed dependency ids, or `null` for “no whitelist”.
- `$allowSelfReference`: Whether a service can list itself as a dependency.
- `$maxDependencyDepth`: Maximum depth allowed (if enforced by the wider validation system).

##### Returns (__construct)

- Nothing.

##### Throws (__construct)

- None.

##### When to Use It (__construct)

- When defining validation rules for service metadata.

##### Common Mistakes (__construct)

- Providing a whitelist that’s out of date and blocking legitimate services.

### Method: validate(…)

#### Technical Explanation (validate)

Validates that the value is an array of well-formed dependency identifiers under configured constraints.

##### For Humans: What This Means (validate)

It answers: “Is this dependency list safe and acceptable?”

##### Parameters (validate)

- `$value`: The value being validated.

##### Returns (validate)

- `true` if valid; otherwise `false`.

##### Throws (validate)

- None (validation returns booleans).

##### When to Use It (validate)

- During service definition validation and ingestion.

##### Common Mistakes (validate)

- Expecting it to resolve services; it validates identifiers only.

### Method: getErrorMessage(…)

#### Technical Explanation (getErrorMessage)

Builds a descriptive error message reflecting active constraints.

##### For Humans: What This Means (getErrorMessage)

It tells you what rule you broke in plain terms.

##### Parameters (getErrorMessage)

- None.

##### Returns (getErrorMessage)

- A human-readable error string.

##### Throws (getErrorMessage)

- None.

##### When to Use It (getErrorMessage)

- When producing validation error output for developers.

##### Common Mistakes (getErrorMessage)

- Showing this message without also showing which specific dependency value failed.

## Risks, Trade-offs & Recommended Practices

- Risk: Overly strict regex rules can reject legitimate ids.
    - Why it matters: many containers use dot-separated ids (`db.primary`) or other conventions.
    - Design stance: keep format validation aligned with your real naming conventions.
    - Recommended practice: document your service id grammar and update the regex to match it.

### For Humans: What This Means (Risks)

Validation should match reality. If your ids allow dots, the rule should too.

## Related Files & Folders

- `docs_md/Guard/Rules/ServiceValidationRule.md`: Validates the service class itself.
- `docs_md/Guard/Rules/ServiceValidator.md`: Orchestrates multi-layer validation and uses rules like this.
- `docs_md/Features/Define/Store/ServiceDependency.md`: The entity representing dependency relationships that this rule
  helps protect.

### For Humans: What This Means (Related)

This rule protects dependency *inputs* so your dependency graph stays clean.

