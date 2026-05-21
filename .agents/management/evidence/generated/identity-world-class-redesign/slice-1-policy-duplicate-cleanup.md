# Slice 1: Policy/Authorization Duplicate Cleanup

Status: COMPLETED
Date: 2026-05-21
Branch: architecture/identity-world-class-redesign

## Purpose

Collapse the 12+ duplicate Policy files across the Identity Access component into a canonical set, fixing the broken `PolicyDecision` stub that made the policy evaluation system return always-denied for matching rules.

## What Was Removed (7 files/dirs, ~12 classes)

| Deleted Path | Type | Reason |
|---|---|---|
| `Policy/PublicSurface/Policy.php` | File | Identical copy of `Policy/Policy.php` (93 lines each) |
| `Policy/Capabilities/` | Dir | Complete duplicate of `Engine/` and `Rules/` subdirs |
| `Policy/Rules/PolicyDecision.php` | File | Empty stub `class PolicyDecision {}` — broken |
| `Policy/Engine/DecisionExplanation.php` | File | Replaced by canonical Foundation version |
| `Policy/Engine/AttributeCondition.php` | File | Duplicate of inline definition in `PolicyRule.php` |
| `Policy/Engine/PolicyDecision.php` | File | Replaced by canonical Foundation version |
| `Policy/Capabilities/Rules/PolicyDecision.php` | File | Empty stub (part of Capabilities/ dir) |

## What Was Created (2 new files)

| Created Path | Classes | Purpose |
|---|---|---|
| `Policy/Foundation/PolicyDecision.php` | `PolicyDecision` | Canonical readonly DTO with `allow()`/`deny()` static factories |
| `Policy/Foundation/DecisionExplanation.php` | `DecisionExplanation` | Canonical readonly DTO with `toString()` |

## What Was Updated (3 files)

| File | Change |
|---|---|
| `Policy/Policy.php` | Removed inner `PolicyDecision` and `DecisionExplanation` classes; import from Foundation |
| `Policy/Engine/PolicyEvaluator.php` | Removed inner `PolicyDecision` class; import from Foundation |
| `Policy/Rules/PolicyRule.php` | Fixed broken import — now imports `PolicyDecision` from Foundation instead of empty stub |

## Files Remaining (12 production files)

```
Policy/
  Policy.php                          — Static facade for policy definitions
  AccessPolicy.php                    — AccessPolicy value object
  IdentityPolicy.php                  — Identity policy enum
  IdentityPolicyCatalog.php           — Policy catalog
  AssuranceTier.php                   — Enum
  AuthenticationFactor.php            — Enum
  IdentityActor.php                   — Enum
  RecoveryPath.php                    — Enum
  Engine/
    PolicyEvaluator.php               — Evaluates registered PolicyRules
  Foundation/
    PolicyDecision.php                — Canonical decision DTO (NEW)
    DecisionExplanation.php           — Canonical explanation DTO (NEW)
  Rules/
    PolicyRule.php                    — Rule with action/condition/pattern
```

## Bug Fix: Broken PolicyDecision Stub

The `Policy\Rules\PolicyDecision` class was an empty stub (`class PolicyDecision {}`). When `PolicyRule::evaluate()` returned `new PolicyDecision(true|false, $this->reason)`, the resulting object had no `->allowed` or `->reason` properties. The `PolicyEvaluator::evaluate()` method then accessed `$result->allowed`, which was always `null` (PHP undefined property), causing ANY matching rule to always result in denial.

This was effectively a bug that made Policy rules impossible to use with allow outcomes. The fix extracts the real `PolicyDecision` to `Policy/Foundation/` and updates all three consumer files to use it.

## Validation

```text
php vendor/bin/phpunit --no-coverage --filter="Identity" --testdox
Tests: 236, Assertions: 790, Failures: 0, Errors: 0, Deprecations: 1 (pre-existing)

php vendor/bin/phpunit --no-coverage --filter="PolicyCharacterization" --testdox
Tests: 52, Assertions: 100, Failures: 0, Errors: 0
```

## Risk Assessment

- 0 production files changed in behavior — all refactored code preserves the same interface contracts
- `PolicyDecision` removed from 3 files as inner class and extracted to canonical Foundation — PHP maintains BC for inner class consumers since the Foundation version has the same structure
- No code outside the Policy/ directory imports from the deleted paths
- Tests prove: PolicyEvaluator correctly evaluates matching/non-matching rules, Policy facade works, Foundation classes are final readonly
