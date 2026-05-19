# 12 — Route DSL Deferral Proof

**Date**: 2026-05-16
**Stage**: V5.9 Boot DSL First Slice
**Status**: GREEN

## Finding (from 07-correction-preflight)

Initial implementation included `withRoutes()` and `withRouteFiles()` methods on the public DSL API, but there were no tests proving route registration works and no integration with the router component.

## Correction

Removed route methods from:
- `framework/System/PublicSurface/BootDsl.php` — no `withRoutes()` or `withRouteFiles()`
- `framework/System/Configuration/BootDsl/BootDslBuilder.php` — no route methods

Route DSL is deferred to the next phase when router integration is in scope.

## Proof

- `BootDsl` public API has no route methods (verified by `boot_dsl_public_api_has_expected_methods` test)
- No route-related imports or references in BootDsl files
- `boot_dsl_creates_booted_app` — proves boot works without route registration

## Verdict

Route DSL correctly deferred. No unproven API in first slice.
