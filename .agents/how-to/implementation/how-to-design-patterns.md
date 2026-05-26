# How To Use Design Patterns

## Purpose

Patterns are tools, not decoration.

## Pattern Usage Rule

Use a pattern only when the problem, forces, and trade-offs justify it.

## Evidence Path

`.agents/management/evidence/generated/<task-name>/pattern-decision.md`

## Pattern Categories

- creational
- structural
- behavioral

## Abuse Rules

- Singleton: forbidden for mutable runtime state; requires lifecycle proof if used.
- Builder: useful for explicit construction, not hidden runtime assembly.
- Factory: useful at creation boundaries, not as a service locator.
- Strategy: useful for interchangeable behavior, not indirection theater.
- Adapter: useful for external translation, not private-internal reach-through.
- Facade/Public Surface: receives and delegates; it must not own runtime machinery.

## Naming Rule

Do not include pattern names unless they clarify the public or local vocabulary.

## Stop Conditions

Stop if the pattern is chosen before the problem is stated or hides dependency direction.

## Severity

Pattern abuse that hides runtime dependency lookup is BLOCKER.
Decorative pattern complexity is MEDIUM or HIGH depending on blast radius.

## Final Report Requirement

Final reports must state pattern used, problem fit, rejected simpler option, and risk.

## Pattern Decision Rubric

| Question | Required Answer |
|---|---|
| What problem is solved? | Concrete behavior or boundary problem. |
| What forces conflict? | Simplicity, flexibility, performance, safety, ownership. |
| What simpler option was rejected? | Named and justified. |
| What consequence is accepted? | Coupling, indirection, lifecycle, testing cost. |
| What proves fit? | Test, review, or fitness function. |

## Problem-Fit Checklist

- the variation is real, not speculative;
- the pattern reduces meaningful coupling or duplication;
- the lifecycle owner remains visible;
- the name remains business or responsibility oriented;
- tests prove behavior through the public contract.

## Consequence Checklist

- added indirection is justified;
- debugging path remains clear;
- dependency direction remains valid;
- runtime performance does not regress unproven;
- future removal path is possible.

## Pattern Abuse Examples

- Singleton used for mutable request state;
- Factory that calls a container as service locator;
- Strategy with only one strategy and no variation pressure;
- Adapter that only forwards private internals;
- Facade that owns business logic.

## Usage Examples

- Builder: configuration-time fluent assembly with validation.
- Factory: creation boundary for external resource adapters.
- Strategy: real interchangeable policy selected at assembly.
- Adapter: translate vendor model into local language.
- Facade/PublicSurface: stable entrypoint that delegates to a flow.

## When Not To Use A Pattern

Do not use a pattern when a direct function, class, flow, or capability names the behavior more clearly with less coupling.

## Naming Rule Detail

Prefer names like `CreateSessionToken` over `TokenFactory` unless creation is the actual public responsibility. Pattern names are secondary to behavior names.

## Accepted Exception Format

```text
pattern:
problem:
simpler option rejected:
accepted consequence:
owner:
review_date:
tests_or_fitness:
```
