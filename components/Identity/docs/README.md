# Identity Component Documentation

## What This Component Owns

The Identity component is responsible for **authentication, authorization, token lifecycle, session management, credential verification, tenant context resolution, and access policy enforcement** within the AvaX platform.

It owns the complete identity security boundary: proving who you are (authentication), determining what you can do (authorization), managing the credentials that prove identity (tokens, sessions, credentials, MFA, passkeys), and resolving tenant context for multi-tenant deployments.

## What Does NOT Belong Here

- **User profile data** — belongs in a user/account component, not identity
- **Password hashing algorithms** — belongs in `Security/Hashing`
- **Encryption/decryption** — belongs in `Security/DataProtection`
- **Audit logging** — belongs in `Observability` or `Security/Audit`
- **Database user storage** — Identity consumes user stores; it does not own them
- **Rate limiting** — belongs in `Operations/Resilience`
- **HTTP middleware registration** — belongs in HTTP component configuration

## Platform Plane

Identity spans multiple platform planes:

| Plane | What Identity Contributes |
|-------|--------------------------|
| **Runtime** | Authentication flows, token validation, session lifecycle |
| **Control Plane** | Access policy configuration, tenant resolution, MFA enrollment |
| **Contract** | Authentication/authorization public APIs, token formats |
| **Integration** | OAuth/OIDC providers, external identity providers, passkey authenticators |
| **Reliability** | Throttle policies, risk-based access, lockout handling |
| **Observability** | Authentication event emission, audit trails, failure reporting |

## Public API

The Identity public surface provides:

- **Authentication entry point** — authenticate credentials, produce authentication context
- **Authorization entry point** — evaluate access policies, resolve permissions
- **Token lifecycle** — issue, validate, refresh, revoke tokens
- **Session management** — create, read, update, destroy sessions
- **Credential verification** — verify passwords, MFA codes, passkey assertions
- **Tenant resolution** — resolve tenant context from request, token, or session

## Flows

Identity Flows execute complete authentication and authorization stories:

- **Login Flow** — credential submission → verification → session creation → token issuance
- **Token Validation Flow** — token receipt → signature verification → claims extraction → context production
- **Request Authentication Lifecycle** — incoming request → auth context resolution → policy evaluation → allow/deny
- **Tenant Resolution Lifecycle** — request → tenant identifier extraction → tenant validation → context binding

## Capabilities

Identity Capabilities power reusable behavior across flows:

- **TokenVerification** — signature validation, expiry checking, revocation lookup
- **CredentialValidation** — password hashing verification, MFA code validation, passkey assertion
- **SessionManagement** — session creation, state tracking, expiry enforcement
- **AccessPolicy** — policy definition, rule evaluation, permission resolution
- **RiskAssessment** — signal collection, risk scoring, adaptive authentication

## Configuration

Identity Configuration assembles the object graph through:

- **AuthBuilder** — fluent builder for authentication graph assembly
- **ServiceProviders** — register authentication, authorization, token, session, and tenant services
- **Assembly classes** — construct identity subgraphs (identity graph, external identity graph)

## Failure Modes

| Failure | Behavior | Why |
|---------|----------|-----|
| Invalid credentials | Deny with generic error | Never reveal whether user or password was wrong |
| Expired token | Deny with re-authentication request | Token expiry is absolute; no grace period |
| Revoked session | Deny with redirect to login | Revocation means immediate termination |
| Unknown tenant | Deny with tenant resolution error | Cannot authenticate without tenant context |
| MFA challenge failure | Deny with retry limit | Brute force protection |
| Token tampering | Deny with security event emission | Tampering is an attack indicator |
| Dependency failure (token store, session store) | Fail closed — deny all authentication | Security boundary must fail closed |

## Observability

Identity emits events for:

- Successful/failed authentication attempts
- Token issuance and revocation
- Session creation and destruction
- Access policy denials
- Risk signal anomalies
- MFA challenge outcomes

All security-sensitive logs redact credentials, tokens, and session identifiers.

## Testing Strategy

Identity requires risk-based behavioral testing:

- **Positive path** — valid credentials authenticate, valid tokens validate, valid sessions work
- **Negative path** — invalid credentials deny, expired tokens deny, revoked sessions deny
- **Fail-closed** — dependency failures deny, not allow
- **Replay protection** — reused tokens/nonce rejected
- **Tenant isolation** — tenant A cannot access tenant B resources

See [testing/test-strategy.md](testing/test-strategy.md) for the complete test strategy.

## Dependencies

Identity depends on:

- **Security/Hashing** — password hashing verification
- **Security/Cryptography** — token signature verification
- **Security/DataProtection** — encryption for sensitive session data
- **HTTP/Session** — session storage adapter
- **Operations/Resilience** — rate limiting, throttle policies
- **Application/Container** — DI container for graph assembly
- **Application/Clock** — time-aware token expiry and session validation

---

## Structure

```
docs/
  README.md                    (this file)
  dictionary/                  Glossary of core identity terms and definitions
  adr/                         Architecture Decision Records
  flows/                       Authentication and authorization flow documentation
  diagrams/                    Visual diagrams (Mermaid)
  mistakes/                    Common authentication mistakes and anti-patterns
  examples/                    Usage examples and integration guides
  security/                    Threat models and security boundary definitions
  testing/                     Test strategy and coverage requirements
```

## Dictionary

Core terms and their precise meanings in AvaX:

- [Authentication](dictionary/authentication.md)
- [Authorization](dictionary/authorization.md)
- [Token](dictionary/token.md)
- [Session](dictionary/session.md)
- [Credential](dictionary/credential.md)
- [Identity](dictionary/identity.md)
- [Tenant Context](dictionary/tenant-context.md)
- [MFA](dictionary/mfa.md)
- [Passkey](dictionary/passkey.md)
- [OAuth](dictionary/oauth.md)
- [OpenID Connect](dictionary/openid-connect.md)

## Architecture Decisions

Key decisions that shaped the Identity component:

- [ADR-0001: Authentication Strategy](adr/0001-authentication-strategy.md)
- [ADR-0002: Token Lifecycle](adr/0002-token-lifecycle.md)
- [ADR-0003: Session Management](adr/0003-session-management.md)

## Flows

Runtime behavior documentation:

- [Login Flow](flows/login-flow.md)
- [Token Validation Flow](flows/token-validation-flow.md)
- [Request Authentication Lifecycle](flows/request-authentication-lifecycle.md)
- [Tenant Resolution Lifecycle](flows/tenant-resolution-lifecycle.md)

## Diagrams

Visual representations of architecture and flows. See [diagrams/](diagrams/) for Mermaid source files.

## Common Mistakes

Anti-patterns and pitfalls to avoid:

- [Common Authentication Mistakes](mistakes/common-authentication-mistakes.md)
- [Token Security Mistakes](mistakes/token-security-mistakes.md)
- [Session Mistakes](mistakes/session-mistakes.md)

## Examples

Integration examples and usage patterns. See [examples/](examples/) for details.

## Security

Security documentation:

- [Threat Model](security/threat-model.md)
- [Security Boundaries](security/security-boundaries.md)

## Testing

Test strategy and requirements:

- [Test Strategy](testing/test-strategy.md)
