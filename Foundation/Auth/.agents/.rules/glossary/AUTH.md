# Auth Glossary — Authentication & Authorization Terms

Version: 1.0.0
Status: Normative / Local
Scope: `Avax\Auth\**`

This glossary defines canonical terminology for the Auth package.

## Authentication (AuthN)

| Term | Definition | Canonical Location |
|---|---|---|
| **Principal** | The authenticated identity performing an action (user, service account, workload) | `Capability/Identity/` |
| **Credential** | Evidence proving identity (password, token, key, biometric) | `Capability/Identity/Credential` |
| **Authentication** | Process of verifying credential validity | `Flow/AuthenticateRequest/` |
| **Authentication Factor** | Category of credential (something you know, have, are) | `Flow/Mfa/MfaMethod` |
| **MFA** | Multi-Factor Authentication — requiring 2+ factors | `Flow/Mfa/` |
| **TOTP** | Time-based One-Time Password (RFC 6238) | `Flow/Mfa/Totp` |
| **Backup Code** | EmergencyCodes for MFA recovery | `Flow/Mfa/Backup/` |
| **Session** | Stateful context after successful auth | `Capability/Session/` |
| **Session Token** | Opaque token referencing session (cookie, JWT, header) | `Capability/Session/SessionRecord` |
| **Password Hash** | Stored representation of password (bcrypt, argon2) | `Capability/PasswordHashing/` |

## Authorization (AuthZ)

| Term | Definition | Canonical Location |
|---|---|---|
| **Authorization** | Process of determining permitted actions | `Capability/Access/` |
| **Permission** | Single action allow/deny | `Capability/Access/Permission` |
| **Role** | Named collection of permissions | `Capability/Access/Role` |
| **Policy** | Formal rules for access decisions | `Capability/Access/Policy` |
| **Access Control** | Enforcement mechanism for permissions | `Capability/Access/` |
| **RBAC** | Role-Based Access Control | `Capability/Access/Role` |
| **ABAC** | Attribute-Based Access Control | `Capability/Access/Attribute` |
| **Least Privilege** | Principle: grant minimum required permission | Policy design rule |

## OAuth 2.0 / OIDC

| Term | Definition | Canonical Location |
|---|---|---|
| **OAuth 2.0** | Authorization framework (RFC 6749) | `Capability/OAuth/` |
| **OIDC** | OpenID Connect — identity layer on OAuth 2.0 | `Capability/Oidc/` |
| **Authorization Code** | Temporary code for token exchange | `Capability/OAuth/AuthorizationCode` |
| **Access Token** | Token authorizing resource access | `Capability/OAuth/Token` |
| **Refresh Token** | Token for obtaining new access tokens | `Capability/OAuth/RefreshToken` |
| **ID Token** | OIDC token containing user identity claims | `Capability/Oidc/OidcIdToken` |
| **Client** | Application requesting authorization | `Capability/OAuth/OAuthClient` |
| **Client ID** | Unique identifier for OAuth client | `Capability/OAuth/OAuthClient` |
| **Client Secret** | Secret for confidential clients | `Capability/OAuth/OAuthClient` |
| **Redirect URI** | Callback URL after auth | `Capability/OAuth/RedirectUri` |
| **Grant Type** | OAuth flow type (authorization_code, client_credentials, etc.) | `Capability/OAuth/OAuthGrantType` |
| **PKCE** | Proof Key for Code Exchange (RFC 7636) | `Capability/OAuth/Pkce` |
| **DPoP** | Demonstrating Proof of Possession | `Capability/OAuth/SenderConstraint/` |
| **mTLS** | Mutual TLS for sender constraint | `Capability/OAuth/SenderConstraint/` |
| **PAR** | Push Authorization Request | `Capability/Oidc/PushAuthorizationRequest` |
| **JARM** | JWT Secured Authorization Response Mode | `Capability/Oidc/JwtAuthorizationResponse` |
| **JAR** | JWT Signed Authorization Request | `Capability/Oidc/RequestObject` |

## Sessions & Tokens

| Term | Definition | Canonical Location |
|---|---|---|
| **Session** | Server-side auth context | `Capability/Session/` |
| **Session Registry** | Storage for session records | `Capability/Session/SessionRegistry` |
| **Session Fixation** | Attack where attacker fixes session ID | Security vulnerability |
| **Session Hijacking** | Attack stealing session | Security vulnerability |
| **Token Introspection** | Process of validating token | `Capability/OAuth/Introspect` |
| **Token Endpoint** | OAuth endpoint for token issuance | `Flow/Token/` |
| **UserInfo Endpoint** | OIDC endpoint for user claims | `Flow/Oidc/UserInfo/` |

## Security Concepts

| Term | Definition | Related |
|---|---|---|
| **CSRF** | Cross-Site Request Forgery | Attack vector |
| **XSS** | Cross-Site Scripting | Attack vector |
| **Token Reuse** | Attempt to reuse consumed tokens | Attack vector |
| **Token Rotation** | Regularly issuing new tokens | Best practice |
| **Refresh Token Rotation** | Issuing new refresh tokens on use | Best practice |
| **Sender Constraint** | Binding token to TLS/client certificate | Best practice |
| **Replay Resistance** | Preventing token replay | Best practice |
| **Nonce** | Unique value to prevent replay | OIDC requirement |
| **State Parameter** | CSRF protection via opaque value | OAuth requirement |

## Tenant & Multi-Tenancy

| Term | Definition | Canonical Location |
|---|---|---|
| **Tenant** | Isolated organization/unit | `Capability/Tenant/` |
| **Membership** | User's relationship to tenant | `Capability/Tenant/Membership` |
| **Tenant Admin** | User with tenant management rights | `Flow/Tenant/` |
| **Invite** | Invitation to join tenant | `Flow/Tenant/InviteMember/` |
| **Owner** | Tenant's super-admin role | `Capability/Tenant/Owner` |
| **Control Plane** | Tenant management surface | `Flow/Tenant/` |

## Identity Provisioning

| Term | Definition | Canonical Location |
|---|---|---|
| **SCIM** | System for Cross-Domain Identity Management | `Capability/Scim/` |
| **User Provisioning** | Creating identity in directory | `Flow/Provisioning/` |
| **Deprovisioning** | Disabling/revoking identity | `Flow/Provisioning/` |
| **Drift** | Divergence between systems | `Capability/Scim/Drift` |
| **Idempotency** | Safe repeated operations | SCIM requirement |
| **Directory** | External identity source | `Capability/Scim/ScimDirectory` |

## Risk & Fraud Detection

| Term | Definition | Canonical Location |
|---|---|---|
| **Risk Signal** | Evidence of suspicious activity | `Capability/Risk/RiskSignal` |
| **Risk Action** | Response to risk (allow, challenge, deny) | `Capability/Risk/RiskAction` |
| **Risk Engine** | System evaluating risk | `Capability/Risk/DeterministicRiskEngine` |
| **Device Trust** | Assessment of device integrity | `Capability/DeviceTrust/` |
| **Endpoint Posture** | Client device security state | `Capability/EndpointPosture/` |

## Recovery & Lifecycle

| Term | Definition | Canonical Location |
|---|---|---|
| **Password Recovery** | Resetting forgotten password | `Flow/Recover/` |
| **Account Recovery** | Restoring locked/lost account | `Flow/Recover/` |
| **Lifecycle** | Identity birth-to-death states | `Capability/Lifecycle/` |
| **Expiration** | Time-based invalidation | Policy |
| **Revocation** | Explicit invalidation | `Capability/Session/Revoke` |

## Events & Audit

| Term | Definition | Canonical Location |
|---|---|---|
| **Audit Event** | Record of security-relevant action | `Capability/Audit/` |
| **Audit Trail** | Sequence of audit events | `Capability/Audit/` |
| **Log** | General system record | Observability |
| **Trace** | Request Correlation | Observability |
| **Metric** | Quantitative measurement | Observability |

---

## Usage

Use terms consistently across:
- Code (class names, method names)
- Documentation
- API contracts
- Error messages
- logs

---

*This glossary is authoritative for `avax/auth` package.*