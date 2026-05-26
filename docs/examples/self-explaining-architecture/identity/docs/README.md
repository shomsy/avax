# Identity Component

> This component owns authentication, authorization, session management, token lifecycle, MFA, and tenant context for the entire AvaX platform.

## What Lives Here

- **PublicSurface/** — Public API for authentication, session, token, and MFA operations
- **Flows/** — Complete user-visible flows: Login, Logout, Register, MfaChallenge, PasswordReset
- **Capabilities/** — Reusable behavior: Authenticate, Authorize, IssueToken, ValidateToken, ManageSession
- **Configuration/** — Service providers, builders, assembly for identity services
- **Foundation/** — Value objects: Credential, TokenPayload, SessionId, TenantContext

## What Does NOT Live Here

- HTTP routing and middleware pipeline (belongs in HTTP component)
- User profile data storage (belongs in User/Profile component)
- Cryptographic primitives (belongs in Security/Cryptography component)
- General-purpose caching (belongs in Cache component)
- Business logic unrelated to identity (belongs in domain components)

## Ownership Model

Owned by the Platform Security team. Changes to authentication, authorization, token, or session behavior require security review. PublicSurface changes require API compatibility review.

## Architectural Intent

Identity is a security boundary. All authentication and authorization must flow through this component. No other component may issue tokens, validate sessions, or make authorization decisions without delegating through Identity's public surface.

## Common Mistakes

1. **Bypassing Identity for authorization** — Other components checking raw tokens or sessions directly. All authorization must go through Identity's authorization capability.
2. **Storing session state outside Identity** — Sessions are owned by Identity. Other components must not create, read, or modify session state directly.
3. **Token logic duplication** — Other components implementing their own token validation. Always delegate to Identity's TokenValidator.
4. **Mixing authentication and authorization** — Authentication (who you are) and authorization (what you can do) are separate concerns with separate capabilities.

## Entry Points

- Start with `PublicSurface/Auth.php` for authentication operations
- Start with `PublicSurface/Session.php` for session operations
- Start with `PublicSurface/Token.php` for token operations

## Key Terms

- **Authentication**: Verifying identity (who you are)
- **Authorization**: Verifying permissions (what you can do)
- **Session**: Stateful user context between requests
- **Token**: Stateless credential for API access
- **MFA**: Multi-factor authentication for elevated security

## Related

- **HTTP Component**: Consumes Identity for authentication middleware
- **Security Component**: Provides cryptographic primitives used by Identity
- **docs/examples/self-explaining-architecture/runtime**: Runtime depends on Identity for request authentication
