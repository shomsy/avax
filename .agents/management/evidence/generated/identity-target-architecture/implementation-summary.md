# Identity Target Architecture Implementation Summary

Date: 2026-05-21

## Implemented

- Added `components/Identity/System/Capabilities/IdentityRuntime/IdentityRuntime.php`.
- Added `components/Identity/System/Capabilities/GuestSession/GuestSessionIdentity.php`.
- Added `components/Identity/System/Configuration/Builders/IdentityRuntime.php`.
- Reworked `components/Identity/System/PublicSurface/Identity.php` so root DSL methods delegate to the root runtime instead of constructing sub-surfaces directly.
- Fixed `IdentityServiceProvider` to register `IdentityConfiguration` and root runtime assembly instead of deleted `IdentityConfig`.
- Updated Identity system tests to cover default runtime assembly and provider registration.
- Replaced direct `date('H')` usage in `AttributeCondition::withinHours()` with caller-provided time context.
- Updated policy characterization tests to prove deterministic time-context behavior.
- Replaced fully-qualified user value object construction inside `Auth` with imports.

## Remaining Partial Items

- The root static DSL still uses default assembly indirectly because the existing public API is static. A full DI-only root Identity surface requires a larger compatibility decision.
- Existing sub-surfaces still contain legacy static state in Credentials, Tenancy, ExternalIdentity, Policy, and JWT areas. This slice did not claim to close those.
- The default token graph still uses the existing HMAC convenience assembly path. Token hardening remains a later security slice.

## Status

PARTIAL. This implements a bounded root DSL delegation slice from `refactor-identity.md`; it does not complete the full Identity redesign.
