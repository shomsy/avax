# Capability Boundaries

`System/Capability/` owns shared system abilities, not leftover code.

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

`System/Capability/Access/`, `System/Capability/Identity/`, `System/Capability/ExternalIdentity/`,
`System/Capability/IdentitySync/`, `System/Capability/Tenant/`, and `System/Capability/Diagnostics/` are owner
facades, not replacements for the detailed shared capabilities that live alongside them.
