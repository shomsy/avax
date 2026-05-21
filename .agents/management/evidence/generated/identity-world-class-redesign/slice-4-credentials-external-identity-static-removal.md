# Slice 4: Credentials and ExternalIdentity Static State Removal

Status: COMPLETED
Date: 2026-05-21
Branch: architecture/identity-world-class-redesign

## Purpose

Remove mutable static state from Credentials and ExternalIdentity PublicSurface classes.
Replace with injectable store interfaces behind a static facade for backward compatibility.

## Problem

Both `Credentials` and `ExternalIdentity` owned mutable static arrays (`$store`, `$links`).
This leaks state across requests in long-lived workers (Swoole, RoadRunner, FrankenPHP).

## Changes

### Created: CredentialStoreInterface & InMemoryCredentialStore
- `Credentials/System/Capabilities/CredentialStore/CredentialStoreInterface.php`
  — `store()`, `read()`, `forget()` contract
- `Credentials/System/Capabilities/CredentialStore/InMemoryCredentialStore.php`
  — instance-based in-memory implementation (replaces the old static array)
- `Credentials/System/Configuration/Assembly/CredentialsGraph.php`
  — configures the static facade with a store via `setStore()`

### Created: ExternalIdentityLinkStoreInterface & InMemoryExternalIdentityLinkStore
- `ExternalIdentity/System/Capabilities/ExternalIdentityLink/ExternalIdentityLinkStoreInterface.php`
  — `link()`, `resolve()` contract
- `ExternalIdentity/System/Capabilities/ExternalIdentityLink/InMemoryExternalIdentityLinkStore.php`
  — instance-based in-memory implementation (replaces the old static array)
- `ExternalIdentity/System/Configuration/Assembly/ExternalIdentityGraph.php`
  — configures the static facade with a store via `setLinkStore()`

### Modified: Credentials PublicSurface
- Replaced `private static array $store` with `private static ?CredentialStoreInterface $store`
- Added `setStore()` for DI integration
- `store()`, `read()`, `forget()` now delegate to the store instance
- Default fallback: `InMemoryCredentialStore` (same behavior as before)
- Marked `@deprecated` — users should inject `CredentialStoreInterface` directly

### Modified: ExternalIdentity PublicSurface
- Replaced `private static array $links` with `private static ?ExternalIdentityLinkStoreInterface $linkStore`
- Added `setLinkStore()` for DI integration
- `link()`, `resolve()` now delegate to the store instance
- Default fallback: `InMemoryExternalIdentityLinkStore` (same behavior as before)
- Marked `@deprecated` — users should inject `ExternalIdentityLinkStoreInterface` directly

### Modified: Tests
- `CredentialsCharacterizationTest` — replaced `allMethodsAreStatic` with `backwardCompatWithReplaceableStore` test
- `ExternalIdentityCharacterizationTest` — replaced `allMethodsAreStatic` with `backwardCompatWithReplaceableStore` test; updated `setUp()` to reset `linkStore` instead of accessing `$links`

## Public API Compatibility

| Old | New | Status |
|-----|-----|--------|
| `Credentials::store()` | same (delegates) | backward compat |
| `Credentials::read()` | same (delegates) | backward compat |
| `Credentials::forget()` | same (delegates) | backward compat |
| — | `Credentials::setStore()` | new |
| `ExternalIdentity::link()` | same (delegates) | backward compat |
| `ExternalIdentity::resolve()` | same (delegates) | backward compat |
| — | `ExternalIdentity::setLinkStore()` | new |

## Validation

```text
php vendor/bin/phpunit --no-coverage --filter="Identity"
Tests: 236, Assertions: 784, Failures: 0, Errors: 0
```

## Risk Assessment

- Backward compatible — all static method signatures preserved
- New `setStore()` / `setLinkStore()` methods allow DI integration without breaking existing callers
- Default fallback creates `InMemoryCredentialStore` / `InMemoryExternalIdentityLinkStore` (same behavior as old static arrays)
- Thread-unsafe static state is now isolated to the store implementation, not the facade itself
- `CredentialsGraph` and `ExternalIdentityGraph` are void-returning configurators (not instance builders) due to PHP's limitation on mixed static/instance method names
