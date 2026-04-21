# Tenant Control-Plane Posture

Tenant security changes are high-impact operations and need a control-plane,
not ad hoc admin toggles.

## Kernel-Owned Today

- tenant creation, membership, invite acceptance, suspension, removal, and owner transfer
- tenant-owned OAuth client registration, update, disable, secret rotation, and
  explicit approval for high-risk registrations
- tenant security configuration state
- request, approval, apply, and rollback workflow
- reference validation against federation connections and SCIM directories
- config diff capture for audit
- rollout versioning per tenant
- framework-neutral tenant-admin HTTP surface through
  `integrations/http/TenantSecurity/ServeTenantSecurityHttpSurface.php`

The package exposes this through `Auth::readTenantSecurityConfiguration()`,
`beginTenantSecurityChange()`, `approveTenantSecurityChange()`,
`applyTenantSecurityChange()`, `rollbackTenantSecurityChange()`, plus
`createTenant()`, `inviteTenantMember()`, `acceptTenantInvite()`,
`readTenantMembers()`, `suspendTenantMember()`, `removeTenantMember()`,
`transferTenantOwnership()`, `registerOAuthClient()`, `updateOAuthClient()`,
`disableOAuthClient()`, and `rotateOAuthClientSecret()`.

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
- billing control planes
- organizational SoD policy outside the kernel
