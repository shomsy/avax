# Slice 6: Identity Capability Constructor Hardening

Status: COMPLETED
Date: 2026-05-21
Branch: architecture/identity-world-class-redesign

## Purpose

Add a `Identity::create()` named constructor that requires all 9 parameters
(7 sub-capabilities + 2 backends) for the runtime assembly path, while keeping
the existing nullable constructor for bootstrap flows.

## Problem

The `Identity` constructor accepted all 9 sub-capability/backend parameters
with null defaults. The assembly graph (`buildIdentity()`) always provided all 9,
but there was no compile-time or named-constructor enforcement.

## Changes

### Modified
- `Auth/System/Capabilities/Identity/Identity.php`
  - Added `static create(Authentication, Sessions, Account, Recovery, Verification, Mfa, Passkey, ?SessionIdentityInterface, ?JwtIdentityInterface): self`
    — requires all 7 sub-capabilities non-null, backends nullable (constructor validates
    at least one backend)
  - `buildIdentity()` now uses `Identity::create()` instead of `new Identity()`
  - Marked existing constructor `@deprecated` — use `create()` for runtime assembly
  - Marked `fromBackends()` `@deprecated` — use `create()` with all params

- `Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php`
  - `buildIdentity()` calls `Identity::create()` instead of `new Identity()`

### Unchanged
- Constructor signature (backward compat for bootstrap Identity with nullable sub-capabilities)
- `fromBackends()` behavior (deprecated, still works for bootstrap)
- All accessor methods with `?? throw IdentityCapabilityUnavailable` (safety net)

## Public API Compatibility

| Old | New | Status |
|-----|-----|--------|
| `new Identity(...)` | same | backward compat, @deprecated |
| `Identity::fromBackends(...)` | same | backward compat, @deprecated |
| — | `Identity::create(...)` | new, requires all 7 sub-capabilities |

## Validation

```text
php vendor/bin/phpunit --no-coverage --filter="Identity"
Tests: 236, Assertions: 784, Failures: 0, Errors: 0
```

## Risk Assessment

- Zero behavior change — constructor and fromBackends() retain full backward compat
- `Identity::create()` enforces non-null sub-capabilities at the assembly boundary
- The assembly graph (`buildIdentity()`) already provides all 9 params — no caller changes needed
