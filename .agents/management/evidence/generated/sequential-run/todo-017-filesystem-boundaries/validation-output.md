# Validation Output

| Command | Result |
|---------|--------|
| composer validate --no-check-publish | GREEN |
| composer dump-autoload -o | GREEN |
| vendor/bin/phpunit --no-coverage | 8752 tests, 25001 assertions — 12 pre-existing failures (ProcessPoolParallelismProofTest) |
| vendor/bin/phpunit --filter "FileSession|FailureBoundary" --no-coverage | GREEN (138 tests) |
| vendor/bin/phpstan analyse (scope) | GREEN (0 errors) |
| php tooling/refactor/check-broken-reference-semantics.php | PASS |
| php tooling/refactor/check-public-surface.php | PASS |
| php tooling/refactor/check-runtime-leaks.php | PASS |
