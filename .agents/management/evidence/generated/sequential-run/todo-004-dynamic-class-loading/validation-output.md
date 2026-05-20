# Validation Output

## Focused Validation (scope: QueueWorker + FailureBoundary)

| Command | Result |
|---------|--------|
| composer validate --no-check-publish | GREEN (valid) |
| composer dump-autoload -o | GREEN (3518 classes) |
| vendor/bin/phpunit --filter "QueueWorkerSecurityTest\|FailureBoundary\|RecoverWithEnforcement\|FailurePolicyCompilerTest" --no-coverage | GREEN (63 tests, 108 assertions) |
| vendor/bin/phpstan analyse framework/System/Capabilities/FailureBoundary components/Operations/Queue --memory-limit=1G | GREEN (0 errors) |
| php tooling/refactor/check-runtime-composition-leaks.php | PASS |
| php tooling/refactor/check-broken-reference-semantics.php | PASS (0 active broken refs) |
| php tooling/refactor/check-public-surface.php | PASS |
| php tooling/refactor/check-runtime-leaks.php | PASS |

Remaining: Full validation not run (localized scope). check-direct-instantiation has pre-existing failures unrelated to this task.
