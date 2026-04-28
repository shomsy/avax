# Security Rules

## Core Rules

- Never expose internal `User` entities at the public auth boundary.
- Use `AuthenticationContext` and `AuthenticatedUser` for current auth state.
- Keep login, reset, and verify failures safe and boring.
- Centralize session and bearer-token resolution inside `AuthenticateRequest`.
- Regenerate session IDs on login and invalidate sessions on logout.
- Track server-side sessions in an owned registry when the application provides one.
- Rotate refresh tokens and revoke reused families.
- Prefer phishing-resistant auth for privileged posture and enforce it
  explicitly where policy requires it.
- Prefer sender-constrained tokens for high-security API clients.
- Do not log raw passwords, access tokens, refresh tokens, or MFA codes.
- Do not log TOTP secrets, otpauth URIs, recovery tokens, or plain backup codes.

## Implemented Controls

- login rate limiting
- recovery throttling for password reset and MFA recovery
- anti-enumeration password reset begin flow
- immutable auth context and public auth user snapshot
- access token revocation
- refresh token rotation and reuse detection
- sender-constrained OAuth access and refresh token binding metadata
- secure logout invalidation
- tracked-session listing, revoke, logout-all, and idle/absolute expiry enforcement
- email verification state and one-time challenges
- TOTP MFA enrollment with first-proof activation
- MFA challenge throttling, replay protection, backup codes, recovery tokens, and fresh-MFA step-up guards
- audit events for login, logout, reset, verify, refresh, and MFA actions
- actor-tier assurance catalog for user, admin, tenant-admin, support, break-glass, and machine posture
- token key-version support through `kid` in the HMAC JWT codec

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
