# SCIM Runtime Boundary

SCIM is conditional and becomes a must-have when the product targets enterprise
workforce or B2B provisioning.

## Kernel-Owned Today

- directory registration
- directory token authentication and rotation
- user provision or update
- derived `/Groups` projection
- `/Bulk` orchestration over package-owned user flows
- group sync
- directory-scoped read and delete
- idempotent updates and drift detection
- explicit `active`, `suspended`, and `disabled` semantics
- audit trail for provisioning actions
- tenant security references into SCIM directory ownership
- framework-neutral HTTP metadata, `/Users` CRUD, `/Groups`, and `/Bulk` through
  `integrations/http/Scim/ServeScimHttpSurface.php`

## Still Outside This Package

- tenant-admin UI or self-service enablement portal
- external directory ownership workflows outside the kernel
- per-directory throttling, sync health scoring, and outage recovery orchestration

## Package Posture

- keep user lifecycle primitives in `Provisioning/`
- keep the SCIM HTTP surface in `integrations/http/Scim/` or another adapter
  package, not inside the kernel flows
- require tenant-owned approval and config audit before enabling SCIM for a
  tenant
