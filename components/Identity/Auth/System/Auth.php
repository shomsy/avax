<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System;

use Avax\Components\Identity\Access\System\Capabilities\AccessInterface;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Support\RiskDecision;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Support\RiskSignal;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Diagnostics;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentity;
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
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\IssuedAuthorizationCode;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\RegisteredOAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponseData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\JarmResponse\JarmResponse;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout\LogoutData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout\LogoutResult;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequestData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\PushAuthorizationRequest\PushedAuthorizationRequest;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadUserInfo\OidcUserInfo;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Support\OidcJsonWebKeySet;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Support\OidcProviderMetadata;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\CompleteFederatedLogin\CompleteFederatedLoginData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\RegisterConnection\RegisterFederationConnectionData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\StartFederatedLogin\StartFederatedLoginData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\VerifyDomain\VerifyFederationDomainData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport\FederationConnectionHealth;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport\StartedFederatedLogin;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\BackupCodeSet;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Data\VerifyMfaChallengeData;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\ConfirmMfaEnrollmentData;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\MfaEnrollment;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\MfaChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\BeginMfaRecoveryData;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\ConfirmMfaRecoveryData;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\MfaRecoveryChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\PasskeyAuthenticationChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\PasskeyRegistration;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\RenamePasskey\RenamePasskeyData;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Support\PasskeyCredential;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\ActiveSession;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Flow\RefreshAuthenticationRequest;
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
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\BeginEmailChangeData;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\ConfirmEmailChangeData;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\EmailChangeChallenge;
use Avax\Components\Identity\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationRequest;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordResetData;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\PasswordResetChallenge;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPasswordData;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationData;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationResult;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerificationData;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationChallenge;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmailData;
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
