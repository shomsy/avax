# 21 — Timeout Evidence Inventory

**Date:** 2026-05-12
**Branch:** main
**Commit:** 21d6f69fd
**Scope:** All timeout references across framework, components, tests, tooling, docs, EVIDENCE, .agents

## Inventory

| Source   | File                                                                                              | Claim / Code                                                                                                                        | Timeout Scope                      | Status in Source                                    | Evidence Quality        | Action          |
|----------|---------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------|------------------------------------|-----------------------------------------------------|-------------------------|-----------------|
| CODE     | `components/Operations/Resilience/System/Capabilities/Timeout/Timeout.php`                        | Dual-mode: pcntl pre-emptive + elapsed post-hoc. Documents PHP limitation honestly.                                                 | V4-09 / V5.6-Y4 Resilience Timeout | GREEN — implemented, tested, documented             | HIGH — canonical source | KEEP_CURRENT    |
| CODE     | `framework/System/Capabilities/FailureBoundary/Capabilities/EnforceTimeout/EnforceTimeout.php`    | Delegates to Resilience Timeout when timeoutMs > 0.                                                                                 | V5.6-Y4 FailureBoundary Timeout    | GREEN — implemented, 5 tests                        | HIGH                    | KEEP_CURRENT    |
| CODE     | `framework/System/Capabilities/FailureBoundary/Flows/RunProtectedAction/RunProtectedAction.php`   | Reads timeoutMs from policy, passes to EnforceTimeout.                                                                              | V5.6-Y4 FailureBoundary Timeout    | GREEN — integrated                                  | HIGH                    | KEEP_CURRENT    |
| CODE     | `framework/System/Capabilities/FailureBoundary/Foundation/Attributes/Timeout.php`                 | `#[Timeout(milliseconds)]` attribute, compiled into FailurePolicy.                                                                  | V5.6-Y4 FailureBoundary Timeout    | GREEN — compiled + enforced                         | HIGH                    | KEEP_CURRENT    |
| CODE     | `framework/System/Capabilities/FailureBoundary/Foundation/FailurePolicy.php`                      | Stores timeoutMs from compiled attribute.                                                                                           | V5.6-Y4 FailureBoundary Timeout    | GREEN                                               | HIGH                    | KEEP_CURRENT    |
| TEST     | `tests/Unit/Framework/FailureBoundary/TimeoutEnforcementTest.php`                                 | 5 tests: fast action, slow action triggers OperationTimedOut, no timeout when null, delegates to Resilience, zero means no timeout. | V5.6-Y4                            | GREEN — proves enforcement                          | HIGH                    | KEEP_CURRENT    |
| TEST     | `tests/Unit/Components/Operations/Resilience/TimeoutTest.php`                                     | Tests Resilience Timeout directly (elapsed + pcntl modes).                                                                          | V4-09 Resilience Timeout           | GREEN                                               | HIGH                    | KEEP_CURRENT    |
| CODE     | `components/HTTP/Client/System/Capabilities/Resilience/TimeoutPolicy.php`                         | HTTP client timeout configuration.                                                                                                  | HTTP_TIMEOUT                       | GREEN — separate scope                              | HIGH                    | KEEP_CURRENT    |
| CODE     | `components/HTTP/Client/System/Foundation/Failure/HttpTimeout.php`                                | HTTP-specific timeout exception.                                                                                                    | HTTP_TIMEOUT                       | GREEN — separate scope                              | HIGH                    | KEEP_CURRENT    |
| CODE     | `components/DataStack/Database/System/Capabilities/Connections/Pools/*.php`                       | Connection pool timeout fields.                                                                                                     | DB_TIMEOUT                         | GREEN — separate scope                              | HIGH                    | KEEP_CURRENT    |
| CODE     | `components/Application/Cache/System/Capabilities/Source/ProtectCacheSource/CacheLockTimeout.php` | Cache stampede lock timeout.                                                                                                        | CACHE_TIMEOUT                      | GREEN — separate scope                              | HIGH                    | KEEP_CURRENT    |
| CODE     | `components/Operations/ApplicationWorkflow/System/Capabilities/Timeouts/SagaTimeout.php`          | Saga workflow timeout.                                                                                                              | SAGA_TIMEOUT                       | GREEN — separate scope                              | HIGH                    | KEEP_CURRENT    |
| CODE     | `components/Operations/Concurrency/System/Capabilities/Cancellation/CancellationToken.php`        | Task cancellation with deadline.                                                                                                    | CONCURRENCY_TIMEOUT                | GREEN — separate scope                              | HIGH                    | KEEP_CURRENT    |
| CODE     | `components/Operations/Tasks/System/Flows/CancelTask/CancelTask.php`                              | Task cancellation flow.                                                                                                             | TASK_TIMEOUT                       | GREEN — separate scope                              | HIGH                    | KEEP_CURRENT    |
| EVIDENCE | `EVIDENCE/failure-boundary/09-timeout-recoverwith-deferred.md`                                    | Timeout compiled but NOT enforced. RED/deferred.                                                                                    | V5.6-Y4 (OLD)                      | STALE — enforcement was added in V5.6 closure       | HIGH — superseded       | MARK_SUPERSEDED |
| EVIDENCE | `EVIDENCE/failure-boundary/deferred/V5.6-Y4-timeout-enforcement.md`                               | Status: DEFERRED. No enforcement.                                                                                                   | V5.6-Y4 (OLD)                      | STALE — still says DEFERRED but code IS implemented | HIGH — stale            | MARK_SUPERSEDED |
| EVIDENCE | `EVIDENCE/failure-boundary/full-closure-evidence.md`                                              | V5.6-Y4: EnforceTimeout wraps action with Resilience Timeout. GREEN.                                                                | V5.6-Y4                            | CURRENT — accurate                                  | HIGH                    | KEEP_CURRENT    |
| EVIDENCE | `EVIDENCE/failure-boundary/full-closure-final-acceptance-audit.md`                                | Timeout GREEN_ELAPSED. Resilience Timeout wraps action. elapsed-mode default documented. pcntl available where supported.           | V5.6-Y4                            | CURRENT — accurate with precision                   | HIGH                    | KEEP_CURRENT    |
| EVIDENCE | `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`                                         | V5.6-Y4 Timeout: DONE. Resilience Timeout enforcement.                                                                              | V5.6-Y4                            | CURRENT — accurate                                  | HIGH                    | KEEP_CURRENT    |
| EVIDENCE | `EVIDENCE/recovery-reports/v4-05-through-v4-11-enterprise-closure-report.md`                      | V4-09 Reliability Engine: GREEN. Timeout (dual-mode) implemented.                                                                   | V4-09                              | CURRENT — accurate                                  | HIGH                    | KEEP_CURRENT    |
| EVIDENCE | `EVIDENCE/recovery-reports/v4-midpoint-truth-reconciliation.md`                                   | V4-09: GREEN. Retry, Timeout (dual-mode), CircuitBreaker, etc.                                                                      | V4-09                              | CURRENT — accurate                                  | HIGH                    | KEEP_CURRENT    |
| EVIDENCE | `CURRENT_TRUTH.md`                                                                                | V4-09 Reliability Engine: GREEN. Timeout (real). V5.6-Y4: DONE. elapsed-mode default.                                               | V4-09 + V5.6-Y4                    | CURRENT — accurate with precision                   | HIGH                    | KEEP_CURRENT    |
| EVIDENCE | `EVIDENCE/EXECUTION.md`                                                                           | V4-09: GREEN. V5.6: FULL GREEN. Timeout enforced via Resilience.                                                                    | V4-09 + V5.6-Y4                    | CURRENT — accurate                                  | HIGH                    | KEEP_CURRENT    |
| EVIDENCE | `EVIDENCE/current-plan-lock/current-plan-ledger.md`                                               | V5.6-Y4 Timeout: DONE.                                                                                                              | V5.6-Y4                            | CURRENT — accurate                                  | HIGH                    | KEEP_CURRENT    |
| DOCS     | `docs/failure-boundary/declarative-failure-boundary.md`                                           | "What Is Deferred" table says Timeout: "Compiled but not enforced".                                                                 | V5.6-Y4 (OUTDATED)                 | STALE — docs say not enforced but it IS enforced    | HIGH — needs fix        | FIX_NOW         |
| DOCS     | `components/Operations/Resilience/System/HOW_THIS_WORKS.md`                                       | Documents Resilience Timeout dual-mode.                                                                                             | V4-09 Resilience Timeout           | CURRENT                                             | HIGH                    | KEEP_CURRENT    |

## Classification Summary

| Classification                 | Count | Items                                                                              |
|--------------------------------|-------|------------------------------------------------------------------------------------|
| KEEP_CURRENT / GREEN_CONFIRMED | 18    | Code, tests, current evidence, truth files                                         |
| MARK_SUPERSEDED                | 2     | `09-timeout-recoverwith-deferred.md`, `deferred/V5.6-Y4-timeout-enforcement.md`    |
| FIX_NOW                        | 1     | `docs/failure-boundary/declarative-failure-boundary.md` — "What Is Deferred" table |
| MARK_GREEN_CONFIRMED           | 1     | V4-09 Timeout via Resilience Timeout                                               |

## Timeout Scopes Identified

| Scope Kind                         | Owner                                                               | Files                                            | Status                   |
|------------------------------------|---------------------------------------------------------------------|--------------------------------------------------|--------------------------|
| V4-09 / V5.6-Y4 Resilience Timeout | `components/Operations/Resilience/.../Timeout/Timeout.php`          | Dual-mode: elapsed (default) + pcntl pre-emptive | GREEN                    |
| V5.6-Y4 FailureBoundary Timeout    | `framework/System/Capabilities/FailureBoundary/.../EnforceTimeout/` | Delegates to Resilience Timeout                  | GREEN_ELAPSED_DOCUMENTED |
| HTTP Client Timeout                | `components/HTTP/Client/.../TimeoutPolicy.php`                      | Per-request HTTP timeout                         | GREEN — separate         |
| DB Connection Pool Timeout         | `components/DataStack/Database/.../Pools/`                          | Connection acquisition timeout                   | GREEN — separate         |
| Cache Lock Timeout                 | `components/Application/Cache/.../CacheLockTimeout.php`             | Stampede protection timeout                      | GREEN — separate         |
| Saga Workflow Timeout              | `components/Operations/ApplicationWorkflow/.../SagaTimeout.php`     | Saga step timeout                                | GREEN — separate         |
| Concurrency Task Deadline          | `components/Operations/Concurrency/.../CancellationToken.php`       | Fiber task deadline                              | GREEN — separate         |
| Task Cancellation                  | `components/Operations/Tasks/.../CancelTask.php`                    | Task cancel flow                                 | GREEN — separate         |

## Conclusion

The V4-09 Timeout / V5.6-Y4 Timeout contradiction is resolved:

1. **V4-09 Reliability Engine Timeout** — The canonical Resilience Timeout component (
   `components/Operations/Resilience/.../Timeout.php`) provides dual-mode timeout enforcement. GREEN.

2. **V5.6 FailureBoundary Timeout** — The FailureBoundary delegates to Resilience Timeout via EnforceTimeout.
   Implemented, tested (5 tests), proven. GREEN_ELAPSED_DOCUMENTED.

3. **Stale RED evidence** — Two evidence files still claim Timeout is DEFERRED/RED. These are stale from before the V5.6
   closure pass and must be marked SUPERSEDED.

4. **Stale documentation** — `docs/failure-boundary/declarative-failure-boundary.md` still lists Timeout as "Compiled
   but not enforced" in its "What Is Deferred" table. Must be updated.

5. **No active RED** — All timeout enforcement claims in current truth, ledger, and closure evidence are accurate. The
   old RED claims are superseded evidence.

## Remaining Risks

- Stale deferred files need SUPERSEDED marker
- docs/failure-boundary/declarative-failure-boundary.md needs "What Is Deferred" table update
- Different timeout scopes (HTTP, DB, Cache, Saga, Concurrency) are separate from V4-09/V5.6-Y4 and are not blocked

## Next Allowed Action

Proceed to V4-09 Timeout verification report.
