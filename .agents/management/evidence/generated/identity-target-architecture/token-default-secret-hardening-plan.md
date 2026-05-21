# Token Default Secret Hardening Plan

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Source Of Truth Decision

Current git state wins over the stale Qoder handoff. The PermissionDenied, governance,
Access runtime enforcement, and broad Identity DSL slices are already committed.

The next safe slice is the remaining security finding in root Identity assembly:
`components/Identity/System/Configuration/Builders/IdentityRuntime.php` still builds
`TokensGraph::hmac(secret: 'test')`.

## Scope

- Replace the hardcoded root token secret with explicit `IdentityConfiguration`.
- Make default runtime assembly fail closed when `TOKEN_SECRET` is absent.
- Preserve the public `Identity::tokens()` and `Tokens::issue(TokenSubject)` API.
- Update Identity DSL tests to provide an explicit test secret.

## Out Of Scope

- No redesign of all AuthBuilder internals.
- No Tokens public API changes.
- No ExternalIdentity behavior changes.
- No broad provider cleanup.

## Security Decision

Hardcoded HMAC secrets are forbidden. The root Identity default runtime must use an
explicit configuration value or refuse to assemble token capabilities.

## Expected Validation

- `git diff --check`
- PHP syntax checks for changed PHP files if PHP is available
- focused Identity tests if PHP/PHPUnit is available
- static grep proving no `TokensGraph::hmac(secret: 'test')` remains
