# External Identity Assembly Naming Cleanup Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/ExternalIdentity/System/Configuration/Assembly/ExternalIdentity.php`
- old `ExternalIdentityGraph.php` deleted by move
- `components/Identity/System/Configuration/Builders/IdentityRuntime.php`

## Implementation

`ExternalIdentityGraph` became `Configuration/Assembly/ExternalIdentity`, matching the
assembled product. The root Identity runtime uses `ExternalIdentityAssembly` as a local
alias to avoid confusion with the PublicSurface class.

Runtime behavior is unchanged.
