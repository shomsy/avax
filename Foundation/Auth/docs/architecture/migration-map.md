# Migration Map

This map translates the original implementation lanes into the canonical ownership model.

| Current location | Canonical owner | Notes |
| --- | --- | --- |
| `System/Flow/Login/*` | `Identity` | local login story |
| `System/Flow/Register/*` | `Identity` | local account creation |
| `System/Flow/Recover/*` | `Identity` | recover-access story |
| `System/Flow/Verify/*` | `Identity` | verify-identity story |
| `System/Flow/Mfa/*` | `Identity` | factor lifecycle |
| `System/Flow/Passkey/*` | `Identity` | phishing-resistant factor lifecycle |
| `System/Flow/AuthenticateRequest/*` | `Access` | authentication-context entry |
| `System/Capability/Access/*` | `Access` | shared access policy |
| `System/Flow/OAuth/*` | `ExternalIdentity` | OAuth stories |
| `System/Flow/Oidc/*` | `ExternalIdentity` | OIDC stories |
| `System/Flow/Federation/*` | `ExternalIdentity` | SSO/federation stories |
| `System/Capability/OAuth/*`, `Capability/Oidc/*`, `Capability/Federation/*` | `ExternalIdentity` | shared protocol capabilities |
| `System/Flow/Scim/*` | `IdentitySync` | sync and provisioning stories |
| `System/Flow/Provisioning/*` | `IdentitySync` | lifecycle bridge into synced identity state |
| `System/Capability/Scim/*`, `Capability/Lifecycle/*` | `IdentitySync` | shared sync/runtime mechanics |
| `System/Flow/Tenant/*`, `Flow/TenantSecurity/*` | `Tenancy` | tenant product stories |
| `System/Capability/Tenant/*`, `Capability/TenantSecurity/*` | `Tenancy` | shared tenant capabilities |
| `System/Flow/Diagnostics/*`, `Capability/Explainability/*` | `Diagnostics` | operator-facing diagnostics |

Files that should not introduce a new root zone:

- temporary migrations
- generic adapters with no bounded owner
- duplicate aliases for concepts already named elsewhere
