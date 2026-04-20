# Capability Boundaries

`System/Capabilities/` owns shared system abilities, not leftover code.

A capability exists only when:

- multiple flows use it
- its policy is broader than one use case
- extraction improves clarity more than locality

Current shared capabilities:

- `Access`
- `Identity`
- `OAuth`
- `Oidc`
- `Session`
- `Passkey`
- `Tenant`
- `TenantSecurity`
- `Scim`
- `Lifecycle`
- `Throttle`
- `Explainability`

Capability slices must not absorb:

- one-off orchestration
- feature-specific validation
- temporary migration code
- unnamed “shared” leftovers

`System/Capabilities/Access/`, `System/Capabilities/Identity/`, `System/Capabilities/ExternalIdentity/`,
`System/Capabilities/IdentitySync/`, `System/Capabilities/Tenancy/`, and `System/Capabilities/Diagnostics/` are owner
zones, not replacements for the detailed shared capabilities that live alongside them.
