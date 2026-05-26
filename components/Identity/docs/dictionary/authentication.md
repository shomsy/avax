# Authentication

## What It Is

Authentication is the process of verifying that an entity (user, service, or system) is who or what it claims to be. In AvaX, authentication produces a verified identity context that downstream systems can trust.

Authentication answers the question: "Who are you?" and proves it.

## What It Is NOT

- Authentication is NOT authorization. Proving identity does not grant permission.
- Authentication is NOT session management. A session is a mechanism for maintaining authenticated state, not authentication itself.
- Authentication is NOT token generation. Tokens are a transport mechanism for carrying authenticated identity.
- Authentication is NOT tenant resolution. Tenant context is a separate concern that may use authenticated identity as input.

## Common Confusion

People often conflate authentication with the entire login experience. Authentication is specifically the verification step. The login flow includes authentication, but also includes credential collection, multi-step challenges (MFA), session creation, and token issuance.

Another common confusion is treating the presence of a token as proof of authentication. A token is only as trustworthy as the authentication process that produced it.

## In AvaX

AvaX treats authentication as a distinct capability that:

- Accepts credentials through a defined boundary
- Verifies credentials against a credential store
- Produces a verified identity claim
- Delegates session and token concerns to separate capabilities
- Fails closed on any verification uncertainty
- Produces observable events for security monitoring

Authentication in AvaX is pluggable. The authentication capability accepts any credential type but always produces the same verified identity output format.
