# Tenant Control-Plane Posture

Tenant security changes are high-impact operations and need a control-plane,
not ad hoc admin toggles.

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
