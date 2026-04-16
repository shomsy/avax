# Flow Boundaries

`System/Flow/` owns end-to-end behavior.

A flow is the first honest owner when:

- one use case completes the story
- the logic is local to one action
- extraction would hide the domain sequence

Examples in this package:

- `Flow/Login/`
- `Flow/Register/`
- `Flow/ChangePassword/`
- `Flow/Recover/`
- `Flow/Oidc/`
- `Flow/Scim/`
- `Flow/TenantSecurity/`

Locality rules:

- login throttling stays with login until proven shared
- password reset throttling stays with recovery
- tenant security approval steps stay with `TenantSecurity`
- SCIM bulk semantics stay with `Scim`

A flow should not become a generic protocol bucket or helper drawer.
