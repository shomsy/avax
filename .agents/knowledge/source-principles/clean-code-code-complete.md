# Source Principles: Clean Code and Code Complete

## Role in Governance

Use this source family for construction discipline, names, cohesion, routines, tests, comments, defensive programming, and local maintainability.

## Construction Is Not Typing

Construction starts after scenario, ownership, dependencies, failure modes, and tests are known.

## Upstream Prerequisites

Before code: scenario for behavior, domain discovery for modeling, coupling decision for boundaries, and fitness evidence for governance/architecture changes.

## Problem Definition

The agent must state the behavior or rule being changed in one concrete sentence.

## Requirements/Scenario Prerequisite

No production behavior change from a title alone. Actor, goal, guarantees, failure paths, and tests must be known.

## Architecture Prerequisite

Boundary, dependency direction, and assembly/runtime ownership must be settled before implementation.

## Construction Decision Checklist

Owner, invariant, dependencies, inputs, outputs, failure behavior, tests, and evidence path.

## Meaningful Names

Good: `IssueAccessToken`, `ResolveTenantContext`. Bad: `TokenManager`, `DataHelper`, `GenericService`.

## Functions and Routines

A routine should do one exact action. If it says "process" or "handle" without a domain object, it likely hides multiple actions.

## Classes and Cohesion

A class is cohesive when its methods change for the same reason and protect the same invariant.

## Defensive Programming

Validate boundaries, fail closed for security, and make invalid states unrepresentable where practical.

## Assertions and Invariants

Use explicit checks for invariants that protect data, security, or runtime safety.

## Error Handling

Errors must be specific, safe, and actionable. Catch-all swallowing is forbidden.

## Variable Lifetime and Scope

Keep state short-lived. Long-lived worker state needs reset/lifecycle proof.

## Control Flow Complexity

Nested branching, flags, and mode switches require review. Prefer explicit flows or capabilities.

## Comments and Self-Documenting Code

Good comments explain why, risk, invariant, or external constraint. Bad comments narrate the next line.

## Developer Testing

Tests prove behavior, failure, regression, and public contract. Constructor-only tests are not enough.

## Debugging Discipline

Fix causes, not symptoms. Add regression proof when a bug is fixed.

## Refactoring Discipline

Do not mix refactor and feature unless evidence states why. Refactors need behavior proof.

## Integration Strategy

Small vertical slices with changed-scope validation are preferred over broad speculative edits.

## Code Tuning Only After Measurement

Performance changes require before/after measurement or a clear hot-path proof.

## Construction Checklist

Name, owner, invariant, dependency direction, failure mode, tests, evidence, review risk.

## Agent Failure Modes

- vague names
- large coordinator
- hidden dependency lookup
- shallow tests
- comments instead of clarity
- premature optimization
- refactor mixed with feature

## Evidence Requirements

`construction-checklist.md`, scenario/coupling evidence when applicable, tests and validation output.

## Severity Rules

Service locator in runtime code is BLOCKER. Shallow tests supporting GREEN are HIGH. Vague touched production names are MEDIUM/HIGH.

## Stop Conditions

Stop when ownership, dependency visibility, behavior proof, or failure semantics are unclear.

