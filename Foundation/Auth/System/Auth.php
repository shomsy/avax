<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Capabilities\Access\Access;
use Avax\Auth\System\Capabilities\Access\AccessInterface;
use Avax\Auth\System\Capabilities\Diagnostics\Diagnostics;
use Avax\Auth\System\Capabilities\ExternalIdentity\ExternalIdentity;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Capabilities\IdentitySync\IdentitySync;
use Avax\Auth\System\Capabilities\Tenancy\Tenancy;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Auth\System\Flows\Login\Credentials;
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
final readonly class Auth implements AuthInterface
{
    public function __construct(
        private Access           $access,
        private Diagnostics      $diagnostics,
        private Identity         $identity,
        private ExternalIdentity $externalIdentity,
        private IdentitySync     $identitySync,
        private Tenancy          $tenancy
    ) {}

    public static function configuration() : AuthBuilder
    {
        return new AuthBuilder();
    }

    // ── Fast-path convenience methods (high-frequency, cross-cutting) ──

    public function login(#[SensitiveParameter] Credentials $credentials) : AuthenticationResult
    {
        return $this->identity->login(credentials: $credentials);
    }

    public function logout() : void
    {
        $this->identity->logout();
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

    public function refresh(\Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow\RefreshAuthenticationRequest $request) : AuthenticationResult
    {
        return $this->identity->authentication()->refresh(request: $request);
    }

    public function logoutAllSessions() : void
    {
        $this->identity->sessions()->logoutAllSessions();
    }

    /**
     * @return list<\Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\ActiveSession>
     */
    public function readActiveSessions() : array
    {
        return $this->identity->sessions()->readActiveSessions();
    }

    public function revokeSession(string $sessionId) : void
    {
        $this->identity->sessions()->revokeSession(sessionId: $sessionId);
    }

    public function register(\Avax\Auth\System\Flows\Register\RegistrationData $data) : \Avax\Auth\System\Flows\Register\RegistrationResult
    {
        return $this->identity->account()->register(data: $data);
    }

    public function changePassword(\Avax\Auth\System\Flows\ChangePassword\ChangePasswordData $data) : void
    {
        $this->identity->account()->changePassword(data: $data);
    }

    public function beginEmailChange(\Avax\Auth\System\Flows\ChangeEmail\BeginEmailChangeData $data) : \Avax\Auth\System\Flows\ChangeEmail\EmailChangeChallenge
    {
        return $this->identity->account()->beginEmailChange(data: $data);
    }

    public function confirmEmailChange(\Avax\Auth\System\Flows\ChangeEmail\ConfirmEmailChangeData $data) : bool
    {
        return $this->identity->account()->confirmEmailChange(data: $data);
    }

    public function beginPasswordReset(\Avax\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordResetData $data) : \Avax\Auth\System\Flows\RecoverAccess\PasswordReset\PasswordResetChallenge
    {
        return $this->identity->recovery()->beginPasswordReset(data: $data);
    }

    public function resetPassword(\Avax\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPasswordData $data) : bool
    {
        return $this->identity->recovery()->resetPassword(data: $data);
    }

    public function beginEmailVerification(\Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerificationData $data) : \Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationChallenge
    {
        return $this->identity->verification()->beginEmailVerification(data: $data);
    }

    public function verifyEmail(\Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmailData $data) : bool
    {
        return $this->identity->verification()->verifyEmail(data: $data);
    }

    public function startMfaEnrollment() : \Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\MfaEnrollment
    {
        return $this->identity->mfa()->startMfaEnrollment();
    }

    public function confirmMfaEnrollment(\Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollmentData $data) : \Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\BackupCodeSet
    {
        return $this->identity->mfa()->confirmMfaEnrollment(data: $data);
    }

    public function cancelMfaEnrollment() : void
    {
        $this->identity->mfa()->cancelMfaEnrollment();
    }

    public function beginMfaChallenge() : \Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallenge
    {
        return $this->identity->mfa()->beginMfaChallenge();
    }

    public function verifyMfaChallenge(\Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Data\VerifyMfaChallengeData $data) : AuthenticationResult
    {
        return $this->identity->mfa()->verifyMfaChallenge(data: $data);
    }

    public function regenerateBackupCodes() : \Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\BackupCodeSet
    {
        return $this->identity->mfa()->regenerateBackupCodes();
    }

    public function disableMfa() : void
    {
        $this->identity->mfa()->disableMfa();
    }

    public function beginMfaRecovery(\Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\BeginMfaRecoveryData $data) : \Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\MfaRecoveryChallenge
    {
        return $this->identity->mfa()->beginMfaRecovery(data: $data);
    }

    public function confirmMfaRecovery(\Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\ConfirmMfaRecoveryData $data) : void
    {
        $this->identity->mfa()->confirmMfaRecovery(data: $data);
    }

    public function beginPasskeyRegistration() : \Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyRegistration
    {
        return $this->identity->passkey()->beginPasskeyRegistration();
    }

    public function completePasskeyRegistration(\Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistrationData $data) : \Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential
    {
        return $this->identity->passkey()->completePasskeyRegistration(data: $data);
    }

    public function beginPasskeyAuthentication(\Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthenticationData $data) : \Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyAuthenticationChallenge
    {
        return $this->identity->passkey()->beginPasskeyAuthentication(data: $data);
    }

    public function completePasskeyAuthentication(\Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthenticationData $data) : AuthenticationResult
    {
        return $this->identity->passkey()->completePasskeyAuthentication(data: $data);
    }

    /**
     * @return list<\Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential>
     */
    public function readPasskeys() : array
    {
        return $this->identity->passkey()->readPasskeys();
    }

    public function renamePasskey(\Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey\RenamePasskeyData $data) : \Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential
    {
        return $this->identity->passkey()->renamePasskey(data: $data);
    }

    public function revokePasskey(string $credentialId) : void
    {
        $this->identity->passkey()->revokePasskey(credentialId: $credentialId);
    }

    public function registerOAuthClient(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RegisterClient\RegisterClientData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\RegisteredOAuthClient
    {
        return $this->externalIdentity->oauth()->registerClient(data: $data);
    }

    public function approveOAuthClientRegistration(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistrationData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient
    {
        return $this->externalIdentity->oauth()->approveClientRegistration(data: $data);
    }

    public function updateOAuthClient(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\UpdateClient\UpdateClientData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient
    {
        return $this->externalIdentity->oauth()->updateClient(data: $data);
    }

    public function disableOAuthClient(string $clientId) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient
    {
        return $this->externalIdentity->oauth()->disableClient(clientId: $clientId);
    }

    public function rotateOAuthClientSecret(string $clientId) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\RegisteredOAuthClient
    {
        return $this->externalIdentity->oauth()->rotateClientSecret(clientId: $clientId);
    }

    /**
     * @return list<\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient>
     */
    public function readOAuthClients() : array
    {
        return $this->externalIdentity->oauth()->readClients();
    }

    /**
     * @return list<\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ReadWorkloadIdentities\WorkloadIdentityProfile>
     */
    public function readWorkloadIdentities() : array
    {
        return $this->externalIdentity->oauth()->readWorkloadIdentities();
    }

    public function authorizeOAuthCode(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\AuthorizeCode\AuthorizeCodeData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\IssuedAuthorizationCode
    {
        return $this->externalIdentity->oauth()->authorizeCode(data: $data);
    }

    public function exchangeOAuthCode(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\OAuthTokenGrant
    {
        return $this->externalIdentity->oauth()->exchangeAuthorizationCode(data: $data);
    }

    public function exchangeOAuthClientCredentials(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeClientCredentials\ExchangeClientCredentialsData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\OAuthTokenGrant
    {
        return $this->externalIdentity->oauth()->exchangeClientCredentials(data: $data);
    }

    public function exchangeOAuthRefreshToken(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeRefreshToken\ExchangeRefreshTokenData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\OAuthTokenGrant
    {
        return $this->externalIdentity->oauth()->exchangeRefreshToken(data: $data);
    }

    public function revokeOAuthToken(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RevokeToken\RevokeTokenData $data) : void
    {
        $this->externalIdentity->oauth()->revokeToken(data: $data);
    }

    public function introspectOAuthToken(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\IntrospectToken\IntrospectTokenData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\IntrospectToken\TokenIntrospection
    {
        return $this->externalIdentity->oauth()->introspectToken(data: $data);
    }

    public function readOidcProviderMetadata() : \Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcProviderMetadata
    {
        return $this->externalIdentity->oidc()->readProviderMetadata();
    }

    public function readOidcJsonWebKeySet() : \Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcJsonWebKeySet
    {
        return $this->externalIdentity->oidc()->readJsonWebKeySet();
    }

    public function readOidcUserInfo(string $accessToken) : \Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadUserInfo\OidcUserInfo
    {
        return $this->externalIdentity->oidc()->readUserInfo(accessToken: $accessToken);
    }

    public function pushOidcAuthorizationRequest(\Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequestData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushedAuthorizationRequest
    {
        return $this->externalIdentity->oidc()->pushAuthorizationRequest(data: $data);
    }

    public function oidcLogout(\Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutResult
    {
        return $this->externalIdentity->oidc()->logout(data: $data);
    }

    public function buildOidcJarmResponse(\Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponseData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse\JarmResponse
    {
        return $this->externalIdentity->oidc()->buildJarmResponse(data: $data);
    }

    public function registerFederationConnection(\Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\RegisterConnection\RegisterFederationConnectionData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection
    {
        return $this->externalIdentity->sso()->registerConnection(data: $data);
    }

    /**
     * @return list<\Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection>
     */
    public function readFederationConnections() : array
    {
        return $this->externalIdentity->sso()->readConnections();
    }

    public function verifyFederationDomain(\Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\VerifyDomain\VerifyFederationDomainData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection
    {
        return $this->externalIdentity->sso()->verifyDomain(data: $data);
    }

    public function syncFederationMetadata(string $connectionId) : \Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection
    {
        return $this->externalIdentity->sso()->syncMetadata(connectionId: $connectionId);
    }

    public function checkFederationConnectionHealth(string $connectionId) : \Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionHealth
    {
        return $this->externalIdentity->sso()->checkConnectionHealth(connectionId: $connectionId);
    }

    public function evaluateFederationBreakGlassBypass(string $connectionId) : bool
    {
        return $this->externalIdentity->sso()->evaluateBreakGlassBypass(connectionId: $connectionId);
    }

    public function discoverFederationConnection(string $email) : \Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection|null
    {
        return $this->externalIdentity->sso()->discoverConnection(email: $email);
    }

    public function startFederatedLogin(\Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\StartFederatedLogin\StartFederatedLoginData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\StartedFederatedLogin
    {
        return $this->externalIdentity->sso()->startFederatedLogin(data: $data);
    }

    public function completeFederatedLogin(\Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\CompleteFederatedLogin\CompleteFederatedLoginData $data) : AuthenticationResult
    {
        return $this->externalIdentity->sso()->completeFederatedLogin(data: $data);
    }

    public function registerScimDirectory(\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectoryData $data) : \Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\RegisteredScimDirectory
    {
        return $this->identitySync->scim()->registerDirectory(data: $data);
    }

    /**
     * @return list<\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory>
     */
    public function readScimDirectories(string|null $tenantSlug = null) : array
    {
        return $this->identitySync->scim()->readDirectories(tenantSlug: $tenantSlug);
    }

    public function rotateScimToken(string $directoryId) : \Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken\RotatedScimToken
    {
        return $this->identitySync->scim()->rotateToken(directoryId: $directoryId);
    }

    public function markScimDirectoryOutage(\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutageData $data) : \Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory
    {
        return $this->identitySync->scim()->markDirectoryOutage(data: $data);
    }

    public function recoverScimDirectoryOutage(\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage\RecoverScimDirectoryOutageData $data) : \Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory
    {
        return $this->identitySync->scim()->recoverDirectoryOutage(data: $data);
    }

    public function provisionScimUser(\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData $data) : \Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ScimProvisioningResult
    {
        return $this->identitySync->scim()->provisionUser(data: $data);
    }

    public function deleteScimUser(\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUserData $data) : void
    {
        $this->identitySync->scim()->deleteUser(data: $data);
    }

    /**
     * @return list<\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers\ScimUserProjection>
     */
    public function readScimUsers(string $directoryId) : array
    {
        return $this->identitySync->scim()->readUsers(directoryId: $directoryId);
    }

    /**
     * @return list<\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups\ScimGroupProjection>
     */
    public function readScimGroups(string $directoryId) : array
    {
        return $this->identitySync->scim()->readGroups(directoryId: $directoryId);
    }

    public function syncScimGroups(\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups\SyncScimGroupsData $data) : \Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ScimProvisioningResult
    {
        return $this->identitySync->scim()->syncGroups(data: $data);
    }

    public function runScimBulk(\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkRequest $data) : \Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkResponse
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

    public function createTenant(\Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\CreateTenant\CreateTenantData $data) : \Avax\Auth\System\Capabilities\Tenancy\Model\Tenant
    {
        return $this->tenancy->tenants()->createTenant(data: $data);
    }

    /**
     * @return list<\Avax\Auth\System\Capabilities\Tenancy\Model\Tenant>
     */
    public function readTenants() : array
    {
        return $this->tenancy->tenants()->readTenants();
    }

    public function inviteTenantMember(\Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\InviteTenantMemberData $data) : \Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\IssuedTenantInvite
    {
        return $this->tenancy->tenants()->inviteTenantMember(data: $data);
    }

    public function acceptTenantInvite(\Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\AcceptInvite\AcceptTenantInviteData $data) : \Avax\Auth\System\Capabilities\Tenancy\Model\TenantMember
    {
        return $this->tenancy->tenants()->acceptTenantInvite(data: $data);
    }

    /**
     * @return list<\Avax\Auth\System\Capabilities\Tenancy\Model\TenantMember>
     */
    public function readTenantMembers(string $tenantSlug) : array
    {
        return $this->tenancy->tenants()->readTenantMembers(tenantSlug: $tenantSlug);
    }

    public function removeTenantMember(\Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\RemoveMember\RemoveTenantMemberData $data) : void
    {
        $this->tenancy->tenants()->removeTenantMember(data: $data);
    }

    public function suspendTenantMember(\Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\SuspendMember\SuspendTenantMemberData $data) : \Avax\Auth\System\Capabilities\Tenancy\Model\TenantMember
    {
        return $this->tenancy->tenants()->suspendTenantMember(data: $data);
    }

    public function transferTenantOwnership(\Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TransferOwnership\TransferTenantOwnershipData $data) : \Avax\Auth\System\Capabilities\Tenancy\Model\Tenant
    {
        return $this->tenancy->tenants()->transferTenantOwnership(data: $data);
    }

    public function readTenantSecurityConfiguration(string $tenantSlug) : \Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration|null
    {
        return $this->tenancy->security()->readConfiguration(tenantSlug: $tenantSlug);
    }

    public function readTenantSecurityChangeRequest(string $changeId) : \Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest|null
    {
        return $this->tenancy->security()->readChangeRequest(changeId: $changeId);
    }

    /**
     * @return list<\Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest>
     */
    public function readTenantSecurityChangeRequests(string $tenantSlug) : array
    {
        return $this->tenancy->security()->readChangeRequests(tenantSlug: $tenantSlug);
    }

    public function beginTenantSecurityChange(\Avax\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\BeginChange\BeginTenantSecurityChangeData $data) : \Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest
    {
        return $this->tenancy->security()->beginChange(data: $data);
    }

    public function approveTenantSecurityChange(string $changeId, string $approvedBy) : \Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest
    {
        return $this->tenancy->security()->approveChange(changeId: $changeId, approvedBy: $approvedBy);
    }

    public function applyTenantSecurityChange(string $changeId) : \Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration
    {
        return $this->tenancy->security()->applyChange(changeId: $changeId);
    }

    public function rollbackTenantSecurityChange(string $changeId) : \Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration
    {
        return $this->tenancy->security()->rollbackChange(changeId: $changeId);
    }

    public function beginAdminElevation() : \Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevation
    {
        return $this->access->beginAdminElevation();
    }

    public function endAdminElevation() : void
    {
        $this->access->endAdminElevation();
    }

    public function requireAdminElevation() : void
    {
        $this->access->requireAdminElevation();
    }

    public function assessCurrentRisk(string|null $ipAddress = null, string|null $userAgent = null) : \Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskDecision|null
    {
        return $this->access->assessCurrentRisk(ipAddress: $ipAddress, userAgent: $userAgent);
    }

    /**
     * @return list<\Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskSignal>
     */
    public function readRiskSignals(int|null $userId = null) : array
    {
        return $this->access->readRiskSignals(userId: $userId);
    }

    public function explainAccessDenied(string $resource, string|null $requiredPermission = null, string|null $tenant = null, string|null $resourceTenant = null) : \Avax\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation
    {
        return $this->diagnostics->explainAccessDenied(
            resource          : $resource,
            requiredPermission: $requiredPermission,
            tenant            : $tenant,
            resourceTenant    : $resourceTenant
        );
    }

    public function explainStepUpRequired(string $action, bool|null $phishingResistantRequired = null, int|null $freshAfterSeconds = null) : \Avax\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation
    {
        return $this->diagnostics->explainStepUpRequired(
            action                   : $action,
            phishingResistantRequired: $phishingResistantRequired,
            freshAfterSeconds        : $freshAfterSeconds
        );
    }

    public function explainSenderConstraintFailure(string $reason, string|null $requiredConstraint = null) : \Avax\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation
    {
        return $this->diagnostics->explainSenderConstraintFailure(
            reason            : $reason,
            requiredConstraint: $requiredConstraint
        );
    }

    public function explainSessionRevocation(string $status, string|null $sessionId = null) : \Avax\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation
    {
        return $this->diagnostics->explainSessionRevocation(status: $status, sessionId: $sessionId);
    }

    public function explainTrustedDeviceDecision(string|null $deviceId = null) : \Avax\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation
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
