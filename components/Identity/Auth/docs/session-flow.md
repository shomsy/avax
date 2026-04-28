# Session Flow

Session auth is a strategy, not a second personality.

## Pieces

- `SessionIdentity`
- `Capability/Session/SessionRegistryInterface`
- `SessionStoreInterface`
- `NativeSessionStore`
- `AuthenticationContext`
- `ActiveSession`

## Lifecycle

1. Successful login regenerates the session ID.
2. `SessionIdentity` stores only the user ID.
3. Optional `SessionRegistryInterface` tracks durable session ownership, last-seen state, and revocation.
4. `AuthenticateRequest` resolves session state and projects it into `AuthenticationContext`.
5. `Logout`, `LogoutAllSessions`, `RevokeSession`, and password-reset flows revoke tracked session state.

## Cookie Policy

`NativeSessionStore` applies explicit cookie settings for:

- `secure`
- `httponly`
- `samesite`

Custom session backends can implement `SessionStoreInterface`.

## Session Management Flows

- `readActiveSessions()` returns the current user's tracked active sessions.
- `revokeSession(string $sessionId)` revokes one owned tracked session.
- `logoutAllSessions()` revokes all tracked sessions for the current user.
