# ServiceValidator

## Quick Summary
- This file orchestrates multi-layer validation of `ServiceDefinitionEntity` objects: attribute rules, business constraints, dependency integrity, security scanning, and performance heuristics.
- It exists so “container validity” becomes a real, repeatable check instead of tribal knowledge.
- It removes the complexity of scattered validations by centralizing the validation story into one “pre-flight check” service.

### For Humans: What This Means (Summary)
It’s a safety inspector for your container: it checks wiring, safety, and obvious performance problems before you run the machine.

## Terminology (MANDATORY, EXPANSIVE)
- **Validation orchestrator**: A component that runs many validation checks and merges results.
  - In this file: `ServiceValidator` runs multiple private validation layers.
  - Why it matters: a single rule rarely captures the whole truth.
- **Attribute validation**: Validation driven by PHP attributes placed on properties.
  - In this file: reflection looks for `AbstractRule` attributes and runs them.
  - Why it matters: it keeps rules close to the data model.
- **Business rules**: Domain-specific constraints beyond “type and format”.
  - In this file: business validation checks lifetime/environment/tag/dependency constraints.
  - Why it matters: a technically valid value can still be domain-invalid.
- **Dependency integrity**: Whether dependencies exist and don’t create invalid graphs.
  - In this file: dependency repo is used to detect cycles and missing links.
  - Why it matters: many container failures are dependency shape failures.
- **Security scanning**: Detecting potentially dangerous patterns in service classes.
  - In this file: checks for suspicious function patterns.
  - Why it matters: container services are powerful; you want to notice risky ones.
- **Warnings vs errors**: Issues that block registration vs issues that should be addressed.
  - In this file: results contain `errors` and `warnings`.
  - Why it matters: you need to distinguish “stop now” from “fix soon”.

### For Humans: What This Means (Terms)
This is the place where the container decides “is our service catalog safe enough to use?”

## Think of It
Think of it like a car inspection: some issues fail the inspection (errors), some are advisories (warnings). Either way, you get a clear list of what to fix.

### For Humans: What This Means (Think)
You don’t want your container to be “surprising” on the road. Inspection makes it boring (in a good way).

## Story Example
You import service definitions from a JSON file. Some services reference missing dependencies, one class no longer exists after a refactor, and a few services have suspicious names that might indicate unsafe behavior. `ServiceValidator` runs once, gives you a structured report, and you fix issues before booting the container.

### For Humans: What This Means (Story)
It turns “it crashed somewhere later” into “here’s exactly what’s wrong right now”.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. You give it a service definition (or many).
2. It runs multiple checks.
3. It returns a report: valid/invalid + errors + warnings.
4. You fix problems and repeat until the report is clean.

## How It Works (Technical)
The validator is constructed with access to repositories for definitions and dependencies. `validateServices()` validates a list, while `validateService()` composes multiple private checks: attribute-based rule evaluation via reflection, business rule checks, dependency checks (including cycle detection), security scanning, and performance heuristics (complexity scoring and hotspots). Results are normalized into a consistent array schema used by tooling.

### For Humans: What This Means (How)
It’s a single “run all checks and give me a report” API.

## Architecture Role
- Why this file lives in `Guard/Rules`: it’s about preventing bad data/behavior from entering container operation.
- What depends on it: boot-time validation, CI checks, tooling, and diagnostics.
- What it depends on: service repositories, dependency repository, and the validation framework.
- System-level reasoning: a container is a runtime system; you want a “pre-flight” to reduce outages.

### For Humans: What This Means (Role)
It’s cheaper to fail fast than to debug weird runtime behavior.

## Methods 


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation (__construct)
Injects repositories needed for validation: definitions for existence, dependencies for graph integrity.

##### For Humans: What This Means (__construct)
It needs the list of services and the map of dependencies to inspect properly.

##### Parameters (__construct)
- `$serviceRepo`: Source of service definitions.
- `$dependencyRepo`: Source of dependency edges and graph operations.

##### Returns (__construct)
- Nothing.

##### Throws (__construct)
- None.

##### When to Use It (__construct)
- When wiring validation as part of boot tooling or CI.

##### Common Mistakes (__construct)
- Constructing the validator with empty/uninitialized repositories (you’ll get misleading results).

### Method: getValidationSummary(…)

#### Technical Explanation (getValidationSummary)
Validates all services and returns aggregate counts (valid/invalid/errors/warnings by rule).

##### For Humans: What This Means (getValidationSummary)
It gives you a dashboard summary, not just raw results.

##### Parameters (getValidationSummary)
- None.

##### Returns (getValidationSummary)
- Summary array with totals and per-rule counts.

##### Throws (getValidationSummary)
- Underlying repository exceptions.

##### When to Use It (getValidationSummary)
- CI gates and admin dashboards.

##### Common Mistakes (getValidationSummary)
- Using summary alone without viewing detailed reports for fixing issues.

### Method: validateServices(…)

#### Technical Explanation (validateServices)
Validates multiple services and returns a list of per-service validation results.

##### For Humans: What This Means (validateServices)
It’s batch validation: check the whole container catalog at once.

##### Parameters (validateServices)
- `$services`: Array of service objects; only `ServiceDefinitionEntity` items are validated.

##### Returns (validateServices)
- Array of validation result structures.

##### Throws (validateServices)
- Underlying repository exceptions.

##### When to Use It (validateServices)
- Bulk checks after imports, migrations, or large refactors.

##### Common Mistakes (validateServices)
- Passing raw arrays instead of entities (they’ll be ignored).

### Method: validateService(…)

#### Technical Explanation (validateService)
Runs the full validation pipeline for a single service definition and returns structured results.

##### For Humans: What This Means (validateService)
It answers: “Is this one service definition safe and correct?”

##### Parameters (validateService)
- `$service`: The `ServiceDefinitionEntity` to validate.

##### Returns (validateService)
- A structured result including `isValid`, `errors`, `warnings`, and `serviceId`.

##### Throws (validateService)
- Validation catches many reflection exceptions internally and reports them as errors; repository exceptions may still bubble up depending on implementation.

##### When to Use It (validateService)
- Validating a new/updated service definition before saving.

##### Common Mistakes (validateService)
- Ignoring warnings; they’re often early signals of future errors.

## Risks, Trade-offs & Recommended Practices
- Risk: Reflection-heavy validation can be slow on huge catalogs.
  - Why it matters: attribute scanning and reflection cost CPU time.
  - Design stance: validation is a tooling/boot concern, not a hot-path concern.
  - Recommended practice: run full validation in CI/admin contexts; cache results and avoid running it per request.
- Trade-off: Security scanning can produce noisy results.
  - Why it matters: pattern-based scanning can flag legitimate code.
  - Design stance: treat security checks as warnings by default unless you’re in strict security mode.
  - Recommended practice: allow configuration to tune what is “dangerous” and how it’s classified.

### For Humans: What This Means (Risks)
This is a powerful check, but you should run it at the right time (boot/CI), not constantly.

## Related Files & Folders
- `docs_md/Features/Define/Store/ServiceDefinitionEntity.md`: The objects being validated.
- `docs_md/Guard/Rules/ServiceValidationRule.md`: Validates service class strings.
- `docs_md/Guard/Rules/DependencyValidationRule.md`: Validates dependency lists.
- `docs_md/Features/Define/Store/ServiceDependencyRepository.md`: Used for dependency graph validation.

### For Humans: What This Means (Related)
The validator is the “conductor”; the individual rules are the “instruments”.

