# Slice 0: Public DSL Characterization Tests

Status: COMPLETED
Date: 2026-05-21
Branch: architecture/identity-world-class-redesign

## Purpose

Lock current behavior of all 6 Identity PublicSurface interfaces before any redesign changes. These characterization tests serve as regression anchors — if a production change breaks existing behavior, these tests catch it.

## Files Created

| Test File | Tests | Coverage |
|-----------|-------|----------|
| `tests/.../Auth/AuthCharacterizationTest.php` | 5 | Final, AuthInterface, method signatures, no internal type leakage |
| `tests/.../Access/AccessCharacterizationTest.php` | 11 | Final, AccessInterface, allows/denies/authorize, elevation flow, no internal leakage |
| `tests/.../Tenancy/TenancyCharacterizationTest.php` | 8 | Static methods, set/get/clear/run/switch, exception safety, static isolation |
| `tests/.../Credentials/CredentialsCharacterizationTest.php` | 7 | Static store/read/forget, overwrite, multi-user isolation, arbitrary arrays |
| `tests/.../ExternalIdentity/ExternalIdentityCharacterizationTest.php` | 6 | Static link/resolve, multi-user, multi-provider, null for unknown |
| `tests/.../Tokens/TokensCharacterizationTest.php` | 12 | Final, TokensInterface, factory methods, assembly leak, exception behavior |

## Key Characterization Findings

1. **Auth PublicSurface is clean** — final readonly, delegates to Identity, no assembly types exposed
2. **Access uses real AuthorizationEngine** — `allows()` delegates to engine or checks elevation; `execute()` on BeginAdminElevation is an instance method (not static) but sets static `$elevated` state
3. **Tenancy is fully static** with TenantContext managing `$current` as a `?string`; `run()` restores previous tenant even on exception
4. **Credentials is static array store** — `$store` is a `private static array` with `store/read/forget`; no expiration, no persistence, no boundaries
5. **ExternalIdentity is static array store** — `$links` is a `private static array` with `link/resolve`; same limitations as Credentials
6. **Tokens has assembly leak in `hmac()` factory** — creates InMemory* stores inside PublicSurface; `authorize()` requires `subject`/`sub`/`user_id` in request array or throws; `exchangeCode()` throws on invalid code

## Behaviors Locked for Future Slices

- Auth method signatures (Slice 2, 9)
- Access elevation flow via BeginAdminElevation/Access (Slices 1, 10)
- Tenancy static API surface (Slice 4)
- Credentials static store shape (Slice 5)
- ExternalIdentity static links shape (Slice 5)
- Tokens factory methods and exception behavior (Slices 3, 6, 8)

## Validation

```text
php vendor/bin/phpunit --no-coverage --filter="CharacterizationTest" --testdox
Tests: 120, Assertions: 586, Failures: 0, Errors: 0
```

## Risk Assessment

- Static state in Tenancy/Credentials/ExternalIdentity tests managed via setUp/tearDown/Reflection — safe for CI
- Existing static state (TenantContext::$current, BeginAdminElevation::$elevated) is not reset in production — known problem to fix in Slices 4-6
- No production code changed — 100% backward compatible
