<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System;

use Avax\Components\Identity\Auth\System\Capabilities\Access\Access;
use Avax\Components\Identity\Auth\System\Capabilities\Access\AccessInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskDecision;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskSignal;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Diagnostics;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\ExternalIdentity;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistrationData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\AuthorizeCode\AuthorizeCodeData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeClientCredentials\ExchangeClientCredentialsData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeRefreshToken\ExchangeRefreshTokenData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\IntrospectToken\IntrospectTokenData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\IntrospectToken\TokenIntrospection;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\OAuthTokenGrant;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ReadWorkloadIdentities\WorkloadIdentityProfile;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RegisterClient\RegisterClientData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RevokeToken\RevokeTokenData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\UpdateClient\UpdateClientData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\IssuedAuthorizationCode;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\RegisteredOAuthClient;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponseData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse\JarmResponse;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutResult;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequestData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushedAuthorizationRequest;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadUserInfo\OidcUserInfo;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcJsonWebKeySet;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcProviderMetadata;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\CompleteFederatedLogin\CompleteFederatedLoginData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\RegisterConnection\RegisterFederationConnectionData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\StartFederatedLogin\StartFederatedLoginData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\VerifyDomain\VerifyFederationDomainData;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionHealth;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\StartedFederatedLogin;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\BackupCodeSet;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Data\VerifyMfaChallengeData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollmentData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\MfaEnrollment;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallenge;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\BeginMfaRecoveryData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\ConfirmMfaRecoveryData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\MfaRecoveryChallenge;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyAuthenticationChallenge;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyRegistration;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey\RenamePasskeyData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\ActiveSession;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow\RefreshAuthenticationRequest;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\IdentitySync;
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
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\RegisteredScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevation;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevationFailed;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\Tenant;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\TenantMember;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\AcceptInvite\AcceptTenantInviteData;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\CreateTenant\CreateTenantData;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\InviteTenantMemberData;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\IssuedTenantInvite;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\RemoveMember\RemoveTenantMemberData;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\SuspendMember\SuspendTenantMemberData;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TransferOwnership\TransferTenantOwnershipData;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Tenancy;
use Avax\Components\Identity\Auth\System\Configuration\AuthBuilder;
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
final readonly class DefaultAuth implements Auth
{
    public function __construct(
        #[SensitiveParameter]
        private Access           $access,
        private Diagnostics      $diagnostics,
        private Identity         $identity,
        private ExternalIdentity $externalIdentity,
        private IdentitySync     $identitySync,
        private Tenancy          $tenancy,
    ) {}

    public static function configuration() : AuthBuilder
    {
        return new AuthBuilder();
    }

    // ── Fast-path convenience methods (high-frequency, cross-cutting) ──

    /**
     * @throws AuthenticationFailed
     * @throws RateLimitException
     */
    public function login(#[SensitiveParameter] Credentials $credentials) : AuthenticationResult
    {
        return $this->identity->login(credentials: $credentials);
    }

    public function authenticateRequest(AuthenticationRequest $request) : AuthenticationContext
    {
        return $this->access->authenticateRequest(request: $request);
    }

    public function current() : AuthenticationContext
    {
        return $this->access->current();
    }

    public function check() : bool
    {
        return $this->access->check();
    }

    public function user() : AuthenticatedUser|null
    {
        return $this->access->user();
    }

    /**
     * @throws DateMalformedStringException
     */
    public function refresh(RefreshAuthenticationRequest $request) : AuthenticationResult
    {
        return $this->identity->authentication()->refresh(request: $request);
    }

    /**
     * @throws Unauthenticated
     */
    public function logoutAllSessions() : void
    {
        $this->identity->sessions()->logoutAllSessions();
    }

    /**
     * @return list<ActiveSession>
     */
    public function readActiveSessions() : array
    {
        return $this->identity->sessions()->readActiveSessions();
    }

    /**
     * @throws Unauthenticated
     */
    public function revokeSession(#[SensitiveParameter] string $sessionId) : void
    {
        $this->identity->sessions()->revokeSession(sessionId: $sessionId);
    }

    /**
     * @throws RateLimitException
     * @throws RegistrationFailed
     */
    public function register(RegistrationData $data) : RegistrationResult
    {
        return $this->identity->account()->register(data: $data);
    }

    /**
     * @throws RateLimitException
     * @throws PasswordChangeFailed
     * @throws Unauthenticated
     */
    public function changePassword(ChangePasswordData $data) : void
    {
        $this->identity->account()->changePassword(data: $data);
    }

    /**
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function beginEmailChange(BeginEmailChangeData $data) : EmailChangeChallenge
    {
        return $this->identity->account()->beginEmailChange(data: $data);
    }

    public function confirmEmailChange(ConfirmEmailChangeData $data) : bool
    {
        return $this->identity->account()->confirmEmailChange(data: $data);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function beginPasswordReset(BeginPasswordResetData $data) : PasswordResetChallenge
    {
        return $this->identity->recovery()->beginPasswordReset(data: $data);
    }

    public function resetPassword(ResetPasswordData $data) : bool
    {
        return $this->identity->recovery()->resetPassword(data: $data);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function beginEmailVerification(BeginEmailVerificationData $data) : EmailVerificationChallenge
    {
        return $this->identity->verification()->beginEmailVerification(data: $data);
    }

    public function verifyEmail(VerifyEmailData $data) : bool
    {
        return $this->identity->verification()->verifyEmail(data: $data);
    }

    /**
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function startMfaEnrollment() : MfaEnrollment
    {
        return $this->identity->mfa()->startMfaEnrollment();
    }

    /**
     * @throws RandomException
     * @throws Unauthenticated
     */
    public function confirmMfaEnrollment(ConfirmMfaEnrollmentData $data) : BackupCodeSet
    {
        return $this->identity->mfa()->confirmMfaEnrollment(data: $data);
    }

    /**
     * @throws Unauthenticated
     */
    public function cancelMfaEnrollment() : void
    {
        $this->identity->mfa()->cancelMfaEnrollment();
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws Unauthenticated
     */
    public function beginMfaChallenge() : MfaChallenge
    {
        return $this->identity->mfa()->beginMfaChallenge();
    }

    public function verifyMfaChallenge(VerifyMfaChallengeData $data) : AuthenticationResult
    {
        return $this->identity->mfa()->verifyMfaChallenge(data: $data);
    }

    /**
     * @throws RandomException
     * @throws Unauthenticated
     */
    public function regenerateBackupCodes() : BackupCodeSet
    {
        return $this->identity->mfa()->regenerateBackupCodes();
    }

    /**
     * @throws Unauthenticated
     */
    public function disableMfa() : void
    {
        $this->identity->mfa()->disableMfa();
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function beginMfaRecovery(BeginMfaRecoveryData $data) : MfaRecoveryChallenge
    {
        return $this->identity->mfa()->beginMfaRecovery(data: $data);
    }

    public function confirmMfaRecovery(ConfirmMfaRecoveryData $data) : void
    {
        $this->identity->mfa()->confirmMfaRecovery(data: $data);
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws Unauthenticated
     */
    public function beginPasskeyRegistration() : PasskeyRegistration
    {
        return $this->identity->passkey()->beginPasskeyRegistration();
    }

    public function completePasskeyRegistration(CompletePasskeyRegistrationData $data) : PasskeyCredential
    {
        return $this->identity->passkey()->completePasskeyRegistration(data: $data);
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function beginPasskeyAuthentication(BeginPasskeyAuthenticationData $data) : PasskeyAuthenticationChallenge
    {
        return $this->identity->passkey()->beginPasskeyAuthentication(data: $data);
    }

    public function completePasskeyAuthentication(CompletePasskeyAuthenticationData $data) : AuthenticationResult
    {
        return $this->identity->passkey()->completePasskeyAuthentication(data: $data);
    }

    /**
     * @return list<PasskeyCredential>
     */
    public function readPasskeys() : array
    {
        return $this->identity->passkey()->readPasskeys();
    }

    public function renamePasskey(RenamePasskeyData $data) : PasskeyCredential
    {
        return $this->identity->passkey()->renamePasskey(data: $data);
    }

    /**
     * @throws Unauthenticated
     */
    public function revokePasskey(#[SensitiveParameter] string $credentialId) : void
    {
        $this->identity->passkey()->revokePasskey(credentialId: $credentialId);
    }

    public function registerOAuthClient(RegisterClientData $data) : RegisteredOAuthClient
    {
        return $this->externalIdentity->oauth()->registerClient(data: $data);
    }

    public function approveOAuthClientRegistration(ApproveClientRegistrationData $data) : OAuthClient
    {
        return $this->externalIdentity->oauth()->approveClientRegistration(data: $data);
    }

    public function updateOAuthClient(UpdateClientData $data) : OAuthClient
    {
        return $this->externalIdentity->oauth()->updateClient(data: $data);
    }

    public function disableOAuthClient(string $clientId) : OAuthClient
    {
        return $this->externalIdentity->oauth()->disableClient(clientId: $clientId);
    }

    public function rotateOAuthClientSecret(string $clientId) : RegisteredOAuthClient
    {
        return $this->externalIdentity->oauth()->rotateClientSecret(clientId: $clientId);
    }

    /**
     * @return list<OAuthClient>
     */
    public function readOAuthClients() : array
    {
        return $this->externalIdentity->oauth()->readClients();
    }

    /**
     * @return list<WorkloadIdentityProfile>
     */
    public function readWorkloadIdentities() : array
    {
        return $this->externalIdentity->oauth()->readWorkloadIdentities();
    }

    /**
     * @throws DateMalformedStringException
     */
    public function authorizeOAuthCode(AuthorizeCodeData $data) : IssuedAuthorizationCode
    {
        return $this->externalIdentity->oauth()->authorizeCode(data: $data);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function exchangeOAuthCode(ExchangeAuthorizationCodeData $data) : OAuthTokenGrant
    {
        return $this->externalIdentity->oauth()->exchangeAuthorizationCode(data: $data);
    }

    public function exchangeOAuthClientCredentials(ExchangeClientCredentialsData $data) : OAuthTokenGrant
    {
        return $this->externalIdentity->oauth()->exchangeClientCredentials(data: $data);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function exchangeOAuthRefreshToken(ExchangeRefreshTokenData $data) : OAuthTokenGrant
    {
        return $this->externalIdentity->oauth()->exchangeRefreshToken(data: $data);
    }

    public function revokeOAuthToken(RevokeTokenData $data) : void
    {
        $this->externalIdentity->oauth()->revokeToken(data: $data);
    }

    public function introspectOAuthToken(IntrospectTokenData $data) : TokenIntrospection
    {
        return $this->externalIdentity->oauth()->introspectToken(data: $data);
    }

    public function readOidcProviderMetadata() : OidcProviderMetadata
    {
        return $this->externalIdentity->oidc()->readProviderMetadata();
    }

    public function readOidcJsonWebKeySet() : OidcJsonWebKeySet
    {
        return $this->externalIdentity->oidc()->readJsonWebKeySet();
    }

    public function readOidcUserInfo(#[SensitiveParameter] string $accessToken) : OidcUserInfo
    {
        return $this->externalIdentity->oidc()->readUserInfo(accessToken: $accessToken);
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function pushOidcAuthorizationRequest(PushAuthorizationRequestData $data) : PushedAuthorizationRequest
    {
        return $this->externalIdentity->oidc()->pushAuthorizationRequest(data: $data);
    }

    public function oidcLogout(LogoutData $data) : LogoutResult
    {
        return $this->externalIdentity->oidc()->logout(data: $data);
    }

    public function logout() : void
    {
        $this->identity->logout();
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function buildOidcJarmResponse(BuildJarmResponseData $data) : JarmResponse
    {
        return $this->externalIdentity->oidc()->buildJarmResponse(data: $data);
    }

    /**
     * @throws RandomException
     */
    public function registerFederationConnection(RegisterFederationConnectionData $data) : FederationConnection
    {
        return $this->externalIdentity->sso()->registerConnection(data: $data);
    }

    /**
     * @return list<FederationConnection>
     */
    public function readFederationConnections() : array
    {
        return $this->externalIdentity->sso()->readConnections();
    }

    public function verifyFederationDomain(VerifyFederationDomainData $data) : FederationConnection
    {
        return $this->externalIdentity->sso()->verifyDomain(data: $data);
    }

    /**
     * @throws JsonException
     */
    public function syncFederationMetadata(string $connectionId) : FederationConnection
    {
        return $this->externalIdentity->sso()->syncMetadata(connectionId: $connectionId);
    }

    public function checkFederationConnectionHealth(string $connectionId) : FederationConnectionHealth
    {
        return $this->externalIdentity->sso()->checkConnectionHealth(connectionId: $connectionId);
    }

    public function evaluateFederationBreakGlassBypass(string $connectionId) : bool
    {
        return $this->externalIdentity->sso()->evaluateBreakGlassBypass(connectionId: $connectionId);
    }

    public function discoverFederationConnection(#[SensitiveParameter] string $email) : FederationConnection|null
    {
        return $this->externalIdentity->sso()->discoverConnection(email: $email);
    }

    public function startFederatedLogin(StartFederatedLoginData $data) : StartedFederatedLogin
    {
        return $this->externalIdentity->sso()->startFederatedLogin(data: $data);
    }

    /**
     * @throws RandomException
     */
    public function completeFederatedLogin(CompleteFederatedLoginData $data) : AuthenticationResult
    {
        return $this->externalIdentity->sso()->completeFederatedLogin(data: $data);
    }

    /**
     * @throws RandomException
     */
    public function registerScimDirectory(RegisterScimDirectoryData $data) : RegisteredScimDirectory
    {
        return $this->identitySync->scim()->registerDirectory(data: $data);
    }

    /**
     * @return list<ScimDirectory>
     */
    public function readScimDirectories(string $tenantSlug = null) : array
    {
        return $this->identitySync->scim()->readDirectories(tenantSlug: $tenantSlug);
    }

    /**
     * @throws RandomException
     */
    public function rotateScimToken(string $directoryId) : RotatedScimToken
    {
        return $this->identitySync->scim()->rotateToken(directoryId: $directoryId);
    }

    public function markScimDirectoryOutage(MarkScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->identitySync->scim()->markDirectoryOutage(data: $data);
    }

    public function recoverScimDirectoryOutage(RecoverScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->identitySync->scim()->recoverDirectoryOutage(data: $data);
    }

    /**
     * @throws RandomException
     */
    public function provisionScimUser(ProvisionScimUserData $data) : ScimProvisioningResult
    {
        return $this->identitySync->scim()->provisionUser(data: $data);
    }

    public function deleteScimUser(DeleteScimUserData $data) : void
    {
        $this->identitySync->scim()->deleteUser(data: $data);
    }

    /**
     * @return list<ScimUserProjection>
     */
    public function readScimUsers(string $directoryId) : array
    {
        return $this->identitySync->scim()->readUsers(directoryId: $directoryId);
    }

    /**
     * @return list<ScimGroupProjection>
     */
    public function readScimGroups(string $directoryId) : array
    {
        return $this->identitySync->scim()->readGroups(directoryId: $directoryId);
    }

    /**
     * @throws RandomException
     */
    public function syncScimGroups(SyncScimGroupsData $data) : ScimProvisioningResult
    {
        return $this->identitySync->scim()->syncGroups(data: $data);
    }

    public function runScimBulk(ScimBulkRequest $data) : ScimBulkResponse
    {
        return $this->identitySync->scim()->runBulk(data: $data);
    }

    public function suspendUser(int $userId) : void
    {
        $this->identitySync->provisioning()->suspendUser(userId: $userId);
    }

    public function reactivateUser(int $userId) : void
    {
        $this->identitySync->provisioning()->reactivateUser(userId: $userId);
    }

    public function deprovisionUser(int $userId) : void
    {
        $this->identitySync->provisioning()->deprovisionUser(userId: $userId);
    }

    /**
     * @throws RandomException
     */
    public function createTenant(CreateTenantData $data) : Tenant
    {
        return $this->tenancy->tenants()->createTenant(data: $data);
    }

    /**
     * @return list<Tenant>
     */
    public function readTenants() : array
    {
        return $this->tenancy->tenants()->readTenants();
    }

    /**
     * @throws RandomException
     */
    public function inviteTenantMember(InviteTenantMemberData $data) : IssuedTenantInvite
    {
        return $this->tenancy->tenants()->inviteTenantMember(data: $data);
    }

    public function acceptTenantInvite(AcceptTenantInviteData $data) : TenantMember
    {
        return $this->tenancy->tenants()->acceptTenantInvite(data: $data);
    }

    /**
     * @return list<TenantMember>
     */
    public function readTenantMembers(string $tenantSlug) : array
    {
        return $this->tenancy->tenants()->readTenantMembers(tenantSlug: $tenantSlug);
    }

    public function removeTenantMember(RemoveTenantMemberData $data) : void
    {
        $this->tenancy->tenants()->removeTenantMember(data: $data);
    }

    public function suspendTenantMember(SuspendTenantMemberData $data) : TenantMember
    {
        return $this->tenancy->tenants()->suspendTenantMember(data: $data);
    }

    public function transferTenantOwnership(TransferTenantOwnershipData $data) : Tenant
    {
        return $this->tenancy->tenants()->transferTenantOwnership(data: $data);
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
    public function readTenantSecurityChangeRequests(string $tenantSlug) : array
    {
        return $this->tenancy->security()->readChangeRequests(tenantSlug: $tenantSlug);
    }

    /**
     * @throws RandomException
     */
    public function beginTenantSecurityChange(BeginTenantSecurityChangeData $data) : TenantSecurityChangeRequest
    {
        return $this->tenancy->security()->beginChange(data: $data);
    }

    public function approveTenantSecurityChange(string $changeId, string $approvedBy) : TenantSecurityChangeRequest
    {
        return $this->tenancy->security()->approveChange(changeId: $changeId, approvedBy: $approvedBy);
    }

    public function applyTenantSecurityChange(string $changeId) : TenantSecurityConfiguration
    {
        return $this->tenancy->security()->applyChange(changeId: $changeId);
    }

    public function rollbackTenantSecurityChange(string $changeId) : TenantSecurityConfiguration
    {
        return $this->tenancy->security()->rollbackChange(changeId: $changeId);
    }

    /**
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function beginAdminElevation() : AdminElevation
    {
        return $this->access->beginAdminElevation();
    }

    public function endAdminElevation() : void
    {
        $this->access->endAdminElevation();
    }

    /**
     * @throws AdminElevationFailed
     */
    public function requireAdminElevation() : void
    {
        $this->access->requireAdminElevation();
    }

    public function assessCurrentRisk(#[SensitiveParameter] string $ipAddress = null, string $userAgent = null) : RiskDecision|null
    {
        return $this->access->assessCurrentRisk(ipAddress: $ipAddress, userAgent: $userAgent);
    }

    /**
     * @return list<RiskSignal>
     */
    public function readRiskSignals(int $userId = null) : array
    {
        return $this->access->readRiskSignals(userId: $userId);
    }

    public function explainAccessDenied(string $resource, string $requiredPermission = null, string $tenant = null, string $resourceTenant = null) : AuthIssueExplanation
    {
        return $this->diagnostics->explainAccessDenied(
            resource          : $resource,
            requiredPermission: $requiredPermission,
            tenant            : $tenant,
            resourceTenant    : $resourceTenant,
        );
    }

    public function explainStepUpRequired(string $action, bool $phishingResistantRequired = null, int $freshAfterSeconds = null) : AuthIssueExplanation
    {
        return $this->diagnostics->explainStepUpRequired(
            action                   : $action,
            phishingResistantRequired: $phishingResistantRequired,
            freshAfterSeconds        : $freshAfterSeconds,
        );
    }

    public function explainSenderConstraintFailure(string $reason, string $requiredConstraint = null) : AuthIssueExplanation
    {
        return $this->diagnostics->explainSenderConstraintFailure(
            reason            : $reason,
            requiredConstraint: $requiredConstraint,
        );
    }

    public function explainSessionRevocation(string $status, #[SensitiveParameter] string $sessionId = null) : AuthIssueExplanation
    {
        return $this->diagnostics->explainSessionRevocation(status: $status, sessionId: $sessionId);
    }

    public function explainTrustedDeviceDecision(string $deviceId = null) : AuthIssueExplanation
    {
        return $this->diagnostics->explainTrustedDeviceDecision(deviceId: $deviceId);
    }

    // ── Capability accessors ──

    public function access() : AccessInterface
    {
        return $this->access->access();
    }

    public function identity() : Identity
    {
        return $this->identity;
    }

    public function externalIdentity() : ExternalIdentity
    {
        return $this->externalIdentity;
    }

    public function identitySync() : IdentitySync
    {
        return $this->identitySync;
    }

    public function tenancy() : Tenancy
    {
        return $this->tenancy;
    }

    public function diagnostics() : Diagnostics
    {
        return $this->diagnostics;
    }
}
