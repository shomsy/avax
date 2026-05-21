# Token Default Secret Hardening Summary

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Changed Files

- `components/Identity/System/Configuration/IdentityConfiguration.php`
- `components/Identity/System/Configuration/Builders/IdentityRuntime.php`
- `components/Identity/System/Configuration/IdentityServiceProvider.php`
- `components/Identity/System/PublicSurface/Identity.php`
- `tests/Unit/Components/Identity/System/IdentitySystemCapabilitiesTest.php`
- `tests/Unit/Components/Identity/System/IdentityTargetDslCharacterizationTest.php`

## Implementation

Root Identity default token assembly no longer uses `TokensGraph::hmac(secret: 'test')`.

`IdentityConfiguration` now carries an optional sensitive `tokenSecret`, can load it from
`TOKEN_SECRET`, and exposes `requireTokenSecret()` for fail-closed assembly.

`Configuration/Builders/IdentityRuntime` receives `IdentityConfiguration` and passes the
configured secret into `TokensGraph::hmac()`. Missing token secret now fails during runtime
assembly instead of silently issuing tokens with a hardcoded test secret.

`IdentityServiceProvider` now resolves `IdentityRuntime` through the registered builder and
registered configuration, preserving a pre-bound `IdentityConfiguration` when one exists.

Identity DSL tests now provide an explicit test token secret and include a fail-closed test
for missing token configuration.

## API Compatibility

- Public API changed: NO.
- `Identity::tokens()` remains available.
- `Tokens::issue(TokenSubject)` remains unchanged.
- `IdentityRuntime::defaults()` remains callable with no arguments and now accepts optional
  `IdentityConfiguration` for explicit assembly tests.

## Remaining Yellow

The static root `Identity` public surface still creates default runtime assembly via
`Configuration/Builders/IdentityRuntime::defaults()` because the existing static DSL has no
application container bridge yet. This is a known architecture follow-up, not part of this
token-secret security slice.
