<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Capabilities\Access\AccessInterface;
use Avax\Auth\System\Capabilities\Diagnostics\Diagnostics;
use Avax\Auth\System\Capabilities\ExternalIdentity\ExternalIdentity;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Capabilities\IdentitySync\IdentitySync;
use Avax\Auth\System\Capabilities\Tenancy\Tenancy;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Auth\System\Flows\Login\Credentials;

/**
 * Contract for the core authentication system.
 *
 * Provides high-frequency convenience methods directly and exposes
 * capability owners via accessor methods for domain-specific operations.
 */
interface AuthInterface
{
    // ── Fast-path convenience methods ──

    public function login(Credentials $credentials) : AuthenticationResult;

    public function logout() : void;

    public function authenticateRequest(AuthenticationRequest $request) : AuthenticationContext;

    public function current() : AuthenticationContext;

    public function check() : bool;

    public function user() : AuthenticatedUser|null;

    public function refresh(\Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow\RefreshAuthenticationRequest $request) : AuthenticationResult;

    public function logoutAllSessions() : void;

    /**
     * @return list<\Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\ActiveSession>
     */
    public function readActiveSessions() : array;

    public function revokeSession(string $sessionId) : void;

    public function register(\Avax\Auth\System\Flows\Register\RegistrationData $data) : \Avax\Auth\System\Flows\Register\RegistrationResult;

    public function changePassword(\Avax\Auth\System\Flows\ChangePassword\ChangePasswordData $data) : void;

    public function beginEmailChange(\Avax\Auth\System\Flows\ChangeEmail\BeginEmailChangeData $data) : \Avax\Auth\System\Flows\ChangeEmail\EmailChangeChallenge;

    public function confirmEmailChange(\Avax\Auth\System\Flows\ChangeEmail\ConfirmEmailChangeData $data) : bool;

    public function beginPasswordReset(\Avax\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordResetData $data) : \Avax\Auth\System\Flows\RecoverAccess\PasswordReset\PasswordResetChallenge;

    public function resetPassword(\Avax\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPasswordData $data) : bool;

    public function beginEmailVerification(\Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerificationData $data) : \Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationChallenge;

    public function verifyEmail(\Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmailData $data) : bool;

    public function startMfaEnrollment() : \Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\MfaEnrollment;

    public function confirmMfaEnrollment(\Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollmentData $data) : \Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\BackupCodeSet;

    public function cancelMfaEnrollment() : void;

    public function beginMfaChallenge() : \Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallenge;

    public function verifyMfaChallenge(\Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Data\VerifyMfaChallengeData $data) : AuthenticationResult;

    public function regenerateBackupCodes() : \Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\BackupCodeSet;

    public function disableMfa() : void;

    public function beginMfaRecovery(\Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\BeginMfaRecoveryData $data) : \Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\MfaRecoveryChallenge;

    public function confirmMfaRecovery(\Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\ConfirmMfaRecoveryData $data) : void;

    public function beginPasskeyRegistration() : \Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyRegistration;

    public function completePasskeyRegistration(\Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistrationData $data) : \Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential;

    public function beginPasskeyAuthentication(\Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthenticationData $data) : \Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyAuthenticationChallenge;

    public function completePasskeyAuthentication(\Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthenticationData $data) : AuthenticationResult;

    /**
     * @return list<\Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential>
     */
    public function readPasskeys() : array;

    public function renamePasskey(\Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey\RenamePasskeyData $data) : \Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential;

    public function revokePasskey(string $credentialId) : void;

    public function registerOAuthClient(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RegisterClient\RegisterClientData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\RegisteredOAuthClient;

    public function approveOAuthClientRegistration(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistrationData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient;

    public function updateOAuthClient(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\UpdateClient\UpdateClientData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient;

    public function disableOAuthClient(string $clientId) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient;

    public function rotateOAuthClientSecret(string $clientId) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\RegisteredOAuthClient;

    /**
     * @return list<\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient>
     */
    public function readOAuthClients() : array;

    /**
     * @return list<\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ReadWorkloadIdentities\WorkloadIdentityProfile>
     */
    public function readWorkloadIdentities() : array;

    public function authorizeOAuthCode(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\AuthorizeCode\AuthorizeCodeData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\IssuedAuthorizationCode;

    public function exchangeOAuthCode(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\OAuthTokenGrant;

    public function exchangeOAuthClientCredentials(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeClientCredentials\ExchangeClientCredentialsData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\OAuthTokenGrant;

    public function exchangeOAuthRefreshToken(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeRefreshToken\ExchangeRefreshTokenData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\OAuthTokenGrant;

    public function revokeOAuthToken(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RevokeToken\RevokeTokenData $data) : void;

    public function introspectOAuthToken(\Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\IntrospectToken\IntrospectTokenData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\IntrospectToken\TokenIntrospection;

    public function readOidcProviderMetadata() : \Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcProviderMetadata;

    public function readOidcJsonWebKeySet() : \Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcJsonWebKeySet;

    public function readOidcUserInfo(string $accessToken) : \Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadUserInfo\OidcUserInfo;

    public function pushOidcAuthorizationRequest(\Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequestData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushedAuthorizationRequest;

    public function oidcLogout(\Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\LogoutResult;

    public function buildOidcJarmResponse(\Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponseData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse\JarmResponse;

    public function registerFederationConnection(\Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\RegisterConnection\RegisterFederationConnectionData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection;

    /**
     * @return list<\Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection>
     */
    public function readFederationConnections() : array;

    public function verifyFederationDomain(\Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\VerifyDomain\VerifyFederationDomainData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection;

    public function syncFederationMetadata(string $connectionId) : \Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection;

    public function checkFederationConnectionHealth(string $connectionId) : \Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionHealth;

    public function evaluateFederationBreakGlassBypass(string $connectionId) : bool;

    public function discoverFederationConnection(string $email) : \Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection|null;

    public function startFederatedLogin(\Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\StartFederatedLogin\StartFederatedLoginData $data) : \Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\StartedFederatedLogin;

    public function completeFederatedLogin(\Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\CompleteFederatedLogin\CompleteFederatedLoginData $data) : AuthenticationResult;

    public function registerScimDirectory(\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectoryData $data) : \Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\RegisteredScimDirectory;

    /**
     * @return list<\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory>
     */
    public function readScimDirectories(string|null $tenantSlug = null) : array;

    public function rotateScimToken(string $directoryId) : \Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken\RotatedScimToken;

    public function markScimDirectoryOutage(\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutageData $data) : \Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;

    public function recoverScimDirectoryOutage(\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage\RecoverScimDirectoryOutageData $data) : \Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectory;

    public function provisionScimUser(\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData $data) : \Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ScimProvisioningResult;

    public function deleteScimUser(\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUserData $data) : void;

    /**
     * @return list<\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers\ScimUserProjection>
     */
    public function readScimUsers(string $directoryId) : array;

    /**
     * @return list<\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups\ScimGroupProjection>
     */
    public function readScimGroups(string $directoryId) : array;

    public function syncScimGroups(\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups\SyncScimGroupsData $data) : \Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ScimProvisioningResult;

    public function runScimBulk(\Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkRequest $data) : \Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkResponse;

    public function suspendUser(int $userId) : void;

    public function reactivateUser(int $userId) : void;

    public function deprovisionUser(int $userId) : void;

    public function createTenant(\Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\CreateTenant\CreateTenantData $data) : \Avax\Auth\System\Capabilities\Tenancy\Model\Tenant;

    /**
     * @return list<\Avax\Auth\System\Capabilities\Tenancy\Model\Tenant>
     */
    public function readTenants() : array;

    public function inviteTenantMember(\Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\InviteTenantMemberData $data) : \Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\IssuedTenantInvite;

    public function acceptTenantInvite(\Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\AcceptInvite\AcceptTenantInviteData $data) : \Avax\Auth\System\Capabilities\Tenancy\Model\TenantMember;

    /**
     * @return list<\Avax\Auth\System\Capabilities\Tenancy\Model\TenantMember>
     */
    public function readTenantMembers(string $tenantSlug) : array;

    public function removeTenantMember(\Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\RemoveMember\RemoveTenantMemberData $data) : void;

    public function suspendTenantMember(\Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\SuspendMember\SuspendTenantMemberData $data) : \Avax\Auth\System\Capabilities\Tenancy\Model\TenantMember;

    public function transferTenantOwnership(\Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TransferOwnership\TransferTenantOwnershipData $data) : \Avax\Auth\System\Capabilities\Tenancy\Model\Tenant;

    public function readTenantSecurityConfiguration(string $tenantSlug) : \Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration|null;

    public function readTenantSecurityChangeRequest(string $changeId) : \Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest|null;

    /**
     * @return list<\Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest>
     */
    public function readTenantSecurityChangeRequests(string $tenantSlug) : array;

    public function beginTenantSecurityChange(\Avax\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\BeginChange\BeginTenantSecurityChangeData $data) : \Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;

    public function approveTenantSecurityChange(string $changeId, string $approvedBy) : \Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;

    public function applyTenantSecurityChange(string $changeId) : \Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration;

    public function rollbackTenantSecurityChange(string $changeId) : \Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration;

    public function beginAdminElevation() : \Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevation;

    public function endAdminElevation() : void;

    public function requireAdminElevation() : void;

    public function assessCurrentRisk(string|null $ipAddress = null, string|null $userAgent = null) : \Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskDecision|null;

    /**
     * @return list<\Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskSignal>
     */
    public function readRiskSignals(int|null $userId = null) : array;

    public function explainAccessDenied(string $resource, string|null $requiredPermission = null, string|null $tenant = null, string|null $resourceTenant = null) : \Avax\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation;

    public function explainStepUpRequired(string $action, bool|null $phishingResistantRequired = null, int|null $freshAfterSeconds = null) : \Avax\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation;

    public function explainSenderConstraintFailure(string $reason, string|null $requiredConstraint = null) : \Avax\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation;

    public function explainSessionRevocation(string $status, string|null $sessionId = null) : \Avax\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation;

    public function explainTrustedDeviceDecision(string|null $deviceId = null) : \Avax\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation;

    // ── Capability accessors ──

    public function access() : AccessInterface;

    public function identity() : Identity;

    public function externalIdentity() : ExternalIdentity;

    public function identitySync() : IdentitySync;

    public function tenancy() : Tenancy;

    public function diagnostics() : Diagnostics;
}
