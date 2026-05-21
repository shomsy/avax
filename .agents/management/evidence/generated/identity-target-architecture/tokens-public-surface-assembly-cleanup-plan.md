# Tokens PublicSurface Assembly Cleanup Plan

Date: 2026-05-21

## Slice Scope

- Remove deprecated static assembly helpers from `Tokens` PublicSurface.
- Update tests to assemble tokens through `TokensGraph`.
- Preserve `Tokens` runtime methods and `TokensInterface`.

## Out of Scope

- JwtAuth static state cleanup.
- Token flow behavior changes.
- Token codec/revocation store redesign.

## Compatibility

`Tokens::hmac()` and `Tokens::fromRuntime()` were documented as deprecated assembly leaks. This slice removes them from PublicSurface; callers should use `TokensGraph::hmac()` or `TokensGraph::fromRuntime()`.
