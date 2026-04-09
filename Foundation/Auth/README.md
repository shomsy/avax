# Avax Auth

Pure PHP 8.3+ auth kernel with one obvious ingress, immutable auth context, interchangeable session/JWT runtime
strategies, first-class MFA, and a thin optional integration surface.

## What Changed

- `Auth::login()` now returns `AuthenticationResult`, not an internal `User` entity.
- `Auth::authenticateRequest()` is the canonical request ingress for bearer token/session resolution.
- `Auth::current()`, `Auth::check()`, and `Auth::user()` now read one immutable `AuthenticationContext`.
- Session and JWT now share the same public result model, logout path, and refresh/revocation lifecycle.
- Password reset, email verification, MFA enrollment/challenge/recovery, refresh rotation, audit events, and
  anti-enumeration flows are package-owned.

## Quick Start

```php
use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\Identity\Session\SessionIdentity;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\Mfa\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Flow\Mfa\Totp;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use DateTimeImmutable;

$refreshTokens = new InMemoryRefreshTokenStore();
$totp = new Totp();

$auth = Auth::configuration()
    ->forUser($userSource)
    ->withIdentity(new Identity(
        sessionIdentity: new SessionIdentity(),
        jwtIdentity: new JwtIdentity(
            userSource: $userSource,
            codec: new HmacTokenCodec(secret: 'change-me'),
            clock: new Clock(),
            revocationStore: new InMemoryTokenRevocationStore(),
            refreshTokenStore: $refreshTokens,
        ),
    ))
    ->withRefreshTokenStore($refreshTokens)
    ->usingTotp($totp)
    ->ready();

$login = $auth->login(new Credentials(
    identifier: 'user@example.com',
    password: 'secret',
));

if ($login->requiresMfa()) {
    $login = $auth->verifyMfaChallenge(
        new \Avax\Auth\System\Flow\Mfa\VerifyMfaChallengeData(
            challengeId: $login->mfaChallengeId() ?? '',
            code: $backupCodeOrTotp
        )
    );
}

$enrollment = $auth->startMfaEnrollment();
$backupCodes = $auth->confirmMfaEnrollment(
    new ConfirmMfaEnrollmentData(
        code: $totp->codeAt($enrollment->secret(), new DateTimeImmutable())
    )
);
```

## Public API

- `login(Credentials): AuthenticationResult`
- `authenticateRequest(AuthenticationRequest): AuthenticationContext`
- `current(): AuthenticationContext`
- `check(): bool`
- `user(): ?AuthenticatedUser`
- `logout(): void`
- `access(): AccessInterface`
- `changePassword(ChangePasswordData): void`
- `register(RegistrationData): RegistrationResult`
- `refresh(RefreshAuthenticationRequest): AuthenticationResult`
- `beginPasswordReset(BeginPasswordResetData): PasswordResetChallenge`
- `resetPassword(ResetPasswordData): bool`
- `beginEmailVerification(BeginEmailVerificationData): EmailVerificationChallenge`
- `verifyEmail(VerifyEmailData): bool`
- `startMfaEnrollment(): MfaEnrollment`
- `confirmMfaEnrollment(ConfirmMfaEnrollmentData): BackupCodeSet`
- `cancelMfaEnrollment(): void`
- `beginMfaChallenge(): MfaChallenge`
- `verifyMfaChallenge(VerifyMfaChallengeData): AuthenticationResult`
- `regenerateBackupCodes(): BackupCodeSet`
- `disableMfa(): void`
- `beginMfaRecovery(BeginMfaRecoveryData): MfaRecoveryChallenge`
- `confirmMfaRecovery(ConfirmMfaRecoveryData): void`

## Architecture

The package now has two explicit lanes:

- `System/` is the auth kernel.
- `integrations/` contains optional adapters.

Runtime ownership lives in auth-flow slices:

- `System/Flow/AuthenticateRequest/` owns ingress resolution and current auth context.
- `System/Flow/Login/`, `Register/`, `Logout/`, `Recover/`, `Verify/`, `Mfa/`, and `Token/` own package behavior.
- `System/Capability/Identity/` now only coordinates strategy issuance/clear semantics.
- `System/Capability/User/` stays internal domain state; public auth output is `AuthenticatedUser`.
- `System/Flow/Diagnostics/` owns audit events without becoming a second source of truth.
- `integrations/http/` maps transport input and safe failures without leaking HTTP concerns into the kernel.
- `integrations/avax-container/` is the optional container adapter.

See [docs/boundary.md](docs/boundary.md) for the final kernel vs integration boundary, API freeze, target tree, and
non-goals.

## Security Notes

- Session fixation protection via session ID regeneration on login.
- Token revocation and refresh rotation through package-owned stores.
- Password reset and login failures keep safe public messages.
- Password reset begin flow is anti-enumeration by default.
- MFA uses TOTP with replay protection, backup codes, recovery tokens, step-up freshness checks, and per-user challenge
  throttling.
- Auth context is immutable and password hashes never leave the internal `User` entity.

## Optional Adapter

`integrations/avax-container/AuthServiceProvider.php` is an optional Avax Container adapter. Core runtime does not
require that package.
