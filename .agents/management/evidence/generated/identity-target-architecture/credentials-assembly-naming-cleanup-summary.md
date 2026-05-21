# Credentials Assembly Naming Cleanup Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Credentials/System/Configuration/Assembly/Credentials.php`
- old `CredentialsGraph.php` deleted by move
- `components/Identity/System/Configuration/Builders/IdentityRuntime.php`
- `tests/Unit/Components/Identity/Credentials/CredentialsCharacterizationTest.php`

## Implementation

`CredentialsGraph` became `Configuration/Assembly/Credentials`, matching the product it
assembles. Call sites use `CredentialsAssembly` aliases where the PublicSurface
`Credentials` class is also in scope.

Runtime behavior is unchanged.
