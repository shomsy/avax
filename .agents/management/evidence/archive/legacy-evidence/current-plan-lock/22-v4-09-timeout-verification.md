# 22 — V4-09 Timeout Verification

**Date:** 2026-05-12
**Branch:** main
**Commit:** 21d6f69fd
**Scope:** Verify V4-09 Reliability Engine Timeout status

## V4-09 Definition

V4-09 is the Reliability Engine stage. It includes:
- Retry (with backoff strategies)
- Timeout (dual-mode: elapsed + pcntl pre-emptive)
- CircuitBreaker (half-open)
- Bulkhead
- Fallback
- Backpressure
- RateLimiter
- Idempotency

## Canonical Timeout Source

**File:** `components/Operations/Resilience/System/Capabilities/Timeout/Timeout.php`

**Implementation:**
- `run(Closure $operation)` — main entry point
- `runWithPcntl()` — pre-emptive timeout via `pcntl_alarm` + `SIGALRM` (CLI only)
- `runElapsed()` — post-hoc elapsed time check using `hrtime()`
- `preEmptive(int $timeoutMs)` — factory for pcntl mode
- `elapsed(int $timeoutMs)` — factory for elapsed mode
- Honest documentation of PHP limitation: "No PHP mechanism can interrupt a blocking I/O call mid-execution without pcntl"

**Exception:** `components/Operations/Resilience/System/Foundation/Failure/OperationTimedOut/OperationTimedOut.php`

## Tests

**File:** `tests/Unit/Components/Operations/Resilience/TimeoutTest.php`

Proves:
- Timeout throws OperationTimedOut when exceeded
- Operation completes when under timeout
- pcntl mode falls back to elapsed when unavailable
- Factory methods work correctly

## Dogfooding

V4-09 Timeout is dogfooded through:
1. **V5.6 FailureBoundary** — `EnforceTimeout` delegates to Resilience Timeout
2. **HTTP Client** — `TimeoutPolicy` references Resilience Timeout patterns
3. **ExecuteWithReliability** — `components/Operations/Resilience/System/Flows/ExecuteWithReliability/ExecuteWithReliability.php` uses Timeout in reliability flow

## Old RED Claim

**File:** `EVIDENCE/failure-boundary/09-timeout-recoverwith-deferred.md`

Claim: "Runtime Enforcement: NONE" — RED/deferred

**Why stale:** This file was written before the V5.6 closure pass implemented `EnforceTimeout`, `RunProtectedAction` timeout integration, and Resilience Timeout enforcement. The full-closure evidence (7 files) supersedes this.

**File:** `EVIDENCE/failure-boundary/deferred/V5.6-Y4-timeout-enforcement.md`

Claim: "Status: DEFERRED"

**Why stale:** Never updated after V5.6-Y4 was implemented. Code exists, tests pass, evidence exists. The deferred directory file is a stale artifact.

## Current GREEN Evidence

| Evidence | Status |
|---|---|
| `EVIDENCE/failure-boundary/full-closure-evidence.md` | GREEN — Y4 implemented |
| `EVIDENCE/failure-boundary/full-closure-final-acceptance-audit.md` | GREEN_ELAPSED — precise classification |
| `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md` | DONE |
| `EVIDENCE/recovery-reports/v4-05-through-v4-11-enterprise-closure-report.md` | GREEN |
| `EVIDENCE/recovery-reports/v4-midpoint-truth-reconciliation.md` | GREEN |
| `CURRENT_TRUTH.md` | GREEN with precision notes |
| `EVIDENCE/current-plan-lock/current-plan-ledger.md` | DONE |
| `tests/Unit/Framework/FailureBoundary/TimeoutEnforcementTest.php` | 5 tests GREEN |
| `tests/Unit/Components/Operations/Resilience/TimeoutTest.php` | GREEN |

## Classification: STALE_RED_SUPERSEDED

**Old reports say RED** (09-timeout-recoverwith-deferred.md, deferred/V5.6-Y4-timeout-enforcement.md).

**Newer code/evidence proves it was fixed** (EnforceTimeout, Resilience Timeout, 5+ tests, full-closure evidence, CURRENT_TRUTH.md).

**Old reports need superseded marker.**

## Does V4-09 Block V5.7?

**NO.** V4-09 Timeout is GREEN (via Resilience). The elapsed-mode limitation is documented and honest. V5.7 Events DSL does not require pre-emptive timeout. V5.7 Events DSL does not depend on the Resilience Timeout component.

## Does V4-09 Block Only Future Async/Parallel Work?

**PARTIALLY.** The elapsed-mode default means that for truly pre-emptive async timeout (interrupting a long-running HTTP call mid-flight), pcntl or runtime adapter support is needed. This is a V6 async/parallel prerequisite, not a V5.7 blocker. The elapsed-mode timeout IS functional — it measures and throws OperationTimedOut. It just cannot interrupt the operation mid-execution in elapsed mode.

## Required Actions

1. Mark `EVIDENCE/failure-boundary/09-timeout-recoverwith-deferred.md` as SUPERSEDED
2. Mark `EVIDENCE/failure-boundary/deferred/V5.6-Y4-timeout-enforcement.md` as SUPERSEDED
3. Fix `docs/failure-boundary/declarative-failure-boundary.md` "What Is Deferred" table

## Remaining Risks

- Stale evidence files still in repo without SUPERSEDED markers
- docs/failure-boundary/declarative-failure-boundary.md "What Is Deferred" table is outdated
- No code fix needed — timeout IS enforced

## Next Allowed Action

Proceed to V5.6 FailureBoundary Timeout verification.
