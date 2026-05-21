# Access Interface Import Cleanup Plan

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Scope

- Replace fully-qualified `UserRole` and `UserPermission` parameter types in
  `Access/System/PublicSurface/AccessInterface.php` with explicit imports.
- Preserve the exact public contract types.

## Out Of Scope

- No Access API shape change.
- No runtime behavior change.
