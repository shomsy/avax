# Tenancy Boundary

`Tenancy` owns tenant product structure and tenant-scoped security change control.

It owns:

- tenant creation and reading
- invitations
- membership state
- transfer ownership
- tenant security configuration
- change request, approval, apply, rollback for tenant security

It does not own:

- generic access enforcement primitives
- local account authenticators
- OAuth/OIDC protocol internals

Boundary rule:

- `Tenancy` decides who belongs to a tenant and how tenant policy changes are controlled
- `Access` decides whether a current actor passes a given access gate
