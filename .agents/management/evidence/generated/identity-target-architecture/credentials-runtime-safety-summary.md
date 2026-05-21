# Credentials Runtime Safety Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Credentials/System/Capabilities/CredentialsRuntime/CredentialsRuntime.php`
- `components/Identity/Credentials/System/PublicSurface/Credentials.php`
- `components/Identity/Credentials/System/Configuration/Assembly/CredentialsGraph.php`
- `components/Identity/Credentials/System/Configuration/CredentialsServiceProvider.php`
- `components/Identity/System/Configuration/Builders/IdentityRuntime.php`
- `tests/Unit/Components/Identity/Credentials/CredentialsCharacterizationTest.php`

## Implementation

`Credentials` PublicSurface now receives `CredentialsRuntime` and delegates storage and sub-surface access.

`CredentialsRuntime` owns the credential store dependency and returns the assembled `Mfa` and `Passkey` sub-surfaces.

Root Identity default assembly now creates `Credentials` through `CredentialsGraph::fromStore()` with an explicit in-memory credential store.

The characterization test now proves separate credentials runtimes do not share stored credentials.

## Boundary Result

- PublicSurface static mutable credential-store state removed.
- PublicSurface direct fallback construction removed.
- Configuration assembly owns the default runtime graph.
