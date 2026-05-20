---
name: avax-test-evidence-quality
description: Ensures tests prove behavior, security, compatibility, and regression closure, not only construction. Forbids shallow tests, changing tests to fit broken behavior, missing negative tests, and fake GREEN. Use when changing tests, claiming validation, fixing bugs, or proving public API contracts.
---

# AvaX Test Evidence Quality

## Purpose

Tests must prove behavior, security, compatibility, and regression closure, not only construction.

A test that only instantiates a class is not proof.

A test that changes to match broken behavior is not proof.

## Activation Triggers

Activate when task:

- changes test files
- claims validation GREEN
- fixes a bug
- changes public API/DSL
- changes runtime/lifecycle/state behavior
- adds security-sensitive behavior
- adds performance-sensitive behavior
- refactors existing tests
- removes existing tests
- adds contract tests
- claims regression closure

## Forbidden Test Patterns

Forbid:

- tests that only instantiate classes
- tests asserting irrelevant implementation details
- changing tests to fit broken behavior
- no negative tests for security-sensitive behavior
- no regression test for fixed finding
- no public contract test for public API change
- no worker/runtime safety test for lifecycle/state changes
- fake GREEN by running irrelevant tests
- tests that mock everything and assert nothing
- tests that depend on execution order
- tests that leak state between runs
- tests that use real I/O without cleanup
- tests that assert internal implementation instead of behavior

## Required Test Coverage

Must require:

- behavior test: proves the change works correctly
- negative test where relevant: proves incorrect input/state fails properly
- regression test for fixed finding: proves the bug cannot recur
- contract test for public API/DSL: proves public interface stability
- worker/runtime test for runtime state: proves no state leak between requests
- focused command: the exact test command that proves the claim
- explanation of what the test proves

## Required Evidence

Every test-related task must include:

- `test-proof.md`
- `negative-test-proof.md` if security/safety-relevant
- `regression-proof.md` if fixing a bug

## Test Classification

Classify each test change:

- BEHAVIOR_PROVEN: test proves correct behavior
- SECURITY_NEGATIVE_PROVEN: negative test proves security boundary
- CONTRACT_PROVEN: contract test proves public API stability
- REGRESSION_PROVEN: regression test proves bug cannot recur
- TESTS_TOO_SHALLOW_BLOCKER: test does not prove meaningful behavior
- TEST_SCOPE_WRONG_BLOCKER: test asserts wrong scope (implementation vs behavior)

## Commit Rules

Commit is forbidden if classification is:

- TESTS_TOO_SHALLOW_BLOCKER
- TEST_SCOPE_WRONG_BLOCKER

Unless explicitly documented as accepted YELLOW with owner, risk, mitigation, and expiry.

## Regression Test Rule

Every fixed bug must have a regression test.

A fix without a regression test is not complete.

The regression test must:

- reproduce the original failure
- pass after the fix
- be named to describe the bug
- live in the appropriate test category

## Negative Test Rule

Security-sensitive behavior must have negative tests.

A security boundary without a negative test is not proven.

Negative tests must:

- attempt the attack/incorrect behavior
- assert the correct failure response
- prove no data/state leakage

## Contract Test Rule

Public API changes must have contract tests.

A public API without a contract test is not stable.

Contract tests must:

- exercise the public interface
- assert expected behavior
- assert expected exceptions
- not depend on internal implementation

## Integration with Other Skills

This skill must be loaded together with:

- `avax-enterprise-remediation`
- `avax-test-evidence-quality` for test-related tasks
- `avax-security-threat-model` for security negative tests
- `avax-api-compatibility-contract` for public API contract tests
- `validation` skill

## Final Rule

No behavior proof, no GREEN.

No negative test for security, no commit.

No regression test for fixed bug, not done.

No contract test for public API, not stable.
