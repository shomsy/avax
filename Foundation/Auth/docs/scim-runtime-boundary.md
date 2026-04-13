# SCIM Runtime Boundary

SCIM is conditional and becomes a must-have when the product targets enterprise
workforce or B2B provisioning.

## Required Runtime Behaviors

- CRUD over provisioned users
- group sync
- token authentication and rotation
- idempotent updates
- drift detection
- explicit disable vs suspend semantics
- audit trail for provisioning actions

## Package Posture

- keep user lifecycle primitives in `Provisioning/`
- keep the full SCIM HTTP surface in a separate adapter or package
- require tenant-owned approval and config audit before enabling SCIM for a
  tenant
