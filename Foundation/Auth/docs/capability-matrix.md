# Capability Matrix

Version: 2.1.0

This matrix is the canonical capability truth table. Every evidence path below
exists in the current repository shape.

| Capability                                              | Status      | Ownership   | Evidence                                                                                                                                                                                                          |
|---------------------------------------------------------|-------------|-------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| password hashing                                        | ✅ supported | kernel      | `System/Capabilities/Identity/PasswordHashing/`, `tests/Capabilities/PasswordHashing/PasswordHashingTest.php`                                                                                                       |
| MFA enrollment, challenge, recovery                     | ✅ supported | kernel      | `System/Capabilities/Identity/Mfa/Runtime/`, `tests/Flows/Mfa/`                                                                                                                                                     |
| password and email recovery                             | ✅ supported | kernel      | `System/Flows/RecoverAccess/PasswordReset/`, `System/Flows/ChangeEmail/`, `tests/Flows/Recover/`, `tests/Flows/ChangeEmail/`                                                                                       |
| session registry and revocation                         | ✅ supported | kernel      | `System/Capabilities/Identity/Sessions/Registry/`, `System/Capabilities/Identity/Sessions/Runtime/`, `tests/Capabilities/Session/`, `tests/Flows/Session/`                                                         |
| authorization and assurance policy                      | ✅ supported | kernel      | `System/Capabilities/Access/`, `tests/Capabilities/Access/`                                                                                                                                                       |
| admin elevation                                         | ✅ supported | kernel      | `System/Capabilities/Tenancy/AdminRealmRuntime/`, `tests/Flows/AdminRealm/AdminElevationTest.php`                                                                                                                  |
| OAuth authorization code + refresh + client credentials | ✅ supported | kernel      | `System/Capabilities/ExternalIdentity/OAuth/Runtime/`, `tests/Flows/OAuth/OAuthFlowTest.php`                                                                                                                       |
| sender-constrained OAuth (DPoP/mTLS)                    | ✅ supported | kernel      | `System/Capabilities/ExternalIdentity/OAuth/Support/SenderConstraint/`, `integrations/http/VerifyOAuthSenderConstraint.php`, `tests/Integrations/Http/VerifyOAuthSenderConstraintTest.php`                         |
| OAuth client management                                 | ✅ supported | kernel      | `System/Capabilities/ExternalIdentity/OAuth/Runtime/RegisterClient/`, `System/Capabilities/ExternalIdentity/OAuth/Runtime/UpdateClient/`, `System/Capabilities/ExternalIdentity/OAuth/Runtime/DisableClient/`, `System/Capabilities/ExternalIdentity/OAuth/Runtime/ApproveClientRegistration/`, `tests/Flows/OAuth/ApproveClientRegistrationTest.php` |
| OIDC discovery, JWKS, ID token, userinfo                | ✅ supported | kernel      | `System/Capabilities/ExternalIdentity/OpenIDConnect/Support/`, `System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/ReadProviderMetadata/`, `System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/ReadJsonWebKeySet/`, `System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/ReadUserInfo/`, `tests/Flows/Oidc/OidcFlowTest.php` |
| OIDC dynamic client registration                        | ✅ supported | integration | `integrations/http/Oidc/ServeOidcHttpSurface.php`, `tests/Integrations/Http/Oidc/ServeOidcHttpSurfaceTest.php`                                                                                                    |
| OIDC logout                                             | ✅ supported | kernel      | `System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/FrontChannelLogout/`, `System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/BackChannelLogout/`, `tests/Flows/Oidc/OidcFlowTest.php`            |
| PAR and JARM                                            | ✅ supported | kernel      | `System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/PushAuthorizationRequest/`, `System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/JarmResponse/`, `tests/Flows/Oidc/OidcFlowTest.php`           |
| request-object claim validation                         | ✅ supported | kernel      | `System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/ValidateRequestObject/`, `tests/Flows/Oidc/ValidateRequestObjectTest.php`                                                                             |
| client-signed JAR validation                            | ✅ supported | kernel      | `System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/PushAuthorizationRequest/`, `tests/Flows/Oidc/PushAuthorizationRequestTest.php`, `tests/Integrations/Http/Oidc/ServeOidcHttpSurfaceTest.php`         |
| pairwise subject identifiers                            | ✅ supported | kernel      | `System/Capabilities/ExternalIdentity/OpenIDConnect/Support/SubjectIdentifierStrategy.php`, `tests/Flows/Oidc/OidcFlowTest.php`                                                                                   |
| tenant lifecycle and membership                         | ✅ supported | kernel      | `System/Capabilities/Tenancy/Model/`, `System/Capabilities/Tenancy/Runtime/Tenant/`, `tests/Flows/Tenant/`                                                                                                       |
| tenant security control-plane                           | ✅ supported | kernel      | `System/Capabilities/Tenancy/Security/`, `System/Capabilities/Tenancy/Runtime/TenantSecurity/`, `tests/Flows/TenantSecurity/TenantSecurityFlowTest.php`                                                         |
| federation                                              | ✅ supported | kernel      | `System/Capabilities/ExternalIdentity/SingleSignOn/FederationSupport/`, `System/Capabilities/ExternalIdentity/SingleSignOn/FederationRuntime/`, `tests/Flows/Federation/FederationFlowTest.php`                  |
| SCIM runtime core                                       | ✅ supported | kernel      | `System/Capabilities/IdentitySync/SCIM/Support/`, `System/Capabilities/IdentitySync/SCIM/Runtime/`, `tests/Flows/Scim/ScimFlowTest.php`                                                                          |
| lifecycle orchestration                                 | ✅ supported | kernel      | `System/Capabilities/IdentitySync/Lifecycle/`, `tests/Capabilities/Lifecycle/`                                                                                                                                    |
| release hardening tooling                               | ✅ supported | integration | `integrations/release/`, `tests/Integrations/Release/ReleaseToolingTest.php`                                                                                                                                      |
| source-truth verification                               | ✅ supported | integration | `integrations/release/CheckSourceTruth.php`, `tooling/check-source-truth.php`, `tests/Integrations/Release/ReleaseToolingTest.php`                                                                                |
| migration boundary verification                         | ✅ supported | integration | `integrations/release/CheckMigrationPath.php`, `tooling/check-migration-path.php`, `tests/Integrations/Release/ReleaseToolingTest.php`, `docs/upgrade-migration-guide.md`                                         |
| trusted device / remembered device                      | ❌ non-goal  | external    | `docs/trusted-device-policy.md`, `docs/product-boundary.md`                                                                                                                                                       |
| tenant-admin UI                                         | ❌ non-goal  | external    | `docs/product-boundary.md`                                                                                                                                                                                        |
| external certification program                          | ❌ non-goal  | external    | `docs/certification-profile.md`                                                                                                                                                                                   |
| SAML brokering runtime                                  | ❌ non-goal  | external    | `docs/product-boundary.md`                                                                                                                                                                                        |

## Status Legend

| Symbol      | Meaning                                                                  |
|-------------|--------------------------------------------------------------------------|
| ✅ supported | Package owns the capability and executable evidence exists               |
| ⚠️ partial  | Package owns a kernel slice, but the full product contract stays outside |
| ❌ non-goal  | Explicitly outside shipped package scope                                 |

## Evidence Rule

Run:

```bash
php composer.phar quality-gates
php composer.phar conformance
php composer.phar evidence:bundle
```
