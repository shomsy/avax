<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Capability\Access\AccessInterface;
use Avax\Auth\System\Capability\Federation\FederationConnection;
use Avax\Auth\System\Capability\Federation\FederationConnectionHealth;
use Avax\Auth\System\Capability\Federation\StartedFederatedLogin;
use Avax\Auth\System\Capability\Oidc\OidcJsonWebKeySet;
use Avax\Auth\System\Capability\Oidc\OidcProviderMetadata;
use Avax\Auth\System\Capability\OAuth\IssuedAuthorizationCode;
use Avax\Auth\System\Capability\OAuth\OAuthClient;
use Avax\Auth\System\Capability\OAuth\RegisteredOAuthClient;
use Avax\Auth\System\Capability\Passkey\PasskeyCredential;
use Avax\Auth\System\Capability\Risk\RiskDecision;
use Avax\Auth\System\Capability\Risk\RiskSignal;
use Avax\Auth\System\Capability\Scim\RegisteredScimDirectory;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfiguration;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticateRequest;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\AdminRealm\AdminElevation;
use Avax\Auth\System\Flow\AdminRealm\BeginAdminElevation\BeginAdminElevation;
use Avax\Auth\System\Flow\AdminRealm\EndAdminElevation\EndAdminElevation;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\ChangeEmail\BeginEmailChange;
use Avax\Auth\System\Flow\ChangeEmail\BeginEmailChangeData;
use Avax\Auth\System\Flow\ChangeEmail\ConfirmEmailChange;
use Avax\Auth\System\Flow\ChangeEmail\ConfirmEmailChangeData;
use Avax\Auth\System\Flow\ChangeEmail\EmailChangeChallenge;
use Avax\Auth\System\Flow\ChangePassword\ChangePassword;
use Avax\Auth\System\Flow\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flow\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flow\Federation\CompleteFederatedLogin\CompleteFederatedLogin;
use Avax\Auth\System\Flow\Federation\CompleteFederatedLogin\CompleteFederatedLoginData;
use Avax\Auth\System\Flow\Federation\CheckHealth\CheckFederationConnectionHealth;
use Avax\Auth\System\Flow\Federation\DiscoverConnection\DiscoverFederationConnection;
use Avax\Auth\System\Flow\Federation\EvaluateBreakGlass\EvaluateFederationBreakGlassBypass;
use Avax\Auth\System\Flow\Federation\ReadConnections\ReadFederationConnections;
use Avax\Auth\System\Flow\Federation\RegisterConnection\RegisterFederationConnection;
use Avax\Auth\System\Flow\Federation\RegisterConnection\RegisterFederationConnectionData;
use Avax\Auth\System\Flow\Federation\StartFederatedLogin\StartFederatedLogin;
use Avax\Auth\System\Flow\Federation\StartFederatedLogin\StartFederatedLoginData;
use Avax\Auth\System\Flow\Federation\SyncMetadata\SyncFederationMetadata;
use Avax\Auth\System\Flow\Federation\VerifyDomain\VerifyFederationDomain;
use Avax\Auth\System\Flow\Federation\VerifyDomain\VerifyFederationDomainData;
use Avax\Auth\System\Flow\Login\AuthenticationResult;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\Login\Login;
use Avax\Auth\System\Flow\Logout\Logout;
use Avax\Auth\System\Flow\Mfa\Backup\RegenerateBackupCodes;
use Avax\Auth\System\Flow\Mfa\BackupCodeSet;
use Avax\Auth\System\Flow\Mfa\Challenge\StartMfaChallenge;
use Avax\Auth\System\Flow\Mfa\Challenge\VerifyMfaChallenge;
use Avax\Auth\System\Flow\Mfa\Disable\DisableMfa;
use Avax\Auth\System\Flow\Mfa\Enroll\CancelMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\Enroll\ConfirmMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Flow\Mfa\Enroll\StartMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\MfaChallenge;
use Avax\Auth\System\Flow\Mfa\MfaEnrollment;
use Avax\Auth\System\Flow\Mfa\MfaRecoveryChallenge;
use Avax\Auth\System\Flow\Mfa\Recover\BeginMfaRecoveryData;
use Avax\Auth\System\Flow\Mfa\Recover\ConfirmMfaRecovery;
use Avax\Auth\System\Flow\Mfa\Recover\ConfirmMfaRecoveryData;
use Avax\Auth\System\Flow\Mfa\Recover\StartMfaRecovery;
use Avax\Auth\System\Flow\Mfa\VerifyMfaChallengeData;
use Avax\Auth\System\Flow\OAuth\AuthorizeCode\AuthorizeCode;
use Avax\Auth\System\Flow\OAuth\AuthorizeCode\AuthorizeCodeData;
use Avax\Auth\System\Flow\OAuth\ExchangeAuthorizationCode\ExchangeAuthorizationCode;
use Avax\Auth\System\Flow\OAuth\ExchangeAuthorizationCode\ExchangeAuthorizationCodeData;
use Avax\Auth\System\Flow\OAuth\ExchangeClientCredentials\ExchangeClientCredentials;
use Avax\Auth\System\Flow\OAuth\ExchangeClientCredentials\ExchangeClientCredentialsData;
use Avax\Auth\System\Flow\OAuth\ExchangeRefreshToken\ExchangeRefreshToken;
use Avax\Auth\System\Flow\OAuth\ExchangeRefreshToken\ExchangeRefreshTokenData;
use Avax\Auth\System\Flow\OAuth\IntrospectToken\IntrospectToken;
use Avax\Auth\System\Flow\OAuth\IntrospectToken\IntrospectTokenData;
use Avax\Auth\System\Flow\OAuth\IntrospectToken\TokenIntrospection;
use Avax\Auth\System\Flow\OAuth\OAuthTokenGrant;
use Avax\Auth\System\Flow\OAuth\ReadClients\ReadClients;
use Avax\Auth\System\Flow\OAuth\ReadWorkloadIdentities\ReadWorkloadIdentities;
use Avax\Auth\System\Flow\OAuth\ReadWorkloadIdentities\WorkloadIdentityProfile;
use Avax\Auth\System\Flow\OAuth\RegisterClient\RegisterClient;
use Avax\Auth\System\Flow\OAuth\RegisterClient\RegisterClientData;
use Avax\Auth\System\Flow\OAuth\RevokeToken\RevokeToken;
use Avax\Auth\System\Flow\OAuth\RevokeToken\RevokeTokenData;
use Avax\Auth\System\Flow\Oidc\ReadJsonWebKeySet\ReadOidcJsonWebKeySet;
use Avax\Auth\System\Flow\Oidc\ReadProviderMetadata\ReadOidcProviderMetadata;
use Avax\Auth\System\Flow\Oidc\ReadUserInfo\OidcUserInfo;
use Avax\Auth\System\Flow\Oidc\ReadUserInfo\ReadOidcUserInfo;
use Avax\Auth\System\Flow\Passkey\BeginAuthentication\BeginPasskeyAuthentication;
use Avax\Auth\System\Flow\Passkey\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Auth\System\Flow\Passkey\BeginRegistration\BeginPasskeyRegistration;
use Avax\Auth\System\Flow\Passkey\CompleteAuthentication\CompletePasskeyAuthentication;
use Avax\Auth\System\Flow\Passkey\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Auth\System\Flow\Passkey\CompleteRegistration\CompletePasskeyRegistration;
use Avax\Auth\System\Flow\Passkey\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Auth\System\Flow\Passkey\ListPasskeys\ListPasskeys;
use Avax\Auth\System\Flow\Passkey\PasskeyAuthenticationChallenge;
use Avax\Auth\System\Flow\Passkey\PasskeyRegistration;
use Avax\Auth\System\Flow\Passkey\RenamePasskey\RenamePasskey;
use Avax\Auth\System\Flow\Passkey\RenamePasskey\RenamePasskeyData;
use Avax\Auth\System\Flow\Passkey\RevokePasskey\RevokePasskey as RevokePasskeyFlow;
use Avax\Auth\System\Flow\Provisioning\DeprovisionUser\DeprovisionUser;
use Avax\Auth\System\Flow\Provisioning\ReactivateUser\ReactivateUser;
use Avax\Auth\System\Flow\Provisioning\SuspendUser\SuspendUser;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Flow\Recover\BeginPasswordReset;
use Avax\Auth\System\Flow\Recover\BeginPasswordResetData;
use Avax\Auth\System\Flow\Recover\PasswordResetChallenge;
use Avax\Auth\System\Flow\Recover\ResetPassword;
use Avax\Auth\System\Flow\Recover\ResetPasswordData;
use Avax\Auth\System\Flow\Register\Register;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Flow\Register\RegistrationResult;
use Avax\Auth\System\Flow\Risk\AssessCurrentRisk\AssessCurrentRisk;
use Avax\Auth\System\Flow\Risk\ReadRiskSignals\ReadRiskSignals;
use Avax\Auth\System\Flow\Scim\DeleteUser\DeleteScimUser;
use Avax\Auth\System\Flow\Scim\DeleteUser\DeleteScimUserData;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ProvisionScimUser;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ProvisionScimUserData;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ScimProvisioningResult;
use Avax\Auth\System\Flow\Scim\ReadDirectories\ReadScimDirectories;
use Avax\Auth\System\Flow\Scim\ReadUsers\ReadScimUsers;
use Avax\Auth\System\Flow\Scim\ReadUsers\ScimUserProjection;
use Avax\Auth\System\Flow\Scim\RegisterDirectory\RegisterScimDirectory;
use Avax\Auth\System\Flow\Scim\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Auth\System\Flow\Scim\RotateToken\RotateScimToken;
use Avax\Auth\System\Flow\Scim\RotateToken\RotatedScimToken;
use Avax\Auth\System\Flow\Scim\SyncGroups\SyncScimGroups;
use Avax\Auth\System\Flow\Scim\SyncGroups\SyncScimGroupsData;
use Avax\Auth\System\Flow\Session\ActiveSession;
use Avax\Auth\System\Flow\Session\LogoutAllSessions\LogoutAllSessions;
use Avax\Auth\System\Flow\Session\ReadActiveSessions\ReadActiveSessions;
use Avax\Auth\System\Flow\Session\RevokeSession\RevokeSession;
use Avax\Auth\System\Flow\TenantSecurity\ApplyChange\ApplyTenantSecurityChange;
use Avax\Auth\System\Flow\TenantSecurity\ApproveChange\ApproveTenantSecurityChange;
use Avax\Auth\System\Flow\TenantSecurity\BeginChange\BeginTenantSecurityChange;
use Avax\Auth\System\Flow\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use Avax\Auth\System\Flow\TenantSecurity\ReadChangeRequest\ReadTenantSecurityChangeRequest;
use Avax\Auth\System\Flow\TenantSecurity\ReadChangeRequests\ReadTenantSecurityChangeRequests;
use Avax\Auth\System\Flow\TenantSecurity\ReadConfiguration\ReadTenantSecurityConfiguration;
use Avax\Auth\System\Flow\TenantSecurity\RollbackChange\RollbackTenantSecurityChange;
use Avax\Auth\System\Flow\Token\RefreshAuthentication;
use Avax\Auth\System\Flow\Token\RefreshAuthenticationRequest;
use Avax\Auth\System\Flow\Verify\BeginEmailVerification;
use Avax\Auth\System\Flow\Verify\BeginEmailVerificationData;
use Avax\Auth\System\Flow\Verify\EmailVerificationChallenge;
use Avax\Auth\System\Flow\Verify\VerifyEmail;
use Avax\Auth\System\Flow\Verify\VerifyEmailData;
use RuntimeException;

/**
 * Main entry point for Avax Auth System.
 *
 * Banal: The facade that connects all system flows.
 */
final readonly class Auth implements AuthInterface
{
    public function __construct(
        private Login                  $login,
        private AuthenticateRequest    $authenticateRequest,
        private Logout                 $logout,
        private LogoutAllSessions      $logoutAllSessions,
        private CheckAuthentication    $checkAuthentication,
        private ReadCurrentUser        $readCurrentUser,
        private ReadActiveSessions     $readActiveSessions,
        private RevokeSession          $revokeSession,
        private CurrentAuthentication  $currentAuthentication,
        private AccessInterface        $access,
        private ChangePassword         $changePassword,
        private BeginEmailChange|null  $beginEmailChange,
        private ConfirmEmailChange|null $confirmEmailChange,
        private Register               $register,
        private RefreshAuthentication  $refreshAuthentication,
        private RegisterClient|null    $registerOAuthClient,
        private ReadClients|null       $readOAuthClients,
        private ReadWorkloadIdentities|null $readWorkloadIdentities,
        private ReadOidcProviderMetadata|null $readOidcProviderMetadata,
        private ReadOidcJsonWebKeySet|null $readOidcJsonWebKeySet,
        private ReadOidcUserInfo|null $readOidcUserInfo,
        private AuthorizeCode|null     $authorizeOAuthCode,
        private ExchangeAuthorizationCode|null $exchangeOAuthCode,
        private ExchangeClientCredentials|null $exchangeOAuthClientCredentials,
        private ExchangeRefreshToken|null $exchangeOAuthRefreshToken,
        private RevokeToken|null       $revokeOAuthToken,
        private IntrospectToken|null   $introspectOAuthToken,
        private BeginAdminElevation    $beginAdminElevation,
        private EndAdminElevation      $endAdminElevation,
        private RequireAdminElevation  $requireAdminElevation,
        private SuspendUser|null       $suspendUser,
        private ReactivateUser|null    $reactivateUser,
        private DeprovisionUser|null   $deprovisionUser,
        private BeginPasskeyRegistration|null $beginPasskeyRegistration,
        private CompletePasskeyRegistration|null $completePasskeyRegistration,
        private BeginPasskeyAuthentication|null $beginPasskeyAuthentication,
        private CompletePasskeyAuthentication|null $completePasskeyAuthentication,
        private ListPasskeys|null      $readPasskeys,
        private RenamePasskey|null     $renamePasskey,
        private RevokePasskeyFlow|null $revokePasskey,
        private RegisterFederationConnection|null $registerFederationConnection,
        private ReadFederationConnections|null $readFederationConnections,
        private VerifyFederationDomain|null $verifyFederationDomain,
        private SyncFederationMetadata|null $syncFederationMetadata,
        private CheckFederationConnectionHealth|null $checkFederationConnectionHealth,
        private EvaluateFederationBreakGlassBypass|null $evaluateFederationBreakGlassBypass,
        private DiscoverFederationConnection|null $discoverFederationConnection,
        private StartFederatedLogin|null $startFederatedLogin,
        private CompleteFederatedLogin|null $completeFederatedLogin,
        private RegisterScimDirectory|null $registerScimDirectory,
        private ReadScimDirectories|null $readScimDirectories,
        private RotateScimToken|null $rotateScimToken,
        private ProvisionScimUser|null $provisionScimUser,
        private DeleteScimUser|null $deleteScimUser,
        private ReadScimUsers|null $readScimUsers,
        private SyncScimGroups|null $syncScimGroups,
        private ReadTenantSecurityConfiguration $readTenantSecurityConfiguration,
        private ReadTenantSecurityChangeRequest $readTenantSecurityChangeRequest,
        private ReadTenantSecurityChangeRequests $readTenantSecurityChangeRequests,
        private BeginTenantSecurityChange $beginTenantSecurityChange,
        private ApproveTenantSecurityChange $approveTenantSecurityChange,
        private ApplyTenantSecurityChange $applyTenantSecurityChange,
        private RollbackTenantSecurityChange $rollbackTenantSecurityChange,
        private AssessCurrentRisk      $assessCurrentRisk,
        private ReadRiskSignals        $readRiskSignals,
        private BeginPasswordReset     $beginPasswordReset,
        private ResetPassword          $resetPassword,
        private BeginEmailVerification $beginEmailVerification,
        private VerifyEmail            $verifyEmail,
        private StartMfaEnrollment     $startMfaEnrollment,
        private ConfirmMfaEnrollment   $confirmMfaEnrollment,
        private CancelMfaEnrollment    $cancelMfaEnrollment,
        private StartMfaChallenge      $startMfaChallenge,
        private VerifyMfaChallenge     $verifyMfaChallenge,
        private RegenerateBackupCodes  $regenerateBackupCodes,
        private DisableMfa             $disableMfa,
        private StartMfaRecovery       $startMfaRecovery,
        private ConfirmMfaRecovery     $confirmMfaRecovery
    ) {}

    /**
     * Start the fluent configuration builder.
     */
    public static function configuration() : AuthBuilder
    {
        return new AuthBuilder();
    }

    public function login(Credentials $credentials) : AuthenticationResult
    {
        return $this->login->execute(credentials: $credentials);
    }

    public function authenticateRequest(AuthenticationRequest $request) : AuthenticationContext
    {
        return $this->authenticateRequest->execute($request);
    }

    public function current() : AuthenticationContext
    {
        return $this->currentAuthentication->read();
    }

    public function logout() : void
    {
        $this->logout->execute();
    }

    public function logoutAllSessions() : void
    {
        $this->logoutAllSessions->execute();
    }

    public function readActiveSessions() : array
    {
        return $this->readActiveSessions->execute();
    }

    public function revokeSession(string $sessionId) : void
    {
        $this->revokeSession->execute($sessionId);
    }

    public function check() : bool
    {
        return $this->checkAuthentication->execute();
    }

    public function user() : AuthenticatedUser|null
    {
        return $this->readCurrentUser->execute();
    }

    public function access() : AccessInterface
    {
        return $this->access;
    }

    public function changePassword(ChangePasswordData $data) : void
    {
        $this->changePassword->execute(data: $data);
    }

    public function beginEmailChange(BeginEmailChangeData $data) : EmailChangeChallenge
    {
        return $this->beginEmailChangeOrFail()->execute($data);
    }

    public function confirmEmailChange(ConfirmEmailChangeData $data) : bool
    {
        return $this->confirmEmailChangeOrFail()->execute($data);
    }

    public function register(RegistrationData $data) : RegistrationResult
    {
        return $this->register->execute(data: $data);
    }

    public function refresh(RefreshAuthenticationRequest $request) : AuthenticationResult
    {
        return $this->refreshAuthentication->execute($request);
    }

    public function registerOAuthClient(RegisterClientData $data) : RegisteredOAuthClient
    {
        return $this->registerOAuthClientOrFail()->execute($data);
    }

    public function readOAuthClients() : array
    {
        return $this->readOAuthClientsOrFail()->execute();
    }

    /**
     * @return list<WorkloadIdentityProfile>
     */
    public function readWorkloadIdentities() : array
    {
        return $this->readWorkloadIdentitiesOrFail()->execute();
    }

    public function readOidcProviderMetadata() : OidcProviderMetadata
    {
        return $this->readOidcProviderMetadataOrFail()->execute();
    }

    public function readOidcJsonWebKeySet() : OidcJsonWebKeySet
    {
        return $this->readOidcJsonWebKeySetOrFail()->execute();
    }

    public function readOidcUserInfo(string $accessToken) : OidcUserInfo
    {
        return $this->readOidcUserInfoOrFail()->execute($accessToken);
    }

    public function registerScimDirectory(RegisterScimDirectoryData $data) : RegisteredScimDirectory
    {
        return $this->registerScimDirectoryOrFail()->execute($data);
    }

    public function readScimDirectories(string|null $tenantSlug = null) : array
    {
        return $this->readScimDirectoriesOrFail()->execute($tenantSlug);
    }

    public function rotateScimToken(string $directoryId) : RotatedScimToken
    {
        return $this->rotateScimTokenOrFail()->execute($directoryId);
    }

    public function provisionScimUser(ProvisionScimUserData $data) : ScimProvisioningResult
    {
        return $this->provisionScimUserOrFail()->execute($data);
    }

    public function deleteScimUser(DeleteScimUserData $data) : void
    {
        $this->deleteScimUserOrFail()->execute($data);
    }

    public function readScimUsers(string $directoryId) : array
    {
        return $this->readScimUsersOrFail()->execute($directoryId);
    }

    public function syncScimGroups(SyncScimGroupsData $data) : ScimProvisioningResult
    {
        return $this->syncScimGroupsOrFail()->execute($data);
    }

    public function readTenantSecurityConfiguration(string $tenantSlug) : TenantSecurityConfiguration|null
    {
        return $this->readTenantSecurityConfiguration->execute($tenantSlug);
    }

    public function readTenantSecurityChangeRequest(string $changeId) : TenantSecurityChangeRequest|null
    {
        return $this->readTenantSecurityChangeRequest->execute($changeId);
    }

    public function readTenantSecurityChangeRequests(string $tenantSlug) : array
    {
        return $this->readTenantSecurityChangeRequests->execute($tenantSlug);
    }

    public function beginTenantSecurityChange(BeginTenantSecurityChangeData $data) : TenantSecurityChangeRequest
    {
        return $this->beginTenantSecurityChange->execute($data);
    }

    public function approveTenantSecurityChange(string $changeId, string $approvedBy) : TenantSecurityChangeRequest
    {
        return $this->approveTenantSecurityChange->execute($changeId, $approvedBy);
    }

    public function applyTenantSecurityChange(string $changeId) : TenantSecurityConfiguration
    {
        return $this->applyTenantSecurityChange->execute($changeId);
    }

    public function rollbackTenantSecurityChange(string $changeId) : TenantSecurityConfiguration
    {
        return $this->rollbackTenantSecurityChange->execute($changeId);
    }

    public function authorizeOAuthCode(AuthorizeCodeData $data) : IssuedAuthorizationCode
    {
        return $this->authorizeOAuthCodeOrFail()->execute($data);
    }

    public function exchangeOAuthCode(ExchangeAuthorizationCodeData $data) : OAuthTokenGrant
    {
        return $this->exchangeOAuthCodeOrFail()->execute($data);
    }

    public function exchangeOAuthClientCredentials(ExchangeClientCredentialsData $data) : OAuthTokenGrant
    {
        return $this->exchangeOAuthClientCredentialsOrFail()->execute($data);
    }

    public function exchangeOAuthRefreshToken(ExchangeRefreshTokenData $data) : OAuthTokenGrant
    {
        return $this->exchangeOAuthRefreshTokenOrFail()->execute($data);
    }

    public function revokeOAuthToken(RevokeTokenData $data) : void
    {
        $this->revokeOAuthTokenOrFail()->execute($data);
    }

    public function introspectOAuthToken(IntrospectTokenData $data) : TokenIntrospection
    {
        return $this->introspectOAuthTokenOrFail()->execute($data);
    }

    public function beginAdminElevation() : AdminElevation
    {
        return $this->beginAdminElevation->execute();
    }

    public function endAdminElevation() : void
    {
        $this->endAdminElevation->execute();
    }

    public function requireAdminElevation() : void
    {
        $this->requireAdminElevation->execute();
    }

    public function suspendUser(int $userId) : void
    {
        $this->suspendUserOrFail()->execute($userId);
    }

    public function reactivateUser(int $userId) : void
    {
        $this->reactivateUserOrFail()->execute($userId);
    }

    public function deprovisionUser(int $userId) : void
    {
        $this->deprovisionUserOrFail()->execute($userId);
    }

    public function beginPasskeyRegistration() : PasskeyRegistration
    {
        return $this->beginPasskeyRegistrationOrFail()->execute();
    }

    public function completePasskeyRegistration(CompletePasskeyRegistrationData $data) : PasskeyCredential
    {
        return $this->completePasskeyRegistrationOrFail()->execute($data);
    }

    public function beginPasskeyAuthentication(BeginPasskeyAuthenticationData $data) : PasskeyAuthenticationChallenge
    {
        return $this->beginPasskeyAuthenticationOrFail()->execute($data);
    }

    public function completePasskeyAuthentication(CompletePasskeyAuthenticationData $data) : AuthenticationResult
    {
        return $this->completePasskeyAuthenticationOrFail()->execute($data);
    }

    public function readPasskeys() : array
    {
        return $this->readPasskeysOrFail()->execute();
    }

    public function renamePasskey(RenamePasskeyData $data) : PasskeyCredential
    {
        return $this->renamePasskeyOrFail()->execute($data);
    }

    public function revokePasskey(string $credentialId) : void
    {
        $this->revokePasskeyOrFail()->execute($credentialId);
    }

    public function registerFederationConnection(RegisterFederationConnectionData $data) : FederationConnection
    {
        return $this->registerFederationConnectionOrFail()->execute($data);
    }

    public function readFederationConnections() : array
    {
        return $this->readFederationConnectionsOrFail()->execute();
    }

    public function verifyFederationDomain(VerifyFederationDomainData $data) : FederationConnection
    {
        return $this->verifyFederationDomainOrFail()->execute($data);
    }

    public function syncFederationMetadata(string $connectionId) : FederationConnection
    {
        return $this->syncFederationMetadataOrFail()->execute($connectionId);
    }

    public function checkFederationConnectionHealth(string $connectionId) : FederationConnectionHealth
    {
        return $this->checkFederationConnectionHealthOrFail()->execute($connectionId);
    }

    public function evaluateFederationBreakGlassBypass(string $connectionId) : bool
    {
        return $this->evaluateFederationBreakGlassBypassOrFail()->execute($connectionId);
    }

    public function discoverFederationConnection(string $email) : FederationConnection|null
    {
        return $this->discoverFederationConnectionOrFail()->execute($email);
    }

    public function startFederatedLogin(StartFederatedLoginData $data) : StartedFederatedLogin
    {
        return $this->startFederatedLoginOrFail()->execute($data);
    }

    public function completeFederatedLogin(CompleteFederatedLoginData $data) : AuthenticationResult
    {
        return $this->completeFederatedLoginOrFail()->execute($data);
    }

    public function assessCurrentRisk(string|null $ipAddress = null, string|null $userAgent = null) : RiskDecision|null
    {
        return $this->assessCurrentRisk->execute($ipAddress, $userAgent);
    }

    public function readRiskSignals(int|null $userId = null) : array
    {
        return $this->readRiskSignals->execute($userId);
    }

    public function beginPasswordReset(BeginPasswordResetData $data) : PasswordResetChallenge
    {
        return $this->beginPasswordReset->execute($data);
    }

    public function resetPassword(ResetPasswordData $data) : bool
    {
        return $this->resetPassword->execute($data);
    }

    public function beginEmailVerification(BeginEmailVerificationData $data) : EmailVerificationChallenge
    {
        return $this->beginEmailVerification->execute($data);
    }

    public function verifyEmail(VerifyEmailData $data) : bool
    {
        return $this->verifyEmail->execute($data);
    }

    public function startMfaEnrollment() : MfaEnrollment
    {
        return $this->startMfaEnrollment->execute();
    }

    public function confirmMfaEnrollment(ConfirmMfaEnrollmentData $data) : BackupCodeSet
    {
        return $this->confirmMfaEnrollment->execute($data);
    }

    public function cancelMfaEnrollment() : void
    {
        $this->cancelMfaEnrollment->execute();
    }

    public function beginMfaChallenge() : MfaChallenge
    {
        return $this->startMfaChallenge->execute();
    }

    public function verifyMfaChallenge(VerifyMfaChallengeData $data) : AuthenticationResult
    {
        return $this->verifyMfaChallenge->execute($data);
    }

    public function regenerateBackupCodes() : BackupCodeSet
    {
        return $this->regenerateBackupCodes->execute();
    }

    public function disableMfa() : void
    {
        $this->disableMfa->execute();
    }

    public function beginMfaRecovery(BeginMfaRecoveryData $data) : MfaRecoveryChallenge
    {
        return $this->startMfaRecovery->execute($data);
    }

    public function confirmMfaRecovery(ConfirmMfaRecoveryData $data) : void
    {
        $this->confirmMfaRecovery->execute($data);
    }

    private function registerOAuthClientOrFail() : RegisterClient
    {
        return $this->registerOAuthClient ?? throw new RuntimeException('OAuth client registry is not configured.');
    }

    private function readOAuthClientsOrFail() : ReadClients
    {
        return $this->readOAuthClients ?? throw new RuntimeException('OAuth client registry is not configured.');
    }

    private function readWorkloadIdentitiesOrFail() : ReadWorkloadIdentities
    {
        return $this->readWorkloadIdentities ?? throw new RuntimeException('OAuth client registry is not configured.');
    }

    private function readOidcProviderMetadataOrFail() : ReadOidcProviderMetadata
    {
        return $this->readOidcProviderMetadata ?? throw new RuntimeException('OIDC provider is not configured.');
    }

    private function readOidcJsonWebKeySetOrFail() : ReadOidcJsonWebKeySet
    {
        return $this->readOidcJsonWebKeySet ?? throw new RuntimeException('OIDC provider is not configured.');
    }

    private function readOidcUserInfoOrFail() : ReadOidcUserInfo
    {
        return $this->readOidcUserInfo ?? throw new RuntimeException('OIDC provider is not configured.');
    }

    private function registerScimDirectoryOrFail() : RegisterScimDirectory
    {
        return $this->registerScimDirectory ?? throw new RuntimeException('SCIM runtime is not configured.');
    }

    private function rotateScimTokenOrFail() : RotateScimToken
    {
        return $this->rotateScimToken ?? throw new RuntimeException('SCIM runtime is not configured.');
    }

    private function readScimDirectoriesOrFail() : ReadScimDirectories
    {
        return $this->readScimDirectories ?? throw new RuntimeException('SCIM runtime is not configured.');
    }

    private function provisionScimUserOrFail() : ProvisionScimUser
    {
        return $this->provisionScimUser ?? throw new RuntimeException('SCIM runtime is not configured.');
    }

    private function deleteScimUserOrFail() : DeleteScimUser
    {
        return $this->deleteScimUser ?? throw new RuntimeException('SCIM runtime is not configured.');
    }

    private function readScimUsersOrFail() : ReadScimUsers
    {
        return $this->readScimUsers ?? throw new RuntimeException('SCIM runtime is not configured.');
    }

    private function syncScimGroupsOrFail() : SyncScimGroups
    {
        return $this->syncScimGroups ?? throw new RuntimeException('SCIM runtime is not configured.');
    }

    private function authorizeOAuthCodeOrFail() : AuthorizeCode
    {
        return $this->authorizeOAuthCode ?? throw new RuntimeException('OAuth authorization code flow is not configured.');
    }

    private function exchangeOAuthCodeOrFail() : ExchangeAuthorizationCode
    {
        return $this->exchangeOAuthCode ?? throw new RuntimeException('OAuth token exchange is not configured.');
    }

    private function exchangeOAuthClientCredentialsOrFail() : ExchangeClientCredentials
    {
        return $this->exchangeOAuthClientCredentials ?? throw new RuntimeException('OAuth client credentials flow is not configured.');
    }

    private function exchangeOAuthRefreshTokenOrFail() : ExchangeRefreshToken
    {
        return $this->exchangeOAuthRefreshToken ?? throw new RuntimeException('OAuth refresh flow is not configured.');
    }

    private function revokeOAuthTokenOrFail() : RevokeToken
    {
        return $this->revokeOAuthToken ?? throw new RuntimeException('OAuth revoke flow is not configured.');
    }

    private function introspectOAuthTokenOrFail() : IntrospectToken
    {
        return $this->introspectOAuthToken ?? throw new RuntimeException('OAuth introspection is not configured.');
    }

    private function suspendUserOrFail() : SuspendUser
    {
        return $this->suspendUser ?? throw new RuntimeException('Provisioning lifecycle is not configured.');
    }

    private function beginEmailChangeOrFail() : BeginEmailChange
    {
        return $this->beginEmailChange ?? throw new RuntimeException('Email change flow is not configured.');
    }

    private function confirmEmailChangeOrFail() : ConfirmEmailChange
    {
        return $this->confirmEmailChange ?? throw new RuntimeException('Email change flow is not configured.');
    }

    private function reactivateUserOrFail() : ReactivateUser
    {
        return $this->reactivateUser ?? throw new RuntimeException('Provisioning lifecycle is not configured.');
    }

    private function deprovisionUserOrFail() : DeprovisionUser
    {
        return $this->deprovisionUser ?? throw new RuntimeException('Provisioning lifecycle is not configured.');
    }

    private function beginPasskeyRegistrationOrFail() : BeginPasskeyRegistration
    {
        return $this->beginPasskeyRegistration ?? throw new RuntimeException('Passkey runtime is not configured.');
    }

    private function completePasskeyRegistrationOrFail() : CompletePasskeyRegistration
    {
        return $this->completePasskeyRegistration ?? throw new RuntimeException('Passkey runtime is not configured.');
    }

    private function beginPasskeyAuthenticationOrFail() : BeginPasskeyAuthentication
    {
        return $this->beginPasskeyAuthentication ?? throw new RuntimeException('Passkey runtime is not configured.');
    }

    private function completePasskeyAuthenticationOrFail() : CompletePasskeyAuthentication
    {
        return $this->completePasskeyAuthentication ?? throw new RuntimeException('Passkey runtime is not configured.');
    }

    private function readPasskeysOrFail() : ListPasskeys
    {
        return $this->readPasskeys ?? throw new RuntimeException('Passkey runtime is not configured.');
    }

    private function renamePasskeyOrFail() : RenamePasskey
    {
        return $this->renamePasskey ?? throw new RuntimeException('Passkey runtime is not configured.');
    }

    private function revokePasskeyOrFail() : RevokePasskeyFlow
    {
        return $this->revokePasskey ?? throw new RuntimeException('Passkey runtime is not configured.');
    }

    private function registerFederationConnectionOrFail() : RegisterFederationConnection
    {
        return $this->registerFederationConnection ?? throw new RuntimeException('Federation runtime is not configured.');
    }

    private function readFederationConnectionsOrFail() : ReadFederationConnections
    {
        return $this->readFederationConnections ?? throw new RuntimeException('Federation runtime is not configured.');
    }

    private function verifyFederationDomainOrFail() : VerifyFederationDomain
    {
        return $this->verifyFederationDomain ?? throw new RuntimeException('Federation runtime is not configured.');
    }

    private function syncFederationMetadataOrFail() : SyncFederationMetadata
    {
        return $this->syncFederationMetadata ?? throw new RuntimeException('Federation metadata runtime is not configured.');
    }

    private function checkFederationConnectionHealthOrFail() : CheckFederationConnectionHealth
    {
        return $this->checkFederationConnectionHealth ?? throw new RuntimeException('Federation health checks are not configured.');
    }

    private function evaluateFederationBreakGlassBypassOrFail() : EvaluateFederationBreakGlassBypass
    {
        return $this->evaluateFederationBreakGlassBypass ?? throw new RuntimeException('Federation runtime is not configured.');
    }

    private function discoverFederationConnectionOrFail() : DiscoverFederationConnection
    {
        return $this->discoverFederationConnection ?? throw new RuntimeException('Federation runtime is not configured.');
    }

    private function startFederatedLoginOrFail() : StartFederatedLogin
    {
        return $this->startFederatedLogin ?? throw new RuntimeException('Federation runtime is not configured.');
    }

    private function completeFederatedLoginOrFail() : CompleteFederatedLogin
    {
        return $this->completeFederatedLogin ?? throw new RuntimeException('Federation runtime is not configured.');
    }
}
