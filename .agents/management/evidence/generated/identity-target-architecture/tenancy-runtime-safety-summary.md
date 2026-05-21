# Tenancy Runtime Safety Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Tenancy/System/Capabilities/TenancyRuntime/TenancyRuntime.php`
- `components/Identity/Tenancy/System/PublicSurface/Tenancy.php`
- `components/Identity/Tenancy/System/Configuration/Assembly/TenancyGraph.php`
- `components/Identity/Tenancy/System/Configuration/TenancyServiceProvider.php`
- `components/Identity/Tenancy/System/Capabilities/Context/TenantContext.php`
- `components/Identity/Tenancy/System/Capabilities/Context/DefaultTenantContext.php`
- `components/Identity/System/Configuration/Builders/IdentityRuntime.php`
- `tests/Unit/Components/Identity/Tenancy/TenancyCharacterizationTest.php`
- `tests/Integration/GoldenPath/GoldenPathTest.php`

## Implementation

`Tenancy` PublicSurface now receives `TenancyRuntime` and delegates tenant context behavior.

`TenancyRuntime` owns tenant context state through `TenantContextInterface`, returns the assembled `Admin` sub-surface, and adds fail-closed `requireTenant()`.

`TenancyGraph::fromContext()` assembles the public surface from an explicit context. `TenancyServiceProvider` registers `TenantContextInterface` and `Tenancy` as scoped services.

The unused static `TenantContext` facade was removed so tenant runtime state is not backed by mutable static context.

## Boundary Result

- PublicSurface static tenant context API removed.
- Tenant context state is explicit and scoped by assembly/provider.
- Root Identity default assembly uses `DefaultTenantContext`.
