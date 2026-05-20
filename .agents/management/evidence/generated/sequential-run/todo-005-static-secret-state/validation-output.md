# Validation Output

## Focused Validation

| Command | Result |
|---------|--------|
| composer validate --no-check-publish | GREEN |
| composer dump-autoload -o | GREEN |
| vendor/bin/phpunit --no-coverage | 8767 tests, 25034 assertions — 12 failures in ProcessPoolParallelismProofTest (pre-existing) |
| vendor/bin/phpunit --filter "SecretsSecurityTest" --no-coverage | GREEN (15 tests, 21 assertions) |
| vendor/bin/phpunit --filter "SecretsCapabilitiesTest" --no-coverage | GREEN (24 tests, 39 assertions) |
| vendor/bin/phpunit --filter "RuntimeResetProofTest|WorkerLoopTest|RuntimeSafetyFeatureTest" --no-coverage | GREEN (42 tests, 210 assertions) |
| php tooling/refactor/check-broken-reference-semantics.php | PASS |
| php tooling/refactor/check-public-surface.php | PASS |
| php tooling/refactor/check-runtime-leaks.php | PASS |
