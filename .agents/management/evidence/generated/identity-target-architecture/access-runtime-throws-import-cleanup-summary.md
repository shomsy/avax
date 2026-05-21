# Access Runtime Throws Import Cleanup Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Access/System/Capabilities/AccessRuntime/AccessRuntime.php`

## Implementation

`AccessRuntime` now imports `Unauthenticated`, `RoleDenied`, and `ResourceOwnerDenied`
and uses those names in `@throws` annotations.

Runtime code and public signatures are unchanged.
