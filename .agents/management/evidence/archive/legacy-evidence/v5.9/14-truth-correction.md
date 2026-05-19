# 14 — Truth Correction

**Date**: 2026-05-16
**Stage**: V5.9 Boot DSL First Slice
**Status**: GREEN

## What Changed from Initial Implementation

| Component | Before | After | Status |
|-----------|--------|-------|--------|
| `Avax::dsl()` return | `BootDslBuilder` (internal) | `BootDsl` (PublicSurface) | FIXED |
| Provider instances | New instance for register, new for boot | Same instance for both | FIXED |
| Container freeze | Bool flag on engine | `FrozenContainer` with real mutation blocking | FIXED |
| Phase order | Freeze before Boot (mismatched) | Boot before Freeze (correct) | FIXED |
| Route DSL | `withRoutes()`, `withRouteFiles()` (unproven) | Removed (deferred) | FIXED |
| Test count | 6 basic tests | 25 comprehensive tests | EXPANDED |
| PHPStan | Not checked | Clean on all new files | FIXED |
| Composition gate | Not considered | `BootDsl.php` in composition roots | FIXED |
| `FrozenContainer::resolve()` | Did not check instances first | Checks instances first | FIXED |

## Files Created
- `framework/System/PublicSurface/BootDsl.php`
- `components/Application/Container/System/Foundation/FrozenContainer.php`
- `EVIDENCE/v5.9/07-correction-preflight.md`
- `EVIDENCE/v5.9/08-publicsurface-boot-dsl-boundary.md`
- `EVIDENCE/v5.9/09-provider-lifecycle-correction.md`
- `EVIDENCE/v5.9/10-container-freeze-proof.md`
- `EVIDENCE/v5.9/11-root-container-ownership-truth.md`
- `EVIDENCE/v5.9/12-route-dsl-behavior-correction.md`
- `EVIDENCE/v5.9/13-correction-test-proof.md`

## Files Modified
- `framework/System/PublicSurface/Avax.php`
- `framework/System/Flows/BootApplication/BootWithDsl.php`
- `framework/System/Configuration/BootDsl/BootPhase.php`
- `framework/System/Configuration/BootDsl/BootDslEngine.php`
- `framework/System/Configuration/BootDsl/BootDslBuilder.php`
- `tests/Unit/Framework/Configuration/BootDsl/BootDslTest.php`
- `tooling/refactor/check-runtime-composition-leaks.php`

## Verdict

All corrections applied. Truth updated. No claims without proof.
