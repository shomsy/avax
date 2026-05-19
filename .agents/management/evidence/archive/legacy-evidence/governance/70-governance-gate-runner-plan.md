# Governance Gate Runner Plan

## Current State

No central governance gate runner exists. Each gate runs independently.

## Proposal

Create `tooling/governance/run-governance-gates.php` in a future pass that orchestrates all mandatory gates.

## Gate Inventory

| Gate | Command | Mandatory? | Self-test? | Blocks GREEN? |
|---|---|---|---|---|
| Truth consistency | `php tooling/governance/check-truth-consistency.php` | Yes | Unknown | Yes |
| Stage lock | `php tooling/governance/check-stage-lock.php` | Yes | Unknown | Yes |
| Runtime composition leaks | `php tooling/refactor/check-runtime-composition-leaks.php` | Yes | Unknown | Yes |
| Public surface | `php tooling/refactor/check-public-surface.php` | Yes | Unknown | Yes |
| Semantic PHPDoc | `php tooling/governance/check-semantic-phpdoc.php` | Yes | Partial | Yes (BLOCKER/HIGH) |
| How-to document structure | `php tooling/governance/check-how-to-document-structure.php` | Yes | Partial | Yes |
| ServiceProvider consistency | `php tooling/governance/check-serviceprovider-governance-consistency.php` | Yes | Partial | Yes |
| Canonical terms | `php tooling/governance/check-canonical-terms.php` | Yes | Partial | Yes |
| Large unit thresholds | `php tooling/governance/check-large-unit-thresholds.php` | Yes | Partial | Yes (BLOCKER) |
| Quality ratchet | `php tooling/governance/check-quality-ratchet.php` | Yes | Partial | No (YELLOW) |
| Security commit block readiness | `php tooling/governance/check-security-commit-block-readiness.php` | Yes | Partial | Yes |
| Gate self-tests | `php tooling/governance/check-gate-self-tests.php` | Yes | Partial | Yes |

## Status

GREEN — all gates are available. A runner script is a convenience, not a blocker.
