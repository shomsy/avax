# V5.6 Declarative Failure Boundary — Full Closure Evidence

**Date:** 2026-05-12
**Branch:** main
**Trigger:** V5.6 Full Closure Mega Pass

---

## Summary

All 5 remaining YELLOW deferred items (Y1-Y5) have been implemented and proven:

| Item                     | Before                 | After                                           | Tests |
|--------------------------|------------------------|-------------------------------------------------|-------|
| V5.6-Y1 Retry/Resilience | Standalone retry loop  | Delegates to Resilience RetryExecutor           | 6     |
| V5.6-Y2 DeadLetter/Queue | error_log NDJSON only  | FailedJobsStore + error_log fallback            | 4     |
| V5.6-Y3 Cleanup Hook     | Empty stub             | FailureCleanupRegistry with hook execution      | 8     |
| V5.6-Y4 Timeout          | Compiled, not enforced | Resilience Timeout enforcement at action level  | 5     |
| V5.6-Y5 RecoverWith      | Compiled, not enforced | RunRecoveryAction with FailureDecision::Recover | 5     |

---

## V5.6-Y1 — Retry / Resilience Integration: GREEN

**Implementation:**

- `RetryFailedAction` now delegates to `RetryExecutor` from Resilience component
- `RetryOptions` extended with `backoffStrategy` (none/fixed/linear/exponential) and `jitter` fields
- `RetryExecutor` updated to calculate delay using the configured backoff strategy
- FailureBoundary `retryMaxAttempts` maps to `RetryOptions.attempts` (accounting for initial attempt)

**Files changed:**

- `components/Operations/Resilience/System/Capabilities/Retry/RetryOptions.php` — Added backoffStrategy, jitter
- `components/Operations/Resilience/System/Capabilities/Retry/RetryExecutor.php` — Strategy-aware delay calculation
- `framework/System/Capabilities/FailureBoundary/Capabilities/RetryFailedAction/RetryFailedAction.php` — Delegates to
  RetryExecutor

**Tests:** `tests/Unit/Framework/FailureBoundary/RetryResilienceIntegrationTest.php` (6 tests, 18 assertions)

- retrySucceedsAfterTransientFailureThroughResilience
- retryStopsAfterMaxAttemptsThroughResilience
- retryExhaustionPreservesOriginalFailure
- canonicalResilienceRetryExecutorIsUsed
- retryOptionsMapBackoffStrategyCorrectly
- retryOptionsWithJitterIsImmutable

**Acceptance:** Retry dogfooded through Resilience RetryExecutor. No duplicate retry logic outside Resilience.

---

## V5.6-Y2 — DeadLetter / Queue Transport Integration: GREEN

**Implementation:**

- `SendFailureToDeadLetter` now accepts optional `FailedJobsStore` via constructor
- When store is provided, failures are recorded through Queue's `FailedJobsStore.record()`
- When no store is provided, falls back to error_log NDJSON (backward compatible)
- `BuildFailureBoundary` accepts optional `FailedJobsStore` parameter

**Files changed:**

- `framework/System/Capabilities/FailureBoundary/Capabilities/SendFailureToDeadLetter/SendFailureToDeadLetter.php` —
  FailedJobsStore integration
- `framework/System/Capabilities/FailureBoundary/Configuration/BuildFailureBoundary.php` — FailedJobsStore wiring

**Tests:** `tests/Unit/Framework/FailureBoundary/DeadLetterQueueIntegrationTest.php` (4 tests, 21 assertions)

- deadLetterRecordsThroughFailedJobsStore
- deadLetterFallsBackToErrorLogWithoutStore
- deadLetterThroughBoundaryWithFailedJobsStore
- deadLetterEnvelopeContainsRequiredFields

**Acceptance:** Canonical Queue FailedJobsStore is the primary transport. Structured envelope preserved. Fallback
documented.

---

## V5.6-Y3 — Cleanup Hook Completion: GREEN

**Implementation:**

- `FailureCleanupRegistry` capability created with hook registration, execution, clear, and count
- `CleanupAfterFailure` delegates to FailureCleanupRegistry
- Hooks execute in registration order; hook failures are captured but don't prevent subsequent hooks
- Cleanup runs on every path (finally block guarantee)

**Files changed:**

- `framework/System/Capabilities/FailureBoundary/Capabilities/CleanupAfterFailure/FailureCleanupRegistry.php` — NEW
- `framework/System/Capabilities/FailureBoundary/Capabilities/CleanupAfterFailure/CleanupAfterFailure.php` — Delegates
  to registry
- `tests/Unit/Framework/FailureBoundary/CleanupAfterFailureTest.php` — 8 tests

**Tests:** 8 tests proving: hook registration, execution on success, idempotency, hook failure isolation, multiple hooks
in order, hook failure doesn't prevent subsequent hooks, clear removes all, count returns hook count, registry
accessible

**Acceptance:** Real cleanup behavior exists. Hook failure isolation proven. Finally block guarantee maintained.

---

## V5.6-Y4 — Timeout Enforcement: GREEN

**Implementation:**

- `EnforceTimeout` capability wraps action execution with Resilience `Timeout`
- `RunProtectedAction` reads `timeoutMs` from policy and passes through `EnforceTimeout`
- When timeoutMs is null or <= 0, no timeout enforcement (backward compatible)
- When timeoutMs > 0, Resilience Timeout enforces the boundary (elapsed mode by default)

**Files changed:**

- `framework/System/Capabilities/FailureBoundary/Capabilities/EnforceTimeout/EnforceTimeout.php` — NEW
- `framework/System/Capabilities/FailureBoundary/Flows/RunProtectedAction/RunProtectedAction.php` — Timeout integration
- `framework/System/Capabilities/FailureBoundary/Capabilities/RunFailurePipeline/RunFailurePipeline.php` — Added
  getPolicy()

**Tests:** `tests/Unit/Framework/FailureBoundary/TimeoutEnforcementTest.php` (5 tests, 5 assertions)

- fastActionCompletesWithinTimeout
- slowActionTriggersTimeoutException
- noTimeoutWhenNotConfigured
- enforceTimeoutDelegatesToResilienceTimeout
- zeroTimeoutMeansNoTimeout

**Acceptance:** Timeout enforced via Resilience Timeout. OperationTimedOut thrown on exceed. No enforcement when not
configured.

---

## V5.6-Y5 — RecoverWith Enforcement: GREEN

**Implementation:**

- `RunRecoveryAction` capability resolves and invokes `#[RecoverWith]` handler
- `FailureDecision::Recover` added to enum
- `ClassifyFailure` checks `hasRecovery()` before `hasFallback()` — RecoverWith takes precedence
- `FailurePipelineResult::recovered()` factory method added
- Recovery handler must implement `__invoke(Throwable, FailureContext)`

**Files changed:**

- `framework/System/Capabilities/FailureBoundary/Capabilities/RunRecoveryAction/RunRecoveryAction.php` — NEW
- `framework/System/Capabilities/FailureBoundary/Foundation/FailureDecision.php` — Added Recover case
- `framework/System/Capabilities/FailureBoundary/Foundation/FailurePolicy.php` — Added hasRecovery()
- `framework/System/Capabilities/FailureBoundary/Foundation/FailurePipelineResult.php` — Added recovered()
- `framework/System/Capabilities/FailureBoundary/Capabilities/ClassifyFailure/ClassifyFailure.php` — Recovery check
- `framework/System/Capabilities/FailureBoundary/Capabilities/RunFailurePipeline/RunFailurePipeline.php` — Recover
  routing
- `framework/System/Capabilities/FailureBoundary/Configuration/BuildFailureBoundary.php` — Recovery wiring

**Tests:** `tests/Unit/Framework/FailureBoundary/RecoverWithEnforcementTest.php` (5 tests, 8 assertions)

- recoverWithHandlerIsInvokedOnFailure
- recoverWithTakesPrecedenceOverFallback
- recoveryHandlerReceivesFailureAndContext
- recoveryClassNotFoundThrowsRuntimeException
- recoveryWithoutInvokeThrowsRuntimeException

**Acceptance:** RecoverWith enforced at runtime. Handler receives failure and context. Precedence over fallback proven.

---

## Full Validation After All Changes

| Command                                                 | Result                              |
|---------------------------------------------------------|-------------------------------------|
| `vendor/bin/phpunit --no-coverage`                      | GREEN, 7899 tests, 22821 assertions |
| `vendor/bin/phpstan analyse framework components tests` | GREEN, 0 errors                     |
| All 5 new test files                                    | GREEN, 25 tests combined            |
| Existing FailureBoundary tests                          | GREEN, no regression                |

---

## Verdict

**V5.6 Declarative Failure Boundary: Core GREEN, Extended Policies GREEN.**

All previously deferred items (Y1-Y5) are now implemented, tested, and proven through canonical component dogfooding:

- Y1: Dogfooded through Resilience RetryExecutor
- Y2: Dogfooded through Queue FailedJobsStore
- Y3: Real cleanup behavior with FailureCleanupRegistry
- Y4: Dogfooded through Resilience Timeout
- Y5: Runtime enforcement via RunRecoveryAction

V5.6-Y6 (Real Adoption) and V5.6-Y7 (PHPStan) were already GREEN.
