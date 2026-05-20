# Threat Analysis

## Threats Mitigated

| Threat | Severity | Before | After |
|--------|----------|--------|-------|
| Secrets leak between requests in long-lived worker | CRITICAL | Static $secretStore never cleared between requests | Secrets::reset() clears store; wired into StateResetRegistry → WorkerLoop lifecycle |
| Secrets persist after application reset | HIGH | No way to clear accumulated secrets without process restart | Explicit reset() clears all secrets |
| Secrets cross-contamination during testing | MEDIUM | Tests had no tearDown for shared Secrets state | Secrets::reset() available and used in tearDown |

## Residual Risks

- GlobalEventListenerState has reset() but not wired into StaticStateReset (MEDIUM, separate scope)
- StatelessBoundary::$mode has no reset() (LOW, configuration set once at boot)
