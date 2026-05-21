<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Authentication;

use Avax\Components\Identity\Access\System\Capabilities\Access;
use Avax\Components\Identity\Access\System\Capabilities\AccessInterface;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\RiskDecision;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\RiskSignal;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Diagnostics;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Explainability\AuthIssueExplanation;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\ActiveSession;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\IdentitySync;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\RegisteredScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkRequest;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkResponse;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUserData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutageData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ScimProvisioningResult;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups\ScimGroupProjection;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers\ScimUserProjection;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage\RecoverScimDirectoryOutageData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken\RotatedScimToken;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups\SyncScimGroupsData;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\BeginEmailChangeData;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\ConfirmEmailChangeData;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\EmailChangeChallenge;
use Avax\Components\Identity\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Components\Identity\Auth\System\Flows\ChangePassword\PasswordChangeFailed;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationRequest;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordResetData;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\PasswordResetChallenge;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPasswordData;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationData;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationFailed;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationResult;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerificationData;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationChallenge;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmailData;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Auth\System\PublicSurface\AuthInterface;
use Avax\Components\Identity\Auth\System\PublicSurface\User;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\BackupCodeSet;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Data\VerifyMfaChallengeData;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\ConfirmMfaEnrollmentData;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\MfaEnrollment;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\MfaChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\BeginMfaRecoveryData;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\ConfirmMfaRecoveryData;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\MfaRecoveryChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredential;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\PasskeyAuthenticationChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\PasskeyRegistration;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\RenamePasskey\RenamePasskeyData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentity;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\IssuedAuthorizationCode;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\RegisteredOAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistrationData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\AuthorizeCode\AuthorizeCodeData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeClientCredentials\ExchangeClientCredentialsData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeRefreshToken\ExchangeRefreshTokenData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\IntrospectToken\IntrospectTokenData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\IntrospectToken\TokenIntrospection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\OAuthTokenGrant;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ReadWorkloadIdentities\WorkloadIdentityProfile;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RegisterClient\RegisterClientData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RevokeToken\RevokeTokenData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\UpdateClient\UpdateClientData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcJsonWebKeySet;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderMetadata;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponseData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\JarmResponse\JarmResponse;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout\LogoutData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout\LogoutResult;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequestData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\PushAuthorizationRequest\PushedAuthorizationRequest;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadUserInfo\OidcUserInfo;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionHealth;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\StartedFederatedLogin;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\CompleteFederatedLogin\CompleteFederatedLoginData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\RegisterConnection\RegisterFederationConnectionData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\StartFederatedLogin\StartFederatedLoginData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\VerifyDomain\VerifyFederationDomainData;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\AdminElevation;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\AdminElevationFailed;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\Tenant;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantMember;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\AcceptInvite\AcceptTenantInviteData;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\CreateTenant\CreateTenantData;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\InviteMember\InviteTenantMemberData;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\InviteMember\IssuedTenantInvite;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\RemoveMember\RemoveTenantMemberData;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\SuspendMember\SuspendTenantMemberData;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\TransferOwnership\TransferTenantOwnershipData;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequest;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityConfiguration;
use Avax\Components\Identity\Tenancy\System\Capabilities\Tenancy;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Flow\RefreshAuthenticationRequest;
use DateMalformedStringException;
use JsonException;
use Random\RandomException;
use SensitiveParameter;

/**
 * Main entry point for Avax Auth.
 *
 * Exposes the most common authentication operations directly.
 * For capability-specific operations, navigate to the owning capability:
 *
 *   $auth->identity()->beginPasskeyRegistration();
 *   $auth->externalIdentity()->registerOAuthClient($data);
 *   $auth->tenancy()->createTenant($data);
 */
final readonly class DefaultAuth implements AuthInterface
{
    public function __construct(
        #[SensitiveParameter]
        private Access $access,
        private Diagnostics $diagnostics,
        private Identity $identity,
        private ExternalIdentity $externalIdentity,
        private IdentitySync $identitySync,
        private Tenancy $tenancy,
    ) {
    }

    // ── Fast-path convenience methods (high-frequency, cross-cutting) ──

    /**
     * @throws AuthenticationFailed
     * @throws RateLimitException
     */
    public function login(#[SensitiveParameter] Credentials $credentials): AuthenticationResult
    {
        return $this->identity->login(credentials: $credentials);
    }

    public function authenticateRequest(AuthenticationRequest $authenticationRequest): AuthenticationContext
    {
        return $this->access->authenticateRequest(request: $authenticationRequest);
    }

    public function current(): AuthenticationContext
    {
        return $this->access->current();
    }

    public function check(): bool
    {
        return $this->access->check();
    }

    public function user() : User|null
    {
        $entity = $this->identity->authentication()->user();

        return $entity ? User::fromEntity($entity) : null;
    }

    public function guest() : bool
    {
        return ! $this->check();
    }

    /**
     * @throws DateMalformedStringException
     */
    public function refresh(RefreshAuthenticationRequest $refreshAuthenticationRequest): AuthenticationResult
    {
        return $this->identity->authentication()->refresh(request: $refreshAuthenticationRequest);
    }

    /**
     * @throws Unauthenticated
     */
    public function logoutAllSessions(): void
    {
        $this->identity->sessions()->logoutAllSessions();
    }

    /**
     * @return list<ActiveSession>
     */
    public function readActiveSessions(): array
    {
        return $this->identity->sessions()->readActiveSessions();
    }

    /**
     * @throws Unauthenticated
     */
    public function revokeSession(#[SensitiveParameter] string $sessionId): void
    {
        $this->identity->sessions()->revokeSession(sessionId: $sessionId);
    }

    /**
     * @throws RateLimitException
     * @throws RegistrationFailed
     */
    public function register(RegistrationData $registrationData): RegistrationResult
    {
        return $this->identity->account()->register(data: $registrationData);
    }

    /**
     * @throws RateLimitException
     * @throws PasswordChangeFailed
     * @throws Unauthenticated
     */
    public function changePassword(ChangePasswordData $changePasswordData): void
    {
        $this->identity->account()->changePassword(data: $changePasswordData);
    }

    /**
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function beginEmailChange(BeginEmailChangeData $beginEmailChangeData): EmailChangeChallenge
    {
        return $this->identity->account()->beginEmailChange(data: $beginEmailChangeData);
    }

    public function confirmEmailChange(ConfirmEmailChangeData $confirmEmailChangeData): bool
    {
        return $this->identity->account()->confirmEmailChange(data: $confirmEmailChangeData);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function beginPasswordReset(BeginPasswordResetData $beginPasswordResetData): PasswordResetChallenge
    {
        return $this->identity->recovery()->beginPasswordReset(data: $beginPasswordResetData);
    }

    public function resetPassword(ResetPasswordData $resetPasswordData): bool
    {
        return $this->identity->recovery()->resetPassword(data: $resetPasswordData);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function beginEmailVerification(BeginEmailVerificationData $beginEmailVerificationData): EmailVerificationChallenge
    {
        return $this->identity->verification()->beginEmailVerification(data: $beginEmailVerificationData);
    }

    public function verifyEmail(VerifyEmailData $verifyEmailData): bool
    {
        return $this->identity->verification()->verifyEmail(data: $verifyEmailData);
    }

    /**
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function startMfaEnrollment(): MfaEnrollment
    {
        return $this->identity->mfa()->startMfaEnrollment();
    }

    /**
     * @throws RandomException
     * @throws Unauthenticated
     */
    public function confirmMfaEnrollment(ConfirmMfaEnrollmentData $confirmMfaEnrollmentData): BackupCodeSet
    {
        return $this->identity->mfa()->confirmMfaEnrollment(data: $confirmMfaEnrollmentData);
    }

    /**
     * @throws Unauthenticated
     */
    public function cancelMfaEnrollment(): void
    {
        $this->identity->mfa()->cancelMfaEnrollment();
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws Unauthenticated
     */
    public function beginMfaChallenge(): MfaChallenge
    {
        return $this->identity->mfa()->beginMfaChallenge();
    }

    public function verifyMfaChallenge(VerifyMfaChallengeData $verifyMfaChallengeData): AuthenticationResult
    {
        return $this->identity->mfa()->verifyMfaChallenge(data: $verifyMfaChallengeData);
    }

    /**
     * @throws RandomException
     * @throws Unauthenticated
     */
    public function regenerateBackupCodes(): BackupCodeSet
    {
        return $this->identity->mfa()->regenerateBackupCodes();
    }

    /**
     * @throws Unauthenticated
     */
    public function disableMfa(): void
    {
        $this->identity->mfa()->disableMfa();
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function beginMfaRecovery(BeginMfaRecoveryData $beginMfaRecoveryData): MfaRecoveryChallenge
    {
        return $this->identity->mfa()->beginMfaRecovery(data: $beginMfaRecoveryData);
    }

    public function confirmMfaRecovery(ConfirmMfaRecoveryData $confirmMfaRecoveryData): void
    {
        $this->identity->mfa()->confirmMfaRecovery(data: $confirmMfaRecoveryData);
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws Unauthenticated
     */
    public function beginPasskeyRegistration(): PasskeyRegistration
    {
        return $this->identity->passkey()->beginPasskeyRegistration();
    }

    public function completePasskeyRegistration(CompletePasskeyRegistrationData $completePasskeyRegistrationData): PasskeyCredential
    {
        return $this->identity->passkey()->completePasskeyRegistration(data: $completePasskeyRegistrationData);
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function beginPasskeyAuthentication(BeginPasskeyAuthenticationData $beginPasskeyAuthenticationData): PasskeyAuthenticationChallenge
    {
        return $this->identity->passkey()->beginPasskeyAuthentication(data: $beginPasskeyAuthenticationData);
    }

    public function completePasskeyAuthentication(CompletePasskeyAuthenticationData $completePasskeyAuthenticationData): AuthenticationResult
    {
        return $this->identity->passkey()->completePasskeyAuthentication(data: $completePasskeyAuthenticationData);
    }

    /**
     * @return list<PasskeyCredential>
     */
    public function readPasskeys(): array
    {
        return $this->identity->passkey()->readPasskeys();
    }

    public function renamePasskey(RenamePasskeyData $renamePasskeyData): PasskeyCredential
    {
        return $this->identity->passkey()->renamePasskey(data: $renamePasskeyData);
    }

    /**
     * @throws Unauthenticated
     */
    public function revokePasskey(#[SensitiveParameter] string $credentialId): void
    {
        $this->identity->passkey()->revokePasskey(credentialId: $credentialId);
    }

    public function registerOAuthClient(RegisterClientData $registerClientData): RegisteredOAuthClient
    {
        return $this->externalIdentity->oauth()->registerClient(data: $registerClientData);
    }

    public function approveOAuthClientRegistration(ApproveClientRegistrationData $approveClientRegistrationData): OAuthClient
    {
        return $this->externalIdentity->oauth()->approveClientRegistration(data: $approveClientRegistrationData);
    }

    public function updateOAuthClient(UpdateClientData $updateClientData): OAuthClient
    {
        return $this->externalIdentity->oauth()->updateClient(data: $updateClientData);
    }

    public function disableOAuthClient(string $clientId): OAuthClient
    {
        return $this->externalIdentity->oauth()->disableClient(clientId: $clientId);
    }

    public function rotateOAuthClientSecret(string $clientId): RegisteredOAuthClient
    {
        return $this->externalIdentity->oauth()->rotateClientSecret(clientId: $clientId);
    }

    /**
     * @return list<OAuthClient>
     */
    public function readOAuthClients(): array
    {
        return $this->externalIdentity->oauth()->readClients();
    }

    /**
     * @return list<WorkloadIdentityProfile>
     */
    public function readWorkloadIdentities(): array
    {
        return $this->externalIdentity->oauth()->readWorkloadIdentities();
    }

    /**
     * @throws DateMalformedStringException
     */
    public function authorizeOAuthCode(AuthorizeCodeData $authorizeCodeData): IssuedAuthorizationCode
    {
        return $this->externalIdentity->oauth()->authorizeCode(data: $authorizeCodeData);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function exchangeOAuthCode(ExchangeAuthorizationCodeData $exchangeAuthorizationCodeData): OAuthTokenGrant
    {
        return $this->externalIdentity->oauth()->exchangeAuthorizationCode(data: $exchangeAuthorizationCodeData);
    }

    public function exchangeOAuthClientCredentials(ExchangeClientCredentialsData $exchangeClientCredentialsData): OAuthTokenGrant
    {
        return $this->externalIdentity->oauth()->exchangeClientCredentials(data: $exchangeClientCredentialsData);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function exchangeOAuthRefreshToken(ExchangeRefreshTokenData $exchangeRefreshTokenData): OAuthTokenGrant
    {
        return $this->externalIdentity->oauth()->exchangeRefreshToken(data: $exchangeRefreshTokenData);
    }

    public function revokeOAuthToken(RevokeTokenData $revokeTokenData): void
    {
        $this->externalIdentity->oauth()->revokeToken(data: $revokeTokenData);
    }

    public function introspectOAuthToken(IntrospectTokenData $introspectTokenData): TokenIntrospection
    {
        return $this->externalIdentity->oauth()->introspectToken(data: $introspectTokenData);
    }

    public function readOidcProviderMetadata(): OidcProviderMetadata
    {
        return $this->externalIdentity->oidc()->readProviderMetadata();
    }

    public function readOidcJsonWebKeySet(): OidcJsonWebKeySet
    {
        return $this->externalIdentity->oidc()->readJsonWebKeySet();
    }

    public function readOidcUserInfo(#[SensitiveParameter] string $accessToken): OidcUserInfo
    {
        return $this->externalIdentity->oidc()->readUserInfo(accessToken: $accessToken);
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function pushOidcAuthorizationRequest(PushAuthorizationRequestData $pushAuthorizationRequestData): PushedAuthorizationRequest
    {
        return $this->externalIdentity->oidc()->pushAuthorizationRequest(data: $pushAuthorizationRequestData);
    }

    public function oidcLogout(LogoutData $logoutData): LogoutResult
    {
        return $this->externalIdentity->oidc()->logout(data: $logoutData);
    }

    public function logout(): void
    {
        $this->identity->logout();
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function buildOidcJarmResponse(BuildJarmResponseData $buildJarmResponseData): JarmResponse
    {
        return $this->externalIdentity->oidc()->buildJarmResponse(data: $buildJarmResponseData);
    }

    /**
     * @throws RandomException
     */
    public function registerFederationConnection(RegisterFederationConnectionData $registerFederationConnectionData): FederationConnection
    {
        return $this->externalIdentity->sso()->registerConnection(data: $registerFederationConnectionData);
    }

    /**
     * @return list<FederationConnection>
     */
    public function readFederationConnections(): array
    {
        return $this->externalIdentity->sso()->readConnections();
    }

    public function verifyFederationDomain(VerifyFederationDomainData $verifyFederationDomainData): FederationConnection
    {
        return $this->externalIdentity->sso()->verifyDomain(data: $verifyFederationDomainData);
    }

    /**
     * @throws JsonException
     */
    public function syncFederationMetadata(string $connectionId): FederationConnection
    {
        return $this->externalIdentity->sso()->syncMetadata(connectionId: $connectionId);
    }

    public function checkFederationConnectionHealth(string $connectionId): FederationConnectionHealth
    {
        return $this->externalIdentity->sso()->checkConnectionHealth(connectionId: $connectionId);
    }

    public function evaluateFederationBreakGlassBypass(string $connectionId): bool
    {
        return $this->externalIdentity->sso()->evaluateBreakGlassBypass(connectionId: $connectionId);
    }

    public function discoverFederationConnection(#[SensitiveParameter] string $email) : FederationConnection|null
    {
        return $this->externalIdentity->sso()->discoverConnection(email: $email);
    }

    public function startFederatedLogin(StartFederatedLoginData $startFederatedLoginData): StartedFederatedLogin
    {
        return $this->externalIdentity->sso()->startFederatedLogin(data: $startFederatedLoginData);
    }

    /**
     * @throws RandomException
     */
    public function completeFederatedLogin(CompleteFederatedLoginData $completeFederatedLoginData): AuthenticationResult
    {
        return $this->externalIdentity->sso()->completeFederatedLogin(data: $completeFederatedLoginData);
    }

    /**
     * @throws RandomException
     */
    public function registerScimDirectory(RegisterScimDirectoryData $registerScimDirectoryData): RegisteredScimDirectory
    {
        return $this->identitySync->scim()->registerDirectory(data: $registerScimDirectoryData);
    }

    /**
     * @return list<ScimDirectory>
     */
    public function readScimDirectories(string|null $tenantSlug = null) : array
    {
        return $this->identitySync->scim()->readDirectories(tenantSlug: $tenantSlug);
    }

    /**
     * @throws RandomException
     */
    public function rotateScimToken(string $directoryId): RotatedScimToken
    {
        return $this->identitySync->scim()->rotateToken(directoryId: $directoryId);
    }

    public function markScimDirectoryOutage(MarkScimDirectoryOutageData $markScimDirectoryOutageData): ScimDirectory
    {
        return $this->identitySync->scim()->markDirectoryOutage(data: $markScimDirectoryOutageData);
    }

    public function recoverScimDirectoryOutage(RecoverScimDirectoryOutageData $recoverScimDirectoryOutageData): ScimDirectory
    {
        return $this->identitySync->scim()->recoverDirectoryOutage(data: $recoverScimDirectoryOutageData);
    }

    /**
     * @throws RandomException
     */
    public function provisionScimUser(ProvisionScimUserData $provisionScimUserData): ScimProvisioningResult
    {
        return $this->identitySync->scim()->provisionUser(data: $provisionScimUserData);
    }

    public function deleteScimUser(DeleteScimUserData $deleteScimUserData): void
    {
        $this->identitySync->scim()->deleteUser(data: $deleteScimUserData);
    }

    /**
     * @return list<ScimUserProjection>
     */
    public function readScimUsers(string $directoryId): array
    {
        return $this->identitySync->scim()->readUsers(directoryId: $directoryId);
    }

    /**
     * @return list<ScimGroupProjection>
     */
    public function readScimGroups(string $directoryId): array
    {
        return $this->identitySync->scim()->readGroups(directoryId: $directoryId);
    }

    /**
     * @throws RandomException
     */
    public function syncScimGroups(SyncScimGroupsData $syncScimGroupsData): ScimProvisioningResult
    {
        return $this->identitySync->scim()->syncGroups(data: $syncScimGroupsData);
    }

    public function runScimBulk(ScimBulkRequest $scimBulkRequest): ScimBulkResponse
    {
        return $this->identitySync->scim()->runBulk(data: $scimBulkRequest);
    }

    public function suspendUser(int $userId): void
    {
        $this->identitySync->provisioning()->suspendUser(userId: $userId);
    }

    public function reactivateUser(int $userId): void
    {
        $this->identitySync->provisioning()->reactivateUser(userId: $userId);
    }

    public function deprovisionUser(int $userId): void
    {
        $this->identitySync->provisioning()->deprovisionUser(userId: $userId);
    }

    /**
     * @throws RandomException
     */
    public function createTenant(CreateTenantData $createTenantData): Tenant
    {
        return $this->tenancy->tenants()->createTenant(data: $createTenantData);
    }

    /**
     * @return list<Tenant>
     */
    public function readTenants(): array
    {
        return $this->tenancy->tenants()->readTenants();
    }

    /**
     * @throws RandomException
     */
    public function inviteTenantMember(InviteTenantMemberData $inviteTenantMemberData): IssuedTenantInvite
    {
        return $this->tenancy->tenants()->inviteTenantMember(data: $inviteTenantMemberData);
    }

    public function acceptTenantInvite(AcceptTenantInviteData $acceptTenantInviteData): TenantMember
    {
        return $this->tenancy->tenants()->acceptTenantInvite(data: $acceptTenantInviteData);
    }

    /**
     * @return list<TenantMember>
     */
    public function readTenantMembers(string $tenantSlug): array
    {
        return $this->tenancy->tenants()->readTenantMembers(tenantSlug: $tenantSlug);
    }

    public function removeTenantMember(RemoveTenantMemberData $removeTenantMemberData): void
    {
        $this->tenancy->tenants()->removeTenantMember(data: $removeTenantMemberData);
    }

    public function suspendTenantMember(SuspendTenantMemberData $suspendTenantMemberData): TenantMember
    {
        return $this->tenancy->tenants()->suspendTenantMember(data: $suspendTenantMemberData);
    }

    public function transferTenantOwnership(TransferTenantOwnershipData $transferTenantOwnershipData): Tenant
    {
        return $this->tenancy->tenants()->transferTenantOwnership(data: $transferTenantOwnershipData);
    }

    public function readTenantSecurityConfiguration(string $tenantSlug) : TenantSecurityConfiguration|null
    {
        return $this->tenancy->security()->readConfiguration(tenantSlug: $tenantSlug);
    }

    public function readTenantSecurityChangeRequest(string $changeId) : TenantSecurityChangeRequest|null
    {
        return $this->tenancy->security()->readChangeRequest(changeId: $changeId);
    }

    /**
     * @return list<TenantSecurityChangeRequest>
     */
    public function readTenantSecurityChangeRequests(string $tenantSlug): array
    {
        return $this->tenancy->security()->readChangeRequests(tenantSlug: $tenantSlug);
    }

    /**
     * @throws RandomException
     */
    public function beginTenantSecurityChange(BeginTenantSecurityChangeData $beginTenantSecurityChangeData): TenantSecurityChangeRequest
    {
        return $this->tenancy->security()->beginChange(data: $beginTenantSecurityChangeData);
    }

    public function approveTenantSecurityChange(string $changeId, string $approvedBy): TenantSecurityChangeRequest
    {
        return $this->tenancy->security()->approveChange(changeId: $changeId, approvedBy: $approvedBy);
    }

    public function applyTenantSecurityChange(string $changeId): TenantSecurityConfiguration
    {
        return $this->tenancy->security()->applyChange(changeId: $changeId);
    }

    public function rollbackTenantSecurityChange(string $changeId): TenantSecurityConfiguration
    {
        return $this->tenancy->security()->rollbackChange(changeId: $changeId);
    }

    /**
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function beginAdminElevation(): AdminElevation
    {
        return $this->access->beginAdminElevation();
    }

    public function endAdminElevation(): void
    {
        $this->access->endAdminElevation();
    }

    /**
     * @throws AdminElevationFailed
     */
    public function requireAdminElevation(): void
    {
        $this->access->requireAdminElevation();
    }

    public function assessCurrentRisk(#[SensitiveParameter] ?string $ipAddress = null, string|null $userAgent = null) : RiskDecision|null
    {
        return $this->access->assessCurrentRisk(ipAddress: $ipAddress, userAgent: $userAgent);
    }

    /**
     * @return list<RiskSignal>
     */
    public function readRiskSignals(int|null $userId = null) : array
    {
        return $this->access->readRiskSignals(userId: $userId);
    }

    public function explainAccessDenied(string $resource, string|null $requiredPermission = null, string|null $tenant = null, string|null $resourceTenant = null) : AuthIssueExplanation
    {
        return $this->diagnostics->explainAccessDenied(
            resource          : $resource,
            requiredPermission: $requiredPermission,
            tenant            : $tenant,
            resourceTenant    : $resourceTenant,
        );
    }

    public function explainStepUpRequired(string $action, bool|null $phishingResistantRequired = null, int|null $freshAfterSeconds = null) : AuthIssueExplanation
    {
        return $this->diagnostics->explainStepUpRequired(
            action                   : $action,
            phishingResistantRequired: $phishingResistantRequired,
            freshAfterSeconds        : $freshAfterSeconds,
        );
    }

    public function explainSenderConstraintFailure(string $reason, string|null $requiredConstraint = null) : AuthIssueExplanation
    {
        return $this->diagnostics->explainSenderConstraintFailure(
            reason            : $reason,
            requiredConstraint: $requiredConstraint,
        );
    }

    public function explainSessionRevocation(string $status, #[SensitiveParameter] ?string $sessionId = null): AuthIssueExplanation
    {
        return $this->diagnostics->explainSessionRevocation(status: $status, sessionId: $sessionId);
    }

    public function explainTrustedDeviceDecision(string|null $deviceId = null) : AuthIssueExplanation
    {
        return $this->diagnostics->explainTrustedDeviceDecision(deviceId: $deviceId);
    }

    // ── Capability accessors ──

    public function access(): AccessInterface
    {
        return $this->access->access();
    }

    public function identity(): Identity
    {
        return $this->identity;
    }

    public function externalIdentity(): ExternalIdentity
    {
        return $this->externalIdentity;
    }

    public function identitySync(): IdentitySync
    {
        return $this->identitySync;
    }

    public function tenancy(): Tenancy
    {
        return $this->tenancy;
    }

    public function diagnostics(): Diagnostics
    {
        return $this->diagnostics;
    }
}
