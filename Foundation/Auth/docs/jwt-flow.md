# JWT Flow

JWT support is package-owned and no longer depends on `firebase/php-jwt`.

## Pieces

- `JwtIdentity`
- `HmacTokenCodec`
- `RefreshTokenStoreInterface`
- `TokenRevocationStoreInterface`
- `RefreshAuthentication`

## Lifecycle

1. `JwtIdentity::issue()` signs an access token and assigns a token ID.
2. Optional refresh tokens are issued from `RefreshTokenStoreInterface`.
3. `AuthenticateRequest` verifies bearer tokens through `JwtIdentity::resolve()`.
4. `Logout` and password reset can revoke token families through package stores.
5. `RefreshAuthentication` rotates refresh tokens and blocks reuse.

## Supported Output

JWT login still returns `AuthenticationResult`, the same shape used by session-backed or hybrid auth.
