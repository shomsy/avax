# Governance Gate Adoption Report

Generated: 2026-05-26

## What Changed

- Added legacy baselines for PHPStan, self-explaining architecture, and shallow-test findings.
- Added `--mode=full`, `--mode=baseline`, and `--mode=changed` behavior for the new adoption gates.
- Added PHPStan wrapper at `tooling/validation/check-phpstan-baseline.php`.
- Updated governance policy to distinguish full production readiness from changed-scope slice readiness.
- Updated review-pack generator so blocked/adoption packs clearly state YELLOW/RED status.

## Baseline Files

- `.agents/management/baselines/phpstan-baseline.json`: 100 entries.
- `.agents/management/baselines/self-explaining-architecture-baseline.json`: 563 entries.
- `.agents/management/baselines/shallow-tests-baseline.json`: 392 entries.

## Enforcement Semantics

| Mode | Meaning |
|---|---|
| `full` | Fail on every finding. |
| `baseline` | Allow only findings already recorded in baseline; fail on new or stale entries. |
| `changed` | Fail on changed-scope findings according to the gate policy. |

## Identity Rewrite Impact

The Identity rewrite is not FULL_GREEN-ready. It is eligible for bounded slices only if every slice runs changed-scope gates and fixes all touched-scope blockers before claiming readiness.
