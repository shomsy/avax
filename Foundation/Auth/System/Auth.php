<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Capabilities\Access\AccessInterface;
use Avax\Auth\System\Capabilities\Access\AccessFacade;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Diagnostics\DiagnosticsFacade;
use Avax\Auth\System\Capabilities\Explainability\AuthIssueExplanation;
use Avax\Auth\System\Capabilities\ExternalIdentity\ExternalIdentityFacade;
use Avax\Auth\System\Capabilities\Federation\FederationConnection;
use Avax\Auth\System\Capabilities\Federation\FederationConnectionHealth;
use Avax\Auth\System\Capabilities\Federation\StartedFederatedLogin;
use Avax\Auth\System\Capabilities\Identity\IdentityFacade;
use Avax\Auth\System\Capabilities\IdentitySync\IdentitySyncFacade;
use Avax\Auth\System\Capabilities\OAuth\IssuedAuthorizationCode;
use Avax\Auth\System\Capabilities\OAuth\OAuthClient;
use Avax\Auth\System\Capabilities\OAuth\RegisteredOAuthClient;
use Avax\Auth\System\Capabilities\Oidc\OidcJsonWebKeySet;
use Avax\Auth\System\Capabilities\Oidc\OidcProviderMetadata;
use Avax\Auth\System\Capabilities\Passkey\PasskeyCredential;
use Avax\Auth\System\Capabilities\Risk\RiskDecision;
use Avax\Auth\System\Capabilities\Risk\RiskSignal;
use Avax\Auth\System\Capabilities\Scim\RegisteredScimDirectory;
use Avax\Auth\System\Capabilities\Scim\ScimDirectory;
use Avax\Auth\System\Capabilities\Tenant\Tenant;
use Avax\Auth\System\Capabilities\Tenant\TenantMember;
use Avax\Auth\System\Capabilities\Tenant\TenancyFacade;
use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityConfiguration;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Flows\AdminRealm\AdminElevation;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flows\ChangeEmail\BeginEmailChangeData;
use Avax\Auth\System\Flows\ChangeEmail\ConfirmEmailChangeData;
use Avax\Auth\System\Flows\ChangeEmail\EmailChangeChallenge;
use Avax\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flows\Federation\CompleteFederatedLogin\CompleteFederatedLoginData;
use Avax\Auth\System\Flows\Federation\RegisterConnection\RegisterFederationConnectionData;
use Avax\Auth\System\Flows\Federation\StartFederatedLogin\StartFederatedLoginData;
use Avax\Auth\System\Flows\Federation\VerifyDomain\VerifyFederationDomainData;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\Mfa\BackupCodeSet;
use Avax\Auth\System\Flows\Mfa\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Flows\Mfa\MfaChallenge;
use Avax\Auth\System\Flows\Mfa\MfaEnrollment;
use Avax\Auth\System\Flows\Mfa\MfaRecoveryChallenge;
use Avax\Auth\System\Flows\Mfa\Recover\BeginMfaRecoveryData;
use Avax\Auth\System\Flows\Mfa\Recover\ConfirmMfaRecoveryData;
use Avax\Auth\System\Flows\Mfa\VerifyMfaChallengeData;
use Avax\Auth\System\Flows\OAuth\ApproveClientRegistration\ApproveClientRegistrationData;
use Avax\Auth\System\Flows\OAuth\AuthorizeCode\AuthorizeCodeData;
use Avax\Auth\System\Flows\OAuth\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use Avax\Auth\System\Flows\OAuth\ExchangeClientCredentials\ExchangeClientCredentialsData;
use Avax\Auth\System\Flows\OAuth\ExchangeRefreshToken\ExchangeRefreshTokenData;
use Avax\Auth\System\Flows\OAuth\IntrospectToken\IntrospectTokenData;
use Avax\Auth\System\Flows\OAuth\IntrospectToken\TokenIntrospection;
use Avax\Auth\System\Flows\OAuth\OAuthTokenGrant;
use Avax\Auth\System\Flows\OAuth\ReadWorkloadIdentities\WorkloadIdentityProfile;
use Avax\Auth\System\Flows\OAuth\RegisterClient\RegisterClientData;
use Avax\Auth\System\Flows\OAuth\RevokeToken\RevokeTokenData;
use Avax\Auth\System\Flows\OAuth\UpdateClient\UpdateClientData;
use Avax\Auth\System\Flows\Oidc\JarmResponse\BuildJarmResponseData;
use Avax\Auth\System\Flows\Oidc\JarmResponse\JarmResponse;
use Avax\Auth\System\Flows\Oidc\Logout\LogoutData as OidcLogoutData;
use Avax\Auth\System\Flows\Oidc\Logout\LogoutResult as OidcLogoutResult;
use Avax\Auth\System\Flows\Oidc\PushAuthorizationRequest\PushAuthorizationRequestData;
use Avax\Auth\System\Flows\Oidc\PushAuthorizationRequest\PushedAuthorizationRequest;
use Avax\Auth\System\Flows\Oidc\ReadUserInfo\OidcUserInfo;
use Avax\Auth\System\Flows\Passkey\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Auth\System\Flows\Passkey\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Auth\System\Flows\Passkey\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Auth\System\Flows\Passkey\PasskeyAuthenticationChallenge;
use Avax\Auth\System\Flows\Passkey\PasskeyRegistration;
use Avax\Auth\System\Flows\Passkey\RenamePasskey\RenamePasskeyData;
use Avax\Auth\System\Flows\Recover\BeginPasswordResetData;
use Avax\Auth\System\Flows\Recover\PasswordResetChallenge;
use Avax\Auth\System\Flows\Recover\ResetPasswordData;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Flows\Register\RegistrationResult;
use Avax\Auth\System\Flows\Scim\Bulk\ScimBulkRequest;
use Avax\Auth\System\Flows\Scim\Bulk\ScimBulkResponse;
use Avax\Auth\System\Flows\Scim\DeleteUser\DeleteScimUserData;
use Avax\Auth\System\Flows\Scim\MarkOutage\MarkScimDirectoryOutageData;
use Avax\Auth\System\Flows\Scim\ProvisionUser\ProvisionScimUserData;
use Avax\Auth\System\Flows\Scim\ProvisionUser\ScimProvisioningResult;
use Avax\Auth\System\Flows\Scim\RecoverOutage\RecoverScimDirectoryOutageData;
use Avax\Auth\System\Flows\Scim\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Auth\System\Flows\Scim\RotateToken\RotatedScimToken;
use Avax\Auth\System\Flows\Scim\SyncGroups\SyncScimGroupsData;
use Avax\Auth\System\Flows\Session\ActiveSession;
use Avax\Auth\System\Flows\Tenant\AcceptInvite\AcceptTenantInviteData;
use Avax\Auth\System\Flows\Tenant\CreateTenant\CreateTenantData;
use Avax\Auth\System\Flows\Tenant\InviteMember\InviteTenantMemberData;
use Avax\Auth\System\Flows\Tenant\InviteMember\IssuedTenantInvite;
use Avax\Auth\System\Flows\Tenant\RemoveMember\RemoveTenantMemberData;
use Avax\Auth\System\Flows\Tenant\SuspendMember\SuspendTenantMemberData;
use Avax\Auth\System\Flows\Tenant\TransferOwnership\TransferTenantOwnershipData;
use Avax\Auth\System\Flows\Token\RefreshAuthenticationRequest;
use Avax\Auth\System\Flows\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use Avax\Auth\System\Flows\Verify\BeginEmailVerificationData;
use Avax\Auth\System\Flows\Verify\EmailVerificationChallenge;
use Avax\Auth\System\Flows\Verify\VerifyEmailData;
use SensitiveParameter;

/**
 * Main entry point for Avax Auth.
 *
 * Small public surface, explicit ownership: zone facades own behavior, Auth only delegates.
 */
final readonly class Auth implements AuthInterface
{
    public function __construct(
        #[\SensitiveParameter] private AccessFacade $accessFacade,
        private DiagnosticsFacade                   $diagnosticsFacade,
        private IdentityFacade                      $identityFacade,
        private ExternalIdentityFacade              $externalIdentityFacade,
        private IdentitySyncFacade                  $identitySyncFacade,
        private TenancyFacade                       $tenancyFacade
    ) {}

    public static function configuration() : AuthBuilder
    {
        return new AuthBuilder();
    }

    public function login(#[SensitiveParameter] Credentials $credentials) : AuthenticationResult
    {
        return $this->identityFacade->login(credentials: $credentials);
    }

    public function authenticateRequest(AuthenticationRequest $request) : AuthenticationContext
    {
        return $this->accessFacade->authenticateRequest(request: $request);
    }

    public function current() : AuthenticationContext
    {
        return $this->accessFacade->current();
    }

    public function logout() : void
    {
        $this->identityFacade->logout();
    }

    public function logoutAllSessions() : void
    {
        $this->identityFacade->logoutAllSessions();
    }

    /**
     * @return list<ActiveSession>
     * @throws Unauthenticated
     */
    public function readActiveSessions() : array
    {
        return $this->identityFacade->readActiveSessions();
    }

    public function revokeSession(#[SensitiveParameter] string $sessionId) : void
    {
        $this->identityFacade->revokeSession(sessionId: $sessionId);
    }

    public function check() : bool
    {
        return $this->accessFacade->check();
    }

    public function user() : AuthenticatedUser|null
    {
        return $this->accessFacade->user();
    }

    public function access() : AccessInterface
    {
        return $this->accessFacade->access();
    }

    public function explainAccessDenied(
        string      $resource,
        string|null $requiredPermission = null,
        string|null $tenant = null,
        string|null $resourceTenant = null
    ) : AuthIssueExplanation
    {
        return $this->diagnosticsFacade->explainAccessDenied(
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
        return $this->diagnosticsFacade->explainStepUpRequired(
            action                   : $action,
            phishingResistantRequired: $phishingResistantRequired,
            freshAfterSeconds        : $freshAfterSeconds
        );
    }

    public function explainSenderConstraintFailure(string $reason, string|null $requiredConstraint = null) : AuthIssueExplanation
    {
        return $this->diagnosticsFacade->explainSenderConstraintFailure(
            reason            : $reason,
            requiredConstraint: $requiredConstraint
        );
    }

    public function explainSessionRevocation(string $status, #[\SensitiveParameter] string|null $sessionId = null) : AuthIssueExplanation
    {
        return $this->diagnosticsFacade->explainSessionRevocation(status: $status, sessionId: $sessionId);
    }

    public function explainTrustedDeviceDecision(string|null $deviceId = null) : AuthIssueExplanation
    {
        return $this->diagnosticsFacade->explainTrustedDeviceDecision(deviceId: $deviceId);
    }

    public function changePassword(ChangePasswordData $data) : void
    {
        $this->identityFacade->changePassword(data: $data);
    }

    public function beginEmailChange(BeginEmailChangeData $data) : EmailChangeChallenge
    {
        return $this->identityFacade->beginEmailChange(data: $data);
    }

    public function confirmEmailChange(ConfirmEmailChangeData $data) : bool
    {
        return $this->identityFacade->confirmEmailChange(data: $data);
    }

    public function register(RegistrationData $data) : RegistrationResult
    {
        return $this->identityFacade->register(data: $data);
    }

    public function refresh(RefreshAuthenticationRequest $request) : AuthenticationResult
    {
        return $this->identityFacade->refresh(request: $request);
    }

    public function registerOAuthClient(RegisterClientData $data) : RegisteredOAuthClient
    {
        return $this->externalIdentityFacade->registerOAuthClient(data: $data);
    }

    public function approveOAuthClientRegistration(ApproveClientRegistrationData $data) : OAuthClient
    {
        return $this->externalIdentityFacade->approveOAuthClientRegistration(data: $data);
    }

    public function updateOAuthClient(UpdateClientData $data) : OAuthClient
    {
        return $this->externalIdentityFacade->updateOAuthClient(data: $data);
    }

    public function disableOAuthClient(string $clientId) : OAuthClient
    {
        return $this->externalIdentityFacade->disableOAuthClient(clientId: $clientId);
    }

    public function rotateOAuthClientSecret(string $clientId) : RegisteredOAuthClient
    {
        return $this->externalIdentityFacade->rotateOAuthClientSecret(clientId: $clientId);
    }

    /**
     * @return list<OAuthClient>
     */
    public function readOAuthClients() : array
    {
        return $this->externalIdentityFacade->readOAuthClients();
    }

    /**
     * @return list<WorkloadIdentityProfile>
     */
    public function readWorkloadIdentities() : array
    {
        return $this->externalIdentityFacade->readWorkloadIdentities();
    }

    public function readOidcProviderMetadata() : OidcProviderMetadata
    {
        return $this->externalIdentityFacade->readOidcProviderMetadata();
    }

    public function readOidcJsonWebKeySet() : OidcJsonWebKeySet
    {
        return $this->externalIdentityFacade->readOidcJsonWebKeySet();
    }

    public function readOidcUserInfo(#[\SensitiveParameter] string $accessToken) : OidcUserInfo
    {
        return $this->externalIdentityFacade->readOidcUserInfo(accessToken: $accessToken);
    }

    public function pushOidcAuthorizationRequest(PushAuthorizationRequestData $data) : PushedAuthorizationRequest
    {
        return $this->externalIdentityFacade->pushOidcAuthorizationRequest(data: $data);
    }

    public function oidcLogout(OidcLogoutData $data) : OidcLogoutResult
    {
        return $this->externalIdentityFacade->oidcLogout(data: $data);
    }

    public function buildOidcJarmResponse(BuildJarmResponseData $data) : JarmResponse
    {
        return $this->externalIdentityFacade->buildOidcJarmResponse(data: $data);
    }

    public function registerScimDirectory(RegisterScimDirectoryData $data) : RegisteredScimDirectory
    {
        return $this->identitySyncFacade->registerScimDirectory(data: $data);
    }

    /**
     * @return list<ScimDirectory>
     */
    public function readScimDirectories(string|null $tenantSlug = null) : array
    {
        return $this->identitySyncFacade->readScimDirectories(tenantSlug: $tenantSlug);
    }

    public function rotateScimToken(string $directoryId) : RotatedScimToken
    {
        return $this->identitySyncFacade->rotateScimToken(directoryId: $directoryId);
    }

    public function markScimDirectoryOutage(MarkScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->identitySyncFacade->markScimDirectoryOutage(data: $data);
    }

    public function recoverScimDirectoryOutage(RecoverScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->identitySyncFacade->recoverScimDirectoryOutage(data: $data);
    }

    public function provisionScimUser(ProvisionScimUserData $data) : ScimProvisioningResult
    {
        return $this->identitySyncFacade->provisionScimUser(data: $data);
    }

    public function deleteScimUser(DeleteScimUserData $data) : void
    {
        $this->identitySyncFacade->deleteScimUser(data: $data);
    }

    public function readScimUsers(string $directoryId) : array
    {
        return $this->identitySyncFacade->readScimUsers(directoryId: $directoryId);
    }

    public function readScimGroups(string $directoryId) : array
    {
        return $this->identitySyncFacade->readScimGroups(directoryId: $directoryId);
    }

    public function syncScimGroups(SyncScimGroupsData $data) : ScimProvisioningResult
    {
        return $this->identitySyncFacade->syncScimGroups(data: $data);
    }

    public function runScimBulk(ScimBulkRequest $data) : ScimBulkResponse
    {
        return $this->identitySyncFacade->runScimBulk(data: $data);
    }

    public function createTenant(CreateTenantData $data) : Tenant
    {
        return $this->tenancyFacade->createTenant(data: $data);
    }

    /**
     * @return list<Tenant>
     */
    public function readTenants() : array
    {
        return $this->tenancyFacade->readTenants();
    }

    public function inviteTenantMember(InviteTenantMemberData $data) : IssuedTenantInvite
    {
        return $this->tenancyFacade->inviteTenantMember(data: $data);
    }

    public function acceptTenantInvite(AcceptTenantInviteData $data) : TenantMember
    {
        return $this->tenancyFacade->acceptTenantInvite(data: $data);
    }

    /**
     * @return list<TenantMember>
     */
    public function readTenantMembers(string $tenantSlug) : array
    {
        return $this->tenancyFacade->readTenantMembers(tenantSlug: $tenantSlug);
    }

    public function removeTenantMember(RemoveTenantMemberData $data) : void
    {
        $this->tenancyFacade->removeTenantMember(data: $data);
    }

    public function suspendTenantMember(SuspendTenantMemberData $data) : TenantMember
    {
        return $this->tenancyFacade->suspendTenantMember(data: $data);
    }

    public function transferTenantOwnership(TransferTenantOwnershipData $data) : Tenant
    {
        return $this->tenancyFacade->transferTenantOwnership(data: $data);
    }

    public function readTenantSecurityConfiguration(string $tenantSlug) : TenantSecurityConfiguration|null
    {
        return $this->tenancyFacade->readTenantSecurityConfiguration(tenantSlug: $tenantSlug);
    }

    public function readTenantSecurityChangeRequest(string $changeId) : TenantSecurityChangeRequest|null
    {
        return $this->tenancyFacade->readTenantSecurityChangeRequest(changeId: $changeId);
    }

    /**
     * @return list<TenantSecurityChangeRequest>
     */
    public function readTenantSecurityChangeRequests(string $tenantSlug) : array
    {
        return $this->tenancyFacade->readTenantSecurityChangeRequests(tenantSlug: $tenantSlug);
    }

    public function beginTenantSecurityChange(BeginTenantSecurityChangeData $data) : TenantSecurityChangeRequest
    {
        return $this->tenancyFacade->beginTenantSecurityChange(data: $data);
    }

    public function approveTenantSecurityChange(string $changeId, string $approvedBy) : TenantSecurityChangeRequest
    {
        return $this->tenancyFacade->approveTenantSecurityChange(changeId: $changeId, approvedBy: $approvedBy);
    }

    public function applyTenantSecurityChange(string $changeId) : TenantSecurityConfiguration
    {
        return $this->tenancyFacade->applyTenantSecurityChange(changeId: $changeId);
    }

    public function rollbackTenantSecurityChange(string $changeId) : TenantSecurityConfiguration
    {
        return $this->tenancyFacade->rollbackTenantSecurityChange(changeId: $changeId);
    }

    public function authorizeOAuthCode(AuthorizeCodeData $data) : IssuedAuthorizationCode
    {
        return $this->externalIdentityFacade->authorizeOAuthCode(data: $data);
    }

    public function exchangeOAuthCode(ExchangeAuthorizationCodeData $data) : OAuthTokenGrant
    {
        return $this->externalIdentityFacade->exchangeOAuthCode(data: $data);
    }

    public function exchangeOAuthClientCredentials(ExchangeClientCredentialsData $data) : OAuthTokenGrant
    {
        return $this->externalIdentityFacade->exchangeOAuthClientCredentials(data: $data);
    }

    public function exchangeOAuthRefreshToken(ExchangeRefreshTokenData $data) : OAuthTokenGrant
    {
        return $this->externalIdentityFacade->exchangeOAuthRefreshToken(data: $data);
    }

    public function revokeOAuthToken(RevokeTokenData $data) : void
    {
        $this->externalIdentityFacade->revokeOAuthToken(data: $data);
    }

    public function introspectOAuthToken(IntrospectTokenData $data) : TokenIntrospection
    {
        return $this->externalIdentityFacade->introspectOAuthToken(data: $data);
    }

    public function beginAdminElevation() : AdminElevation
    {
        return $this->accessFacade->beginAdminElevation();
    }

    public function endAdminElevation() : void
    {
        $this->accessFacade->endAdminElevation();
    }

    public function requireAdminElevation() : void
    {
        $this->accessFacade->requireAdminElevation();
    }

    public function suspendUser(int $userId) : void
    {
        $this->identitySyncFacade->suspendUser(userId: $userId);
    }

    public function reactivateUser(int $userId) : void
    {
        $this->identitySyncFacade->reactivateUser(userId: $userId);
    }

    public function deprovisionUser(int $userId) : void
    {
        $this->identitySyncFacade->deprovisionUser(userId: $userId);
    }

    public function beginPasskeyRegistration() : PasskeyRegistration
    {
        return $this->identityFacade->beginPasskeyRegistration();
    }

    public function completePasskeyRegistration(CompletePasskeyRegistrationData $data) : PasskeyCredential
    {
        return $this->identityFacade->completePasskeyRegistration(data: $data);
    }

    public function beginPasskeyAuthentication(BeginPasskeyAuthenticationData $data) : PasskeyAuthenticationChallenge
    {
        return $this->identityFacade->beginPasskeyAuthentication(data: $data);
    }

    public function completePasskeyAuthentication(CompletePasskeyAuthenticationData $data) : AuthenticationResult
    {
        return $this->identityFacade->completePasskeyAuthentication(data: $data);
    }

    /**
     * @return list<PasskeyCredential>
     */
    public function readPasskeys() : array
    {
        return $this->identityFacade->readPasskeys();
    }

    public function renamePasskey(RenamePasskeyData $data) : PasskeyCredential
    {
        return $this->identityFacade->renamePasskey(data: $data);
    }

    public function revokePasskey(#[\SensitiveParameter] string $credentialId) : void
    {
        $this->identityFacade->revokePasskey(credentialId: $credentialId);
    }

    public function registerFederationConnection(RegisterFederationConnectionData $data) : FederationConnection
    {
        return $this->externalIdentityFacade->registerFederationConnection(data: $data);
    }

    /**
     * @return list<FederationConnection>
     */
    public function readFederationConnections() : array
    {
        return $this->externalIdentityFacade->readFederationConnections();
    }

    public function verifyFederationDomain(VerifyFederationDomainData $data) : FederationConnection
    {
        return $this->externalIdentityFacade->verifyFederationDomain(data: $data);
    }

    public function syncFederationMetadata(string $connectionId) : FederationConnection
    {
        return $this->externalIdentityFacade->syncFederationMetadata(connectionId: $connectionId);
    }

    public function checkFederationConnectionHealth(string $connectionId) : FederationConnectionHealth
    {
        return $this->externalIdentityFacade->checkFederationConnectionHealth(connectionId: $connectionId);
    }

    public function evaluateFederationBreakGlassBypass(string $connectionId) : bool
    {
        return $this->externalIdentityFacade->evaluateFederationBreakGlassBypass(connectionId: $connectionId);
    }

    public function discoverFederationConnection(#[\SensitiveParameter] string $email) : FederationConnection|null
    {
        return $this->externalIdentityFacade->discoverFederationConnection(email: $email);
    }

    public function startFederatedLogin(StartFederatedLoginData $data) : StartedFederatedLogin
    {
        return $this->externalIdentityFacade->startFederatedLogin(data: $data);
    }

    public function completeFederatedLogin(CompleteFederatedLoginData $data) : AuthenticationResult
    {
        return $this->externalIdentityFacade->completeFederatedLogin(data: $data);
    }

    public function assessCurrentRisk(#[SensitiveParameter] string|null $ipAddress = null, string|null $userAgent = null) : RiskDecision|null
    {
        return $this->accessFacade->assessCurrentRisk(ipAddress: $ipAddress, userAgent: $userAgent);
    }

    /**
     * @return list<RiskSignal>
     */
    public function readRiskSignals(int|null $userId = null) : array
    {
        return $this->accessFacade->readRiskSignals(userId: $userId);
    }

    public function beginPasswordReset(BeginPasswordResetData $data) : PasswordResetChallenge
    {
        return $this->identityFacade->beginPasswordReset(data: $data);
    }

    public function resetPassword(ResetPasswordData $data) : bool
    {
        return $this->identityFacade->resetPassword(data: $data);
    }

    public function beginEmailVerification(BeginEmailVerificationData $data) : EmailVerificationChallenge
    {
        return $this->identityFacade->beginEmailVerification(data: $data);
    }

    public function verifyEmail(VerifyEmailData $data) : bool
    {
        return $this->identityFacade->verifyEmail(data: $data);
    }

    public function startMfaEnrollment() : MfaEnrollment
    {
        return $this->identityFacade->startMfaEnrollment();
    }

    public function confirmMfaEnrollment(ConfirmMfaEnrollmentData $data) : BackupCodeSet
    {
        return $this->identityFacade->confirmMfaEnrollment(data: $data);
    }

    public function cancelMfaEnrollment() : void
    {
        $this->identityFacade->cancelMfaEnrollment();
    }

    public function beginMfaChallenge() : MfaChallenge
    {
        return $this->identityFacade->beginMfaChallenge();
    }

    public function verifyMfaChallenge(VerifyMfaChallengeData $data) : AuthenticationResult
    {
        return $this->identityFacade->verifyMfaChallenge(data: $data);
    }

    public function regenerateBackupCodes() : BackupCodeSet
    {
        return $this->identityFacade->regenerateBackupCodes();
    }

    public function disableMfa() : void
    {
        $this->identityFacade->disableMfa();
    }

    public function beginMfaRecovery(BeginMfaRecoveryData $data) : MfaRecoveryChallenge
    {
        return $this->identityFacade->beginMfaRecovery(data: $data);
    }

    public function confirmMfaRecovery(ConfirmMfaRecoveryData $data) : void
    {
        $this->identityFacade->confirmMfaRecovery(data: $data);
    }
}
