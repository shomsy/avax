<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Capabilities\Access\Access;
use Avax\Auth\System\Capabilities\Access\AccessInterface;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskDecision;
use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskSignal;
use Avax\Auth\System\Capabilities\Diagnostics\Diagnostics;
use Avax\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation;
use Avax\Auth\System\Capabilities\ExternalIdentity\ExternalIdentity;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistrationData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\AuthorizeCode\AuthorizeCodeData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeClientCredentials\ExchangeClientCredentialsData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeRefreshToken\ExchangeRefreshTokenData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\IntrospectToken\IntrospectTokenData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\IntrospectToken\TokenIntrospection;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\OAuthTokenGrant;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ReadWorkloadIdentities\WorkloadIdentityProfile;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RegisterClient\RegisterClientData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RevokeToken\RevokeTokenData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\UpdateClient\UpdateClientData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\IssuedAuthorizationCode;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\RegisteredOAuthClient;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponseData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse\JarmResponse;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutData as OidcLogoutData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutResult as OidcLogoutResult;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequestData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushedAuthorizationRequest;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadUserInfo\OidcUserInfo;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcJsonWebKeySet;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcProviderMetadata;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\CompleteFederatedLogin\CompleteFederatedLoginData;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\RegisterConnection\RegisterFederationConnectionData;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\StartFederatedLogin\StartFederatedLoginData;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\VerifyDomain\VerifyFederationDomainData;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionHealth;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\StartedFederatedLogin;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\BackupCodeSet;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaEnrollment;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaRecoveryChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\BeginMfaRecoveryData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\ConfirmMfaRecoveryData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\VerifyMfaChallengeData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyAuthenticationChallenge;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyRegistration;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey\RenamePasskeyData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential;
use Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\ActiveSession;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshAuthenticationRequest;
use Avax\Auth\System\Capabilities\IdentitySync\IdentitySync;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkRequest;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkResponse;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUserData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutageData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ScimProvisioningResult;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage\RecoverScimDirectoryOutageData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken\RotatedScimToken;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups\SyncScimGroupsData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\RegisteredScimDirectory;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevation;
use Avax\Auth\System\Capabilities\Tenancy\Model\Tenant;
use Avax\Auth\System\Capabilities\Tenancy\Model\TenantMember;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\AcceptInvite\AcceptTenantInviteData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\CreateTenant\CreateTenantData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\InviteTenantMemberData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\IssuedTenantInvite;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\RemoveMember\RemoveTenantMemberData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\SuspendMember\SuspendTenantMemberData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TransferOwnership\TransferTenantOwnershipData;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration;
use Avax\Auth\System\Capabilities\Tenancy\Tenancy;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Flows\ChangeEmail\BeginEmailChangeData;
use Avax\Auth\System\Flows\ChangeEmail\ConfirmEmailChangeData;
use Avax\Auth\System\Flows\ChangeEmail\EmailChangeChallenge;
use Avax\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordResetData;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\PasswordResetChallenge;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPasswordData;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Flows\Register\RegistrationResult;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerificationData;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationChallenge;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmailData;
use SensitiveParameter;

/**
 * Main entry point for Avax Auth.
 *
 * Small public surface, explicit ownership: zone facades own behavior, Auth only delegates.
 */
final readonly class Auth implements AuthInterface
{
    public function __construct(
        #[SensitiveParameter] private Access $access,
        private Diagnostics                  $diagnostics,
        private Identity                     $identity,
        private ExternalIdentity             $externalIdentity,
        private IdentitySync                 $identitySync,
        private Tenancy                      $tenancy
    ) {}

    public static function configuration() : AuthBuilder
    {
        return new AuthBuilder();
    }

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

    public function logout() : void
    {
        $this->identity->logout();
    }

    public function logoutAllSessions() : void
    {
        $this->identity->logoutAllSessions();
    }

    /**
     * @return list<ActiveSession>
     * @throws Unauthenticated
     */
    public function readActiveSessions() : array
    {
        return $this->identity->readActiveSessions();
    }

    public function revokeSession(#[SensitiveParameter] string $sessionId) : void
    {
        $this->identity->revokeSession(sessionId: $sessionId);
    }

    public function check() : bool
    {
        return $this->access->check();
    }

    public function user() : AuthenticatedUser|null
    {
        return $this->access->user();
    }

    public function access() : AccessInterface
    {
        return $this->access->access();
    }

    public function explainAccessDenied(
        string      $resource,
        string|null $requiredPermission = null,
        string|null $tenant = null,
        string|null $resourceTenant = null
    ) : AuthIssueExplanation
    {
        return $this->diagnostics->explainAccessDenied(
            resource          : $resource,
            requiredPermission: $requiredPermission,
            tenant            : $tenant,
            resourceTenant    : $resourceTenant
        );
    }

    public function explainStepUpRequired(
        string   $action,
        bool     $phishingResistantRequired = false,
        int|null $freshAfterSeconds = null
    ) : AuthIssueExplanation
    {
        return $this->diagnostics->explainStepUpRequired(
            action                   : $action,
            phishingResistantRequired: $phishingResistantRequired,
            freshAfterSeconds        : $freshAfterSeconds
        );
    }

    public function explainSenderConstraintFailure(string $reason, string|null $requiredConstraint = null) : AuthIssueExplanation
    {
        return $this->diagnostics->explainSenderConstraintFailure(
            reason            : $reason,
            requiredConstraint: $requiredConstraint
        );
    }

    public function explainSessionRevocation(string $status, #[SensitiveParameter] string|null $sessionId = null) : AuthIssueExplanation
    {
        return $this->diagnostics->explainSessionRevocation(status: $status, sessionId: $sessionId);
    }

    public function explainTrustedDeviceDecision(string|null $deviceId = null) : AuthIssueExplanation
    {
        return $this->diagnostics->explainTrustedDeviceDecision(deviceId: $deviceId);
    }

    public function changePassword(ChangePasswordData $data) : void
    {
        $this->identity->changePassword(data: $data);
    }

    public function beginEmailChange(BeginEmailChangeData $data) : EmailChangeChallenge
    {
        return $this->identity->beginEmailChange(data: $data);
    }

    public function confirmEmailChange(ConfirmEmailChangeData $data) : bool
    {
        return $this->identity->confirmEmailChange(data: $data);
    }

    public function register(RegistrationData $data) : RegistrationResult
    {
        return $this->identity->register(data: $data);
    }

    public function refresh(RefreshAuthenticationRequest $request) : AuthenticationResult
    {
        return $this->identity->refresh(request: $request);
    }

    public function registerOAuthClient(RegisterClientData $data) : RegisteredOAuthClient
    {
        return $this->externalIdentity->registerOAuthClient(data: $data);
    }

    public function approveOAuthClientRegistration(ApproveClientRegistrationData $data) : OAuthClient
    {
        return $this->externalIdentity->approveOAuthClientRegistration(data: $data);
    }

    public function updateOAuthClient(UpdateClientData $data) : OAuthClient
    {
        return $this->externalIdentity->updateOAuthClient(data: $data);
    }

    public function disableOAuthClient(string $clientId) : OAuthClient
    {
        return $this->externalIdentity->disableOAuthClient(clientId: $clientId);
    }

    public function rotateOAuthClientSecret(string $clientId) : RegisteredOAuthClient
    {
        return $this->externalIdentity->rotateOAuthClientSecret(clientId: $clientId);
    }

    /**
     * @return list<OAuthClient>
     */
    public function readOAuthClients() : array
    {
        return $this->externalIdentity->readOAuthClients();
    }

    /**
     * @return list<WorkloadIdentityProfile>
     */
    public function readWorkloadIdentities() : array
    {
        return $this->externalIdentity->readWorkloadIdentities();
    }

    public function readOidcProviderMetadata() : OidcProviderMetadata
    {
        return $this->externalIdentity->readOidcProviderMetadata();
    }

    public function readOidcJsonWebKeySet() : OidcJsonWebKeySet
    {
        return $this->externalIdentity->readOidcJsonWebKeySet();
    }

    public function readOidcUserInfo(#[SensitiveParameter] string $accessToken) : OidcUserInfo
    {
        return $this->externalIdentity->readOidcUserInfo(accessToken: $accessToken);
    }

    public function pushOidcAuthorizationRequest(PushAuthorizationRequestData $data) : PushedAuthorizationRequest
    {
        return $this->externalIdentity->pushOidcAuthorizationRequest(data: $data);
    }

    public function oidcLogout(OidcLogoutData $data) : OidcLogoutResult
    {
        return $this->externalIdentity->oidcLogout(data: $data);
    }

    public function buildOidcJarmResponse(BuildJarmResponseData $data) : JarmResponse
    {
        return $this->externalIdentity->buildOidcJarmResponse(data: $data);
    }

    public function registerScimDirectory(RegisterScimDirectoryData $data) : RegisteredScimDirectory
    {
        return $this->identitySync->registerScimDirectory(data: $data);
    }

    /**
     * @return list<ScimDirectory>
     */
    public function readScimDirectories(string|null $tenantSlug = null) : array
    {
        return $this->identitySync->readScimDirectories(tenantSlug: $tenantSlug);
    }

    public function rotateScimToken(string $directoryId) : RotatedScimToken
    {
        return $this->identitySync->rotateScimToken(directoryId: $directoryId);
    }

    public function markScimDirectoryOutage(MarkScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->identitySync->markScimDirectoryOutage(data: $data);
    }

    public function recoverScimDirectoryOutage(RecoverScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->identitySync->recoverScimDirectoryOutage(data: $data);
    }

    public function provisionScimUser(ProvisionScimUserData $data) : ScimProvisioningResult
    {
        return $this->identitySync->provisionScimUser(data: $data);
    }

    public function deleteScimUser(DeleteScimUserData $data) : void
    {
        $this->identitySync->deleteScimUser(data: $data);
    }

    public function readScimUsers(string $directoryId) : array
    {
        return $this->identitySync->readScimUsers(directoryId: $directoryId);
    }

    public function readScimGroups(string $directoryId) : array
    {
        return $this->identitySync->readScimGroups(directoryId: $directoryId);
    }

    public function syncScimGroups(SyncScimGroupsData $data) : ScimProvisioningResult
    {
        return $this->identitySync->syncScimGroups(data: $data);
    }

    public function runScimBulk(ScimBulkRequest $data) : ScimBulkResponse
    {
        return $this->identitySync->runScimBulk(data: $data);
    }

    public function createTenant(CreateTenantData $data) : Tenant
    {
        return $this->tenancy->createTenant(data: $data);
    }

    /**
     * @return list<Tenant>
     */
    public function readTenants() : array
    {
        return $this->tenancy->readTenants();
    }

    public function inviteTenantMember(InviteTenantMemberData $data) : IssuedTenantInvite
    {
        return $this->tenancy->inviteTenantMember(data: $data);
    }

    public function acceptTenantInvite(AcceptTenantInviteData $data) : TenantMember
    {
        return $this->tenancy->acceptTenantInvite(data: $data);
    }

    /**
     * @return list<TenantMember>
     */
    public function readTenantMembers(string $tenantSlug) : array
    {
        return $this->tenancy->readTenantMembers(tenantSlug: $tenantSlug);
    }

    public function removeTenantMember(RemoveTenantMemberData $data) : void
    {
        $this->tenancy->removeTenantMember(data: $data);
    }

    public function suspendTenantMember(SuspendTenantMemberData $data) : TenantMember
    {
        return $this->tenancy->suspendTenantMember(data: $data);
    }

    public function transferTenantOwnership(TransferTenantOwnershipData $data) : Tenant
    {
        return $this->tenancy->transferTenantOwnership(data: $data);
    }

    public function readTenantSecurityConfiguration(string $tenantSlug) : TenantSecurityConfiguration|null
    {
        return $this->tenancy->readTenantSecurityConfiguration(tenantSlug: $tenantSlug);
    }

    public function readTenantSecurityChangeRequest(string $changeId) : TenantSecurityChangeRequest|null
    {
        return $this->tenancy->readTenantSecurityChangeRequest(changeId: $changeId);
    }

    /**
     * @return list<TenantSecurityChangeRequest>
     */
    public function readTenantSecurityChangeRequests(string $tenantSlug) : array
    {
        return $this->tenancy->readTenantSecurityChangeRequests(tenantSlug: $tenantSlug);
    }

    public function beginTenantSecurityChange(BeginTenantSecurityChangeData $data) : TenantSecurityChangeRequest
    {
        return $this->tenancy->beginTenantSecurityChange(data: $data);
    }

    public function approveTenantSecurityChange(string $changeId, string $approvedBy) : TenantSecurityChangeRequest
    {
        return $this->tenancy->approveTenantSecurityChange(changeId: $changeId, approvedBy: $approvedBy);
    }

    public function applyTenantSecurityChange(string $changeId) : TenantSecurityConfiguration
    {
        return $this->tenancy->applyTenantSecurityChange(changeId: $changeId);
    }

    public function rollbackTenantSecurityChange(string $changeId) : TenantSecurityConfiguration
    {
        return $this->tenancy->rollbackTenantSecurityChange(changeId: $changeId);
    }

    public function authorizeOAuthCode(AuthorizeCodeData $data) : IssuedAuthorizationCode
    {
        return $this->externalIdentity->authorizeOAuthCode(data: $data);
    }

    public function exchangeOAuthCode(ExchangeAuthorizationCodeData $data) : OAuthTokenGrant
    {
        return $this->externalIdentity->exchangeOAuthCode(data: $data);
    }

    public function exchangeOAuthClientCredentials(ExchangeClientCredentialsData $data) : OAuthTokenGrant
    {
        return $this->externalIdentity->exchangeOAuthClientCredentials(data: $data);
    }

    public function exchangeOAuthRefreshToken(ExchangeRefreshTokenData $data) : OAuthTokenGrant
    {
        return $this->externalIdentity->exchangeOAuthRefreshToken(data: $data);
    }

    public function revokeOAuthToken(RevokeTokenData $data) : void
    {
        $this->externalIdentity->revokeOAuthToken(data: $data);
    }

    public function introspectOAuthToken(IntrospectTokenData $data) : TokenIntrospection
    {
        return $this->externalIdentity->introspectOAuthToken(data: $data);
    }

    public function beginAdminElevation() : AdminElevation
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

    public function suspendUser(int $userId) : void
    {
        $this->identitySync->suspendUser(userId: $userId);
    }

    public function reactivateUser(int $userId) : void
    {
        $this->identitySync->reactivateUser(userId: $userId);
    }

    public function deprovisionUser(int $userId) : void
    {
        $this->identitySync->deprovisionUser(userId: $userId);
    }

    public function beginPasskeyRegistration() : PasskeyRegistration
    {
        return $this->identity->beginPasskeyRegistration();
    }

    public function completePasskeyRegistration(CompletePasskeyRegistrationData $data) : PasskeyCredential
    {
        return $this->identity->completePasskeyRegistration(data: $data);
    }

    public function beginPasskeyAuthentication(BeginPasskeyAuthenticationData $data) : PasskeyAuthenticationChallenge
    {
        return $this->identity->beginPasskeyAuthentication(data: $data);
    }

    public function completePasskeyAuthentication(CompletePasskeyAuthenticationData $data) : AuthenticationResult
    {
        return $this->identity->completePasskeyAuthentication(data: $data);
    }

    /**
     * @return list<PasskeyCredential>
     */
    public function readPasskeys() : array
    {
        return $this->identity->readPasskeys();
    }

    public function renamePasskey(RenamePasskeyData $data) : PasskeyCredential
    {
        return $this->identity->renamePasskey(data: $data);
    }

    public function revokePasskey(#[SensitiveParameter] string $credentialId) : void
    {
        $this->identity->revokePasskey(credentialId: $credentialId);
    }

    public function registerFederationConnection(RegisterFederationConnectionData $data) : FederationConnection
    {
        return $this->externalIdentity->registerFederationConnection(data: $data);
    }

    /**
     * @return list<FederationConnection>
     */
    public function readFederationConnections() : array
    {
        return $this->externalIdentity->readFederationConnections();
    }

    public function verifyFederationDomain(VerifyFederationDomainData $data) : FederationConnection
    {
        return $this->externalIdentity->verifyFederationDomain(data: $data);
    }

    public function syncFederationMetadata(string $connectionId) : FederationConnection
    {
        return $this->externalIdentity->syncFederationMetadata(connectionId: $connectionId);
    }

    public function checkFederationConnectionHealth(string $connectionId) : FederationConnectionHealth
    {
        return $this->externalIdentity->checkFederationConnectionHealth(connectionId: $connectionId);
    }

    public function evaluateFederationBreakGlassBypass(string $connectionId) : bool
    {
        return $this->externalIdentity->evaluateFederationBreakGlassBypass(connectionId: $connectionId);
    }

    public function discoverFederationConnection(#[SensitiveParameter] string $email) : FederationConnection|null
    {
        return $this->externalIdentity->discoverFederationConnection(email: $email);
    }

    public function startFederatedLogin(StartFederatedLoginData $data) : StartedFederatedLogin
    {
        return $this->externalIdentity->startFederatedLogin(data: $data);
    }

    public function completeFederatedLogin(CompleteFederatedLoginData $data) : AuthenticationResult
    {
        return $this->externalIdentity->completeFederatedLogin(data: $data);
    }

    public function assessCurrentRisk(#[SensitiveParameter] string|null $ipAddress = null, string|null $userAgent = null) : RiskDecision|null
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

    public function beginPasswordReset(BeginPasswordResetData $data) : PasswordResetChallenge
    {
        return $this->identity->beginPasswordReset(data: $data);
    }

    public function resetPassword(ResetPasswordData $data) : bool
    {
        return $this->identity->resetPassword(data: $data);
    }

    public function beginEmailVerification(BeginEmailVerificationData $data) : EmailVerificationChallenge
    {
        return $this->identity->beginEmailVerification(data: $data);
    }

    public function verifyEmail(VerifyEmailData $data) : bool
    {
        return $this->identity->verifyEmail(data: $data);
    }

    public function startMfaEnrollment() : MfaEnrollment
    {
        return $this->identity->startMfaEnrollment();
    }

    public function confirmMfaEnrollment(ConfirmMfaEnrollmentData $data) : BackupCodeSet
    {
        return $this->identity->confirmMfaEnrollment(data: $data);
    }

    public function cancelMfaEnrollment() : void
    {
        $this->identity->cancelMfaEnrollment();
    }

    public function beginMfaChallenge() : MfaChallenge
    {
        return $this->identity->beginMfaChallenge();
    }

    public function verifyMfaChallenge(VerifyMfaChallengeData $data) : AuthenticationResult
    {
        return $this->identity->verifyMfaChallenge(data: $data);
    }

    public function regenerateBackupCodes() : BackupCodeSet
    {
        return $this->identity->regenerateBackupCodes();
    }

    public function disableMfa() : void
    {
        $this->identity->disableMfa();
    }

    public function beginMfaRecovery(BeginMfaRecoveryData $data) : MfaRecoveryChallenge
    {
        return $this->identity->beginMfaRecovery(data: $data);
    }

    public function confirmMfaRecovery(ConfirmMfaRecoveryData $data) : void
    {
        $this->identity->confirmMfaRecovery(data: $data);
    }
}
