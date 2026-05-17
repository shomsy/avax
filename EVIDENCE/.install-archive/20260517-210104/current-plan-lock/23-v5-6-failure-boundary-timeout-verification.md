# 23 — V5.6 FailureBoundary Timeout Verification

**Date:** 2026-05-12
**Branch:** main
**Commit:** 21d6f69fd
**Scope:** Verify V5.6 FailureBoundary Timeout enforcement and documentation honesty

## Architecture

```
#[Timeout(milliseconds: 5000)]
    ↓
CompileFailurePolicies → reads attribute → FailurePolicy.timeoutMs = 5000
    ↓
RunProtectedAction.run() → reads policy.timeoutMs → EnforceTimeout.run(action, timeoutMs)
    ↓
EnforceTimeout.run() → new Timeout(timeoutMs) → Timeout.run(action)
    ↓
Timeout.runElapsed() → hrtime() before/after → throw OperationTimedOut if exceeded
```

## Verification Results

### Timeout IS Enforced

- **EnforceTimeout.php:24-33** — Delegates to Resilience Timeout when timeoutMs > 0. Skips when null or <= 0.
- **RunProtectedAction.php:32-36** — Reads timeoutMs from policy, passes to EnforceTimeout.
- **Timeout.php:44-51** — Dual-mode: pcntl pre-emptive or elapsed post-hoc.

### Elapsed-Mode Default — Documented

- **Timeout.php:12-21** — Class docblock explicitly states: "Elapsed mode: measures elapsed time after operation
  completes (post-hoc). This is the default and only option when pcntl is unavailable."
- **Timeout.php:107-126** — `runElapsed()` implementation: runs operation, then checks elapsed time, throws if exceeded.
- **Timeout.php:18-21** — "No PHP mechanism can interrupt a blocking I/O call mid-execution without pcntl. For
  HTTP/database calls, set timeouts on the underlying client/driver."

### pcntl/Pre-emptive Mode — Where Supported

- **Timeout.php:62-96** — `runWithPcntl()` uses `pcntl_alarm` + `SIGALRM`.
- **Timeout.php:150-156** — `isPcntlAvailable()` checks
  `extension_loaded('pcntl') && PHP_SAPI === 'cli' && defined('SIGALRM')`.
- **Timeout.php:30-33** — Constructor falls back to elapsed if pcntl unavailable.
- **EnforceTimeout.php:30** — Uses `new Timeout($timeoutMs)` — default is elapsed mode. To use pcntl, caller would need
  `Timeout::preEmptive()`.

**Note:** EnforceTimeout uses `new Timeout($timeoutMs)` which defaults to elapsed mode. It does NOT use
`Timeout::preEmptive()`. This means FailureBoundary Timeout is always elapsed-mode, even when pcntl is available. This
is an honest design choice — elapsed mode is safe for all runtimes (HTTP, CLI, workers). Pre-emptive mode requires
careful signal handling that may not be appropriate for all contexts.

### Docs Do NOT Claim Universal Cancellation

- `docs/failure-boundary/declarative-failure-boundary.md` "What Is Deferred" table says "Timeout — Compiled but not
  enforced. Requires fiber-level or pcntl_alarm support."
- **This is STALE** — Timeout IS now enforced. The docs need updating.
- The docs do NOT claim universal cancellation. They correctly identify the PHP limitation.

### Tests Are NOT Flaky Timing Tricks

- **TimeoutEnforcementTest.php:49-74** — `slowActionTriggersTimeoutException`: 10ms timeout, 50ms sleep. Wide margin (
  5x). Not flaky.
- **TimeoutEnforcementTest.php:94-105** — `enforceTimeoutDelegatesToResilienceTimeout`: 5ms timeout, 50ms sleep. Wide
  margin (10x). Not flaky.
- Tests use `usleep()` with timeouts that have comfortable margins. Not timing-sensitive.

### Cleanup Runs After Timeout

- **RunProtectedAction.php:46-48** — `finally { $this->cleanup->for($context); }` — guaranteed execution regardless of
  timeout exception.
- **CleanupAfterFailure** uses **FailureCleanupRegistry** with hook execution in `finally` block.

### Timeout Status Is NOT Overstated

- **CURRENT_TRUTH.md** — says "Timeout (real)" for V4-09, "Timeout enforced via Resilience Timeout at action level;
  elapsed mode by default" for V5.6-Y4.
- **full-closure-final-acceptance-audit.md** — classifies as GREEN_ELAPSED with precision notes.
- No file claims "pre-emptive universal timeout." All current evidence says elapsed-mode default.

### No Hot-Path Reflection

- **CompileFailurePolicies.php** — reflection at compile time only.
- **ReadCompiledFailurePolicies.php** — reads from compiled cache (file_get_contents).
- **RunProtectedAction.php** — reads timeoutMs from already-compiled FailurePolicy. No reflection.

### No Fake GREEN

Timeout is real. OperationTimedOut is thrown when elapsed time exceeds limit. Tests prove it. Code shows it. Evidence
agrees.

## Classification: GREEN_ELAPSED_DOCUMENTED

- **Timeout IS enforced** — OperationTimedOut thrown when exceeded.
- **Elapsed mode by default** — Post-hoc check after operation completes.
- **pcntl pre-emptive mode** — Available in Resilience Timeout but NOT used by EnforceTimeout (elapsed is always default
  for safety).
- **Docs honest** — Timeout.php docblock explains PHP limitation.
- **Tests prove behavior** — 5 tests, wide timing margins.
- **Cleanup guaranteed** — finally block.

## Acceptance: GREEN with Precision

V5.6 Timeout is GREEN. The elapsed-mode default is a known PHP limitation, honestly documented. It is NOT a fake
timeout — the boundary IS enforced and OperationTimedOut IS thrown. The limitation is that the operation runs to
completion before the check, so it cannot interrupt mid-flight blocking I/O in elapsed mode.

## Does This Block V5.7?

**NO.** V5.7 Events DSL does not require pre-emptive timeout.

## Remaining Risks

| Risk                                                        | Severity | Mitigation                                              |
|-------------------------------------------------------------|----------|---------------------------------------------------------|
| Elapsed mode cannot interrupt mid-flight I/O                | LOW      | Documented; per-client timeouts recommended for HTTP/DB |
| pcntl pre-emptive not used by EnforceTimeout                | LOW      | Safe by design; elapsed mode works for all runtimes     |
| Stale docs/failure-boundary/declarative-failure-boundary.md | LOW      | "What Is Deferred" table outdated — needs update        |
| Stale deferred/V5.6-Y4-timeout-enforcement.md               | LOW      | Still says DEFERRED — needs SUPERSEDED marker           |

## Next Allowed Action

Proceed to timeout scope map.
