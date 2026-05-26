# Token

## What It Is

A token is a self-contained or reference-based credential that carries or represents authenticated identity across system boundaries. Tokens enable stateless or semi-stateless authentication between requests, services, or systems.

Tokens have a lifecycle: issuance, validation, use, and revocation/expiry.

## What It Is NOT

- A token is NOT authentication. A token carries the result of authentication; it does not perform authentication.
- A token is NOT a session. A session is server-side state. A token may be stateless (like JWT) or reference a session.
- A token is NOT authorization. A token may carry claims used in authorization decisions, but the token itself does not decide permissions.
- A token is NOT a secret. Tokens are presented by clients and must be assumed visible to the client. Secrets are server-held credentials.

## Common Confusion

The most dangerous confusion is treating token presence as proof of legitimacy. Any client can present any token string. The system must validate the token's signature, expiry, issuer, audience, and scope before trusting it.

Another common confusion is assuming JWT tokens can be instantly revoked. JWT tokens are self-contained and cannot be revoked without an additional mechanism (deny list, short expiry with refresh, or session binding).

## In AvaX

AvaX treats tokens as:

- Carriers of verified identity claims between requests
- Objects with explicit lifecycle management (issue, validate, expire, revoke)
- Either self-contained (JWT-like) or reference-based (opaque)
- Bound to specific audiences, issuers, and scopes
- Subject to strict validation before any trust is granted
- Observable at issuance, validation, and revocation points

Token validation in AvaX is a distinct capability from token issuance. The validation capability must be usable independently by any service that needs to verify presented tokens.
