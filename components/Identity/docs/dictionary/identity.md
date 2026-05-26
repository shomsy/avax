# Identity

## What It Is

An identity is the verified representation of an entity (user, service, or system) within AvaX. Identity is the result of successful authentication and is the foundation for all authorization decisions.

An identity contains claims about the entity: unique identifier, attributes, roles, scopes, and optionally tenant context.

## What It Is NOT

- Identity is NOT a user record. A user record is a data entity. Identity is the authenticated representation derived from it.
- Identity is NOT authentication. Authentication is the process. Identity is the verified result.
- Identity is NOT a profile. A profile is user-facing personal data. Identity is the security-relevant claims used by the system.
- Identity is NOT authorization. Identity provides the subject for authorization decisions but does not make them.

## Common Confusion

People often treat the user database record as the identity. The identity is the verified claim set produced after authentication, not the raw database row. The identity may be derived from a user record, but it is a distinct concept with different lifecycle and security requirements.

Another confusion is assuming identity is static. Identity claims may change (role changes, tenant changes, scope changes) and the system must handle identity refresh without requiring re-authentication in all cases.

## In AvaX

AvaX treats identity as:

- A verified claim set produced by the authentication capability
- The subject for all authorization decisions
- Carried by tokens and sessions across request boundaries
- Immutable within a single request but refreshable across sessions
- Observable: identity creation and changes produce security events
- Tenant-aware: identity may be scoped to a specific tenant context

Identity in AvaX is the central concept that connects authentication (which produces it), authorization (which consumes it), and tenant resolution (which may scope it).
