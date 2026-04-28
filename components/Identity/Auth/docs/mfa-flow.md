# MFA Flow

## Enrollment

1. `startMfaEnrollment()` creates one pending TOTP enrollment for the current user.
2. The returned `MfaEnrollment` exposes the TOTP secret and `otpauth://` payload for QR rendering.
3. `confirmMfaEnrollment()` accepts the first TOTP proof.
4. MFA is marked enabled only after that proof succeeds.
5. Backup codes are generated once and returned as `BackupCodeSet`.

## Login Challenge

1. Username/password is checked first.
2. If MFA is enabled, `login()` returns `AuthenticationResult::requiresMfa()`.
3. No final session or token is issued at that point.
4. `verifyMfaChallenge()` accepts either a valid TOTP or a one-time backup code.
5. Only then does the package issue the final auth state and set `mfaVerifiedAt`.

## Recovery And Step-Up

- `beginMfaRecovery()` is anti-enumeration and returns a hidden result for unknown users.
- `confirmMfaRecovery()` disables MFA, clears recovery state, forgets pending challenges, and revokes refresh tokens for
  that user.
- Sensitive actions such as `changePassword()`, `disableMfa()`, and `regenerateBackupCodes()` require fresh MFA when the
  user has MFA enabled.

## Public Contracts

- `MfaEnrollment`
- `ConfirmMfaEnrollmentData`
- `MfaChallenge`
- `VerifyMfaChallengeData`
- `BackupCodeSet`
- `MfaRecoveryChallenge`
- `BeginMfaRecoveryData`
- `ConfirmMfaRecoveryData`
