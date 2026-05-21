# Tenancy Runtime Safety Plan

Date: 2026-05-21

## Slice Scope

- Convert `Tenancy` PublicSurface from static facade to instance delegator.
- Introduce `TenancyRuntime` with explicit `TenantContextInterface` state.
- Assemble root `Identity::tenancy()` with a `DefaultTenantContext`.
- Register scoped tenancy context/runtime in `TenancyServiceProvider`.
- Update tests from static state checks to instance runtime isolation.

## Out of Scope

- Full tenant store flows.
- Tenant resolver multi-class cleanup.
- Full tenant context API redesign beyond removing the unused static legacy facade.

## Compatibility

The old static `Tenancy::setTenantId()` / `getTenantId()` / `run()` path is not preserved in this slice because preserving it would keep PublicSurface static runtime state. Target DSL is instance-based through `Identity::tenancy()` or explicitly assembled `Tenancy` instances.
