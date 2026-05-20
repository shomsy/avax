# Threat Analysis

## Threats Mitigated

| Threat | Severity | Before | After |
|--------|----------|--------|-------|
| Arbitrary class instantiation via queue payload | HIGH | Any class with `handle()` method could be instantiated | Only classes implementing JobInterface are accepted |
| Arbitrary class instantiation via fallback/recovery config | HIGH | Any class with `__invoke()` could be instantiated | Only classes implementing FailureHandler are accepted |
| Non-existent class causing fatal error | MEDIUM | Silently caught but no interface validation | Fail-closed before construction |

## Residual Risks

- Container `class_exists() + new` sites: NOT addressed in this batch (tracked separately per TODO-004 scope)
- Migration/seeder commands: NOT addressed (separate batch)
- Dynamic class loading from user input via QueueWorker payload is still a vector if an attacker can push jobs to the queue. The JobInterface check limits the damage to only classes implementing JobInterface, but an attacker could still trigger unintended JobInterface implementations.
