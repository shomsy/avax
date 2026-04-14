# Capability Matrix

Version: 2.1.0

This matrix is the canonical capability truth table. Every evidence path below
exists in the current repository shape.

| Capability | Status | Ownership | Evidence |
|---|---|---|---|
| password hashing | ✅ supported | kernel | `System/Capability/PasswordHashing/`, `tests/Capabilities/PasswordHashing/PasswordHashingTest.php` |
| MFA enrollment, challenge, recovery | ✅ supported | kernel | `System/Flow/Mfa/`, `tests/Flows/Mfa/` |
| password and email recovery | ✅ supported | kernel | `System/Flow/Recover/`, `System/Flow/ChangeEmail/`, `tests/Flows/Recover/`, `tests/Flows/ChangeEmail/` |
| session registry and revocation | ✅ supported | kernel | `System/Capability/Session/`, `System/Flow/Session/`, `tests/Capabilities/Session/`, `tests/Flows/Session/` |
| authorization and assurance policy | ✅ supported | kernel | `System/Capability/Access/`, `tests/Capabilities/Access/` |
| admin elevation | ✅ supported | kernel | `System/Flow/AdminRealm/`, `tests/Flows/AdminRealm/AdminElevationTest.php` |
| OAuth authorization code + refresh + client credentials | ✅ supported | kernel | `System/Flow/OAuth/`, `tests/Flows/OAuth/OAuthFlowTest.php` |
| sender-constrained OAuth (DPoP/mTLS) | ✅ supported | kernel | `System/Capability/OAuth/SenderConstraint/`, `integrations/http/VerifyOAuthSenderConstraint.php`, `tests/Integrations/Http/VerifyOAuthSenderConstraintTest.php` |
| OAuth client management | ✅ supported | kernel | `System/Flow/OAuth/RegisterClient/`, `System/Flow/OAuth/UpdateClient/`, `System/Flow/OAuth/DisableClient/`, `System/Flow/OAuth/ApproveClientRegistration/`, `tests/Flows/OAuth/ApproveClientRegistrationTest.php` |
| OIDC discovery, JWKS, ID token, userinfo | ✅ supported | kernel | `System/Capability/Oidc/`, `System/Flow/Oidc/ReadProviderMetadata/`, `System/Flow/Oidc/ReadJsonWebKeySet/`, `System/Flow/Oidc/ReadUserInfo/`, `tests/Flows/Oidc/OidcFlowTest.php` |
| OIDC logout | ✅ supported | kernel | `System/Flow/Oidc/FrontChannelLogout/`, `System/Flow/Oidc/BackChannelLogout/`, `tests/Flows/Oidc/OidcFlowTest.php` |
| PAR and JARM | ✅ supported | kernel | `System/Flow/Oidc/PushAuthorizationRequest/`, `System/Flow/Oidc/JarmResponse/`, `tests/Flows/Oidc/OidcFlowTest.php` |
| request-object claim validation | ✅ supported | kernel | `System/Flow/Oidc/ValidateRequestObject/`, `tests/Flows/Oidc/ValidateRequestObjectTest.php` |
| client-signed JAR validation | ⚠️ partial | boundary | `docs/oidc-provider-boundary.md`, `docs/oidc-conformance-matrix.md` |
| pairwise subject identifiers | ✅ supported | kernel | `System/Capability/Oidc/SubjectIdentifierStrategy.php`, `tests/Flows/Oidc/OidcFlowTest.php` |
| tenant lifecycle and membership | ✅ supported | kernel | `System/Capability/Tenant/`, `System/Flow/Tenant/`, `tests/Flows/Tenant/` |
| tenant security control-plane | ✅ supported | kernel | `System/Capability/TenantSecurity/`, `System/Flow/TenantSecurity/`, `tests/Flows/TenantSecurity/TenantSecurityFlowTest.php` |
| federation | ✅ supported | kernel | `System/Capability/Federation/`, `System/Flow/Federation/`, `tests/Flows/Federation/FederationFlowTest.php` |
| SCIM runtime core | ✅ supported | kernel | `System/Capability/Scim/`, `System/Flow/Scim/`, `tests/Flows/Scim/ScimFlowTest.php` |
| lifecycle orchestration | ✅ supported | kernel | `System/Capability/Lifecycle/`, `tests/Capabilities/Lifecycle/` |
| release hardening tooling | ✅ supported | integration | `integrations/release/`, `tests/Integrations/Release/ReleaseToolingTest.php` |
| source-truth verification | ✅ supported | integration | `integrations/release/CheckSourceTruth.php`, `tooling/check-source-truth.php`, `tests/Integrations/Release/ReleaseToolingTest.php` |
| migration boundary verification | ✅ supported | integration | `integrations/release/CheckMigrationPath.php`, `tooling/check-migration-path.php`, `tests/Integrations/Release/ReleaseToolingTest.php`, `docs/upgrade-migration-guide.md` |
| trusted device / remembered device | ❌ non-goal | external | `docs/trusted-device-policy.md`, `docs/product-boundary.md` |
| tenant-admin UI | ❌ non-goal | external | `docs/product-boundary.md` |
| external certification program | ❌ non-goal | external | `docs/certification-profile.md` |
| SAML brokering runtime | ❌ non-goal | external | `docs/product-boundary.md` |

## Status Legend

| Symbol | Meaning |
|--------|---------|
| ✅ supported | Package owns the capability and executable evidence exists |
| ⚠️ partial | Package owns a kernel slice, but the full product contract stays outside |
| ❌ non-goal | Explicitly outside shipped package scope |

## Evidence Rule

Run:

```bash
php composer.phar quality-gates
php composer.phar conformance
php composer.phar evidence:bundle
```
