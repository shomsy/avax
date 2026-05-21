# Auth Builder Detox Plan

Date: 2026-05-21

## Slice Scope

Smallest safe AuthBuilder detox slice after Auth runtime delegation:

- Remove container-aware default builder configuration from `DefaultAuth`.
- Keep container lookups inside `RegisterAuthDependencies`, the existing configuration adapter.
- Preserve `AuthBuilder` fluent API and `Auth::` public surface.
- Do not redesign AuthBuilder internals in this slice.

## Ownership Decision

- `DefaultAuth` remains executable auth capability behavior and must not know about the container.
- `RegisterAuthDependencies` owns optional Avax Container integration.
- `AuthBuilder` remains a typed assembly builder; no `ContainerInterface` dependency is introduced.

## Compatibility

Tracked repository call sites only use `DefaultAuth::configuration($container)` from `RegisterAuthDependencies`.
This slice updates that call site and records compatibility risk as YELLOW for out-of-repo callers of the non-PublicSurface helper.

## Expected Validation

- `git diff --check`
- static grep: no `ContainerInterface` or `DefaultAuth::configuration` in `DefaultAuth`
- static grep: no `DefaultAuth::configuration` call sites
- PHP/composer/PHPUnit if environment permits, otherwise ENVIRONMENT_YELLOW
