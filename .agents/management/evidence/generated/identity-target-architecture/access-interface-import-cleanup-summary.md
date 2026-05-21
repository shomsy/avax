# Access Interface Import Cleanup Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Access/System/PublicSurface/AccessInterface.php`

## Implementation

`AccessInterface` now imports `UserRole` and `UserPermission` and uses short type names in
method signatures. The resolved public contract types are unchanged.

## API Compatibility

Public API changed: NO semantic type change. This is source readability cleanup only.
