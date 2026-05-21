# Auth Runtime Delegation Plan

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Scope

In scope:

- Introduce `AuthenticationRuntime` for current Auth PublicSurface behavior.
- Make `System/PublicSurface/Auth.php` delegate to `AuthenticationRuntime`.
- Update existing Auth construction sites.
- Preserve existing Auth public methods.

Out of scope:

- AuthBuilder detox beyond the constructor call required by this slice.
- Full authentication graph redesign.
- Adding new target DSL methods such as `current()` / `requireAuthentication()` in this slice.

## Expected Status

PARTIAL_WITH_ENVIRONMENT_YELLOW unless PHP execution becomes available.
