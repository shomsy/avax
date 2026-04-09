# Security Rules

## Core Rules

- Never expose internal `User` entities at the public auth boundary.
- Use `AuthenticationContext` and `AuthenticatedUser` for current auth state.
- Keep login, reset, and verify failures safe and boring.
- Centralize session and bearer-token resolution inside `AuthenticateRequest`.
- Regenerate session IDs on login and invalidate sessions on logout.
- Rotate refresh tokens and revoke reused families.
- Do not log raw passwords, access tokens, refresh tokens, or MFA codes.
- Do not log TOTP secrets, otpauth URIs, recovery tokens, or plain backup codes.

## Implemented Controls

- login rate limiting
- anti-enumeration password reset begin flow
- immutable auth context and public auth user snapshot
- access token revocation
- refresh token rotation and reuse detection
- secure logout invalidation
- email verification state and one-time challenges
- TOTP MFA enrollment with first-proof activation
- MFA challenge throttling, replay protection, backup codes, recovery tokens, and fresh-MFA step-up guards
- audit events for login, logout, reset, verify, refresh, and MFA actions

## Extension Points

- `SessionStoreInterface`
- `TokenCodecInterface`
- `RefreshTokenStoreInterface`
- `TokenRevocationStoreInterface`
- `PasswordResetStoreInterface`
- `EmailVerificationStoreInterface`
- `EmailVerificationStateStoreInterface`
- `MfaStoreInterface`
- `Flow/Mfa/Challenge/MfaChallengeStoreInterface`
- `TotpInterface`
- `AuditLogInterface`
