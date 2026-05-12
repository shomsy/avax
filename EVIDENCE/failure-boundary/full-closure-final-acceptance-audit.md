# V5.6 Declarative Failure Boundary — Full Closure Final Acceptance Audit

**Date:** 2026-05-12
**Branch:** main
**Commit:** c7c10aa91
**Audit type:** Verification only — no feature work, no moves, no renames
**Auditor:** Qoder CLI

---

## Executive Verdict

**V5.6 Declarative Failure Boundary: Core GREEN, Extended Policies GREEN, Timeout classification needs honest note.**

The previous "FULL GREEN" claim is **substantially correct** with the following corrections:

1. **Files changed count was wrong** — commit claims 14, actual is 26.
2. **Dogfooding gate is stale** — `check-dogfooding.php` flags Retry using Resilience as a violation, which is the opposite of the truth. The tool was written before Resilience integration existed and needs updating.
3. **Timeout is elapsed-mode only** — Resilience Timeout uses post-hoc elapsed checking by default (not pre-emptive interruption). This is honest and documented, but must be classified precisely.

---

## 1. Files Changed Count Correction

| Claim | Actual |
|-------|--------|
| 14 files | 26 files (git show --stat) |

**Correction:** The commit message said "Files Changed (14 new/modified)" but git shows 26 files changed (1362 insertions, 441 deletions). The count likely referred only to "new V5.6 closure files" but did not account for all modified files.

**New files (9):** FailureCleanupRegistry, RunRecoveryAction, EnforceTimeout, 5 test files, full-closure-evidence.md
**Modified files (17):** RetryExecutor, RetryOptions, RetryFailedAction, SendFailureToDeadLetter, BuildFailureBoundary, CleanupAfterFailure, FailureDecision, FailurePolicy, FailurePipelineResult, ClassifyFailure, RunFailurePipeline, RunProtectedAction, CURRENT_TRUTH.md, v5.6-deferred-work-backlog.md, plus 3 more in the diff.

---

## 2. Full Validation Results

| Command | Result |
|---------|--------|
| `composer validate --no-check-publish` | GREEN — valid |
| `composer dump-autoload -o` | GREEN — 9175 classes (some PSR-4 warnings pre-existing) |
| `vendor/bin/phpunit --no-coverage` | GREEN — 7899 tests, 22821 assertions |
| `vendor/bin/phpstan analyse framework components tests` | GREEN — 0 errors |
| `php tooling/refactor/check-component-canonical-shape.php` | GREEN |
| `php tooling/refactor/check-namespace-drift.php` | PASS |
| `php tooling/refactor/check-public-surface.php` | PASS |
| `php tooling/refactor/check-runtime-leaks.php` | PASS |
| `php tooling/refactor/check-advanced-pattern-folder-violations.php` | GREEN |
| `php tooling/governance/check-component-adoption.php` | PASS — 8/8 |
| `php tooling/failure-boundary/check-attributes-compiled.php` | GREEN |
| `php tooling/failure-boundary/check-local-try-catch.php` | GREEN |
| `php tooling/failure-boundary/check-dogfooding.php` | **STALE** — flags Resilience integration as violation (false positive) |
| `php tooling/refactor/check-failure-boundary-adoption.php` | GREEN — 11/11 |

---

## 3. Final Acceptance Matrix

| Item | Status | Production-ready | Evidence | Remaining risk |
|---|---|---|---|---|
| OnFailure | GREEN | YES | Attributes compiled, wired in RunFailurePipeline, E2E tests pass, adopted in RegistrationController | None |
| ReportFailure | GREEN | YES | Logger primary, error_log fallback, structured context, tests pass | LOW — fallback path remains |
| Retry | GREEN | YES | Delegates to Resilience RetryExecutor; backoff strategies; max attempts proven; non-retryable failures preserved; 6 integration tests | None — canonical dogfooding proven |
| Fallback | GREEN | YES | RunFallbackAction executes policy-declared fallback class, tested E2E | None |
| DeadLetter | GREEN | YES | Queue FailedJobsStore primary when provided; structured envelope with type/version/failure/context/timestamp; 4 integration tests | LOW — error_log fallback when no store configured |
| Cleanup | GREEN | YES | FailureCleanupRegistry with hook registration, execution on all paths, failure isolation, idempotency; 8 tests | LOW — no production hooks registered yet (infrastructure exists) |
| Timeout | GREEN_ELAPSED | YES | Resilience Timeout wraps action at RunProtectedAction level; OperationTimedOut thrown on exceed; 5 tests | MEDIUM — default mode is post-hoc elapsed check, not pre-emptive interruption. pcntl pre-emptive mode available but requires CLI + pcntl extension. Documented honestly in Timeout.php. |
| RecoverWith | GREEN | YES | RunRecoveryAction resolves and invokes #[RecoverWith] handler; receives FailureContext; takes precedence over Fallback; 5 tests | LOW — handler instantiated via `new $recoverClass()` (composition root pattern, acceptable) |
| Rethrow | GREEN | YES | FailureDecision::Rethrow + rethrowExcept list, classified before other decisions, tested | None |
| HTTP integration | GREEN | YES | Registered in AppKernel via class_exists guard, middleware contract proven | None |
| Compiled metadata | GREEN | YES | CompileFailurePolicies uses reflection at compile time only; static cache with staleness detection; no hot-path reflection | None |
| Real adoption | GREEN | YES | RegistrationController uses #[OnFailure] + #[ReportFailure] in production reference flow | None |

---

## 4. Verification Details

### 4.1 Retry / Resilience Integration — GREEN

**Proof:**
- `RetryFailedAction.execute()` maps FailurePolicy options to `RetryOptions` and delegates to `RetryExecutor.execute()`
- `RetryExecutor` implements the retry loop with backoff calculation (none/fixed/linear/exponential) and jitter
- Tests prove: retry succeeds after transient failure (2 attempts), stops after max attempts (2 attempts for max=2), preserves original failure message, canonical RetryExecutor works directly
- No standalone retry logic exists in FailureBoundary production path

**Classification:** GREEN_REAL_INTEGRATION

### 4.2 DeadLetter / Queue Integration — GREEN

**Proof:**
- `SendFailureToDeadLetter` accepts optional `FailedJobsStore` via constructor injection
- When store is provided: calls `failedJobsStore.record()` with structured envelope
- When no store: `error_log(json_encode($envelope))` as NDJSON fallback
- Tests prove: FailedJobsStore records correctly (InMemoryFailedJobsStore), envelope contains all required fields (type, version, queue, failure, context, timestamp), fallback completes without throwing
- `BuildFailureBoundary` accepts `FailedJobsStore` parameter and wires it through

**Classification:** GREEN_CANONICAL_TRANSPORT with documented fallback

### 4.3 Cleanup Hook — GREEN

**Proof:**
- `FailureCleanupRegistry` provides `register()`, `cleanup()`, `clear()`, `count()`
- `CleanupAfterFailure` delegates to registry
- `RunProtectedAction.run()` calls `$this->cleanup->for($context)` in `finally` block — guaranteed execution
- Tests prove: cleanup runs on success, hook failure doesn't hide original failure, multiple hooks execute in order, failing hook doesn't prevent subsequent hooks, clear/count work
- Cleanup is idempotent per invocation (hooks are re-entrant)

**Classification:** GREEN_REAL_INFRASTRUCTURE

### 4.4 Timeout Enforcement — GREEN_ELAPSED

**Proof:**
- `EnforceTimeout.run()` delegates to `Resilience\Timeout` when timeoutMs > 0
- `Timeout` class has two modes: pcntl pre-emptive (CLI only) and elapsed post-hoc (default)
- Tests prove: fast action completes within timeout, slow action triggers OperationTimedOut, no timeout when not configured, zero timeout means no timeout
- Timeout class documents honestly: "No PHP mechanism can interrupt a blocking I/O call mid-execution without pcntl"

**Classification:** GREEN_ELAPSED_MODE — This is the honest classification. Timeout IS enforced — the operation is measured and OperationTimedOut is thrown if exceeded. It is post-hoc (elapsed) by default, which means the operation runs to completion before the check. This is NOT a fake timeout — it is a real boundary check with a known PHP limitation. Pre-emptive mode is available with pcntl.

### 4.5 RecoverWith Enforcement — GREEN

**Proof:**
- `RunRecoveryAction.execute()` resolves `$policy->recoverWithClass`, instantiates, calls `__invoke($failure, $context)`
- Returns `FailurePipelineResult::recovered($result)`
- Tests prove: recovery handler invoked on failure, takes precedence over fallback, receives failure and context, missing class throws RuntimeException, missing __invoke throws RuntimeException
- ClassifyFailure checks `hasRecovery()` before `hasFallback()` — correct precedence

**Classification:** GREEN_REAL_ENFORCEMENT

---

## 5. Smell Scan Results

| Pattern | Location | Classification | Verdict |
|---------|----------|----------------|---------|
| error_log | ReportFailure.php:57 | ALLOWED_FALLBACK | OK — fallback when no Logger |
| error_log | SendFailureToDeadLetter.php:70 | ALLOWED_FALLBACK | OK — fallback when no FailedJobsStore |
| error_log | Other locations (18 more) | VARIOUS | Not in FailureBoundary scope; pre-existing |
| ReflectionClass | CompileFailurePolicies.php | COMPILE_PATH_ALLOWED | OK — compile-time only |
| ReflectionClass | CompiledMethodPolicy.php:114 | COMPILE_PATH_ALLOWED | OK — staleness check |
| `new $recoverClass()` | RunRecoveryAction.php:38 | COMPOSITION_ROOT_ALLOWED | OK — policy-driven instantiation |
| `new ResponseFactory()` | MapFailureToResult.php:29 | COMPOSITION_ROOT_ALLOWED | OK — default value object |
| `new EnforceTimeout()` | RunProtectedAction.php:26 | COMPOSITION_ROOT_ALLOWED | OK — default dependency |
| `try {` | RunProtectedAction.php:35 | CANONICAL | OK — this IS the failure boundary |
| `catch (Throwable)` | RunProtectedAction.php:37 | CANONICAL | OK — catches all failures for pipeline |
| `try {` | FailureCleanupRegistry.php:51 | ALLOWED_FALLBACK | OK — hook failure isolation |

**No HOT_PATH_VIOLATION found. No FLOW_SMELL found. No NEEDS_FIX found.**

---

## 6. Dogfooding Gate Correction

The tool `tooling/failure-boundary/check-dogfooding.php` line 70-71 says:

```php
if (str_contains($content, 'Resilience')) {
    $failures[] = 'Retry should not duplicate Resilience component — use the existing one';
}
```

This is **backwards**. The check was written when Retry had its OWN implementation (before Resilience existed). Now Retry correctly delegates to Resilience, and the tool falsely flags this as a violation.

**Recommended fix:** Update the tool to check that Retry DOES use Resilience (the opposite of current behavior). This is a tooling bug, not a code bug.

---

## 7. Evidence and Truth Consistency

| Document | Status | Consistent? |
|----------|--------|-------------|
| CURRENT_TRUTH.md | Updated with V5.6 FULL GREEN section | YES — accurate after corrections above |
| EVIDENCE/failure-boundary/full-closure-evidence.md | Detailed evidence for Y1-Y5 | YES — all claims verified |
| EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md | Updated to ALL DONE | YES |
| final-acceptance-audit.md (previous) | YELLOW verdict | SUPERSEDED — this audit replaces it |

**Truth assessment:** The "FULL GREEN" claim is honest with the following precision:
- Timeout is elapsed-mode by default (not pre-emptive), which is correctly documented
- Dogfooding gate is stale and needs tooling fix (not a code issue)
- Files changed count in commit message was inaccurate (26 not 14)

---

## 8. Remaining Risks

| Risk | Severity | Mitigation |
|------|----------|------------|
| Timeout elapsed mode (post-hoc) | MEDIUM | Documented; pcntl pre-emptive available; per-client timeouts recommended for I/O |
| error_log fallback paths | LOW | Fallback only when canonical transport not configured |
| Stale dogfooding gate | LOW | Tooling fix needed, not code issue |
| Commit file count inaccurate | LOW | Documentation only, no code impact |

---

## 9. Final Decision

### V5.6 Declarative Failure Boundary: GREEN (with precision notes)

**GREEN because:**
- All 5 deferred items (Y1-Y5) are genuinely implemented and tested
- Canonical dogfooding proven: Resilience RetryExecutor, Resilience Timeout, Queue FailedJobsStore
- Cleanup infrastructure is real and tested
- RecoverWith is enforced end-to-end
- No hot-path reflection, no standalone retry, no fake timeout, no deferred enforcement
- PHPStan 0 errors, PHPUnit GREEN (7899 tests)
- All governance gates pass (except stale dogfooding tool)
- Real adoption exists in RegistrationController

**Precision notes (not blockers):**
- Timeout uses elapsed mode by default — honest, documented, functional
- Dogfooding gate tool is stale and needs updating — not a code defect
- Commit message file count was wrong (26 not 14) — documentation only

---

## 10. Next Allowed Action

1. Fix stale `tooling/failure-boundary/check-dogfooding.php` to check that Retry DOES use Resilience (not the reverse)
2. Correct commit message file count in evidence (this audit)
3. V5.6 is closed. Next stage may begin after user approval.
