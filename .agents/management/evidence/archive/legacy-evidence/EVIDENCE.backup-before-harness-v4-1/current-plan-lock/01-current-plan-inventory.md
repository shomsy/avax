# 01 — Current Plan Inventory

**Date:** 2026-05-12
**Branch:** main
**Commit:** 36949b7ac25a67412251cac172d1fb36d29b86c6
**Scope:** All current truth, TODO, BUGS, EXECUTION, evidence files

## Inventory

| Source file      | Claim / item            | Current status in file        | Evidence found                    | Real status | Action                                                            |
|------------------|-------------------------|-------------------------------|-----------------------------------|-------------|-------------------------------------------------------------------|
| CURRENT_TRUTH.md | V1 Kernel Green         | PROVEN                        | V5 stage ledger, validation       | GREEN       | KEEP_CURRENT                                                      |
| CURRENT_TRUTH.md | V2 Platform Baseline    | CLOSED / GREEN                | 72 components, tests pass         | GREEN       | KEEP_CURRENT                                                      |
| CURRENT_TRUTH.md | V3 Implementation       | CLOSED / GREEN                | SystemDesign promoted             | GREEN       | KEEP_CURRENT                                                      |
| CURRENT_TRUTH.md | V4-01 through V4-17     | COMPLETE / GREEN              | 7899 tests, PHPStan 0             | GREEN       | KEEP_CURRENT                                                      |
| CURRENT_TRUTH.md | V5 Internal Convergence | COMPLETE / GREEN (23 stages)  | V5-23 truth report                | GREEN       | KEEP_CURRENT                                                      |
| CURRENT_TRUTH.md | V5.5 Benchmark Proof    | GREEN (13 stages)             | v5.5 audit, benchmarks            | GREEN       | KEEP_CURRENT                                                      |
| CURRENT_TRUTH.md | V5.6 Failure Boundary   | FULL GREEN (7/7 backlog done) | full-closure-evidence, dogfooding | GREEN       | KEEP_CURRENT                                                      |
| TODO.md          | V5.6-PHASE              | done                          | 90 tests, 4 gates GREEN           | DONE        | KEEP_CURRENT                                                      |
| TODO.md          | V5.6-Y7                 | done                          | PHPStan 0 errors                  | DONE        | KEEP_CURRENT                                                      |
| TODO.md          | V5.6-Y6                 | done                          | 9 E2E tests                       | DONE        | KEEP_CURRENT                                                      |
| TODO.md          | V5.6-Y3                 | todo                          | CleanupAfterFailure stub          | OPEN        | FIX_NOW — stub needs real hook or document                        |
| TODO.md          | V5.6-Y1                 | todo                          | Replace standalone retry          | BLOCKED     | TODO says todo but CURRENT_TRUTH says Y1 DONE — TRUTH CONFLICT    |
| TODO.md          | V5.6-Y2                 | blocked                       | DeadLetter transport              | BLOCKED     | TODO says blocked but CURRENT_TRUTH says Y2 DONE — TRUTH CONFLICT |
| TODO.md          | V5.6-Y5                 | todo                          | RecoverWith enforcement           | OPEN        | TODO says todo but CURRENT_TRUTH says Y5 DONE — TRUTH CONFLICT    |
| TODO.md          | V5.6-Y4                 | todo                          | Timeout enforcement               | OPEN        | TODO says todo but CURRENT_TRUTH says Y4 DONE — TRUTH CONFLICT    |
| TODO.md          | V5.5-PHASE              | done                          | 13 stages GREEN                   | DONE        | KEEP_CURRENT                                                      |
| TODO.md          | V5-PHASE                | done                          | 23 stages GREEN                   | DONE        | KEEP_CURRENT                                                      |
| BUGS.md          | No active items         | clean                         | —                                 | CLEAN       | KEEP_CURRENT                                                      |
| EXECUTION.md     | Active stage lock       | None — V2 CLOSED              | outdated                          | STALE       | MARK_SUPERSEDED — active stage is V5.6 FULL GREEN                 |
| EXECUTION.md     | V4 IN PROGRESS          | IN PROGRESS                   | V4 COMPLETE                       | STALE       | MARK_SUPERSEDED — V4 is COMPLETE                                  |

## Truth Conflicts Resolved

1. **V5.6-Y1 (Retry/Resilience)**: TODO.md says `todo`, CURRENT_TRUTH says `DONE`. Evidence: `full-closure-evidence.md`
   proves Retry delegates to Resilience RetryExecutor. → MARK_DONE in TODO.
2. **V5.6-Y2 (DeadLetter/Queue)**: TODO.md says `blocked`, CURRENT_TRUTH says `DONE`. Evidence: FailedJobsStore primary
   transport proven. → MARK_DONE in TODO.
3. **V5.6-Y4 (Timeout)**: TODO.md says `todo`, CURRENT_TRUTH says `DONE`. Evidence: Resilience Timeout enforcement
   proven. → MARK_DONE in TODO.
4. **V5.6-Y5 (RecoverWith)**: TODO.md says `todo`, CURRENT_TRUTH says `DONE`. Evidence: RunRecoveryAction enforcement
   proven. → MARK_DONE in TODO.
5. **V5.6-Y3 (Cleanup)**: TODO.md says `todo`, CURRENT_TRUTH says `DONE`. Evidence: FailureCleanupRegistry proven. →
   MARK_DONE in TODO.
6. **EXECUTION.md stale**: Says V4 IN PROGRESS, V2 CLOSED as last completed. V4-V5.6 are all COMPLETE. → Needs update.
