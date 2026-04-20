<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Capabilities\Access\AccessInterface;
use Avax\Auth\System\Capabilities\Explainability\AuthIssueExplanation;
use Avax\Auth\System\Capabilities\Federation\FederationConnection;
use Avax\Auth\System\Capabilities\Federation\FederationConnectionHealth;
use Avax\Auth\System\Capabilities\Federation\StartedFederatedLogin;
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
use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capabilities\TenantSecurity\TenantSecurityConfiguration;
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
use Avax\Auth\System\Flows\Scim\ReadGroups\ScimGroupProjection;
use Avax\Auth\System\Flows\Scim\ReadUsers\ScimUserProjection;
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
use Avax\Auth\System\Flows\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use Avax\Auth\System\Flows\Token\RefreshAuthenticationRequest;
use Avax\Auth\System\Flows\Verify\BeginEmailVerificationData;
use Avax\Auth\System\Flows\Verify\EmailVerificationChallenge;
use Avax\Auth\System\Flows\Verify\VerifyEmailData;

/**
 * Interface AuthInterface
 *
 * Defines the contract for the core authentication system facade.
 */
interface AuthInterface
{
    public function login(Credentials $credentials) : AuthenticationResult;

    public function authenticateRequest(AuthenticationRequest $request) : AuthenticationContext;

    public function current() : AuthenticationContext;

    public function logout() : void;

    public function logoutAllSessions() : void;

    /**
     * @return list<ActiveSession>
     */
    public function readActiveSessions() : array;

    public function revokeSession(string $sessionId) : void;

    public function check() : bool;

    public function user() : AuthenticatedUser|null;

    public function access() : AccessInterface;

    public function explainAccessDenied(
        string      $resource,
        string|null $requiredPermission = null,
        string|null $tenant = null,
        string|null $resourceTenant = null
    ) : AuthIssueExplanation;

    public function explainStepUpRequired(
        string   $action,
        bool     $phishingResistantRequired = false,
        int|null $freshAfterSeconds = null
    ) : AuthIssueExplanation;

    public function explainSenderConstraintFailure(string $reason, string|null $requiredConstraint = null) : AuthIssueExplanation;

    public function explainSessionRevocation(string $status, string|null $sessionId = null) : AuthIssueExplanation;

    public function explainTrustedDeviceDecision(string|null $deviceId = null) : AuthIssueExplanation;

    public function changePassword(ChangePasswordData $data) : void;

    public function beginEmailChange(BeginEmailChangeData $data) : EmailChangeChallenge;

    public function confirmEmailChange(ConfirmEmailChangeData $data) : bool;

    public function register(RegistrationData $data) : RegistrationResult;

    public function refresh(RefreshAuthenticationRequest $request) : AuthenticationResult;

    public function registerOAuthClient(RegisterClientData $data) : RegisteredOAuthClient;

    public function approveOAuthClientRegistration(ApproveClientRegistrationData $data) : OAuthClient;

    public function updateOAuthClient(UpdateClientData $data) : OAuthClient;

    public function disableOAuthClient(string $clientId) : OAuthClient;

    public function rotateOAuthClientSecret(string $clientId) : RegisteredOAuthClient;

    /**
     * @return list<OAuthClient>
     */
    public function readOAuthClients() : array;

    /**
     * @return list<WorkloadIdentityProfile>
     */
    public function readWorkloadIdentities() : array;

    public function readOidcProviderMetadata() : OidcProviderMetadata;

    public function readOidcJsonWebKeySet() : OidcJsonWebKeySet;

    public function readOidcUserInfo(string $accessToken) : OidcUserInfo;

    public function pushOidcAuthorizationRequest(PushAuthorizationRequestData $data) : PushedAuthorizationRequest;

    public function oidcLogout(OidcLogoutData $data) : OidcLogoutResult;

    public function buildOidcJarmResponse(BuildJarmResponseData $data) : JarmResponse;

    public function authorizeOAuthCode(AuthorizeCodeData $data) : IssuedAuthorizationCode;

    public function exchangeOAuthCode(ExchangeAuthorizationCodeData $data) : OAuthTokenGrant;

    public function exchangeOAuthClientCredentials(ExchangeClientCredentialsData $data) : OAuthTokenGrant;

    public function exchangeOAuthRefreshToken(ExchangeRefreshTokenData $data) : OAuthTokenGrant;

    public function revokeOAuthToken(RevokeTokenData $data) : void;

    public function introspectOAuthToken(IntrospectTokenData $data) : TokenIntrospection;

    public function beginAdminElevation() : AdminElevation;

    public function endAdminElevation() : void;

    public function requireAdminElevation() : void;

    public function suspendUser(int $userId) : void;

    public function reactivateUser(int $userId) : void;

    public function deprovisionUser(int $userId) : void;

    public function beginPasskeyRegistration() : PasskeyRegistration;

    public function completePasskeyRegistration(CompletePasskeyRegistrationData $data) : PasskeyCredential;

    public function beginPasskeyAuthentication(BeginPasskeyAuthenticationData $data) : PasskeyAuthenticationChallenge;

    public function completePasskeyAuthentication(CompletePasskeyAuthenticationData $data) : AuthenticationResult;

    /**
     * @return list<PasskeyCredential>
     */
    public function readPasskeys() : array;

    public function renamePasskey(RenamePasskeyData $data) : PasskeyCredential;

    public function revokePasskey(string $credentialId) : void;

    public function registerFederationConnection(RegisterFederationConnectionData $data) : FederationConnection;

    /**
     * @return list<FederationConnection>
     */
    public function readFederationConnections() : array;

    public function verifyFederationDomain(VerifyFederationDomainData $data) : FederationConnection;

    public function syncFederationMetadata(string $connectionId) : FederationConnection;

    public function checkFederationConnectionHealth(string $connectionId) : FederationConnectionHealth;

    public function evaluateFederationBreakGlassBypass(string $connectionId) : bool;

    public function discoverFederationConnection(string $email) : FederationConnection|null;

    public function startFederatedLogin(StartFederatedLoginData $data) : StartedFederatedLogin;

    public function completeFederatedLogin(CompleteFederatedLoginData $data) : AuthenticationResult;

    public function registerScimDirectory(RegisterScimDirectoryData $data) : RegisteredScimDirectory;

    /**
     * @return list<ScimDirectory>
     */
    public function readScimDirectories(string|null $tenantSlug = null) : array;

    public function rotateScimToken(string $directoryId) : RotatedScimToken;

    public function markScimDirectoryOutage(MarkScimDirectoryOutageData $data) : ScimDirectory;

    public function recoverScimDirectoryOutage(RecoverScimDirectoryOutageData $data) : ScimDirectory;

    public function provisionScimUser(ProvisionScimUserData $data) : ScimProvisioningResult;

    public function deleteScimUser(DeleteScimUserData $data) : void;

    /**
     * @return list<ScimUserProjection>
     */
    public function readScimUsers(string $directoryId) : array;

    /**
     * @return list<ScimGroupProjection>
     */
    public function readScimGroups(string $directoryId) : array;

    public function syncScimGroups(SyncScimGroupsData $data) : ScimProvisioningResult;

    public function runScimBulk(ScimBulkRequest $data) : ScimBulkResponse;

    public function createTenant(CreateTenantData $data) : Tenant;

    /**
     * @return list<Tenant>
     */
    public function readTenants() : array;

    public function inviteTenantMember(InviteTenantMemberData $data) : IssuedTenantInvite;

    public function acceptTenantInvite(AcceptTenantInviteData $data) : TenantMember;

    /**
     * @return list<TenantMember>
     */
    public function readTenantMembers(string $tenantSlug) : array;

    public function removeTenantMember(RemoveTenantMemberData $data) : void;

    public function suspendTenantMember(SuspendTenantMemberData $data) : TenantMember;

    public function transferTenantOwnership(TransferTenantOwnershipData $data) : Tenant;

    public function readTenantSecurityConfiguration(string $tenantSlug) : TenantSecurityConfiguration|null;

    public function readTenantSecurityChangeRequest(string $changeId) : TenantSecurityChangeRequest|null;

    /**
     * @return list<TenantSecurityChangeRequest>
     */
    public function readTenantSecurityChangeRequests(string $tenantSlug) : array;

    public function beginTenantSecurityChange(BeginTenantSecurityChangeData $data) : TenantSecurityChangeRequest;

    public function approveTenantSecurityChange(string $changeId, string $approvedBy) : TenantSecurityChangeRequest;

    public function applyTenantSecurityChange(string $changeId) : TenantSecurityConfiguration;

    public function rollbackTenantSecurityChange(string $changeId) : TenantSecurityConfiguration;

    public function assessCurrentRisk(string|null $ipAddress = null, string|null $userAgent = null) : RiskDecision|null;

    /**
     * @return list<RiskSignal>
     */
    public function readRiskSignals(int|null $userId = null) : array;

    public function beginPasswordReset(BeginPasswordResetData $data) : PasswordResetChallenge;

    public function resetPassword(ResetPasswordData $data) : bool;

    public function beginEmailVerification(BeginEmailVerificationData $data) : EmailVerificationChallenge;

    public function verifyEmail(VerifyEmailData $data) : bool;

    public function startMfaEnrollment() : MfaEnrollment;

    public function confirmMfaEnrollment(ConfirmMfaEnrollmentData $data) : BackupCodeSet;

    public function cancelMfaEnrollment() : void;

    public function beginMfaChallenge() : MfaChallenge;

    public function verifyMfaChallenge(VerifyMfaChallengeData $data) : AuthenticationResult;

    public function regenerateBackupCodes() : BackupCodeSet;

    public function disableMfa() : void;

    public function beginMfaRecovery(BeginMfaRecoveryData $data) : MfaRecoveryChallenge;

    public function confirmMfaRecovery(ConfirmMfaRecoveryData $data) : void;
}
