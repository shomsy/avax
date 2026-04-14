<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Capability\Access\AccessInterface;
use Avax\Auth\System\Capability\Federation\FederationConnection;
use Avax\Auth\System\Capability\Federation\FederationConnectionHealth;
use Avax\Auth\System\Capability\Oidc\OidcJsonWebKeySet;
use Avax\Auth\System\Capability\Oidc\OidcProviderMetadata;
use Avax\Auth\System\Capability\Federation\StartedFederatedLogin;
use Avax\Auth\System\Capability\OAuth\IssuedAuthorizationCode;
use Avax\Auth\System\Capability\OAuth\OAuthClient;
use Avax\Auth\System\Capability\Passkey\PasskeyCredential;
use Avax\Auth\System\Capability\Risk\RiskDecision;
use Avax\Auth\System\Capability\Risk\RiskSignal;
use Avax\Auth\System\Capability\OAuth\RegisteredOAuthClient;
use Avax\Auth\System\Capability\Scim\RegisteredScimDirectory;
use Avax\Auth\System\Capability\Scim\ScimDirectory;
use Avax\Auth\System\Capability\Tenant\Tenant;
use Avax\Auth\System\Capability\Tenant\TenantMember;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfiguration;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flow\ChangeEmail\BeginEmailChangeData;
use Avax\Auth\System\Flow\ChangeEmail\ConfirmEmailChangeData;
use Avax\Auth\System\Flow\ChangeEmail\EmailChangeChallenge;
use Avax\Auth\System\Flow\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flow\Login\AuthenticationResult;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\AdminRealm\AdminElevation;
use Avax\Auth\System\Flow\Federation\CompleteFederatedLogin\CompleteFederatedLoginData;
use Avax\Auth\System\Flow\Federation\RegisterConnection\RegisterFederationConnectionData;
use Avax\Auth\System\Flow\Federation\StartFederatedLogin\StartFederatedLoginData;
use Avax\Auth\System\Flow\Federation\VerifyDomain\VerifyFederationDomainData;
use Avax\Auth\System\Flow\Mfa\BackupCodeSet;
use Avax\Auth\System\Flow\Mfa\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Flow\Mfa\MfaChallenge;
use Avax\Auth\System\Flow\Mfa\MfaEnrollment;
use Avax\Auth\System\Flow\Mfa\MfaRecoveryChallenge;
use Avax\Auth\System\Flow\Mfa\Recover\BeginMfaRecoveryData;
use Avax\Auth\System\Flow\Mfa\Recover\ConfirmMfaRecoveryData;
use Avax\Auth\System\Flow\Mfa\VerifyMfaChallengeData;
use Avax\Auth\System\Flow\OAuth\AuthorizeCode\AuthorizeCodeData;
use Avax\Auth\System\Flow\OAuth\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use Avax\Auth\System\Flow\OAuth\ExchangeClientCredentials\ExchangeClientCredentialsData;
use Avax\Auth\System\Flow\OAuth\ExchangeRefreshToken\ExchangeRefreshTokenData;
use Avax\Auth\System\Flow\OAuth\IntrospectToken\IntrospectTokenData;
use Avax\Auth\System\Flow\OAuth\IntrospectToken\TokenIntrospection;
use Avax\Auth\System\Flow\OAuth\OAuthTokenGrant;
use Avax\Auth\System\Flow\OAuth\RegisterClient\RegisterClientData;
use Avax\Auth\System\Flow\OAuth\ReadWorkloadIdentities\WorkloadIdentityProfile;
use Avax\Auth\System\Flow\OAuth\RevokeToken\RevokeTokenData;
use Avax\Auth\System\Flow\OAuth\UpdateClient\UpdateClientData;
use Avax\Auth\System\Flow\Oidc\ReadUserInfo\OidcUserInfo;
use Avax\Auth\System\Flow\Passkey\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Auth\System\Flow\Passkey\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Auth\System\Flow\Passkey\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Auth\System\Flow\Passkey\PasskeyAuthenticationChallenge;
use Avax\Auth\System\Flow\Passkey\PasskeyRegistration;
use Avax\Auth\System\Flow\Passkey\RenamePasskey\RenamePasskeyData;
use Avax\Auth\System\Flow\Recover\BeginPasswordResetData;
use Avax\Auth\System\Flow\Recover\PasswordResetChallenge;
use Avax\Auth\System\Flow\Recover\ResetPasswordData;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Flow\Register\RegistrationResult;
use Avax\Auth\System\Flow\Scim\DeleteUser\DeleteScimUserData;
use Avax\Auth\System\Flow\Scim\Bulk\ScimBulkRequest;
use Avax\Auth\System\Flow\Scim\Bulk\ScimBulkResponse;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ProvisionScimUserData;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ScimProvisioningResult;
use Avax\Auth\System\Flow\Scim\ReadGroups\ScimGroupProjection;
use Avax\Auth\System\Flow\Scim\ReadUsers\ScimUserProjection;
use Avax\Auth\System\Flow\Scim\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Auth\System\Flow\Scim\RotateToken\RotatedScimToken;
use Avax\Auth\System\Flow\Scim\SyncGroups\SyncScimGroupsData;
use Avax\Auth\System\Flow\Session\ActiveSession;
use Avax\Auth\System\Flow\Tenant\AcceptInvite\AcceptTenantInviteData;
use Avax\Auth\System\Flow\Tenant\CreateTenant\CreateTenantData;
use Avax\Auth\System\Flow\Tenant\InviteMember\InviteTenantMemberData;
use Avax\Auth\System\Flow\Tenant\InviteMember\IssuedTenantInvite;
use Avax\Auth\System\Flow\Tenant\RemoveMember\RemoveTenantMemberData;
use Avax\Auth\System\Flow\Tenant\SuspendMember\SuspendTenantMemberData;
use Avax\Auth\System\Flow\Tenant\TransferOwnership\TransferTenantOwnershipData;
use Avax\Auth\System\Flow\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use Avax\Auth\System\Flow\Token\RefreshAuthenticationRequest;
use Avax\Auth\System\Flow\Verify\BeginEmailVerificationData;
use Avax\Auth\System\Flow\Verify\EmailVerificationChallenge;
use Avax\Auth\System\Flow\Verify\VerifyEmailData;

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

    public function changePassword(ChangePasswordData $data) : void;

    public function beginEmailChange(BeginEmailChangeData $data) : EmailChangeChallenge;

    public function confirmEmailChange(ConfirmEmailChangeData $data) : bool;

    public function register(RegistrationData $data) : RegistrationResult;

    public function refresh(RefreshAuthenticationRequest $request) : AuthenticationResult;

    public function registerOAuthClient(RegisterClientData $data) : RegisteredOAuthClient;

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
