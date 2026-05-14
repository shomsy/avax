# Authorization Hardening

Authorization is not complete when roles exist. It is complete when the system
can show how it denies, reviews, and tests access decisions.

## Mandatory Rules

- deny by default
- require object or resource checks for object-scoped actions
- treat separation of duties as mandatory for admin, support, tenant-admin, and
  break-glass posture
- require approval paths for privileged administrative actions
- review permissions and role assignments on a fixed cadence
- test both horizontal and vertical escalation paths

## Package Surface

- `Capability/Access/Access.php` is the single authorization facade
- `Capability/Access/Policy/AccessPolicy.php` is the composed requirement model
- `Capability/Access/Policy/IdentityPolicyCatalog.php` owns the default actor
  tiers
- `Capability/Access/RequireAccessPolicy/RequireAccessPolicy.php` enforces role,
  permission, resource-owner, phishing-resistant, fresh-MFA, and admin
  elevation checks in one place

## Review Checklist

- confirm the flow has one clear owner
- confirm the access decision is local to the owning flow or capability
- confirm the request path starts from deny by default
- confirm resource ownership is checked where data is user- or tenant-scoped
- confirm privileged actions require recent auth or admin elevation
- confirm approval and SoD expectations are documented when the action is
  privileged
- confirm the negative test matrix covers missing role, missing permission,
  wrong resource owner, stale MFA, and missing elevation

## Test Matrix

- unauthenticated caller -> denied
- authenticated wrong role -> denied
- authenticated missing permission -> denied
- authenticated different resource owner -> denied
- authenticated stale MFA -> denied
- authenticated missing phishing-resistant factor -> denied
- authenticated missing admin elevation -> denied
- authenticated valid subject -> allowed

## Operational Cadence

- review role mappings at least quarterly
- review high-risk permissions before each release affecting privileged flows
- review break-glass assignments after every use
