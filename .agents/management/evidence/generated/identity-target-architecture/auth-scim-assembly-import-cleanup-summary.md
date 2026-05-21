# Auth SCIM Assembly Import Cleanup Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php`

## Implementation

The SCIM assembly section now uses explicit imports for:

- `RegisterScimDirectory`
- `ReadScimDirectories`
- `RotateScimToken`
- `MarkScimDirectoryOutage`
- `RecoverScimDirectoryOutage`

No constructor arguments or runtime behavior changed.
