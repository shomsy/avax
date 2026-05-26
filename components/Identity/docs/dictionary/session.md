# Session

## What It Is

A session is server-side state that represents an authenticated user's active interaction with the system. Sessions track identity, context, and optionally additional state across multiple requests.

Sessions have a lifecycle: creation, active use, renewal, and termination.

## What It Is NOT

- A session is NOT a token. A session is server-side state. A token (like a session ID cookie) is the client-side reference to that session.
- A session is NOT authentication. A session stores the result of authentication; it does not perform it.
- A session is NOT authorization. Session state may inform authorization, but the session itself does not decide permissions.
- A session is NOT persistent storage. Sessions are transient and lifecycle-bound, not a database.

## Common Confusion

A common confusion is storing too much state in sessions. Sessions should carry identity and minimal context, not application data. Overloaded sessions become difficult to invalidate, migrate, and reason about.

Another confusion is treating session ID as security. The session ID is merely a reference. Session security comes from how the server manages session creation, validation, renewal, and termination.

## In AvaX

AvaX treats sessions as:

- Server-side state with a clear owner and lifecycle
- Bounded to a specific authenticated identity and optionally a tenant context
- Subject to explicit expiry, renewal, and termination policies
- Accessible through a defined session capability, not through raw storage
- Observable at creation, renewal, and termination points
- Cleanable on security events (password change, logout, revocation)

Sessions in AvaX are separate from tokens. A session may be referenced by a token, but the session capability owns the server-side state.
