# V5.8.8 Validation Reality

## Date
2026-05-15

## Validation Results

| Command | Claimed before | Actual result now | Clean? | Notes |
|---|---|---|---:|---|
| `composer validate --no-check-publish` | GREEN | `./composer.json is valid` | YES | Clean |
| `composer dump-autoload -o` | GREEN, 9319 classes | 9319 classes, 1 xhp_ compat warning | YES | xhp_ in compat.php is expected, not a leak |
| `vendor/bin/phpunit --no-coverage` | 8351 tests, 24012 assertions, 0 errors, 0 failures | 8351 tests, 24012 assertions, 0 errors, 0 failures, 1 deprecation | YES | Confirmed GREEN |
| `vendor/bin/phpstan analyse framework components tests` | 308 errors (pre-existing) | **308 errors** | NO | PHPStan 308 errors is not GREEN |
| `check-runtime-composition-leaks.php` | PASS with pre-existing Cache/GraphQL findings | **FAIL** — 163 findings including 3 new from V5.8.7 (DispatchConfiguredRoute) | NO | Gate now says FAIL, not PASS |
| `check-component-runtime-assembly.php` | Unknown | **FAIL** — 3 violations in GraphQLSchema.php | NO | Pre-existing null-coalescing new violations |
| `check-public-surface.php` | GREEN | PASS | YES | Clean |
| `check-hollow-public-surfaces.php` | GREEN, 228 files | PASS, 228 files | YES | Clean |
| `check-truth-consistency.php` | GREEN | PASS (4 checks) | PARTIAL | Gate passes but truth claims "FULL GREEN" while PHPStan has 308 errors |

## Key Findings

1. **PHPStan 308 errors** — All pre-existing from V5.8.7. None introduced by this pass yet. Not GREEN.
2. **Runtime composition gate FAIL** — 163 findings. 3 new findings in DispatchConfiguredRoute.php from V5.8.7 changes. Pre-existing findings span Cache, GraphQL, Auth, Container, Database, API, and many other components.
3. **Runtime assembly gate FAIL** — 3 violations in GraphQLSchema.php (null-coalescing new patterns).
4. **Truth claim dishonesty** — CURRENT_TRUTH.md and EXECUTION.md claim "FULL GREEN" despite PHPStan 308 errors and runtime gate FAIL.
5. **PHPUnit genuinely GREEN** — 8351 tests pass, no errors, no failures.

## V5.8.7 Honesty Verdict

V5.8.7 PHPUnit baseline restoration is genuine.
V5.8.7 calling itself "FULL GREEN" is **dishonest** because:
- PHPStan reports 308 errors
- Runtime composition gate reports FAIL (not PASS as claimed)
- Truth files say "Remaining YELLOW: None" while PHPStan has 308 errors

## Planned Remediation

1. PHPStan 308 errors — classify into fixable groups, fix root causes
2. Runtime composition gate — classify every finding as active/non-runtime/blocking/accepted
3. Runtime assembly gate — fix GraphQLSchema.php violations
4. Truth files — update to match actual validation state
