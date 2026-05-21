# Access Runtime Delegation Summary

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Implemented

- Added `components/Identity/Access/System/Capabilities/AccessRuntime/AccessRuntime.php`.
- Moved executable Access public-surface behavior from `System/PublicSurface/Access.php` into `AccessRuntime`.
- `Access` now receives `AccessRuntime` and delegates all public methods.
- Updated `AccessServiceProvider` to assemble `AccessRuntime` inside the composition boundary.
- Updated root `IdentityRuntime` builder to assemble `AccessRuntime`.
- Updated Access characterization helper to construct Access through `AccessRuntime`.

## Public API Note

The public methods on `Access` are preserved. The constructor now receives `AccessRuntime`, matching the target plan that PublicSurface receives runtime/delegation objects. This is recorded as PLAN_APPROVED_COMPATIBILITY_YELLOW because constructor consumers outside the current repo may need migration.

## Status

PARTIAL_WITH_ENVIRONMENT_YELLOW. Static checks pass, but PHP/composer/PHPUnit execution remains blocked by Docker socket permissions.
