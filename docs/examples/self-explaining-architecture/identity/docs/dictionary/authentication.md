# Authentication

## What It Is

Authentication is the process of verifying that a user or system is who they claim to be. In AvaX, authentication happens once at the start of a request (or at login) and produces a verified identity context.

## Why It Exists

Without authentication, any user could claim to be any other user. Authentication establishes trust before any authorization decision or data access.

## Real-World Analogy

Authentication is like showing your ID at airport security. They check that the photo matches your face and the ID is valid. Once verified, you can proceed to your gate. You do not need to show ID again until you reach the next security checkpoint.

## Ownership

Owned by the Identity component, Authentication capability.

## Common Confusion

People often confuse authentication with authorization. Authentication answers "who are you?" Authorization answers "what are you allowed to do?" They are separate steps with separate responsibilities.

## What It Is NOT

- Authentication is NOT authorization (access control)
- Authentication is NOT session management (state after authentication)
- Authentication is NOT identity storage (user database)
- Authentication is NOT token issuance (tokens are credentials, not identity verification)

## Common Mistakes

1. **Mixing authentication and authorization logic** — They must be separate capabilities.
2. **Short-circuiting authentication** — Allowing unauthenticated access because the route seems harmless.
3. **Token vs session confusion** — Token validation is not the same as session validation.

## Relation to Other Concepts

- **Authorization**: Depends on authentication (must know who before deciding what)
- **Session**: Created after successful authentication
- **Token**: A credential used to authenticate API requests
- **MFA**: An extension of authentication with additional factors

## Where It Appears in Code

- Namespace: `AvaX\Components\Identity\Capabilities\Authenticate`
- Key classes: `Authenticate`, `AuthenticatedContext`, `AuthenticationResult`
- Key interfaces: `AuthenticatorInterface`
