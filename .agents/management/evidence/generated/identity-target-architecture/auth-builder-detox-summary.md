# Auth Builder Detox Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Auth/System/Capabilities/Authentication/DefaultAuth.php`
- `components/Identity/Auth/System/Configuration/Builders/RegisterAuthDependencies.php`

## Implementation

`DefaultAuth` no longer owns container-driven `AuthBuilder` setup. The previous `DefaultAuth::configuration(ContainerInterface $container)` service-locator helper was removed from the capability class.

`RegisterAuthDependencies` now creates and preconfigures `AuthBuilder` from the Avax container inside the existing configuration adapter. This keeps container lookups at the configuration boundary and leaves `AuthBuilder` as a typed fluent assembly object.

## Boundaries

- PublicSurface changed: NO.
- `AuthBuilder` fluent methods changed: NO.
- Runtime behavior changed: NO intended behavior change.
- Container usage moved: YES, from capability helper to configuration adapter.

## Remaining Yellow

`RegisterAuthDependencies` still lives under `Configuration/Builders` and remains a container adapter. The full provider/registrar naming cleanup belongs to a later Provider/Assembly slice.
