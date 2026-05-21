# External Identity OIDC Clock Hardening Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/ExternalIdentity/System/Foundation/Time/Clock.php`
- `components/Identity/ExternalIdentity/System/Foundation/Time/SystemClock.php`
- `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/InMemoryOidcRequestObjectStore.php`
- `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OpenSslOidcProvider.php`
- `components/Identity/Auth/System/Configuration/Providers/RegisterAuthDefaults.php`
- `tests/Unit/Components/Identity/ExternalIdentity/OidcClockCharacterizationTest.php`

## Implementation

OIDC request-object storage now receives a clock and uses it for `createdAt` and expiry checks.

`OpenSslOidcProvider` now uses a clock for ID token `iat`/`exp` generation and token resolve expiry validation.

`RegisterAuthDefaults` assembles the default request-object store with an ExternalIdentity `SystemClock`.

Focused test source covers deterministic request-object timestamps, request-object expiry, deterministic ID token claims, and fail-closed expired token resolution.

## Boundary Result

ExternalIdentity OIDC runtime code no longer calls `time()` or constructs current `DateTimeImmutable` directly outside `SystemClock`.
