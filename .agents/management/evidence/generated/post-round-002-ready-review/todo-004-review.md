# TODO-004 Dynamic Class Loading Review

Branch: `security/todo-004-dynamic-class-loading`
HEAD: `c3d510fec`
Reviewer: Qoder (review-only)
Date: 2026-05-20

## Scope

QueueWorker + FailureBoundary (RunRecoveryAction, RunFallbackAction) dynamic class-loading hardening.

## Code Review

### QueueWorker.php — FAIL-CLOSED: PASS

**Before:** `class_exists($class) && new $class(...$data)` — arbitrary class instantiation from payload.
**After:** Checks `class_exists($class)` AND `is_subclass_of($class, JobInterface::class)`. If either fails, job is silently deleted (fail-closed).

**Findings:**
- **PASS** — JobInterface check prevents arbitrary class instantiation.
- **PASS** — Fail-closed: invalid jobs are deleted, not executed.
- **PASS** — No public API change.
- **PASS** — `RuntimeException` import added, `\RuntimeException` replaced with `RuntimeException` (minor style improvement).
- **INFO** — Silent deletion without logging could make debugging harder in production, but this is a security boundary, not an observability concern.

### RunRecoveryAction.php — FAIL-CLOSED: PASS

**Before:** `method_exists($handler, '__invoke')` — duck-typing check after construction.
**After:** `is_subclass_of($recoverClass, FailureHandler::class)` checked BEFORE construction.

**Findings:**
- **PASS** — Interface check replaces weak method_exists.
- **PASS** — Check happens before instantiation (fail-closed at validation, not at runtime).
- **PASS** — Exception message includes the expected interface class name for debugging.

### RunFallbackAction.php — FAIL-CLOSED: PASS

Same pattern as RunRecoveryAction.

**Findings:**
- **PASS** — Interface check replaces weak method_exists.
- **PASS** — Check happens before instantiation.

### FailureHandler.php (NEW) — PASS

**Interface:** `__invoke(Throwable $failure, FailureContext $context): mixed`

**Findings:**
- **PASS** — Typed contract, not duck-typing.
- **PASS** — Placed in Foundation/ (internal framework primitive, not public surface).
- **PASS** — `Throwable` import included.
- **PASS** — Small, focused interface.

## Test Review

### QueueWorkerSecurityTest.php (NEW) — 3 tests

| Test | What it proves | Verdict |
|------|---------------|---------|
| testRejectsJobClassNotImplementingJobInterface | Class implementing Job (not JobInterface) is rejected | PASS |
| testRejectsNonExistentJobClassAndDeletesJob | Non-existent class string is rejected, job deleted | PASS |
| testAcceptsValidJobClassImplementingJobInterface | Valid JobInterface-implementing class executes | PASS |

**Quality:** Tests use proper mock for QueueDriverInterface. Test doubles (QueueWorkerInvalidJob, QueueWorkerNonExistentJob, QueueWorkerValidJob) are well-structured. All three cover the security boundary correctly.

### FailureBoundaryTest.php (MODIFIED) — +3 tests

| Test | What it proves | Verdict |
|------|---------------|---------|
| testRejectsFallbackHandlerNotImplementingInterface | Fallback without FailureHandler rejected | PASS |
| testRejectsRecoveryHandlerNotImplementingInterface | Recovery without FailureHandler rejected | PASS |
| testRejectsFallbackClassNotFound | Non-existent fallback class throws | PASS |

**Quality:** Tests use real FailureBoundary assembly through BuildFailureBoundary. Assertions check for correct exception type and message.

### RecoverWithEnforcementTest.php (MODIFIED) — Updated existing tests

- Test handlers updated to implement FailureHandler.
- Exception message assertion updated from `'Recovery class must implement __invoke'` to include full interface name.

## Evidence Review

| File | Present | Accurate | Matches code |
|------|---------|----------|--------------|
| context-loaded.md | YES | YES | YES |
| implementation-summary.md | YES | YES | YES |
| validation-output.md | YES | YES | YES |
| governance-review.md | YES | YES | YES |
| final-decision.md | YES | YES | YES |
| test-proof.md | YES | YES | YES |
| threat-analysis.md | YES | YES | YES |

Final decision: `TODO_CLOSED (scope: QueueWorker + FailureBoundary recovery/fallback)` — Accurate. Evidence correctly notes remaining scope (Container/migrations) goes to TODO-004-b.

## Governance Compliance

| Rule | Compliant |
|------|-----------|
| AGENTS.md §25 (Security) | YES |
| AGENTS.md §30 (Testing) | YES |
| AGENTS.md §7 (Component Shape) | YES |
| how-to-system-security.md | YES |
| how-to-use-ai-assisted-execution.md | YES |

## Verdict

**MERGE_READY**

TODO-004 is a clean, focused P0 security hardening. The interface contract (FailureHandler) replaces duck-typing. QueueWorker fail-closed behavior is correct. Tests cover negative injection cases. Evidence is complete. No unrelated cleanup detected.
