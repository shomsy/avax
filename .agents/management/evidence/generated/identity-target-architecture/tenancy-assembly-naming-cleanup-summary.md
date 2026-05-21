# Tenancy Assembly Naming Cleanup Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Tenancy/System/Configuration/Assembly/Tenancy.php`
- old `TenancyGraph.php` deleted by move
- `components/Identity/System/Configuration/Builders/IdentityRuntime.php`
- `components/Identity/Tenancy/System/Configuration/TenancyServiceProvider.php`
- `tests/Unit/Components/Identity/Tenancy/TenancyCharacterizationTest.php`

## Implementation

`TenancyGraph` became `Configuration/Assembly/Tenancy`, matching the assembled product.
Call sites use `TenancyAssembly` aliases where PublicSurface `Tenancy` is also in scope.

Runtime behavior is unchanged.
