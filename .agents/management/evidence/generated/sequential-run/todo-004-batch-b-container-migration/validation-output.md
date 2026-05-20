# Validation Output

## Focused Validation (scope: Container providers + Migration/Seeder)

| Command | Result |
|---------|--------|
| composer validate --no-check-publish | GREEN |
| composer dump-autoload -o | GREEN (3517 classes) |
| vendor/bin/phpunit --no-coverage | 8768 tests, 25024 assertions — 12 failures in ProcessPoolParallelismProofTest (pre-existing, serialized closure) |
| vendor/bin/phpunit --filter "SeederSecurityTest\|MigrationSeedSecurityTest\|ProviderRegistrySecurityTest" --no-coverage | GREEN (16 tests, 24 assertions) |
| vendor/bin/phpstan analyse components/Application/Container/System/Capabilities/Providers components/DataStack/Database/System/Capabilities/Migrations --memory-limit=1G | GREEN (0 errors) |
| php tooling/refactor/check-broken-reference-semantics.php | PASS (0 active broken refs) |
| php tooling/refactor/check-public-surface.php | PASS |
| php tooling/refactor/check-runtime-leaks.php | PASS |

Remaining: Full validation not run (localized scope). 12 pre-existing ProcessPoolParallelismProofTest failures unrelated to this task.
