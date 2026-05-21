# Slice 9: Unified Public DSL

Status: COMPLETED
Date: 2026-05-21
Branch: architecture/identity-world-class-redesign

## Purpose

Create `Identity::tenancy()`, `Identity::credentials()`, `Identity::externalIdentity()` 
as a unified fluent entrypoint for all Identity sub-surfaces.

## Changes

### Modified
- `Identity/System/PublicSurface/Identity.php` — Rewritten from a stub (`authenticate()` returning `new self()`) to a proper fluent DSL entrypoint with `tenancy()`, `credentials()`, `externalIdentity()` methods.

- `tests/Unit/Components/Identity/System/IdentitySystemCapabilitiesTest.php` — Replaced trivial instance assertion with targeted tests for each DSL method (tenancy, credentials, externalIdentity).

## Public API Compatibility

| Old | New | Status |
|-----|-----|--------|
| `Identity::authenticate()` | removed | breaking change (stub method, no callers) |
| — | `Identity::tenancy()` | new |
| — | `Identity::credentials()` | new |
| — | `Identity::externalIdentity()` | new |

## Validation

```text
php vendor/bin/phpunit --no-coverage --filter="Identity"
Tests: 240, Assertions: 788, Failures: 0, Errors: 0
```

## Design

Following the naming rule: the class name `Identity` in `System/PublicSurface/Identity.php`
is clean — no mechanical suffix. The directory (`PublicSurface`) gives context.
Methods are intention-revealing (`tenancy()`, `credentials()`, `externalIdentity()`).

No assembly graph needed — sub-surfaces already manage their own static state.
Auth, Access, and Tokens need Builder/Graph assembly before they can be added.
