# AntiPattern: Spaghetti Code

## What It Is

Behavior is tangled across unrelated units so a reader cannot predict which unit owns a decision, side effect, or failure path.

## Symptoms

- Long procedural flows with many unrelated branches.
- Cross-cutting state passed through wide parameter lists.
- Error handling interleaved with unrelated business decisions.
- Changes require touching many distant files without a clear ownership reason.

## Why It Is Dangerous

Spaghetti code hides invariants and makes security, data, runtime, and rollback behavior hard to prove.

## Common AI Failure Mode

The agent patches the nearest failing branch and adds another conditional instead of naming the missing flow, capability, or boundary.

## How to Fix

Name the behavior owner, split by flow or capability, preserve behavior with tests, and move cross-cutting concerns to explicit boundaries.

## Allowed Exceptions

Short transitional glue is allowed only with evidence, owner, expiry, and a cleanup path.

## Severity

HIGH when it affects production behavior or security-sensitive code. MEDIUM for local maintainability issues with tests.
