# OAuth

## What It Is

OAuth (Open Authorization) is a delegation protocol that allows a resource owner to grant a third-party application limited access to their resources without sharing credentials. OAuth 2.0 is the current version.

OAuth enables scenarios like "Sign in with Google" or "Allow this app to access your GitHub repos."

## What It Is NOT

- OAuth is NOT authentication. OAuth delegates authorization, not identity. OAuth tells you that someone was granted access, not who they are.
- OAuth is NOT OpenID Connect. OIDC is an identity layer built on top of OAuth. OAuth alone does not provide a verified identity.
- OAuth is NOT a replacement for local credentials. OAuth is an external identity source that may be used to establish local identity.
- OAuth is NOT a session mechanism. OAuth provides tokens that may be used within sessions, but OAuth is not session management.

## Common Confusion

The most critical confusion is treating OAuth as authentication. OAuth 2.0 authorizes access to resources; it does not authenticate the user's identity. Using OAuth access tokens as identity proof without OIDC leads to identity confusion attacks.

Another confusion is treating all OAuth flows as equivalent. The Authorization Code flow with PKCE is appropriate for public clients. The Implicit flow is deprecated. Client Credentials is for service-to-service communication. Choosing the wrong flow creates security vulnerabilities.

## In AvaX

AvaX treats OAuth as:

- An external identity provider integration point
- A source of identity claims when used with OIDC
- A credential type that feeds into the authentication capability
- Subject to strict flow selection (PKCE for public clients, no Implicit)
- Observable: OAuth flow initiation and completion produce security events

OAuth integration in AvaX produces the same verified identity output as any other credential type. The OAuth capability handles the protocol specifics; the authentication capability produces the identity.
