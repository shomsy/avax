# Login Flow

## Happy Path

1. `Login` checks rate limit for the identifier.
2. `UserSourceInterface` resolves the internal `User`.
3. `PasswordHasher` verifies the password.
4. If MFA is enabled, `StartMfaChallenge` returns a challenge and the result stops at `requiresMfa`.
5. `VerifyMfaChallenge` accepts either a valid TOTP or a one-time backup code.
6. Only after MFA passes does `Identity` issue session state, access token, and optional refresh token.
7. `CurrentAuthentication` stores one immutable `AuthenticationContext`.
8. `AuthenticationResult` returns public auth data only.

## Failure Model

- Invalid username/email/password: `AuthenticationFailed`
- Rate limit exceeded: `RateLimitException`
- MFA required: successful `AuthenticationResult` with `requiresMfa() === true`
- MFA verification failure: `MfaChallengeFailed`

## Public Types

- `Credentials`
- `AuthenticationResult`
- `AuthenticationContext`
- `AuthenticatedUser`
- `MfaChallenge`
