<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System;

use Avax\Components\Identity\Access\System\Capabilities\AccessInterface;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\RiskDecision;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\RiskSignal;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Diagnostics;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplanation;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\ActiveSession;
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
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentity;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\IssuedAuthorizationCode;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\RegisteredOAuthClient;
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
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcJsonWebKeySet;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderMetadata;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponseData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\JarmResponse\JarmResponse;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout\LogoutData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout\LogoutResult;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequestData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\PushAuthorizationRequest\PushedAuthorizationRequest;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadUserInfo\OidcUserInfo;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionHealth;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\StartedFederatedLogin;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\CompleteFederatedLogin\CompleteFederatedLoginData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\RegisterConnection\RegisterFederationConnectionData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\StartFederatedLogin\StartFederatedLoginData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\VerifyDomain\VerifyFederationDomainData;
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
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Flow\RefreshAuthenticationRequest;
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

    public function login(Credentials $credentials): AuthenticationResult;

    public function logout(): void;

    public function authenticateRequest(AuthenticationRequest $authenticationRequest) : AuthenticationContext;

    public function current(): AuthenticationContext;

    public function check(): bool;

    public function user(): ?AuthenticatedUser;

    public function refresh(RefreshAuthenticationRequest $refreshAuthenticationRequest) : AuthenticationResult;

    public function logoutAllSessions(): void;

    /**
     * @return list<ActiveSession>
     */
    public function readActiveSessions(): array;

    public function revokeSession(string $sessionId): void;

    public function register(RegistrationData $registrationData) : RegistrationResult;

    public function changePassword(ChangePasswordData $changePasswordData) : void;

    public function beginEmailChange(BeginEmailChangeData $beginEmailChangeData) : EmailChangeChallenge;

    public function confirmEmailChange(ConfirmEmailChangeData $confirmEmailChangeData) : bool;

    public function beginPasswordReset(BeginPasswordResetData $beginPasswordResetData) : PasswordResetChallenge;

    public function resetPassword(ResetPasswordData $resetPasswordData) : bool;

    public function beginEmailVerification(BeginEmailVerificationData $beginEmailVerificationData) : EmailVerificationChallenge;

    public function verifyEmail(VerifyEmailData $verifyEmailData) : bool;

    public function startMfaEnrollment(): MfaEnrollment;

    public function confirmMfaEnrollment(ConfirmMfaEnrollmentData $confirmMfaEnrollmentData) : BackupCodeSet;

    public function cancelMfaEnrollment(): void;

    public function beginMfaChallenge(): MfaChallenge;

    public function verifyMfaChallenge(VerifyMfaChallengeData $verifyMfaChallengeData) : AuthenticationResult;

    public function regenerateBackupCodes(): BackupCodeSet;

    public function disableMfa(): void;

    public function beginMfaRecovery(BeginMfaRecoveryData $beginMfaRecoveryData) : MfaRecoveryChallenge;

    public function confirmMfaRecovery(ConfirmMfaRecoveryData $confirmMfaRecoveryData) : void;

    public function beginPasskeyRegistration(): PasskeyRegistration;

    public function completePasskeyRegistration(CompletePasskeyRegistrationData $completePasskeyRegistrationData) : PasskeyCredential;

    public function beginPasskeyAuthentication(BeginPasskeyAuthenticationData $beginPasskeyAuthenticationData) : PasskeyAuthenticationChallenge;

    public function completePasskeyAuthentication(CompletePasskeyAuthenticationData $completePasskeyAuthenticationData) : AuthenticationResult;

    /**
     * @return list<PasskeyCredential>
     */
    public function readPasskeys(): array;

    public function renamePasskey(RenamePasskeyData $renamePasskeyData) : PasskeyCredential;

    public function revokePasskey(string $credentialId): void;

    public function registerOAuthClient(RegisterClientData $registerClientData) : RegisteredOAuthClient;

    public function approveOAuthClientRegistration(ApproveClientRegistrationData $approveClientRegistrationData) : OAuthClient;

    public function updateOAuthClient(UpdateClientData $updateClientData) : OAuthClient;

    public function disableOAuthClient(string $clientId): OAuthClient;

    public function rotateOAuthClientSecret(string $clientId): RegisteredOAuthClient;

    /**
     * @return list<OAuthClient>
     */
    public function readOAuthClients(): array;

    /**
     * @return list<WorkloadIdentityProfile>
     */
    public function readWorkloadIdentities(): array;

    public function authorizeOAuthCode(AuthorizeCodeData $authorizeCodeData) : IssuedAuthorizationCode;

    public function exchangeOAuthCode(ExchangeAuthorizationCodeData $exchangeAuthorizationCodeData) : OAuthTokenGrant;

    public function exchangeOAuthClientCredentials(ExchangeClientCredentialsData $exchangeClientCredentialsData) : OAuthTokenGrant;

    public function exchangeOAuthRefreshToken(ExchangeRefreshTokenData $exchangeRefreshTokenData) : OAuthTokenGrant;

    public function revokeOAuthToken(RevokeTokenData $revokeTokenData) : void;

    public function introspectOAuthToken(IntrospectTokenData $introspectTokenData) : TokenIntrospection;

    public function readOidcProviderMetadata(): OidcProviderMetadata;

    public function readOidcJsonWebKeySet(): OidcJsonWebKeySet;

    public function readOidcUserInfo(string $accessToken): OidcUserInfo;

    public function pushOidcAuthorizationRequest(PushAuthorizationRequestData $pushAuthorizationRequestData) : PushedAuthorizationRequest;

    public function oidcLogout(LogoutData $logoutData) : LogoutResult;

    public function buildOidcJarmResponse(BuildJarmResponseData $buildJarmResponseData) : JarmResponse;

    public function registerFederationConnection(RegisterFederationConnectionData $registerFederationConnectionData) : FederationConnection;

    /**
     * @return list<FederationConnection>
     */
    public function readFederationConnections(): array;

    public function verifyFederationDomain(VerifyFederationDomainData $verifyFederationDomainData) : FederationConnection;

    public function syncFederationMetadata(string $connectionId): FederationConnection;

    public function checkFederationConnectionHealth(string $connectionId): FederationConnectionHealth;

    public function evaluateFederationBreakGlassBypass(string $connectionId): bool;

    public function discoverFederationConnection(string $email): ?FederationConnection;

    public function startFederatedLogin(StartFederatedLoginData $startFederatedLoginData) : StartedFederatedLogin;

    public function completeFederatedLogin(CompleteFederatedLoginData $completeFederatedLoginData) : AuthenticationResult;

    public function registerScimDirectory(RegisterScimDirectoryData $registerScimDirectoryData) : RegisteredScimDirectory;

    /**
     * @return list<ScimDirectory>
     */
    public function readScimDirectories(?string $tenantSlug = null) : array;

    public function rotateScimToken(string $directoryId): RotatedScimToken;

    public function markScimDirectoryOutage(MarkScimDirectoryOutageData $markScimDirectoryOutageData) : ScimDirectory;

    public function recoverScimDirectoryOutage(RecoverScimDirectoryOutageData $recoverScimDirectoryOutageData) : ScimDirectory;

    public function provisionScimUser(ProvisionScimUserData $provisionScimUserData) : ScimProvisioningResult;

    public function deleteScimUser(DeleteScimUserData $deleteScimUserData) : void;

    /**
     * @return list<ScimUserProjection>
     */
    public function readScimUsers(string $directoryId): array;

    /**
     * @return list<ScimGroupProjection>
     */
    public function readScimGroups(string $directoryId): array;

    public function syncScimGroups(SyncScimGroupsData $syncScimGroupsData) : ScimProvisioningResult;

    public function runScimBulk(ScimBulkRequest $scimBulkRequest) : ScimBulkResponse;

    public function suspendUser(int $userId): void;

    public function reactivateUser(int $userId): void;

    public function deprovisionUser(int $userId): void;

    public function createTenant(CreateTenantData $createTenantData) : Tenant;

    /**
     * @return list<Tenant>
     */
    public function readTenants(): array;

    public function inviteTenantMember(InviteTenantMemberData $inviteTenantMemberData) : IssuedTenantInvite;

    public function acceptTenantInvite(AcceptTenantInviteData $acceptTenantInviteData) : TenantMember;

    /**
     * @return list<TenantMember>
     */
    public function readTenantMembers(string $tenantSlug): array;

    public function removeTenantMember(RemoveTenantMemberData $removeTenantMemberData) : void;

    public function suspendTenantMember(SuspendTenantMemberData $suspendTenantMemberData) : TenantMember;

    public function transferTenantOwnership(TransferTenantOwnershipData $transferTenantOwnershipData) : Tenant;

    public function readTenantSecurityConfiguration(string $tenantSlug): ?TenantSecurityConfiguration;

    public function readTenantSecurityChangeRequest(string $changeId): ?TenantSecurityChangeRequest;

    /**
     * @return list<TenantSecurityChangeRequest>
     */
    public function readTenantSecurityChangeRequests(string $tenantSlug): array;

    public function beginTenantSecurityChange(BeginTenantSecurityChangeData $beginTenantSecurityChangeData) : TenantSecurityChangeRequest;

    public function approveTenantSecurityChange(string $changeId, string $approvedBy): TenantSecurityChangeRequest;

    public function applyTenantSecurityChange(string $changeId): TenantSecurityConfiguration;

    public function rollbackTenantSecurityChange(string $changeId): TenantSecurityConfiguration;

    /**
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function beginAdminElevation(): AdminElevation;

    public function endAdminElevation(): void;

    /**
     * @throws AdminElevationFailed
     */
    public function requireAdminElevation(): void;

    public function assessCurrentRisk(?string $ipAddress = null, ?string $userAgent = null) : ?RiskDecision;

    /**
     * @return list<RiskSignal>
     */
    public function readRiskSignals(?int $userId = null) : array;

    public function explainAccessDenied(string $resource, ?string $requiredPermission = null, ?string $tenant = null, ?string $resourceTenant = null) : AuthIssueExplanation;

    public function explainStepUpRequired(string $action, ?bool $phishingResistantRequired = null, ?int $freshAfterSeconds = null) : AuthIssueExplanation;

    public function explainSenderConstraintFailure(string $reason, ?string $requiredConstraint = null) : AuthIssueExplanation;

    public function explainSessionRevocation(string $status, ?string $sessionId = null) : AuthIssueExplanation;

    public function explainTrustedDeviceDecision(?string $deviceId = null) : AuthIssueExplanation;

    // ── Capability accessors ──

    public function access(): AccessInterface;

    public function identity(): Identity;

    public function externalIdentity(): ExternalIdentity;

    public function identitySync(): IdentitySync;

    public function tenancy(): Tenancy;

    public function diagnostics(): Diagnostics;
}
