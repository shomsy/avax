# AuthBuilder Split — Second Slice: Extract External Identity Graph Assembly

**Date**: 2026-05-19
**Commit**: `3c4362407`
**Status**: GREEN_WITH_ACCEPTED_YELLOW_DEBT

---

## What Changed

Extracted Phase 4 (OAuth/OIDC/Federation/provisioning/diagnostics assembly) from `AuthBuilder::ready()` into `AssembleAuthExternalIdentityGraph`.

### Files Changed

| File | Lines Before | Lines After | Delta |
|------|-------------|-------------|-------|
| AuthBuilder.php | 889 | 797 | -92 |
| AssembleAuthExternalIdentityGraph.php | 0 | 216 | +216 |
| AuthBuilderReadyGraphCharacterizationTest.php | 510 | 603 | +93 |
| **Net** | **1399** | **1616** | **+217** |

### Imports

| File | Before | After | Delta |
|------|--------|-------|-------|
| AuthBuilder.php | ~215 | 164 | -51 |

---

## Validation Evidence

### Tests

```
vendor/bin/phpunit tests/Unit/Components/Identity/Auth/AuthBuilderReadyGraphCharacterizationTest.php --no-coverage
OK (12 tests, 124 assertions)

vendor/bin/phpunit tests/Unit/Components/Identity/Auth/ --no-coverage
OK (15 tests, 129 assertions)

vendor/bin/phpunit tests/Unit/Components/Identity/ --no-coverage
OK (21 tests, 139 assertions)
```

### PHPStan

```
vendor/bin/phpstan analyse components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php components/Identity/Auth/System/Configuration/Assembly/AssembleAuthExternalIdentityGraph.php tests/Unit/Components/Identity/Auth/AuthBuilderReadyGraphCharacterizationTest.php --memory-limit=1G --error-format=raw --no-progress
(no output — clean)
```

### Governance Checks

```
php tooling/refactor/check-namespace-drift.php        PASS
php tooling/refactor/check-public-surface.php         PASS
php tooling/refactor/check-component-suite-structure.php  PASS
php tooling/refactor/check-component-canonical-shape.php   GREEN
php tooling/refactor/check-runtime-leaks.php          PASS
php tooling/refactor/check-duplicate-owners.php       PASS
```

### PHPStan Suppressions Added

None. Zero `@phpstan-ignore` annotations added.

---

## Architecture Compliance

- **Folder says capability**: `System/Configuration/Assembly/` — correct
- **Unit says responsibility**: `AssembleAuthExternalIdentityGraph` — assembles external identity graph
- **Function says action**: `assemble(): void` — constructs objects for boot-time validation
- **No forbidden names**: No Services, Managers, Helpers, Utils, etc.
- **Canonical shape**: `final class` with `__construct()` and `assemble(): void`

---

## Absolute Rules Check

| Rule | Status |
|------|--------|
| No public DSL changes | PASS — AuthBuilder DSL methods unchanged |
| No builder methods removed | PASS — all 46+ DSL methods intact |
| No required params added to public methods | PASS — no signature changes |
| No return type changes | PASS — `ready(): Auth` unchanged |
| No internal classes exposed via PublicSurface | PASS — verified by test |
| No weakened tests | PASS — 12 tests (was 9), all passing |
| No broad PHPStan suppressions | PASS — zero suppressions |
| No generic Service/Manager names | PASS — honest responsibility name |

---

## Accepted YELLOW Debt

1. **AssembleAuthIdentityGraph still returns array** (not typed result) — deferred to third slice
2. **AssembleAuthExternalIdentityGraph assemble() returns void** (no typed result) — deferred to third slice
3. **AuthBuilder still has 164 imports** (reduced from 215 but not minimal) — remaining imports are used by DSL methods, `capabilityRequests()`, and `withContainer()`; will naturally reduce when remaining phases are extracted
4. **Phase 1-3 assembly still inline in AuthBuilder** (Phases 1-3 code remains in `ready()` before Phase 4 delegation) — these were already extracted into `AssembleAuthIdentityGraph` but the unpacking/wiring remains inline; further extraction deferred

---

## Remaining Work

- Third slice: Extract typed result objects from both assembly classes
- Fourth slice: Extract remaining inline Phase 1-3 unpacking from AuthBuilder::ready()
