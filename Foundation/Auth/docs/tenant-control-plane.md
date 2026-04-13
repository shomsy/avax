# Tenant Control-Plane Posture

Tenant security changes are high-impact operations and need a control-plane,
not ad hoc admin toggles.

## Kernel-Owned Today

- tenant security configuration state
- request, approval, apply, and rollback workflow
- reference validation against federation connections and SCIM directories
- config diff capture for audit
- rollout versioning per tenant
- framework-neutral tenant-admin HTTP surface through
  `integrations/http/TenantSecurity/ServeTenantSecurityHttpSurface.php`

The package exposes this through `Auth::readTenantSecurityConfiguration()`,
`beginTenantSecurityChange()`, `approveTenantSecurityChange()`,
`applyTenantSecurityChange()`, and `rollbackTenantSecurityChange()`.

## Minimum Control-Plane Fields

- SSO configuration
- SCIM configuration
- verified domains
- group mapping
- tenant policy posture

## Required Change Controls

- approval path for tenant security changes
- config diff in audit
- safe rollout by tenant
- safe rollback by tenant
- correlated evidence for every sensitive change

## Still Application-Owned

- tenant-admin UI
- operator notifications and richer approval UX
- tenant membership and billing control planes
- organizational SoD policy outside the kernel
