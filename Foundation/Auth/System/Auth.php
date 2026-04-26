<?php

declare(strict_types=1);

namespace Avax\Auth\System;

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
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutData;
use Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutResult;
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
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\BackupCodeSet;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Data\VerifyMfaChallengeData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\MfaEnrollment;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\BeginMfaRecoveryData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\ConfirmMfaRecoveryData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\MfaRecoveryChallenge;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyAuthenticationChallenge;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyRegistration;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey\RenamePasskeyData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential;
use Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\ActiveSession;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow\RefreshAuthenticationRequest;
use Avax\Auth\System\Capabilities\IdentitySync\IdentitySync;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkRequest;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkResponse;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUserData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutageData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ScimProvisioningResult;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups\ScimGroupProjection;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers\ScimUserProjection;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage\RecoverScimDirectoryOutageData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken\RotatedScimToken;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups\SyncScimGroupsData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\RegisteredScimDirectory;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevation;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevationFailed;
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
