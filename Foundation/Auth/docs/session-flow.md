# Session Flow

Session auth is a strategy, not a second personality.

## Pieces

- `SessionIdentity`
- `SessionStoreInterface`
- `NativeSessionStore`
- `AuthenticationContext`

## Lifecycle

1. Successful login regenerates the session ID.
2. `SessionIdentity` stores only the user ID.
3. `AuthenticateRequest` resolves session state and projects it into `AuthenticationContext`.
4. `Logout` invalidates the session store.

## Cookie Policy

`NativeSessionStore` applies explicit cookie settings for:

- `secure`
- `httponly`
- `samesite`

Custom session backends can implement `SessionStoreInterface`.
