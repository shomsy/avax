# 00 — Baseline Validation

**Date:** 2026-05-12
**Branch:** main
**Commit:** 36949b7ac25a67412251cac172d1fb36d29b86c6
**Scope:** Current plan lock — full repository baseline

## Git Status

```
M .phpunit.cache/test-results
 M avax.txt
```

Working tree: dirty (test cache + avax.txt — non-production files)

## Validation Commands & Results

| Command                                                                                        | Result                                           |
|------------------------------------------------------------------------------------------------|--------------------------------------------------|
| `composer validate --no-check-publish`                                                         | GREEN — valid                                    |
| `composer dump-autoload -o`                                                                    | YELLOW — 24 PSR-4 autoload warnings (tests)      |
| `vendor/bin/phpunit --no-coverage`                                                             | GREEN — 7899 tests, 22821 assertions, 0 failures |
| `vendor/bin/phpstan analyse framework components tests labs/SystemDesignKit --memory-limit=1G` | GREEN — 0 errors                                 |
| `php tooling/refactor/check-component-suite-structure.php`                                     | PASS                                             |
| `php tooling/refactor/check-namespace-drift.php`                                               | PASS                                             |
| `php tooling/refactor/check-public-surface.php`                                                | PASS                                             |
| `php tooling/refactor/check-runtime-leaks.php`                                                 | PASS                                             |
| `php tooling/refactor/check-component-canonical-shape.php`                                     | GREEN                                            |
| `php tooling/refactor/check-advanced-pattern-folder-violations.php`                            | GREEN                                            |
| `php tooling/refactor/check-duplicate-owners.php`                                              | PASS                                             |
| `php tooling/security/check-security-blockers.php`                                             | PASS                                             |
| `php tooling/governance/check-component-adoption.php`                                          | PASS (8/8)                                       |
| `php tooling/failure-boundary/check-attributes-compiled.php`                                   | GREEN                                            |
| `php tooling/failure-boundary/check-local-try-catch.php`                                       | GREEN                                            |
| `php tooling/failure-boundary/check-dogfooding.php`                                            | GREEN                                            |
| `php tooling/refactor/check-failure-boundary-adoption.php`                                     | GREEN (11/11)                                    |
| `php tooling/refactor/check-raw-file-operations.php`                                           | UNAVAILABLE — file does not exist                |

## Initial Blockers

1. **PSR-4 autoload warnings**: 15+ test files use `Tests\...` namespace instead of `Avax\Tests\...`
2. **FailureBoundary test namespace**: 4 files use `Avax\Tests\Unit\Framework\FailureBoundary\<TestClassName>\` instead
   of `Avax\Tests\Unit\Framework\FailureBoundary\`
3. **Weak test assertions**: 6 `assertTrue(true)` instances in test files
4. **Mutable DateTime**: Several files use `DateTime` where `DateTimeImmutable` would be more appropriate
5. **`check-raw-file-operations.php`**: Gate is UNAVAILABLE

## Conclusion

Baseline: PHPUnit GREEN, PHPStan GREEN, gates PASS.
Autoload has PSR-4 compliance warnings for test files.
Weak assertions need hardening.
