# High-Assurance Admin Examples

The package now ships policy examples that can be copied into host
applications.

## Included Examples

- `examples/policies/AdminPasskeyRequiredPolicy.php`
- `examples/policies/TenantAdminPhishingResistantPolicy.php`
- `examples/policies/RequireFreshAssuranceForAdminAction.php`
- `examples/policies/RequireApprovalForPrivilegedAction.php`
- `examples/policies/EnforceSeparationOfDuties.php`

## Intent

- admin paths default to phishing-resistant posture
- tenant-admin paths keep the same phishing-resistant minimum
- sensitive actions can require fresh assurance instead of stale login state
- approval and SoD are policy decisions, not scattered `if` statements
