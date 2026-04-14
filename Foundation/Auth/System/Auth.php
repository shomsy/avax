<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Capability\Access\AccessInterface;
use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
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
use Avax\Auth\System\Capability\Scim\ScimDirectory;
use Avax\Auth\System\Capability\Tenant\Tenant;
use Avax\Auth\System\Capability\Tenant\TenantMember;
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
use Avax\Auth\System\Flow\ChangePassword\PasswordChangeFailed;
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
use Avax\Auth\System\Flow\Login\AuthenticationFailed;
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
use Avax\Auth\System\Flow\OAuth\ApproveClientRegistration\ApproveClientRegistration;
use Avax\Auth\System\Flow\OAuth\ApproveClientRegistration\ApproveClientRegistrationData;
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
use Avax\Auth\System\Flow\OAuth\RotateClientSecret\RotateClientSecret;
use Avax\Auth\System\Flow\OAuth\RevokeToken\RevokeToken;
use Avax\Auth\System\Flow\OAuth\RevokeToken\RevokeTokenData;
use Avax\Auth\System\Flow\OAuth\DisableClient\DisableClient;
use Avax\Auth\System\Flow\OAuth\UpdateClient\UpdateClient;
use Avax\Auth\System\Flow\OAuth\UpdateClient\UpdateClientData;
use Avax\Auth\System\Flow\Oidc\ReadJsonWebKeySet\ReadOidcJsonWebKeySet;
use Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest\PushAuthorizationRequest;
use Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest\PushAuthorizationRequestData;
use Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest\PushedAuthorizationRequest;
use Avax\Auth\System\Flow\Oidc\Logout\Logout as OidcLogout;
use Avax\Auth\System\Flow\Oidc\Logout\LogoutData as OidcLogoutData;
use Avax\Auth\System\Flow\Oidc\Logout\LogoutResult as OidcLogoutResult;
use Avax\Auth\System\Flow\Oidc\JarmResponse\BuildJarmResponse;
use Avax\Auth\System\Flow\Oidc\JarmResponse\BuildJarmResponseData;
use Avax\Auth\System\Flow\Oidc\JarmResponse\JarmResponse;
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
use Avax\Auth\System\Flow\Register\RegistrationFailed;
use Avax\Auth\System\Flow\Register\RegistrationResult;
use Avax\Auth\System\Flow\Risk\AssessCurrentRisk\AssessCurrentRisk;
use Avax\Auth\System\Flow\Risk\ReadRiskSignals\ReadRiskSignals;
use Avax\Auth\System\Flow\Scim\Bulk\RunScimBulk;
use Avax\Auth\System\Flow\Scim\Bulk\ScimBulkRequest;
use Avax\Auth\System\Flow\Scim\Bulk\ScimBulkResponse;
use Avax\Auth\System\Flow\Scim\DeleteUser\DeleteScimUser;
use Avax\Auth\System\Flow\Scim\DeleteUser\DeleteScimUserData;
use Avax\Auth\System\Flow\Scim\MarkOutage\MarkScimDirectoryOutage;
use Avax\Auth\System\Flow\Scim\MarkOutage\MarkScimDirectoryOutageData;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ProvisionScimUser;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ProvisionScimUserData;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ScimProvisioningResult;
use Avax\Auth\System\Flow\Scim\ReadDirectories\ReadScimDirectories;
use Avax\Auth\System\Flow\Scim\ReadGroups\ReadScimGroups;
use Avax\Auth\System\Flow\Scim\ReadGroups\ScimGroupProjection;
use Avax\Auth\System\Flow\Scim\ReadUsers\ReadScimUsers;
use Avax\Auth\System\Flow\Scim\ReadUsers\ScimUserProjection;
use Avax\Auth\System\Flow\Scim\RegisterDirectory\RegisterScimDirectory;
use Avax\Auth\System\Flow\Scim\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Auth\System\Flow\Scim\RecoverOutage\RecoverScimDirectoryOutage;
use Avax\Auth\System\Flow\Scim\RecoverOutage\RecoverScimDirectoryOutageData;
use Avax\Auth\System\Flow\Scim\RotateToken\RotateScimToken;
use Avax\Auth\System\Flow\Scim\RotateToken\RotatedScimToken;
use Avax\Auth\System\Flow\Scim\SyncGroups\SyncScimGroups;
use Avax\Auth\System\Flow\Scim\SyncGroups\SyncScimGroupsData;
use Avax\Auth\System\Flow\Session\ActiveSession;
use Avax\Auth\System\Flow\Session\LogoutAllSessions\LogoutAllSessions;
use Avax\Auth\System\Flow\Session\ReadActiveSessions\ReadActiveSessions;
use Avax\Auth\System\Flow\Session\RevokeSession\RevokeSession;
use Avax\Auth\System\Flow\Tenant\AcceptInvite\AcceptTenantInvite;
use Avax\Auth\System\Flow\Tenant\AcceptInvite\AcceptTenantInviteData;
use Avax\Auth\System\Flow\Tenant\CreateTenant\CreateTenant;
use Avax\Auth\System\Flow\Tenant\CreateTenant\CreateTenantData;
use Avax\Auth\System\Flow\Tenant\InviteMember\InviteTenantMember;
use Avax\Auth\System\Flow\Tenant\InviteMember\InviteTenantMemberData;
use Avax\Auth\System\Flow\Tenant\InviteMember\IssuedTenantInvite;
use Avax\Auth\System\Flow\Tenant\ReadMembers\ReadTenantMembers;
use Avax\Auth\System\Flow\Tenant\ReadTenants\ReadTenants;
use Avax\Auth\System\Flow\Tenant\RemoveMember\RemoveTenantMember;
use Avax\Auth\System\Flow\Tenant\RemoveMember\RemoveTenantMemberData;
use Avax\Auth\System\Flow\Tenant\SuspendMember\SuspendTenantMember;
use Avax\Auth\System\Flow\Tenant\SuspendMember\SuspendTenantMemberData;
use Avax\Auth\System\Flow\Tenant\TransferOwnership\TransferTenantOwnership;
use Avax\Auth\System\Flow\Tenant\TransferOwnership\TransferTenantOwnershipData;
use Avax\Auth\System\Flow\TenantSecurity\ApplyChange\ApplyTenantSecurityChange;
use Avax\Auth\System\Flow\TenantSecurity\ApproveChange\ApproveTenantSecurityChange;
use Avax\Auth\System\Flow\TenantSecurity\BeginChange\BeginTenantSecurityChange;
use Avax\Auth\System\Flow\TenantSecurity\BeginChange\BeginTenantSecurityChangeData;
use Avax\Auth\System\Flow\TenantSecurity\ReadChangeRequest\ReadTenantSecurityChangeRequest;
use Avax\Auth\System\Flow\TenantSecurity\ReadChangeRequests\ReadTenantSecurityChangeRequests;
use Avax\Auth\System\Flow\TenantSecurity\ReadConfiguration\ReadTenantSecurityConfiguration;
use Avax\Auth\System\Flow\TenantSecurity\RollbackChange\RollbackTenantSecurityChange;
use Avax\Auth\System\Flow\Token\RefreshAuthentication;
use Avax\Auth\System\Flow\Token\RefreshAuthenticationFailed;
use Avax\Auth\System\Flow\Token\RefreshAuthenticationRequest;
use Avax\Auth\System\Flow\Verify\BeginEmailVerification;
use Avax\Auth\System\Flow\Verify\BeginEmailVerificationData;
use Avax\Auth\System\Flow\Verify\EmailVerificationChallenge;
use Avax\Auth\System\Flow\Verify\VerifyEmail;
use Avax\Auth\System\Flow\Verify\VerifyEmailData;
use RuntimeException;
use SensitiveParameter;

/**
 * Main entry point for Avax Auth System.
 *
 * Banal: The facade that connects all system flows.
 */
final readonly class Auth implements AuthInterface
{
    public function __construct(
        private Login                                                    $login,
        private AuthenticateRequest                                      $authenticateRequest,
        private Logout                                                   $logout,
        #[SensitiveParameter] private LogoutAllSessions                  $logoutAllSessions,
        #[SensitiveParameter] private CheckAuthentication                $checkAuthentication,
        private ReadCurrentUser                                          $readCurrentUser,
        #[SensitiveParameter] private ReadActiveSessions                 $readActiveSessions,
        #[SensitiveParameter] private RevokeSession                      $revokeSession,
        #[SensitiveParameter] private CurrentAuthentication              $currentAuthentication,
        #[SensitiveParameter] private AccessInterface                    $access,
        #[SensitiveParameter] private ChangePassword                     $changePassword,
        #[SensitiveParameter] private BeginEmailChange|null              $beginEmailChange,
        #[SensitiveParameter] private ConfirmEmailChange|null            $confirmEmailChange,
        private Register                                                 $register,
        #[SensitiveParameter] private RefreshAuthentication              $refreshAuthentication,
        private RegisterClient|null                                      $registerOAuthClient,
        private ApproveClientRegistration|null                           $approveOAuthClientRegistration,
        private UpdateClient|null                                        $updateOAuthClient,
        private DisableClient|null                                       $disableOAuthClient,
        private RotateClientSecret|null                                  $rotateOAuthClientSecret,
        private ReadClients|null                                         $readOAuthClients,
        private ReadWorkloadIdentities|null                              $readWorkloadIdentities,
        private ReadOidcProviderMetadata|null                            $readOidcProviderMetadata,
        private ReadOidcJsonWebKeySet|null                               $readOidcJsonWebKeySet,
        private ReadOidcUserInfo|null                                    $readOidcUserInfo,
        private PushAuthorizationRequest|null                            $pushOidcAuthorizationRequest,
        private OidcLogout|null                                          $oidcLogout,
        private BuildJarmResponse|null                                   $buildOidcJarmResponse,
        #[SensitiveParameter] private AuthorizeCode|null                 $authorizeOAuthCode,
        #[SensitiveParameter] private ExchangeAuthorizationCode|null     $exchangeOAuthCode,
        #[SensitiveParameter] private ExchangeClientCredentials|null     $exchangeOAuthClientCredentials,
        #[SensitiveParameter] private ExchangeRefreshToken|null          $exchangeOAuthRefreshToken,
        #[SensitiveParameter] private RevokeToken|null                   $revokeOAuthToken,
        #[SensitiveParameter] private IntrospectToken|null               $introspectOAuthToken,
        private BeginAdminElevation                                      $beginAdminElevation,
        private EndAdminElevation                                        $endAdminElevation,
        private RequireAdminElevation                                    $requireAdminElevation,
        private SuspendUser|null                                         $suspendUser,
        private ReactivateUser|null                                      $reactivateUser,
        private DeprovisionUser|null                                     $deprovisionUser,
        private BeginPasskeyRegistration|null                            $beginPasskeyRegistration,
        private CompletePasskeyRegistration|null                         $completePasskeyRegistration,
        #[SensitiveParameter] private BeginPasskeyAuthentication|null    $beginPasskeyAuthentication,
        #[SensitiveParameter] private CompletePasskeyAuthentication|null $completePasskeyAuthentication,
        private ListPasskeys|null                                        $readPasskeys,
        private RenamePasskey|null                                       $renamePasskey,
        private RevokePasskeyFlow|null                                   $revokePasskey,
        private RegisterFederationConnection|null                        $registerFederationConnection,
        private ReadFederationConnections|null                           $readFederationConnections,
        private VerifyFederationDomain|null                              $verifyFederationDomain,
        private SyncFederationMetadata|null                              $syncFederationMetadata,
        private CheckFederationConnectionHealth|null                     $checkFederationConnectionHealth,
        private EvaluateFederationBreakGlassBypass|null                  $evaluateFederationBreakGlassBypass,
        private DiscoverFederationConnection|null                        $discoverFederationConnection,
        private StartFederatedLogin|null                                 $startFederatedLogin,
        private CompleteFederatedLogin|null                              $completeFederatedLogin,
        private RegisterScimDirectory|null                               $registerScimDirectory,
        private ReadScimDirectories|null                                 $readScimDirectories,
        #[SensitiveParameter] private RotateScimToken|null               $rotateScimToken,
        private MarkScimDirectoryOutage|null                             $markScimDirectoryOutage,
        private RecoverScimDirectoryOutage|null                          $recoverScimDirectoryOutage,
        private ProvisionScimUser|null                                   $provisionScimUser,
        private DeleteScimUser|null                                      $deleteScimUser,
        private ReadScimUsers|null                                       $readScimUsers,
        private ReadScimGroups|null                                      $readScimGroups,
        private SyncScimGroups|null                                      $syncScimGroups,
        private RunScimBulk|null                                         $runScimBulk,
        private CreateTenant|null                                        $createTenant,
        private ReadTenants|null                                         $readTenants,
        private InviteTenantMember|null                                  $inviteTenantMember,
        private AcceptTenantInvite|null                                  $acceptTenantInvite,
        private ReadTenantMembers|null                                   $readTenantMembers,
        private RemoveTenantMember|null                                  $removeTenantMember,
        private SuspendTenantMember|null                                 $suspendTenantMember,
        private TransferTenantOwnership|null                             $transferTenantOwnership,
        #[SensitiveParameter] private ReadTenantSecurityConfiguration    $readTenantSecurityConfiguration,
        #[SensitiveParameter] private ReadTenantSecurityChangeRequest    $readTenantSecurityChangeRequest,
        #[SensitiveParameter] private ReadTenantSecurityChangeRequests   $readTenantSecurityChangeRequests,
        #[SensitiveParameter] private BeginTenantSecurityChange          $beginTenantSecurityChange,
        #[SensitiveParameter] private ApproveTenantSecurityChange        $approveTenantSecurityChange,
        #[SensitiveParameter] private ApplyTenantSecurityChange          $applyTenantSecurityChange,
        #[SensitiveParameter] private RollbackTenantSecurityChange       $rollbackTenantSecurityChange,
        private AssessCurrentRisk                                        $assessCurrentRisk,
        private ReadRiskSignals                                          $readRiskSignals,
        #[SensitiveParameter] private BeginPasswordReset                 $beginPasswordReset,
        #[SensitiveParameter] private ResetPassword                      $resetPassword,
        #[SensitiveParameter] private BeginEmailVerification             $beginEmailVerification,
        #[SensitiveParameter] private VerifyEmail                        $verifyEmail,
        private StartMfaEnrollment                                       $startMfaEnrollment,
        private ConfirmMfaEnrollment                                     $confirmMfaEnrollment,
        private CancelMfaEnrollment                                      $cancelMfaEnrollment,
        private StartMfaChallenge                                        $startMfaChallenge,
        private VerifyMfaChallenge                                       $verifyMfaChallenge,
        #[SensitiveParameter] private RegenerateBackupCodes              $regenerateBackupCodes,
        private DisableMfa                                               $disableMfa,
        private StartMfaRecovery                                         $startMfaRecovery,
        private ConfirmMfaRecovery                                       $confirmMfaRecovery
    ) {}

    /**
     * Start the fluent configuration builder.
     */
    public static function configuration() : AuthBuilder
    {
        return new AuthBuilder();
    }

    /**
     * @throws AuthenticationFailed
     */
    public function login(#[SensitiveParameter] Credentials $credentials) : AuthenticationResult
    {
        return $this->login->execute(credentials: $credentials);
    }

    public function authenticateRequest(AuthenticationRequest $request) : AuthenticationContext
    {
        return $this->authenticateRequest->execute(request: $request);
    }

    public function current() : AuthenticationContext
    {
        return $this->currentAuthentication->read();
    }

    public function logout() : void
    {
        $this->logout->execute();
    }

    /**
     * @throws Unauthenticated
     */
    public function logoutAllSessions() : void
    {
        $this->logoutAllSessions->execute();
    }

    /**
     * @throws Unauthenticated
     */
    public function readActiveSessions() : array
    {
        return $this->readActiveSessions->execute();
    }

    /**
     * @throws Unauthenticated
     */
    public function revokeSession(#[SensitiveParameter] string $sessionId) : void
    {
        $this->revokeSession->execute(sessionId: $sessionId);
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

    /**
     * @throws Unauthenticated
     * @throws PasswordChangeFailed
     */
    public function changePassword(ChangePasswordData $data) : void
    {
        $this->changePassword->execute(data: $data);
    }

    public function beginEmailChange(BeginEmailChangeData $data) : EmailChangeChallenge
    {
        return $this->beginEmailChangeOrFail()->execute(data: $data);
    }

    public function confirmEmailChange(ConfirmEmailChangeData $data) : bool
    {
        return $this->confirmEmailChangeOrFail()->execute(data: $data);
    }

    /**
     * @throws RegistrationFailed
     */
    public function register(RegistrationData $data) : RegistrationResult
    {
        return $this->register->execute(data: $data);
    }

    /**
     * @throws RefreshAuthenticationFailed
     */
    public function refresh(RefreshAuthenticationRequest $request) : AuthenticationResult
    {
        return $this->refreshAuthentication->execute(request: $request);
    }

    public function registerOAuthClient(RegisterClientData $data) : RegisteredOAuthClient
    {
        return $this->registerOAuthClientOrFail()->execute(data: $data);
    }

    public function approveOAuthClientRegistration(ApproveClientRegistrationData $data) : OAuthClient
    {
        return $this->approveOAuthClientRegistrationOrFail()->execute(data: $data);
    }

    public function updateOAuthClient(UpdateClientData $data) : OAuthClient
    {
        return $this->updateOAuthClientOrFail()->execute(data: $data);
    }

    public function disableOAuthClient(string $clientId) : OAuthClient
    {
        return $this->disableOAuthClientOrFail()->execute(clientId: $clientId);
    }

    public function rotateOAuthClientSecret(string $clientId) : RegisteredOAuthClient
    {
        return $this->rotateOAuthClientSecretOrFail()->execute(clientId: $clientId);
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

    public function readOidcUserInfo(#[SensitiveParameter] string $accessToken) : OidcUserInfo
    {
        return $this->readOidcUserInfoOrFail()->execute(accessToken: $accessToken);
    }

    public function pushOidcAuthorizationRequest(PushAuthorizationRequestData $data) : PushedAuthorizationRequest
    {
        return $this->pushOidcAuthorizationRequestOrFail()->execute(data: $data);
    }

    public function oidcLogout(OidcLogoutData $data) : OidcLogoutResult
    {
        return $this->oidcLogoutOrFail()->execute(data: $data);
    }

    public function buildOidcJarmResponse(BuildJarmResponseData $data) : JarmResponse
    {
        return $this->buildOidcJarmResponseOrFail()->execute(data: $data);
    }

    public function registerScimDirectory(RegisterScimDirectoryData $data) : RegisteredScimDirectory
    {
        return $this->registerScimDirectoryOrFail()->execute(data: $data);
    }

    public function readScimDirectories(string|null $tenantSlug = null) : array
    {
        return $this->readScimDirectoriesOrFail()->execute(tenantSlug: $tenantSlug);
    }

    public function rotateScimToken(string $directoryId) : RotatedScimToken
    {
        return $this->rotateScimTokenOrFail()->execute(directoryId: $directoryId);
    }

    public function markScimDirectoryOutage(MarkScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->markScimDirectoryOutageOrFail()->execute(data: $data);
    }

    public function recoverScimDirectoryOutage(RecoverScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->recoverScimDirectoryOutageOrFail()->execute(data: $data);
    }

    public function provisionScimUser(ProvisionScimUserData $data) : ScimProvisioningResult
    {
        return $this->provisionScimUserOrFail()->execute(data: $data);
    }

    public function deleteScimUser(DeleteScimUserData $data) : void
    {
        $this->deleteScimUserOrFail()->execute(data: $data);
    }

    public function readScimUsers(string $directoryId) : array
    {
        return $this->readScimUsersOrFail()->execute(directoryId: $directoryId);
    }

    public function readScimGroups(string $directoryId) : array
    {
        return $this->readScimGroupsOrFail()->execute(directoryId: $directoryId);
    }

    public function syncScimGroups(SyncScimGroupsData $data) : ScimProvisioningResult
    {
        return $this->syncScimGroupsOrFail()->execute(data: $data);
    }

    public function runScimBulk(ScimBulkRequest $data) : ScimBulkResponse
    {
        return $this->runScimBulkOrFail()->execute(request: $data);
    }

    public function createTenant(CreateTenantData $data) : Tenant
    {
        return $this->createTenantOrFail()->execute(data: $data);
    }

    public function readTenants() : array
    {
        return $this->readTenantsOrFail()->execute();
    }

    public function inviteTenantMember(InviteTenantMemberData $data) : IssuedTenantInvite
    {
        return $this->inviteTenantMemberOrFail()->execute(data: $data);
    }

    public function acceptTenantInvite(AcceptTenantInviteData $data) : TenantMember
    {
        return $this->acceptTenantInviteOrFail()->execute(data: $data);
    }

    public function readTenantMembers(string $tenantSlug) : array
    {
        return $this->readTenantMembersOrFail()->execute(tenantSlug: $tenantSlug);
    }

    public function removeTenantMember(RemoveTenantMemberData $data) : void
    {
        $this->removeTenantMemberOrFail()->execute(data: $data);
    }

    public function suspendTenantMember(SuspendTenantMemberData $data) : TenantMember
    {
        return $this->suspendTenantMemberOrFail()->execute(data: $data);
    }

    public function transferTenantOwnership(TransferTenantOwnershipData $data) : Tenant
    {
        return $this->transferTenantOwnershipOrFail()->execute(data: $data);
    }

    public function readTenantSecurityConfiguration(string $tenantSlug) : TenantSecurityConfiguration|null
    {
        return $this->readTenantSecurityConfiguration->execute(tenantSlug: $tenantSlug);
    }

    public function readTenantSecurityChangeRequest(string $changeId) : TenantSecurityChangeRequest|null
    {
        return $this->readTenantSecurityChangeRequest->execute(changeId: $changeId);
    }

    public function readTenantSecurityChangeRequests(string $tenantSlug) : array
    {
        return $this->readTenantSecurityChangeRequests->execute(tenantSlug: $tenantSlug);
    }

    public function beginTenantSecurityChange(BeginTenantSecurityChangeData $data) : TenantSecurityChangeRequest
    {
        return $this->beginTenantSecurityChange->execute(data: $data);
    }

    public function approveTenantSecurityChange(string $changeId, string $approvedBy) : TenantSecurityChangeRequest
    {
        return $this->approveTenantSecurityChange->execute(changeId: $changeId, approvedBy: $approvedBy);
    }

    public function applyTenantSecurityChange(string $changeId) : TenantSecurityConfiguration
    {
        return $this->applyTenantSecurityChange->execute(changeId: $changeId);
    }

    public function rollbackTenantSecurityChange(string $changeId) : TenantSecurityConfiguration
    {
        return $this->rollbackTenantSecurityChange->execute(changeId: $changeId);
    }

    public function authorizeOAuthCode(AuthorizeCodeData $data) : IssuedAuthorizationCode
    {
        return $this->authorizeOAuthCodeOrFail()->execute(data: $data);
    }

    public function exchangeOAuthCode(ExchangeAuthorizationCodeData $data) : OAuthTokenGrant
    {
        return $this->exchangeOAuthCodeOrFail()->execute(data: $data);
    }

    public function exchangeOAuthClientCredentials(ExchangeClientCredentialsData $data) : OAuthTokenGrant
    {
        return $this->exchangeOAuthClientCredentialsOrFail()->execute(data: $data);
    }

    public function exchangeOAuthRefreshToken(ExchangeRefreshTokenData $data) : OAuthTokenGrant
    {
        return $this->exchangeOAuthRefreshTokenOrFail()->execute(data: $data);
    }

    public function revokeOAuthToken(RevokeTokenData $data) : void
    {
        $this->revokeOAuthTokenOrFail()->execute(data: $data);
    }

    public function introspectOAuthToken(IntrospectTokenData $data) : TokenIntrospection
    {
        return $this->introspectOAuthTokenOrFail()->execute(data: $data);
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
        $this->suspendUserOrFail()->execute(userId: $userId);
    }

    public function reactivateUser(int $userId) : void
    {
        $this->reactivateUserOrFail()->execute(userId: $userId);
    }

    public function deprovisionUser(int $userId) : void
    {
        $this->deprovisionUserOrFail()->execute(userId: $userId);
    }

    public function beginPasskeyRegistration() : PasskeyRegistration
    {
        return $this->beginPasskeyRegistrationOrFail()->execute();
    }

    public function completePasskeyRegistration(CompletePasskeyRegistrationData $data) : PasskeyCredential
    {
        return $this->completePasskeyRegistrationOrFail()->execute(data: $data);
    }

    public function beginPasskeyAuthentication(BeginPasskeyAuthenticationData $data) : PasskeyAuthenticationChallenge
    {
        return $this->beginPasskeyAuthenticationOrFail()->execute(data: $data);
    }

    public function completePasskeyAuthentication(CompletePasskeyAuthenticationData $data) : AuthenticationResult
    {
        return $this->completePasskeyAuthenticationOrFail()->execute(data: $data);
    }

    public function readPasskeys() : array
    {
        return $this->readPasskeysOrFail()->execute();
    }

    public function renamePasskey(RenamePasskeyData $data) : PasskeyCredential
    {
        return $this->renamePasskeyOrFail()->execute(data: $data);
    }

    public function revokePasskey(#[SensitiveParameter] string $credentialId) : void
    {
        $this->revokePasskeyOrFail()->execute(credentialId: $credentialId);
    }

    public function registerFederationConnection(RegisterFederationConnectionData $data) : FederationConnection
    {
        return $this->registerFederationConnectionOrFail()->execute(data: $data);
    }

    public function readFederationConnections() : array
    {
        return $this->readFederationConnectionsOrFail()->execute();
    }

    public function verifyFederationDomain(VerifyFederationDomainData $data) : FederationConnection
    {
        return $this->verifyFederationDomainOrFail()->execute(data: $data);
    }

    public function syncFederationMetadata(string $connectionId) : FederationConnection
    {
        return $this->syncFederationMetadataOrFail()->execute(connectionId: $connectionId);
    }

    public function checkFederationConnectionHealth(string $connectionId) : FederationConnectionHealth
    {
        return $this->checkFederationConnectionHealthOrFail()->execute(connectionId: $connectionId);
    }

    public function evaluateFederationBreakGlassBypass(string $connectionId) : bool
    {
        return $this->evaluateFederationBreakGlassBypassOrFail()->execute(connectionId: $connectionId);
    }

    public function discoverFederationConnection(#[SensitiveParameter] string $email) : FederationConnection|null
    {
        return $this->discoverFederationConnectionOrFail()->execute(email: $email);
    }

    public function startFederatedLogin(StartFederatedLoginData $data) : StartedFederatedLogin
    {
        return $this->startFederatedLoginOrFail()->execute(data: $data);
    }

    public function completeFederatedLogin(CompleteFederatedLoginData $data) : AuthenticationResult
    {
        return $this->completeFederatedLoginOrFail()->execute(data: $data);
    }

    public function assessCurrentRisk(#[SensitiveParameter] string|null $ipAddress = null, string|null $userAgent = null) : RiskDecision|null
    {
        return $this->assessCurrentRisk->execute(ipAddress: $ipAddress, userAgent: $userAgent);
    }

    public function readRiskSignals(int|null $userId = null) : array
    {
        return $this->readRiskSignals->execute(userId: $userId);
    }

    public function beginPasswordReset(BeginPasswordResetData $data) : PasswordResetChallenge
    {
        return $this->beginPasswordReset->execute(data: $data);
    }

    public function resetPassword(ResetPasswordData $data) : bool
    {
        return $this->resetPassword->execute(data: $data);
    }

    public function beginEmailVerification(BeginEmailVerificationData $data) : EmailVerificationChallenge
    {
        return $this->beginEmailVerification->execute(data: $data);
    }

    public function verifyEmail(VerifyEmailData $data) : bool
    {
        return $this->verifyEmail->execute(data: $data);
    }

    /**
     * @throws Unauthenticated
     */
    public function startMfaEnrollment() : MfaEnrollment
    {
        return $this->startMfaEnrollment->execute();
    }

    /**
     * @throws Unauthenticated
     */
    public function confirmMfaEnrollment(ConfirmMfaEnrollmentData $data) : BackupCodeSet
    {
        return $this->confirmMfaEnrollment->execute(data: $data);
    }

    /**
     * @throws Unauthenticated
     */
    public function cancelMfaEnrollment() : void
    {
        $this->cancelMfaEnrollment->execute();
    }

    /**
     * @throws Unauthenticated
     */
    public function beginMfaChallenge() : MfaChallenge
    {
        return $this->startMfaChallenge->execute();
    }

    public function verifyMfaChallenge(VerifyMfaChallengeData $data) : AuthenticationResult
    {
        return $this->verifyMfaChallenge->execute(data: $data);
    }

    /**
     * @throws Unauthenticated
     */
    public function regenerateBackupCodes() : BackupCodeSet
    {
        return $this->regenerateBackupCodes->execute();
    }

    /**
     * @throws Unauthenticated
     */
    public function disableMfa() : void
    {
        $this->disableMfa->execute();
    }

    public function beginMfaRecovery(BeginMfaRecoveryData $data) : MfaRecoveryChallenge
    {
        return $this->startMfaRecovery->execute(data: $data);
    }

    public function confirmMfaRecovery(ConfirmMfaRecoveryData $data) : void
    {
        $this->confirmMfaRecovery->execute(data: $data);
    }

    private function registerOAuthClientOrFail() : RegisterClient
    {
        return $this->registerOAuthClient ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    private function approveOAuthClientRegistrationOrFail() : ApproveClientRegistration
    {
        return $this->approveOAuthClientRegistration ?? throw new RuntimeException(message: 'OAuth client approval is not configured.');
    }

    private function updateOAuthClientOrFail() : UpdateClient
    {
        return $this->updateOAuthClient ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    private function disableOAuthClientOrFail() : DisableClient
    {
        return $this->disableOAuthClient ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    private function rotateOAuthClientSecretOrFail() : RotateClientSecret
    {
        return $this->rotateOAuthClientSecret ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    private function readOAuthClientsOrFail() : ReadClients
    {
        return $this->readOAuthClients ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    private function readWorkloadIdentitiesOrFail() : ReadWorkloadIdentities
    {
        return $this->readWorkloadIdentities ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    private function readOidcProviderMetadataOrFail() : ReadOidcProviderMetadata
    {
        return $this->readOidcProviderMetadata ?? throw new RuntimeException(message: 'OIDC provider is not configured.');
    }

    private function readOidcJsonWebKeySetOrFail() : ReadOidcJsonWebKeySet
    {
        return $this->readOidcJsonWebKeySet ?? throw new RuntimeException(message: 'OIDC provider is not configured.');
    }

    private function readOidcUserInfoOrFail() : ReadOidcUserInfo
    {
        return $this->readOidcUserInfo ?? throw new RuntimeException(message: 'OIDC provider is not configured.');
    }

    private function pushOidcAuthorizationRequestOrFail() : PushAuthorizationRequest
    {
        return $this->pushOidcAuthorizationRequest ?? throw new RuntimeException(message: 'OIDC PAR support is not configured.');
    }

    private function oidcLogoutOrFail() : OidcLogout
    {
        return $this->oidcLogout ?? throw new RuntimeException(message: 'OIDC logout support is not configured.');
    }

    private function buildOidcJarmResponseOrFail() : BuildJarmResponse
    {
        return $this->buildOidcJarmResponse ?? throw new RuntimeException(message: 'OIDC JARM support is not configured.');
    }

    private function registerScimDirectoryOrFail() : RegisterScimDirectory
    {
        return $this->registerScimDirectory ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function createTenantOrFail() : CreateTenant
    {
        return $this->createTenant ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    private function readTenantsOrFail() : ReadTenants
    {
        return $this->readTenants ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    private function inviteTenantMemberOrFail() : InviteTenantMember
    {
        return $this->inviteTenantMember ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    private function acceptTenantInviteOrFail() : AcceptTenantInvite
    {
        return $this->acceptTenantInvite ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    private function readTenantMembersOrFail() : ReadTenantMembers
    {
        return $this->readTenantMembers ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    private function removeTenantMemberOrFail() : RemoveTenantMember
    {
        return $this->removeTenantMember ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    private function suspendTenantMemberOrFail() : SuspendTenantMember
    {
        return $this->suspendTenantMember ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    private function transferTenantOwnershipOrFail() : TransferTenantOwnership
    {
        return $this->transferTenantOwnership ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    private function rotateScimTokenOrFail() : RotateScimToken
    {
        return $this->rotateScimToken ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function markScimDirectoryOutageOrFail() : MarkScimDirectoryOutage
    {
        return $this->markScimDirectoryOutage ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function recoverScimDirectoryOutageOrFail() : RecoverScimDirectoryOutage
    {
        return $this->recoverScimDirectoryOutage ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function readScimDirectoriesOrFail() : ReadScimDirectories
    {
        return $this->readScimDirectories ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function provisionScimUserOrFail() : ProvisionScimUser
    {
        return $this->provisionScimUser ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function deleteScimUserOrFail() : DeleteScimUser
    {
        return $this->deleteScimUser ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function readScimUsersOrFail() : ReadScimUsers
    {
        return $this->readScimUsers ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function readScimGroupsOrFail() : ReadScimGroups
    {
        return $this->readScimGroups ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function syncScimGroupsOrFail() : SyncScimGroups
    {
        return $this->syncScimGroups ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function runScimBulkOrFail() : RunScimBulk
    {
        return $this->runScimBulk ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    private function authorizeOAuthCodeOrFail() : AuthorizeCode
    {
        return $this->authorizeOAuthCode ?? throw new RuntimeException(message: 'OAuth authorization code flow is not configured.');
    }

    private function exchangeOAuthCodeOrFail() : ExchangeAuthorizationCode
    {
        return $this->exchangeOAuthCode ?? throw new RuntimeException(message: 'OAuth token exchange is not configured.');
    }

    private function exchangeOAuthClientCredentialsOrFail() : ExchangeClientCredentials
    {
        return $this->exchangeOAuthClientCredentials ?? throw new RuntimeException(message: 'OAuth client credentials flow is not configured.');
    }

    private function exchangeOAuthRefreshTokenOrFail() : ExchangeRefreshToken
    {
        return $this->exchangeOAuthRefreshToken ?? throw new RuntimeException(message: 'OAuth refresh flow is not configured.');
    }

    private function revokeOAuthTokenOrFail() : RevokeToken
    {
        return $this->revokeOAuthToken ?? throw new RuntimeException(message: 'OAuth revoke flow is not configured.');
    }

    private function introspectOAuthTokenOrFail() : IntrospectToken
    {
        return $this->introspectOAuthToken ?? throw new RuntimeException(message: 'OAuth introspection is not configured.');
    }

    private function suspendUserOrFail() : SuspendUser
    {
        return $this->suspendUser ?? throw new RuntimeException(message: 'Provisioning lifecycle is not configured.');
    }

    private function beginEmailChangeOrFail() : BeginEmailChange
    {
        return $this->beginEmailChange ?? throw new RuntimeException(message: 'Email change flow is not configured.');
    }

    private function confirmEmailChangeOrFail() : ConfirmEmailChange
    {
        return $this->confirmEmailChange ?? throw new RuntimeException(message: 'Email change flow is not configured.');
    }

    private function reactivateUserOrFail() : ReactivateUser
    {
        return $this->reactivateUser ?? throw new RuntimeException(message: 'Provisioning lifecycle is not configured.');
    }

    private function deprovisionUserOrFail() : DeprovisionUser
    {
        return $this->deprovisionUser ?? throw new RuntimeException(message: 'Provisioning lifecycle is not configured.');
    }

    private function beginPasskeyRegistrationOrFail() : BeginPasskeyRegistration
    {
        return $this->beginPasskeyRegistration ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    private function completePasskeyRegistrationOrFail() : CompletePasskeyRegistration
    {
        return $this->completePasskeyRegistration ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    private function beginPasskeyAuthenticationOrFail() : BeginPasskeyAuthentication
    {
        return $this->beginPasskeyAuthentication ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    private function completePasskeyAuthenticationOrFail() : CompletePasskeyAuthentication
    {
        return $this->completePasskeyAuthentication ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    private function readPasskeysOrFail() : ListPasskeys
    {
        return $this->readPasskeys ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    private function renamePasskeyOrFail() : RenamePasskey
    {
        return $this->renamePasskey ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    private function revokePasskeyOrFail() : RevokePasskeyFlow
    {
        return $this->revokePasskey ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    private function registerFederationConnectionOrFail() : RegisterFederationConnection
    {
        return $this->registerFederationConnection ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    private function readFederationConnectionsOrFail() : ReadFederationConnections
    {
        return $this->readFederationConnections ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    private function verifyFederationDomainOrFail() : VerifyFederationDomain
    {
        return $this->verifyFederationDomain ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    private function syncFederationMetadataOrFail() : SyncFederationMetadata
    {
        return $this->syncFederationMetadata ?? throw new RuntimeException(message: 'Federation metadata runtime is not configured.');
    }

    private function checkFederationConnectionHealthOrFail() : CheckFederationConnectionHealth
    {
        return $this->checkFederationConnectionHealth ?? throw new RuntimeException(message: 'Federation health checks are not configured.');
    }

    private function evaluateFederationBreakGlassBypassOrFail() : EvaluateFederationBreakGlassBypass
    {
        return $this->evaluateFederationBreakGlassBypass ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    private function discoverFederationConnectionOrFail() : DiscoverFederationConnection
    {
        return $this->discoverFederationConnection ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    private function startFederatedLoginOrFail() : StartFederatedLogin
    {
        return $this->startFederatedLogin ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    private function completeFederatedLoginOrFail() : CompleteFederatedLogin
    {
        return $this->completeFederatedLogin ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }
}
