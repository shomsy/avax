# How To Do Software Construction

## Construction Preflight

Before code, state behavior owner, invariant, dependencies, failure modes, tests, and evidence path.

## Naming Rule

Names must reveal responsibility and business or technical ownership.

Forbidden vague names by default:

- Manager
- Helper
- Util
- Utils
- Service
- Processor
- Handler
- Thing
- Data
- Info
- Stuff
- Common
- Base
- Generic

Documented exceptions such as `ServiceProvider` are allowed when the term is project vocabulary.

## Function And Method Rule

Functions should do one exact action and expose failure semantics.

## Class Cohesion Rule

A class should have one clear reason to change and should not collect unrelated behavior.

## Dependency Rule

Required dependencies are injected or assembled at configuration boundaries, not discovered at runtime.

## Boundary Rule

Public boundaries normalize and delegate. They do not own runtime machinery.

## Error Handling Rule

Errors must be specific, debuggable, and safe. Security-sensitive paths fail closed.

## Test Rule

Tests prove behavior, failure paths, and regressions. They do not only prove construction.

## Comment/PHPDoc Rule

Use comments for intent, invariants, failure behavior, and public API meaning. Do not narrate obvious statements.

## Construction Checklist

Every software construction evidence file must contain these exact headings:

- # Construction Checklist
- ## Task
- ## Scope
- ## Naming Discipline
- ## Minimal Viable Construction
- ## Encapsulation
- ## Method Length and Complexity
- ## Guard Clauses
- ## Error Handling
- ## Comments vs Self-Explaining Code
- ## Tests
- ## Review Date

## Stop Conditions

Stop on hidden service locator, generic bucket, missing behavior proof, unclear ownership, or unsafe failure semantics.

## Severity

Hidden runtime dependency lookup is BLOCKER.
Vague construction in touched production code is MEDIUM or HIGH.

## Final Report Requirement

Final reports must state construction checks run and remaining construction risk.

## Construction Prerequisites

Before code, confirm:

- scenario input exists or is explicitly skipped;
- domain terms are understood;
- architecture boundary is known;
- coupling decision exists when dependencies change;
- tests are planned for behavior and failure paths;
- evidence path is created.

## Checklist Before Code

```text
behavior owner:
invariant:
inputs:
outputs:
dependencies:
assembly boundary:
failure modes:
security sensitivity:
runtime sensitivity:
tests:
```

## Function Size / Complexity Heuristic

A routine is suspicious when it mixes validation, authorization, mutation, IO, formatting, logging, and response construction. Split by exact action when a reader cannot name the routine in one verb phrase.

## Class Cohesion Rubric

| Signal | Status |
|---|---|
| One reason to change and one behavior owner | GREEN |
| Two related responsibilities with tests | YELLOW |
| Many unrelated responsibilities or lifecycle concerns | RED |

## Dependency Visibility Rubric

Dependencies are GREEN when required collaborators are constructor arguments, provider outputs, or explicit configuration. They are RED when discovered through globals, containers, fallbacks, or runtime scanning.

## Error Handling Matrix

| Context | Required Behavior |
|---|---|
| Validation | reject with safe, specific reason |
| Security | fail closed and do not leak secret state |
| IO | classify retryable vs fatal |
| Runtime lifecycle | leave worker/request state safe |
| Data mutation | preserve invariant or reconcile |

## Test Quality Rubric

GREEN tests prove observable behavior, negative paths, and regressions. YELLOW tests cover only happy path. RED tests prove only construction, getters, or `assertTrue(true)`.

## Shallow Test Anti-Examples

```php
$this->assertTrue(true);
$this->assertNotNull(new Subject());
```

## Comment Examples

Bad:

```php
// Set the name.
```

Good:

```php
// Token expiry is computed before persistence so retries cannot extend lifetime.
```

## Defensive Programming Examples

- assert impossible internal states early;
- reject invalid boundary input explicitly;
- keep exception messages safe;
- prefer immutable value where shared runtime state would leak.

## Agent Failure Modes

- vague names that hide ownership;
- large coordinator that owns every step;
- hidden dependency lookup;
- shallow tests;
- comments replacing clear units;
- premature optimization without measurement;
- refactor mixed with feature change.

## Final Self-Review Checklist

- no generic bucket names added;
- dependencies are visible;
- failure behavior is safe;
- tests prove behavior;
- runtime and security sensitivity are handled;
- evidence records commands and remaining risk.
