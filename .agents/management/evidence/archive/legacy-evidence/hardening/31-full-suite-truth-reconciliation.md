# V5.8.7 Truth Reconciliation

## Date
2026-05-15

## Validation Results

### PHPUnit
```
vendor/bin/phpunit --no-coverage
Tests: 8351, Assertions: 24012, OK (0 errors, 0 failures, 1 deprecation)
```

### PHPStan
```
vendor/bin/phpstan analyse framework components tests --memory-limit=1G
308 errors (all pre-existing, none introduced by V5.8.7)
```

**Classification of 308 PHPStan errors:**
- Mixed variable warnings: ~50 (pre-existing style warnings)
- Missing/unknown classes (Cache, BuildResponse): ~30 (pre-existing, components not yet promoted)
- Constructor param mismatches (Auth, Passkey, OAuth, Risk): ~25 (pre-existing, AuthBuilder out of sync with capability constructors)
- Array type hints missing: ~100 (pre-existing style warnings)
- Always-true instanceof/assert in tests: ~10 (pre-existing test narrowings)
- Return type specificity: ~10 (pre-existing)
- Unknown parameters in various builders: ~80 (pre-existing, builders not fully synced with target classes)

**V5.8.7 contribution: 0 new PHPStan errors.**

### Composer
```
composer validate --no-check-publish → PASS
```

### Autoload
```
composer dump-autoload -o → 9319 classes
```

## Pass Status

### Pass 1: Runtime Composition Leak Closure
**Status: GREEN**
- `check-runtime-leaks.php` — same pre-existing Cache findings
- No new composition leaks from V5.8.7 changes
- DispatchConfiguredRoute container wiring is clean (save/restore pattern)

### Pass 2: HTTP Response Layer Convergence
**Status: GREEN**
- `Response::json()` static calls eliminated from examples
- Response ownership canonical through CreateHttpResponse
- No ResponseFactory ambiguity
- `check-public-surface.php` — PASS
- `check-hollow-public-surfaces.php` — PASS (228 files checked)

### V5.8.7 Full Suite Baseline Restoration
**Status: GREEN**
- PHPUnit: 8351 tests, 24012 assertions, 0 errors, 0 failures
- All canonical gates GREEN
- No tests deleted, weakened, or hidden
- 15 files fixed, 1 evidence file created

## V5.9 Readiness
**V5.9 Boot DSL is UNBLOCKED.**
- Cleanup program: GREEN
- Full suite baseline: GREEN
- Pass 1: GREEN
- Pass 2: GREEN
- V5.8.7: GREEN
- PHPStan: 308 pre-existing errors (out of scope for V5.8.7, may be addressed in V5.9)

## Truth File Consistency
- `CURRENT_TRUTH.md` — updated with V5.8.7 section
- `EVIDENCE/EXECUTION.md` — updated with V5.8.7 status
- `EVIDENCE/hardening/24-full-suite-baseline-restoration.md` — created
- `EVIDENCE/hardening/30-full-suite-recursive-governance-review.md` — created
- `EVIDENCE/hardening/31-full-suite-truth-reconciliation.md` — this file

## Next Allowed Action
V5.9 Boot DSL may begin.
