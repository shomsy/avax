# ADR-0002: Token Lifecycle

## Status

proposed

## Context

AvaX needs a token lifecycle management strategy that supports:
- Token issuance after successful authentication
- Token validation on every request
- Token refresh for extended sessions
- Token revocation for security events (logout, password change, compromise)
- Multiple token types (access tokens, refresh tokens, optionally ID tokens)
- Stateless or semi-stateless validation for performance
- Long-lived worker safety (no token state leakage between requests)

The challenge is balancing security (short-lived tokens, revocable) with performance (stateless validation) and developer experience (automatic refresh).

## Decision

AvaX will use a dual-token strategy:

1. **Access tokens**: Short-lived (configurable, default 15 minutes), self-contained (JWT-like), validated statelessly
2. **Refresh tokens**: Longer-lived, reference-based (stored server-side), used to obtain new access tokens
3. **Token validation** is a distinct capability that validates signature, expiry, issuer, audience, and scope before trusting any token
4. **Token revocation** operates on refresh tokens (server-side deletion) and may include an access token deny list for immediate revocation scenarios
5. **Token issuance** is bound to the authenticated identity and optionally tenant context

Token validation fails closed on any uncertainty: invalid signature, expired token, unknown issuer, mismatched audience, or revoked token.

## Consequences

- Access tokens can be validated without database access (stateless)
- Refresh tokens require server-side storage for revocation
- Token compromise is limited by access token expiry
- Immediate revocation requires deny list check or refresh token deletion
- Long-lived workers must not cache token validation state between requests
- Token validation capability is independently testable and reusable

## Trade-offs

Gained:
- Stateless access token validation for performance
- Revocable refresh tokens for security
- Clear separation between token validation and token issuance
- Observable token lifecycle events

Given up:
- Simplicity of single-token approach
- Pure statelessness (refresh tokens require server-side state)
- Instant access token revocation without deny list overhead
- Some developer convenience (automatic token refresh must be explicit)
