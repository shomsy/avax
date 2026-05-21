# Slice 5 Evidence — ExternalIdentity Sub-surfaces + Passkey Rename

## Scope

- Add `ExternalIdentity::oauth()`, `oidc()`, `federation()` sub-surfaces per target DSL
- Rename `Passkey` to `Passkeys` (plural, consistent with Mfa/Passwords pattern)

## Changes

### ExternalIdentity Sub-surfaces

Created:
- `OAuth` — stub public surface (following Mfa/Passkeys pattern)
- `Oidc` — stub public surface
- `Federation` — stub public surface

Updated:
- `ExternalIdentityRuntime` — added OAuth/Oidc/Federation constructor deps + accessor methods
- `ExternalIdentity` PublicSurface — added `oauth()`, `oidc()`, `federation()` delegating to runtime
- `ExternalIdentityGraph` — assembly passes new sub-surfaces

### Passkey → Passkeys Rename

- Renamed `Passkey.php` → `Passkeys.php` (file and class)
- Updated all imports and type references in:
  - `CredentialsRuntime`
  - `CredentialsGraph`
  - `Credentials` PublicSurface

## Validation

- PHPUnit: 156 tests, 462 assertions, OK
- PHPStan: clean on all changed files

## Tests Updated

- `ExternalIdentityCharacterizationTest::externalIdentity()` — added new deps

## Final Status: GREEN
