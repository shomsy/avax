# Current Plan Ledger

**Date:** 2026-05-12
**Branch:** main
**Commit:** 36949b7ac25a67412251cac172d1fb36d29b86c6
**Scope:** Final source of truth for all current plans

## Ledger

| Plan / stage                   | Source                    | Previous status | Real status    | Evidence                           | Final action    |
|--------------------------------|---------------------------|-----------------|----------------|------------------------------------|-----------------|
| V1 Kernel                      | CURRENT_TRUTH.md          | PROVEN          | PROVEN         | Stage 00-13 reports                | DONE            |
| V2 Platform (72 components)    | CURRENT_TRUTH.md, TODO.md | GREEN           | GREEN          | V2 engine closure, 7899 tests      | DONE            |
| V3 SystemDesignKit             | CURRENT_TRUTH.md          | GREEN           | GREEN          | components/SystemDesign, 454 tests | DONE            |
| V4-01 through V4-17            | CURRENT_TRUTH.md          | COMPLETE/GREEN  | COMPLETE/GREEN | V4 closure report                  | DONE            |
| V5-00 through V5-22            | CURRENT_TRUTH.md          | GREEN           | GREEN          | V5 stage ledger                    | DONE            |
| V5-23 Final Truth Report       | CURRENT_TRUTH.md          | COMPLETE        | COMPLETE       | v5-23-final-truth-report.md        | DONE            |
| V5.5-00 through V5.5-12        | CURRENT_TRUTH.md          | GREEN           | GREEN          | V5.5 audit, benchmarks             | DONE            |
| V5.6 Core FailureBoundary      | CURRENT_TRUTH.md          | FULL GREEN      | FULL GREEN     | 90 tests, 4 gates                  | DONE            |
| V5.6-Y1 Retry/Resilience       | TODO.md → CURRENT_TRUTH   | todo → DONE     | DONE           | full-closure-evidence.md           | DONE            |
| V5.6-Y2 DeadLetter/Queue       | TODO.md → CURRENT_TRUTH   | blocked → DONE  | DONE           | full-closure-evidence.md           | DONE            |
| V5.6-Y3 Cleanup Hook           | TODO.md → CURRENT_TRUTH   | todo → DONE     | DONE           | full-closure-evidence.md           | DONE            |
| V5.6-Y4 Timeout                | TODO.md → CURRENT_TRUTH   | todo → DONE     | DONE           | full-closure-evidence.md           | DONE            |
| V5.6-Y5 RecoverWith            | TODO.md → CURRENT_TRUTH   | todo → DONE     | DONE           | full-closure-evidence.md           | DONE            |
| V5.6-Y6 Real Adoption          | TODO.md                   | done            | DONE           | E2E tests, RegistrationController  | DONE            |
| V5.6-Y7 PHPStan Warnings       | TODO.md                   | done            | DONE           | PHPStan 0 errors                   | DONE            |
| V5.7 Events DSL                | ROADMAP                   | PLANNED         | PLANNED        | forward-roadmap-lock.md            | ROADMAP         |
| V5.8 Database Lifecycle Events | ROADMAP                   | PLANNED         | PLANNED        | forward-roadmap-lock.md            | ROADMAP         |
| V5.9 Boot DSL                  | ROADMAP                   | PLANNED         | PLANNED        | forward-roadmap-lock.md            | ROADMAP         |
| V5.10 PSR Interop              | ROADMAP                   | PLANNED         | PLANNED        | forward-roadmap-lock.md            | ROADMAP         |
| V6.0-V6.9                      | ROADMAP                   | PLANNED         | PLANNED        | forward-roadmap-lock.md            | ROADMAP         |
| PSR-4 test namespace fix       | BUGS.md scan              | untracked       | P2             | 15-psr4-warning-closure.md         | FIXED_IN_THIS_PASS |
| check-raw-file-operations.php  | tooling audit             | UNAVAILABLE     | P2             | 16-raw-file-gate-closure.md        | FIXED_IN_THIS_PASS |
| DateTime→DateTimeImmutable     | code scan                 | untracked       | P3             | 03-bug-inventory.md                | ROADMAP         |
| EXECUTION.md stale state       | EXECUTION.md              | V4 IN PROGRESS  | STALE          | 17-execution-truth-closure.md      | FIXED_IN_THIS_PASS |
| ACTIVE board stale cards       | ACTIVE.md                 | old stages      | STALE          | 18-active-board-closure.md         | FIXED_IN_THIS_PASS |
| V4-09 Timeout RED stale claim  | 09-timeout-recoverwith-deferred.md | RED/deferred | STALE_RED_SUPERSEDED | 22-v4-09-timeout-verification.md | SUPERSEDED |
| V5.6-Y4 DEFERRED stale claim   | deferred/V5.6-Y4-timeout-enforcement.md | DEFERRED | STALE | 23-v5-6-failure-boundary-timeout-verification.md | SUPERSEDED |
| Docs stale "What Is Deferred"  | docs/failure-boundary/declarative-failure-boundary.md | not enforced | OUTDATED | 21-timeout-evidence-inventory.md | FIXED_IN_THIS_PASS |
| V4-05 to V4-11 docs gap claim  | ROADMAP claim | missing docs | EVIDENCE_ONLY_ACCEPTABLE | 25-v4-05-to-v4-11-documentation-verification.md | NON_BLOCKING |
| V4-10 EventBus vs V5.7 Events  | V5.7 design lock | potential conflict | MESSAGEBUS_SPECIFIC_ADAPTER | 26-v4-10-eventbus-v5-7-events-relationship.md | DOCUMENTED |

## Summary

- **DONE:** 20 (V1 through V5.6-Y7)
- **ROADMAP:** 6 (V5.7 through V5.10, V6.0-V6.9, DateTime cleanup)
- **FIXED_IN_THIS_PASS:** 5 (PSR-4 namespaces, raw file gate, EXECUTION.md, ACTIVE board, docs stale table)
- **SUPERSEDED:** 2 (09-timeout-recoverwith-deferred.md, deferred/V5.6-Y4-timeout-enforcement.md)
- **DOCUMENTED_NON_BLOCKING:** 3 (V4-09 Timeout truth, V4-05-V4-11 docs, V4-10 EventBus relation)
- **Vague/unresolved:** 0
