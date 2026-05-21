# Access Runtime Delegation Plan

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Scope

In scope:

- Introduce a cohesive `AccessRuntime` capability for current Access PublicSurface behavior.
- Make `System/PublicSurface/Access.php` delegate to `AccessRuntime`.
- Update `AccessServiceProvider`, root `IdentityRuntime` builder, and Access characterization tests.
- Preserve existing public methods on `Access`.

Out of scope:

- Full enterprise Access capability convergence with the richer `Capabilities\Access` runtime.
- AuthBuilder changes.
- Adding new Access DSL methods.

## Expected Status

PARTIAL_WITH_ENVIRONMENT_YELLOW unless PHP execution becomes available.
