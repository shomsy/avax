# 08 — PublicSurface BootDsl Boundary Proof

**Date**: 2026-05-16
**Stage**: V5.9 Boot DSL First Slice
**Status**: GREEN

## Finding (from 07-correction-preflight)

The initial implementation returned `BootDslBuilder` (internal Configuration namespace) from `Avax::dsl()`. This leaked
the internal namespace through the public API surface.

## Correction

Created `framework/System/PublicSurface/BootDsl.php` as the public-facing wrapper returned by `Avax::dsl()`.

- `Avax::dsl()` returns `BootDsl` (PublicSurface), not `BootDslBuilder` (Configuration)
- `BootDsl` delegates to internal `BootDslEngine` and `ProviderRegistry`
- `BootDslBuilder` remains internal and is no longer exposed through public API
- `Avax::boot(ApplicationBuilder)` unchanged for backward compatibility

## Proof

- Test: `avax_dsl_returns_public_boot_dsl_not_internal_builder` — proves returned class is `BootDsl`, not
  `BootDslBuilder`
- Test: `boot_dsl_public_api_has_expected_methods` — proves all expected public methods exist and are public via
  reflection
- PHPStan: clean on `framework/System/PublicSurface/BootDsl.php`
- Composition gate: `BootDsl.php` added to explicit `compositionRoots` whitelist

## Files Changed

- `framework/System/PublicSurface/BootDsl.php` (CREATED)
- `framework/System/PublicSurface/Avax.php` (UPDATED — returns `BootDsl`)
- `framework/System/Flows/BootApplication/BootWithDsl.php` (UPDATED — imports `BootDsl`)
- `tooling/refactor/check-runtime-composition-leaks.php` (UPDATED — added `BootDsl.php` to composition roots)

## Verdict

PublicSurface boundary is correct. No internal Configuration namespace leaks through public API.
