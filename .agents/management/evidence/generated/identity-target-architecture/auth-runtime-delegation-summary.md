# Auth Runtime Delegation Summary

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Implemented

- Added `components/Identity/Auth/System/Capabilities/AuthenticationRuntime/AuthenticationRuntime.php`.
- Moved executable Auth PublicSurface behavior from `System/PublicSurface/Auth.php` into `AuthenticationRuntime`.
- `Auth` now receives `AuthenticationRuntime` and delegates existing public methods.
- Updated the root `IdentityRuntime` builder.
- Updated `RegisterAuthDefaults`.
- Updated the required `AuthBuilder::ready()` return construction only; no AuthBuilder detox or broad redesign was performed.

## Public API Note

The public methods on `Auth` are preserved. The constructor now receives `AuthenticationRuntime`, matching the target plan. This is recorded as PLAN_APPROVED_COMPATIBILITY_YELLOW because constructor consumers outside the current repo may need migration.

## Status

PARTIAL_WITH_ENVIRONMENT_YELLOW. Static checks pass, but PHP/composer/PHPUnit execution remains blocked by Docker socket permissions.
