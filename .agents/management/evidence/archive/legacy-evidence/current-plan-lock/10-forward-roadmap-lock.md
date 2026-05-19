# 10 — Forward Roadmap Lock

**Date:** 2026-05-12
**Branch:** main
**Commit:** 36949b7ac25a67412251cac172d1fb36d29b86c6
**Scope:** Future phases — locked, not active

## Current Completed Stages

| Stage                   | Status                          | Evidence                           |
|-------------------------|---------------------------------|------------------------------------|
| V1 Kernel               | PROVEN                          | Stage 00-13 complete               |
| V2 Platform             | GREEN (72 components)           | V2 stage ledger                    |
| V3 SystemDesign         | GREEN                           | components/SystemDesign, 454 tests |
| V4 Product Runtime      | GREEN (V4-01 through V4-17)     | V4 closure report                  |
| V5 Internal Convergence | GREEN (V5-00 through V5-23)     | V5 stage ledger                    |
| V5.5 Benchmark Proof    | GREEN (V5.5-00 through V5.5-12) | V5.5 audit                         |
| V5.6 Failure Boundary   | FULL GREEN (Y1-Y7 complete)     | full-closure-evidence              |

## Locked Future Roadmap

### V5.7 — Events Fluent DSL & PSR-14 Interop

- **Goal:** `onEvent(Event::class)->do(Listener::class)`, `emit(new Event)`, `#[ListensTo]`
- **Non-goals:** Event sourcing, CQRS changes, message bus replacement
- **Entry criteria:** V5.6 FULL GREEN (met)
- **Exit criteria:** PSR-14 adapter, compiled listener registry, no hot-path reflection, tests + gates
- **Dependencies:** Existing MessageBus EventBus, FailureBoundary attributes
- **First audit stage:** Events DSL design doc + PSR-14 interop plan
- **Validation expectation:** PHPUnit + PHPStan clean, PSR-14 compliance tests

### V5.8 — Database Lifecycle Events

- **Goal:** `onEntity()->beforeSave/afterCreate/afterUpdate/beforeDelete`, `onTransaction()->afterCommit/afterRollback`
- **Non-goals:** ORM replacement, schema migration changes
- **Entry criteria:** V5.7 GREEN
- **Exit criteria:** Entity events, transaction events, outbox/afterCommit discipline, bulk operation events
- **Dependencies:** V5.7 Events DSL, QueryBuilder, TransactionManager
- **First audit stage:** Database event lifecycle design
- **Validation expectation:** Integration tests with QueryBuilder, transaction boundary tests

### V5.9 — Fluent Boot DSL & Boot Lifecycle

- **Goal:** `avax()->from()->config()->routes()->events()->boot()`
- **Non-goals:** Container changes, runtime adapter changes
- **Entry criteria:** V5.8 GREEN
- **Exit criteria:** Staged boot pipeline, compile routes/events/metadata, boot report/doctor
- **Dependencies:** V5.7 Events, V4-04 Developer Experience, App API
- **First audit stage:** Boot DSL design + compilation plan
- **Validation expectation:** Boot sequence tests, compiled output verification

### V5.10 — PSR Interop & Standards Hardening

- **Goal:** PSR-7, PSR-15, PSR-17, PSR-14, PSR-18 adapters
- **Non-goals:** Breaking existing AvaX APIs
- **Entry criteria:** V5.9 GREEN
- **Exit criteria:** All PSR adapters, no inline factory fallback in runtime flows
- **Dependencies:** V5.7 PSR-14, existing HTTP layer
- **First audit stage:** PSR gap analysis
- **Validation expectation:** PSR compliance test suite

### V6.0 — Memory Governance Plane

### V6.1 — Bounded Data / StringBounds / PayloadBudget

### V6.2 — Schema-driven Runtime / Pre-database Validation

### V6.3 — Resource Lifecycle Model

### V6.4 — Async / Future DSL

### V6.5 — Async QueryBuilder / ORM Reads

### V6.6 — Parallel Work Execution

### V6.7 — Async / Parallel Arrhae, Collection, Json

### V6.8 — Typed Scenario DSL

### V6.9 — Parallel Test Runtime

V6 phases are PLANNED/LOCKED. Entry criteria for each depends on the previous phase being GREEN.
No V6 phase is active.

## Next Allowed Stage

**V5.7 — Events Fluent DSL & PSR-14 Interop**
Entry criteria: MET (V5.6 FULL GREEN)
Status: READY_NEXT, NOT_STARTED
