# Avax Auth

Pure PHP 8.3+ auth kernel with one obvious ingress, immutable auth context, separate web-session and API-token lanes,
first-class MFA, package-owned OAuth client/token flows, adapter-first passkeys and federation, deterministic risk,
admin elevation, and a thin optional integration surface.

## What Changed

- `Auth::login()` now returns `AuthenticationResult`, not an internal `User` entity.
- `Auth::authenticateRequest()` is the canonical request ingress for bearer token/session resolution.
- `Auth::current()`, `Auth::check()`, and `Auth::user()` now read one immutable `AuthenticationContext`.
- Session and JWT now share the same public result model, logout path, and refresh/revocation lifecycle.
- OAuth v1 now owns client registration, authorization-code issuance, PKCE verification, refresh exchange,
  introspection, token revocation, sender-constrained token binding metadata, and explicit client policy posture.
- `Access` now supports composed access policies with role, permission, resource-owner, fresh-MFA, admin-elevation,
  and actor-tier assurance policies.
- Passkeys now own registration/authentication plus listing, rename, and revoke flows behind a runtime contract.
- Federation now owns tenant-aware connection registration, domain verification, metadata sync, health checks,
  discovery, start/complete login, JIT linking, break-glass policy evaluation, and group-to-role mapping.
- Admin realm, provisioning, deterministic risk, and cleanup/export maintenance flows are package-owned slices.
- Password reset, email verification, MFA enrollment/challenge/recovery, refresh rotation, audit events, and
  anti-enumeration flows are package-owned.
- Assurance, privacy retention, authorization hardening, and crypto lifecycle now have first-class repo docs instead of
  being implied follow-up work.

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
- `logoutAllSessions(): void`
- `readActiveSessions(): list<ActiveSession>`
- `revokeSession(string): void`
- `access(): AccessInterface`
- `changePassword(ChangePasswordData): void`
- `beginEmailChange(BeginEmailChangeData): EmailChangeChallenge`
- `confirmEmailChange(ConfirmEmailChangeData): bool`
- `register(RegistrationData): RegistrationResult`
- `refresh(RefreshAuthenticationRequest): AuthenticationResult`
- `registerOAuthClient(RegisterClientData): RegisteredOAuthClient`
- `readOAuthClients(): list<OAuthClient>`
- `authorizeOAuthCode(AuthorizeCodeData): IssuedAuthorizationCode`
- `exchangeOAuthCode(ExchangeAuthorizationCodeData): OAuthTokenGrant`
- `exchangeOAuthRefreshToken(ExchangeRefreshTokenData): OAuthTokenGrant`
- `revokeOAuthToken(RevokeTokenData): void`
- `introspectOAuthToken(IntrospectTokenData): TokenIntrospection`
- `beginAdminElevation(): AdminElevation`
- `endAdminElevation(): void`
- `requireAdminElevation(): void`
- `suspendUser(int): void`
- `reactivateUser(int): void`
- `deprovisionUser(int): void`
- `beginPasskeyRegistration(): PasskeyRegistration`
- `completePasskeyRegistration(CompletePasskeyRegistrationData): PasskeyCredential`
- `beginPasskeyAuthentication(BeginPasskeyAuthenticationData): PasskeyAuthenticationChallenge`
- `completePasskeyAuthentication(CompletePasskeyAuthenticationData): AuthenticationResult`
- `readPasskeys(): list<PasskeyCredential>`
- `renamePasskey(RenamePasskeyData): PasskeyCredential`
- `revokePasskey(string): void`
- `registerFederationConnection(RegisterFederationConnectionData): FederationConnection`
- `readFederationConnections(): list<FederationConnection>`
- `verifyFederationDomain(VerifyFederationDomainData): FederationConnection`
- `syncFederationMetadata(string): FederationConnection`
- `checkFederationConnectionHealth(string): FederationConnectionHealth`
- `evaluateFederationBreakGlassBypass(string): bool`
- `discoverFederationConnection(string): ?FederationConnection`
- `startFederatedLogin(StartFederatedLoginData): StartedFederatedLogin`
- `completeFederatedLogin(CompleteFederatedLoginData): AuthenticationResult`
- `assessCurrentRisk(?string, ?string): ?RiskDecision`
- `readRiskSignals(?int): list<RiskSignal>`
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
- `System/Flow/Login/`, `Register/`, `Logout/`, `Recover/`, `ChangeEmail/`, `Verify/`, `Mfa/`, `Token/`, `Session/`,
  `AdminRealm/`, `Passkey/`, `Federation/`, `Provisioning/`, and `Risk/` own package behavior.
- `System/Capability/Access/` owns authorization boundaries and composed access-policy evaluation.
- `System/Capability/OAuth/` owns client registry and authorization-code persistence contracts.
- `System/Flow/OAuth/` owns client registration, authorization-code issuance, token exchange, revoke, and introspection.
- `System/Capability/Session/` owns durable tracked-session state and revocation contracts.
- `System/Capability/Passkey/` owns credential and challenge contracts; runtime verification stays behind
  `PasskeyRuntimeInterface`.
- `System/Capability/Federation/` owns tenant-aware connection and identity-link contracts; protocol execution stays
  behind `FederationRuntimeInterface`.
- `System/Flow/*/CleanupExpired*/` and `Flow/Diagnostics/ExportAuditEvents/` own package-local maintenance jobs without
  creating a global operations bucket.
- `System/Capability/Identity/` now only coordinates strategy issuance/clear semantics.
- `System/Capability/User/` stays internal domain state; public auth output is `AuthenticatedUser`.
- `System/Flow/Diagnostics/` owns audit events without becoming a second source of truth.
- `integrations/http/` maps transport input, safe failures, and sender-constrained request verification without leaking
  HTTP concerns into the kernel.
- `integrations/diagnostics/` owns export and notification adapters for JSON lines, syslog, webhook, queue, and
  security-notification delivery.
- `integrations/avax-container/` is the optional container adapter.

See [docs/boundary.md](docs/boundary.md) for the final kernel vs integration boundary, API freeze, target tree, and
non-goals.

## Security Notes

- Session fixation protection via session ID regeneration on login.
- Idle and absolute session lifetime enforcement with tracked-session revocation hooks.
- Active session listing, targeted session revoke, and logout-all flow support.
- Token revocation and refresh rotation through package-owned stores.
- OAuth public clients require PKCE and OAuth refresh reuse revokes the full token family.
- High-assurance OAuth clients can require phishing-resistant auth before authorization-code issuance.
- OAuth clients can require sender-constrained access and refresh tokens with DPoP or mTLS binding metadata.
- HTTP adapters now include DPoP proof verification, mTLS binding verification, and explicit sender-constraint
  enforcement for protected API requests.
- Password reset and login failures keep safe public messages.
- Password reset begin flow is anti-enumeration by default.
- Email change requires the current password, fresh MFA, and revokes the current auth/session family on confirmation.
- MFA uses TOTP with replay protection, backup codes, recovery tokens, step-up freshness checks, and per-user challenge
  throttling.
- Passkeys support multiple credentials per account, user-owned rename/revoke, and strict challenge replay prevention.
- Admin-sensitive actions can be expressed through `AccessPolicy` and enforced with explicit phishing-resistant,
  fresh-MFA, and admin-elevation policy.
- Federation login now requires verified domains for discovery/start, records metadata and health state, and can JIT
  link or create users.
- Deterministic risk rules flag new environments and refresh-token reuse for review-oriented follow-up.
- Auth context is immutable and password hashes never leave the internal `User` entity.
- HMAC JWTs can carry an explicit `kid` key version for rollover-aware deployments.
- `MultiKeyHmacTokenCodec` supports overlap verification during signing-key rollover windows.

## Delivery Docs

- [docs/adr/001-auth-scope-and-trust-boundaries.md](docs/adr/001-auth-scope-and-trust-boundaries.md)
- [docs/assurance-policy.md](docs/assurance-policy.md)
- [docs/authorization-hardening.md](docs/authorization-hardening.md)
- [docs/audit-export-operations.md](docs/audit-export-operations.md)
- [docs/federation-operations.md](docs/federation-operations.md)
- [docs/high-assurance-admin-examples.md](docs/high-assurance-admin-examples.md)
- [docs/privacy-retention-policy.md](docs/privacy-retention-policy.md)
- [docs/crypto-key-lifecycle.md](docs/crypto-key-lifecycle.md)
- [docs/oidc-provider-boundary.md](docs/oidc-provider-boundary.md)
- [docs/scim-runtime-boundary.md](docs/scim-runtime-boundary.md)
- [docs/tenant-control-plane.md](docs/tenant-control-plane.md)
- [docs/release-hardening.md](docs/release-hardening.md)
- [docs/sso-cutover-runbook.md](docs/sso-cutover-runbook.md)
- [docs/verification-matrix.md](docs/verification-matrix.md)
- [docs/workload-identity.md](docs/workload-identity.md)
- [docs/threat-model.md](docs/threat-model.md)
- [docs/implementation-roadmap.md](docs/implementation-roadmap.md)

## Optional Adapter

`integrations/avax-container/AuthServiceProvider.php` is an optional Avax Container adapter. Core runtime does not
require that package.
