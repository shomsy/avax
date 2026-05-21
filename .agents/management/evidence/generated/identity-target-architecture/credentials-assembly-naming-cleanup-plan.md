# Credentials Assembly Naming Cleanup Plan

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Scope

- Rename `Configuration/Assembly/CredentialsGraph` to `Configuration/Assembly/Credentials`.
- Update internal call sites and tests.
- Preserve assembly behavior.

## Reason

Under the fluent class API governance, `Graph` is a mechanical suffix unless graph
semantics are material. This class assembles one Credentials product from a store, so the
class should be named for the product.

## Compatibility

This is an internal configuration class, not a PublicSurface type. Out-of-repo direct use
of `CredentialsGraph` is compatibility-yellow.
