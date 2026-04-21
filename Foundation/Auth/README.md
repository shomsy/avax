# Avax Auth

Pure PHP 8.3+ auth kernel with one obvious ingress, immutable auth context, separate web-session and API-token lanes,
first-class MFA, package-owned OAuth, OIDC, SCIM, and tenant-security flows, workload identity, adapter-first passkeys
and federation, deterministic risk, admin elevation, and a thin optional integration surface.

## What Changed

- `Auth::login()` now returns `AuthenticationResult`, not an internal `User` entity.
- `Auth::authenticateRequest()` is the canonical request ingress for bearer token/session resolution.
- `Auth::current()`, `Auth::check()`, and `Auth::user()` now read one immutable `AuthenticationContext`.
- Session and JWT now share the same public result model, logout path, and refresh/revocation lifecycle.
- OAuth v1 now owns client registration, authorization-code issuance, PKCE verification, refresh exchange,
  `client_credentials`, introspection, token revocation, sender-constrained token binding metadata, workload inventory,
  explicit client policy posture, and tenant-owned client update/disable/secret-rotation flows.
- OIDC now owns provider metadata, JWKS publication, RS256 ID-token issuance, nonce enforcement, and userinfo reads
  behind a dedicated provider seam.
- `Access` now supports composed access policies with role, permission, resource-owner, fresh-MFA, admin-elevation,
  and actor-tier assurance policies.
- Passkeys now own registration/authentication plus listing, rename, and revoke flows behind a runtime contract.
- Federation now owns tenant-aware connection registration, domain verification, metadata sync, health checks,
  discovery, start/complete login, JIT linking, break-glass policy evaluation, and group-to-role mapping.
- SCIM now owns directory registration, token rotation, user provisioning, delete, derived group projection, bulk user
  operations, group sync, idempotency, drift detection, and explicit account-state semantics behind a package-owned
  runtime lane.
- Tenant security now owns requested, approved, applied, and rolled-back security configuration changes with auditable
  config diffs and rollout versioning, while the tenant product lane owns tenant/member/invite/owner-transfer behavior.
- Admin realm, provisioning, deterministic risk, and cleanup/export maintenance flows are package-owned slices.
- Password reset, email verification, MFA enrollment/challenge/recovery, refresh rotation, audit events, and
  anti-enumeration flows are package-owned.
- Assurance, privacy retention, authorization hardening, and crypto lifecycle now have first-class repo docs instead of
  being implied follow-up work.
- Release hardening now has repo-owned dependency review, rollback evidence, SBOM, provenance, secret-scan tooling, and
  CI workflows instead of doc-only intent.
- Canonical shipped-state now lives in `docs/STATUS.md`, with evidence in
  `docs/capability-matrix.md` and scope positioning in `docs/product-boundary.md`.

## Quick Start

```php
use Avax\Auth\System\Auth;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Data\VerifyMfaChallengeData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Totp\Totp;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentity;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryRefreshTokenStore;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryTokenRevocationStore;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Foundation\Clock;
use DateTimeImmutable;

$refreshTokens = new InMemoryRefreshTokenStore();
$totp = new Totp();

$auth = Auth::configuration()
    ->forUser($userSource)
    ->withIdentityBackends(
        sessionIdentity: new SessionIdentity(),
        jwtIdentity: new JwtIdentity(
            userSource: $userSource,
            codec: new HmacTokenCodec(secret: 'change-me'),
            clock: new Clock(),
            revocationStore: new InMemoryTokenRevocationStore(),
            refreshTokenStore: $refreshTokens,
        ),
    )
    ->withRefreshTokenStore($refreshTokens)
    ->usingTotp($totp)
    ->ready();

$login = $auth->login(new Credentials(
    identifier: 'user@example.com',
    password: 'secret',
));

if ($login->requiresMfa()) {
    $login = $auth->verifyMfaChallenge(
        new VerifyMfaChallengeData(
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

### Optional Capability Example

```php
$auth = Auth::configuration()
    ->forUser($userSource)
    ->withIdentityBackends(jwtIdentity: $jwtIdentity)
    ->withRefreshTokenStore($refreshTokens)
    ->withOidcProvider($oidcProvider)
    ->withPasskeyRuntime($passkeyRuntime)
    ->ready();

if ($auth->externalIdentity()->oidc()->isConfigured()) {
    $metadata = $auth->readOidcProviderMetadata();
}
```

### Avax Container Adapter

```php
use Avax\Auth\Integrations\AvaxContainer\AuthServiceProvider;
use Avax\Auth\System\AuthInterface;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Container\Core\AppFactory;

$app = AppFactory::cli(
    providers: [AppDependencies::class, AuthServiceProvider::class],
    cacheDir: __DIR__ . '/var/cache'
);

$auth = $app->get(AuthInterface::class);
```

## Optional Capability Readiness

- OAuth requires a JWT identity backend plus a refresh token store.
- OIDC metadata, JWKS, and userinfo require an OIDC provider; PAR, logout, and JARM stay optional within that surface.
- Federation/SSO requires a federation runtime and related stores.
- SCIM requires a provisionable user source; provisioning lifecycle rides the same capability family.
- Passkeys require a passkey runtime plus credential and challenge stores.
- Optional capability owners expose readiness methods such as `isConfigured()` and fail with explicit
  capability-unavailable exceptions instead of generic late `RuntimeException` errors.

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
- `updateOAuthClient(UpdateClientData): OAuthClient`
- `disableOAuthClient(string): OAuthClient`
- `rotateOAuthClientSecret(string): RegisteredOAuthClient`
- `readOAuthClients(): list<OAuthClient>`
- `readWorkloadIdentities(): list<WorkloadIdentityProfile>`
- `authorizeOAuthCode(AuthorizeCodeData): IssuedAuthorizationCode`
- `exchangeOAuthCode(ExchangeAuthorizationCodeData): OAuthTokenGrant`
- `exchangeOAuthClientCredentials(ExchangeClientCredentialsData): OAuthTokenGrant`
- `exchangeOAuthRefreshToken(ExchangeRefreshTokenData): OAuthTokenGrant`
- `revokeOAuthToken(RevokeTokenData): void`
- `introspectOAuthToken(IntrospectTokenData): TokenIntrospection`
- `readOidcProviderMetadata(): OidcProviderMetadata`
- `readOidcJsonWebKeySet(): OidcJsonWebKeySet`
- `readOidcUserInfo(string): OidcUserInfo`
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
- `registerScimDirectory(RegisterScimDirectoryData): RegisteredScimDirectory`
- `rotateScimToken(string): RotatedScimToken`
- `provisionScimUser(ProvisionScimUserData): ScimProvisioningResult`
- `deleteScimUser(DeleteScimUserData): void`
- `readScimUsers(string): list<ScimUserProjection>`
- `readScimGroups(string): list<ScimGroupProjection>`
- `syncScimGroups(SyncScimGroupsData): ScimProvisioningResult`
- `runScimBulk(ScimBulkRequest): ScimBulkResponse`
- `createTenant(CreateTenantData): Tenant`
- `readTenants(): list<Tenant>`
- `inviteTenantMember(InviteTenantMemberData): IssuedTenantInvite`
- `acceptTenantInvite(AcceptTenantInviteData): TenantMember`
- `readTenantMembers(string): list<TenantMember>`
- `suspendTenantMember(SuspendTenantMemberData): TenantMember`
- `removeTenantMember(RemoveTenantMemberData): void`
- `transferTenantOwnership(TransferTenantOwnershipData): Tenant`
- `readTenantSecurityConfiguration(string): ?TenantSecurityConfiguration`
- `beginTenantSecurityChange(BeginTenantSecurityChangeData): TenantSecurityChangeRequest`
- `approveTenantSecurityChange(string, string): TenantSecurityChangeRequest`
- `applyTenantSecurityChange(string): TenantSecurityConfiguration`
- `rollbackTenantSecurityChange(string): TenantSecurityConfiguration`
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

Canonical architecture documents now live in:

- `docs/architecture/`
- `docs/decisions/`
- `docs/flows/`
- `docs/security/`

Runtime ownership lives in auth-flow slices:

- `System/Flows/` exposes the canonical shared auth story roots; supporting ingress, password-reset, and
  email-verification runtime lives beneath those story roots instead of separate top-level slices.
- `System/Capabilities/Access/` owns authorization boundaries and composed access-policy evaluation, including
  `Authentication/`, `Authorization/`, and `RiskBasedAccess/`, plus runtime posture and risk support beneath those
  owner zones.
- `System/Capabilities/Identity/` owns MFA, passkey, password hashing, session registry, token runtime, and internal
  user state without introducing additional top-level capability roots.
- `System/Capabilities/ExternalIdentity/` owns OAuth, OIDC, federation, sender-constrained runtime, and protocol
  support beneath `OAuth/`, `OpenIDConnect/`, and `SingleSignOn/`.
- `System/Capabilities/IdentitySync/` owns SCIM runtime, provisioning support, and lifecycle orchestration.
- `System/Capabilities/Tenancy/` owns admin realm, tenant model, invitations, membership, ownership transfer, and
  tenant security runtime.
- `System/Capabilities/Diagnostics/` owns audit events and explainability surfaces without becoming a second source of
  truth.
- `integrations/http/` maps transport input, safe failures, and sender-constrained request verification without leaking
  HTTP concerns into the kernel, including framework-neutral OIDC, SCIM, and tenant-security admin surfaces.
- `integrations/diagnostics/` owns export and notification adapters for JSON lines, syslog, webhook, queue, and
  security-notification delivery.
- `integrations/release/` owns dependency review, provenance, rollback evidence, signing, and key-drill tooling.
- `integrations/avax-container/` is the optional container adapter.

See [docs/boundary.md](docs/boundary.md) for the final kernel vs integration boundary, API freeze, target tree, and
non-goals.

## Security Notes

- Session fixation protection via session ID regeneration on login.
- Idle and absolute session lifetime enforcement with tracked-session revocation hooks.
- Active session listing, targeted session revoke, and logout-all flow support.
- Token revocation and refresh rotation through package-owned stores.
- OAuth public clients require PKCE and OAuth refresh reuse revokes the full token family.
- Workload clients can use `client_credentials` with per-service audience and scope ceilings.
- High-assurance OAuth clients can require phishing-resistant auth before authorization-code issuance.
- OAuth clients can require sender-constrained access and refresh tokens with DPoP or mTLS binding metadata.
- OIDC `openid` requests require a nonce, authorization-code exchange can emit RS256 ID tokens, and `Auth` can expose
  discovery metadata, JWKS, and userinfo for relying parties.
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
- SCIM directory tokens rotate safely, provisioning updates are idempotent, drift is detectable, and provisioning
  actions are audited.
- Tenant security changes require explicit request, approval, apply, and rollback flow ownership with auditable diffs.
- Deterministic risk rules flag new environments and refresh-token reuse for review-oriented follow-up.
- Auth context is immutable and password hashes never leave the internal `User` entity.
- HMAC JWTs can carry an explicit `kid` key version for rollover-aware deployments.
- `MultiKeyHmacTokenCodec` supports overlap verification during signing-key rollover windows.
- `RotatingOidcProvider` supports overlap JWKS publication and legacy ID-token verification during OIDC signing-key
  rollover windows.

## Delivery Docs

- [docs/STATUS.md](docs/STATUS.md)
- [docs/product-boundary.md](docs/product-boundary.md)
- [docs/capability-matrix.md](docs/capability-matrix.md)
- [docs/adr/001-auth-scope-and-trust-boundaries.md](docs/adr/001-auth-scope-and-trust-boundaries.md)
- [docs/assurance-policy.md](docs/assurance-policy.md)
- [docs/authorization-hardening.md](docs/authorization-hardening.md)
- [docs/audit-export-operations.md](docs/audit-export-operations.md)
- [docs/federation-operations.md](docs/federation-operations.md)
- [docs/high-assurance-admin-examples.md](docs/high-assurance-admin-examples.md)
- [docs/oidc-conformance-matrix.md](docs/oidc-conformance-matrix.md)
- [docs/oidc-key-rollover-runbook.md](docs/oidc-key-rollover-runbook.md)
- [docs/privacy-retention-policy.md](docs/privacy-retention-policy.md)
- [docs/crypto-key-lifecycle.md](docs/crypto-key-lifecycle.md)
- [docs/oidc-provider-boundary.md](docs/oidc-provider-boundary.md)
- [docs/scim-runtime-boundary.md](docs/scim-runtime-boundary.md)
- [docs/tenant-control-plane.md](docs/tenant-control-plane.md)
- [docs/release-hardening.md](docs/release-hardening.md)
- [docs/certification-profile.md](docs/certification-profile.md)
- [docs/upgrade-migration-guide.md](docs/upgrade-migration-guide.md)
- [docs/supported-deployment-profiles.md](docs/supported-deployment-profiles.md)
- [docs/choose-vs-external-idp.md](docs/choose-vs-external-idp.md)
- [docs/sso-cutover-runbook.md](docs/sso-cutover-runbook.md)
- [docs/verification-matrix.md](docs/verification-matrix.md)
- [docs/workload-identity.md](docs/workload-identity.md)
- [docs/threat-model.md](docs/threat-model.md)
- [docs/implementation-roadmap.md](docs/implementation-roadmap.md)

## Optional Adapter

`integrations/avax-container/AuthServiceProvider.php` is an optional Avax Container adapter. Core runtime does not
require that package.
