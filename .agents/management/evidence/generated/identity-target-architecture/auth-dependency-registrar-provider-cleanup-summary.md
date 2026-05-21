# Auth Dependency Registrar Provider Cleanup Summary

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Changed Files

- `components/Identity/Auth/System/Configuration/Providers/RegisterAuthDependencies.php`
- `components/Identity/Auth/System/Configuration/Builders/RegisterAuthDependencies.php` deleted by move

## Implementation

`RegisterAuthDependencies` now lives in `Configuration/Providers`, matching its real
responsibility as an optional Avax Container dependency registrar.

The moved class imports `Configuration/Builders/AuthBuilder` explicitly and preserves all
existing registration behavior.

## Boundary Result

- Provider/registrar ownership: PASS.
- Auth runtime behavior changed: NO.
- AuthBuilder fluent API changed: NO.
- PublicSurface changed: NO.
- Old active FQCN references: NONE found in active PHP code.

## Remaining Yellow

`AuthBuilder` itself remains large and still delegates to `*Graph`/`Assemble*` classes.
That is a later Auth assembly redesign slice, not part of this registrar-location cleanup.
