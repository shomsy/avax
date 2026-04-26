<?php

declare(strict_types=1);

namespace components\Auth\System;

use components\Auth\System\Capabilities\Access\AccessInterface;
use components\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use components\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskDecision;
use components\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskSignal;
use components\Auth\System\Capabilities\Diagnostics\Diagnostics;
use components\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation;
use components\Auth\System\Capabilities\ExternalIdentity\ExternalIdentity;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistrationData;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\AuthorizeCode\AuthorizeCodeData;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeClientCredentials\ExchangeClientCredentialsData;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeRefreshToken\ExchangeRefreshTokenData;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\IntrospectToken\IntrospectTokenData;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\IntrospectToken\TokenIntrospection;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\OAuthTokenGrant;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ReadWorkloadIdentities\WorkloadIdentityProfile;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RegisterClient\RegisterClientData;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RevokeToken\RevokeTokenData;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\UpdateClient\UpdateClientData;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\IssuedAuthorizationCode;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient;
use components\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\RegisteredOAuthClient;
use components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponseData;
use components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse\JarmResponse;
use components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutData;
use components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutResult;
use components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequestData;
use components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushedAuthorizationRequest;
use components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadUserInfo\OidcUserInfo;
use components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcJsonWebKeySet;
use components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcProviderMetadata;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\CompleteFederatedLogin\CompleteFederatedLoginData;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\RegisterConnection\RegisterFederationConnectionData;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\StartFederatedLogin\StartFederatedLoginData;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\VerifyDomain\VerifyFederationDomainData;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionHealth;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\StartedFederatedLogin;
use components\Auth\System\Capabilities\Identity\Identity;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\BackupCodeSet;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Data\VerifyMfaChallengeData;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollmentData;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\MfaEnrollment;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallenge;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\BeginMfaRecoveryData;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\ConfirmMfaRecoveryData;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\MfaRecoveryChallenge;
use components\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthenticationData;
use components\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthenticationData;
use components\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistrationData;
use components\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyAuthenticationChallenge;
use components\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyRegistration;
use components\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey\RenamePasskeyData;
use components\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential;
use components\Auth\System\Capabilities\Identity\Sessions\Runtime\ActiveSession;
use components\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow\RefreshAuthenticationRequest;
use components\Auth\System\Capabilities\IdentitySync\IdentitySync;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkRequest;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkResponse;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUserData;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutageData;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ScimProvisioningResult;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups\ScimGroupProjection;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers\ScimUserProjection;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage\RecoverScimDirectoryOutageData;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectoryData;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken\RotatedScimToken;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups\SyncScimGroupsData;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Support\RegisteredScimDirectory;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use components\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevation;
use components\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevationFailed;
use components\Auth\System\Capabilities\Tenancy\Model\Tenant;
use components\Auth\System\Capabilities\Tenancy\Model\TenantMember;
use components\Auth\System\Capabilities\Tenancy\Runtime\Tenant\AcceptInvite\AcceptTenantInviteData;
use components\Auth\System\Capabilities\Tenancy\Runtime\Tenant\CreateTenant\CreateTenantData;
use components\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\InviteTenantMemberData;
use components\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\IssuedTenantInvite;
use components\Auth\System\Capabilities\Tenancy\Runtime\Tenant\RemoveMember\RemoveTenantMemberData;
use components\Auth\System\Capabilities\Tenancy\Runtime\Tenant\SuspendMember\SuspendTenantMemberData;
use components\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TransferOwnership\TransferTenantOwnershipData;
use components\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use components\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;
use components\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration;
use components\Auth\System\Capabilities\Tenancy\Tenancy;
use components\Auth\System\Flows\ChangeEmail\BeginEmailChangeData;
use components\Auth\System\Flows\ChangeEmail\ConfirmEmailChangeData;
use components\Auth\System\Flows\ChangeEmail\EmailChangeChallenge;
use components\Auth\System\Flows\ChangePassword\ChangePasswordData;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationRequest;
use components\Auth\System\Flows\Login\AuthenticationResult;
use components\Auth\System\Flows\Login\Credentials;
use components\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordResetData;
use components\Auth\System\Flows\RecoverAccess\PasswordReset\PasswordResetChallenge;
use components\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPasswordData;
use components\Auth\System\Flows\Register\RegistrationData;
use components\Auth\System\Flows\Register\RegistrationResult;
use components\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerificationData;
use components\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationChallenge;
use components\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmailData;
use DateMalformedStringException;

/**
 * Contract for the core authentication system.
 *
 * Provides high-frequency convenience methods directly and exposes
 * capability owners via accessor methods for domain-specific operations.
 */
interface Auth
{
    // ── Fast-path convenience methods ──

    public function login(Credentials $credentials) : AuthenticationResult;

    public function logout() : void;

    public function authenticateRequest(AuthenticationRequest $request) : AuthenticationContext;

    public function current() : AuthenticationContext;

    public function check() : bool;

    public function user() : AuthenticatedUser|null;

    public function refresh(RefreshAuthenticationRequest $request) : AuthenticationResult;

    public function logoutAllSessions() : void;

    /**
     * @return list<ActiveSession>
     */
    public function readActiveSessions() : array;

    public function revokeSession(string $sessionId) : void;

    public function register(RegistrationData $data) : RegistrationResult;

    public function changePassword(ChangePasswordData $data) : void;

    public function beginEmailChange(BeginEmailChangeData $data) : EmailChangeChallenge;

    public function confirmEmailChange(ConfirmEmailChangeData $data) : bool;

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

    public function authorizeOAuthCode(AuthorizeCodeData $data) : IssuedAuthorizationCode;

    public function exchangeOAuthCode(ExchangeAuthorizationCodeData $data) : OAuthTokenGrant;

    public function exchangeOAuthClientCredentials(ExchangeClientCredentialsData $data) : OAuthTokenGrant;

    public function exchangeOAuthRefreshToken(ExchangeRefreshTokenData $data) : OAuthTokenGrant;

    public function revokeOAuthToken(RevokeTokenData $data) : void;

    public function introspectOAuthToken(IntrospectTokenData $data) : TokenIntrospection;

    public function readOidcProviderMetadata() : OidcProviderMetadata;

    public function readOidcJsonWebKeySet() : OidcJsonWebKeySet;

    public function readOidcUserInfo(string $accessToken) : OidcUserInfo;

    public function pushOidcAuthorizationRequest(PushAuthorizationRequestData $data) : PushedAuthorizationRequest;

    public function oidcLogout(LogoutData $data) : LogoutResult;

    public function buildOidcJarmResponse(BuildJarmResponseData $data) : JarmResponse;

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

    public function suspendUser(int $userId) : void;

    public function reactivateUser(int $userId) : void;

    public function deprovisionUser(int $userId) : void;

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

    /**
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function beginAdminElevation() : AdminElevation;

    public function endAdminElevation() : void;

    /**
     * @throws AdminElevationFailed
     */
    public function requireAdminElevation() : void;

    public function assessCurrentRisk(string|null $ipAddress = null, string|null $userAgent = null) : RiskDecision|null;

    /**
     * @return list<RiskSignal>
     */
    public function readRiskSignals(int|null $userId = null) : array;

    public function explainAccessDenied(string $resource, string|null $requiredPermission = null, string|null $tenant = null, string|null $resourceTenant = null) : AuthIssueExplanation;

    public function explainStepUpRequired(string $action, bool|null $phishingResistantRequired = null, int|null $freshAfterSeconds = null) : AuthIssueExplanation;

    public function explainSenderConstraintFailure(string $reason, string|null $requiredConstraint = null) : AuthIssueExplanation;

    public function explainSessionRevocation(string $status, string|null $sessionId = null) : AuthIssueExplanation;

    public function explainTrustedDeviceDecision(string|null $deviceId = null) : AuthIssueExplanation;

    // ── Capability accessors ──

    public function access() : AccessInterface;

    public function identity() : Identity;

    public function externalIdentity() : ExternalIdentity;

    public function identitySync() : IdentitySync;

    public function tenancy() : Tenancy;

    public function diagnostics() : Diagnostics;
}
