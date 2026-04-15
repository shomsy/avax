<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Capability\Access\AccessInterface;
use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Explainability\AuthIssueExplainer;
use Avax\Auth\System\Capability\Explainability\AuthIssueExplanation;
use Avax\Auth\System\Capability\Federation\FederationConnection;
use Avax\Auth\System\Capability\Federation\FederationConnectionHealth;
use Avax\Auth\System\Capability\Federation\StartedFederatedLogin;
use Avax\Auth\System\Capability\OAuth\IssuedAuthorizationCode;
use Avax\Auth\System\Capability\OAuth\OAuthClient;
use Avax\Auth\System\Capability\OAuth\RegisteredOAuthClient;
use Avax\Auth\System\Capability\Oidc\OidcJsonWebKeySet;
use Avax\Auth\System\Capability\Oidc\OidcProviderMetadata;
use Avax\Auth\System\Capability\Passkey\PasskeyCredential;
use Avax\Auth\System\Capability\Risk\RiskDecision;
use Avax\Auth\System\Capability\Scim\RegisteredScimDirectory;
use Avax\Auth\System\Capability\Scim\ScimDirectory;
use Avax\Auth\System\Capability\Tenant\Tenant;
use Avax\Auth\System\Capability\Tenant\TenantMember;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfiguration;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Flow\AdminRealm\AdminElevation;
use Avax\Auth\System\Flow\AdminRealm\BeginAdminElevation\BeginAdminElevation;
use Avax\Auth\System\Flow\AdminRealm\EndAdminElevation\EndAdminElevation;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticateRequest;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\ChangeEmail\BeginEmailChange;
use Avax\Auth\System\Flow\ChangeEmail\BeginEmailChangeData;
use Avax\Auth\System\Flow\ChangeEmail\ConfirmEmailChange;
use Avax\Auth\System\Flow\ChangeEmail\ConfirmEmailChangeData;
use Avax\Auth\System\Flow\ChangeEmail\EmailChangeChallenge;
use Avax\Auth\System\Flow\ChangePassword\ChangePassword;
use Avax\Auth\System\Flow\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flow\ChangePassword\PasswordChangeFailed;
use Avax\Auth\System\Flow\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flow\Federation\CheckHealth\CheckFederationConnectionHealth;
use Avax\Auth\System\Flow\Federation\CompleteFederatedLogin\CompleteFederatedLogin;
use Avax\Auth\System\Flow\Federation\CompleteFederatedLogin\CompleteFederatedLoginData;
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
use Avax\Auth\System\Flow\OAuth\ApproveClientRegistration\ApproveClientRegistration;
use Avax\Auth\System\Flow\OAuth\ApproveClientRegistration\ApproveClientRegistrationData;
use Avax\Auth\System\Flow\OAuth\AuthorizeCode\AuthorizeCode;
use Avax\Auth\System\Flow\OAuth\AuthorizeCode\AuthorizeCodeData;
use Avax\Auth\System\Flow\OAuth\DisableClient\DisableClient;
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
use Avax\Auth\System\Flow\OAuth\RotateClientSecret\RotateClientSecret;
use Avax\Auth\System\Flow\OAuth\UpdateClient\UpdateClient;
use Avax\Auth\System\Flow\OAuth\UpdateClient\UpdateClientData;
use Avax\Auth\System\Flow\Oidc\JarmResponse\BuildJarmResponse;
use Avax\Auth\System\Flow\Oidc\JarmResponse\BuildJarmResponseData;
use Avax\Auth\System\Flow\Oidc\JarmResponse\JarmResponse;
use Avax\Auth\System\Flow\Oidc\Logout\Logout as OidcLogout;
use Avax\Auth\System\Flow\Oidc\Logout\LogoutData as OidcLogoutData;
use Avax\Auth\System\Flow\Oidc\Logout\LogoutResult as OidcLogoutResult;
use Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest\PushAuthorizationRequest;
use Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest\PushAuthorizationRequestData;
use Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest\PushedAuthorizationRequest;
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
use Avax\Auth\System\Flow\Scim\ReadUsers\ReadScimUsers;
use Avax\Auth\System\Flow\Scim\RecoverOutage\RecoverScimDirectoryOutage;
use Avax\Auth\System\Flow\Scim\RecoverOutage\RecoverScimDirectoryOutageData;
use Avax\Auth\System\Flow\Scim\RegisterDirectory\RegisterScimDirectory;
use Avax\Auth\System\Flow\Scim\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Auth\System\Flow\Scim\RotateToken\RotatedScimToken;
use Avax\Auth\System\Flow\Scim\RotateToken\RotateScimToken;
use Avax\Auth\System\Flow\Scim\SyncGroups\SyncScimGroups;
use Avax\Auth\System\Flow\Scim\SyncGroups\SyncScimGroupsData;
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
    private ConfirmMfaRecovery                      $confirmMfaRecovery;
    private StartMfaRecovery                        $startMfaRecovery;
    private DisableMfa                              $disableMfa;
    private RegenerateBackupCodes                   $regenerateBackupCodes;
    private VerifyMfaChallenge                      $verifyMfaChallenge;
    private StartMfaChallenge                       $startMfaChallenge;
    private CancelMfaEnrollment                     $cancelMfaEnrollment;
    private ConfirmMfaEnrollment                    $confirmMfaEnrollment;
    private StartMfaEnrollment                      $startMfaEnrollment;
    private VerifyEmail                             $verifyEmail;
    private BeginEmailVerification                  $beginEmailVerification;
    private ResetPassword                           $resetPassword;
    private BeginPasswordReset                      $beginPasswordReset;
    private ReadRiskSignals                         $readRiskSignals;
    private AssessCurrentRisk                       $assessCurrentRisk;
    private RollbackTenantSecurityChange            $rollbackTenantSecurityChange;
    private ApplyTenantSecurityChange               $applyTenantSecurityChange;
    private ApproveTenantSecurityChange             $approveTenantSecurityChange;
    private BeginTenantSecurityChange               $beginTenantSecurityChange;
    private ReadTenantSecurityChangeRequests        $readTenantSecurityChangeRequests;
    private ReadTenantSecurityChangeRequest         $readTenantSecurityChangeRequest;
    private ReadTenantSecurityConfiguration         $readTenantSecurityConfiguration;
    private TransferTenantOwnership|null            $transferTenantOwnership;
    private SuspendTenantMember|null                $suspendTenantMember;
    private RemoveTenantMember|null                 $removeTenantMember;
    private ReadTenantMembers|null                  $readTenantMembers;
    private AcceptTenantInvite|null                 $acceptTenantInvite;
    private InviteTenantMember|null                 $inviteTenantMember;
    private ReadTenants|null                        $readTenants;
    private CreateTenant|null                       $createTenant;
    private RunScimBulk|null                        $runScimBulk;
    private SyncScimGroups|null                     $syncScimGroups;
    private ReadScimGroups|null                     $readScimGroups;
    private ReadScimUsers|null                      $readScimUsers;
    private DeleteScimUser|null                     $deleteScimUser;
    private ProvisionScimUser|null                  $provisionScimUser;
    private RecoverScimDirectoryOutage|null         $recoverScimDirectoryOutage;
    private MarkScimDirectoryOutage|null            $markScimDirectoryOutage;
    private RotateScimToken|null                    $rotateScimToken;
    private ReadScimDirectories|null                $readScimDirectories;
    private RegisterScimDirectory|null              $registerScimDirectory;
    private CompleteFederatedLogin|null             $completeFederatedLogin;
    private StartFederatedLogin|null                $startFederatedLogin;
    private DiscoverFederationConnection|null       $discoverFederationConnection;
    private EvaluateFederationBreakGlassBypass|null $evaluateFederationBreakGlassBypass;
    private CheckFederationConnectionHealth|null    $checkFederationConnectionHealth;
    private SyncFederationMetadata|null             $syncFederationMetadata;
    private VerifyFederationDomain|null             $verifyFederationDomain;
    private ReadFederationConnections|null          $readFederationConnections;
    private RegisterFederationConnection|null       $registerFederationConnection;
    private RevokePasskeyFlow|null                  $revokePasskey;
    private RenamePasskey|null                      $renamePasskey;
    private ListPasskeys|null                       $readPasskeys;
    private CompletePasskeyAuthentication|null      $completePasskeyAuthentication;
    private BeginPasskeyAuthentication|null         $beginPasskeyAuthentication;
    private CompletePasskeyRegistration|null        $completePasskeyRegistration;
    private BeginPasskeyRegistration|null           $beginPasskeyRegistration;
    private DeprovisionUser|null                    $deprovisionUser;
    private ReactivateUser|null                     $reactivateUser;
    private SuspendUser|null                        $suspendUser;
    private RequireAdminElevation                   $requireAdminElevation;
    private EndAdminElevation                       $endAdminElevation;
    private BeginAdminElevation                     $beginAdminElevation;
    private IntrospectToken|null                    $introspectOAuthToken;
    private RevokeToken|null                        $revokeOAuthToken;
    private ExchangeRefreshToken|null               $exchangeOAuthRefreshToken;
    private ExchangeClientCredentials|null          $exchangeOAuthClientCredentials;
    private ExchangeAuthorizationCode|null          $exchangeOAuthCode;
    private AuthorizeCode|null                      $authorizeOAuthCode;
    private BuildJarmResponse|null                  $buildOidcJarmResponse;
    private OidcLogout|null                         $oidcLogout;
    private PushAuthorizationRequest|null           $pushOidcAuthorizationRequest;
    private ReadOidcUserInfo|null                   $readOidcUserInfo;
    private ReadOidcJsonWebKeySet|null              $readOidcJsonWebKeySet;
    private ReadOidcProviderMetadata|null           $readOidcProviderMetadata;
    private ReadWorkloadIdentities|null             $readWorkloadIdentities;
    private ReadClients|null                        $readOAuthClients;
    private RotateClientSecret|null                 $rotateOAuthClientSecret;
    private DisableClient|null                      $disableOAuthClient;
    private UpdateClient|null                       $updateOAuthClient;
    private ApproveClientRegistration|null          $approveOAuthClientRegistration;
    private RegisterClient|null                     $registerOAuthClient;
    private RefreshAuthentication                   $refreshAuthentication;
    private Register                                $register;
    private ConfirmEmailChange|null                 $confirmEmailChange;
    private BeginEmailChange|null                   $beginEmailChange;
    private ChangePassword                          $changePassword;
    private AuthIssueExplainer                      $authIssueExplainer;
    private AccessInterface                         $access;
    private CurrentAuthentication                   $currentAuthentication;
    private RevokeSession                           $revokeSession;
    private ReadActiveSessions                      $readActiveSessions;
    private ReadCurrentUser                         $readCurrentUser;
    private CheckAuthentication                     $checkAuthentication;
    private LogoutAllSessions                       $logoutAllSessions;
    private Logout                                  $logout;
    private AuthenticateRequest                     $authenticateRequest;
    private Login                                   $login;

    public function __construct(
        Login                                                    $login,
        AuthenticateRequest                                      $authenticateRequest,
        Logout                                                   $logout,
        #[SensitiveParameter] LogoutAllSessions                  $logoutAllSessions,
        #[SensitiveParameter] CheckAuthentication                $checkAuthentication,
        ReadCurrentUser                                          $readCurrentUser,
        #[SensitiveParameter] ReadActiveSessions                 $readActiveSessions,
        #[SensitiveParameter] RevokeSession                      $revokeSession,
        #[SensitiveParameter] CurrentAuthentication              $currentAuthentication,
        #[SensitiveParameter] AccessInterface                    $access,
        #[\SensitiveParameter] AuthIssueExplainer                $authIssueExplainer,
        #[SensitiveParameter] ChangePassword                     $changePassword,
        #[SensitiveParameter] BeginEmailChange|null              $beginEmailChange,
        #[SensitiveParameter] ConfirmEmailChange|null            $confirmEmailChange,
        Register                                                 $register,
        #[SensitiveParameter] RefreshAuthentication              $refreshAuthentication,
        RegisterClient|null                                      $registerOAuthClient,
        ApproveClientRegistration|null                           $approveOAuthClientRegistration,
        UpdateClient|null                                        $updateOAuthClient,
        DisableClient|null                                       $disableOAuthClient,
        #[\SensitiveParameter] RotateClientSecret|null           $rotateOAuthClientSecret,
        ReadClients|null                                         $readOAuthClients,
        ReadWorkloadIdentities|null                              $readWorkloadIdentities,
        ReadOidcProviderMetadata|null                            $readOidcProviderMetadata,
        ReadOidcJsonWebKeySet|null                               $readOidcJsonWebKeySet,
        ReadOidcUserInfo|null                                    $readOidcUserInfo,
        PushAuthorizationRequest|null                            $pushOidcAuthorizationRequest,
        OidcLogout|null                                          $oidcLogout,
        BuildJarmResponse|null                                   $buildOidcJarmResponse,
        #[SensitiveParameter] AuthorizeCode|null                 $authorizeOAuthCode,
        #[SensitiveParameter] ExchangeAuthorizationCode|null     $exchangeOAuthCode,
        #[SensitiveParameter] ExchangeClientCredentials|null     $exchangeOAuthClientCredentials,
        #[SensitiveParameter] ExchangeRefreshToken|null          $exchangeOAuthRefreshToken,
        #[SensitiveParameter] RevokeToken|null                   $revokeOAuthToken,
        #[SensitiveParameter] IntrospectToken|null               $introspectOAuthToken,
        BeginAdminElevation                                      $beginAdminElevation,
        EndAdminElevation                                        $endAdminElevation,
        RequireAdminElevation                                    $requireAdminElevation,
        SuspendUser|null                                         $suspendUser,
        ReactivateUser|null                                      $reactivateUser,
        DeprovisionUser|null                                     $deprovisionUser,
        BeginPasskeyRegistration|null                            $beginPasskeyRegistration,
        CompletePasskeyRegistration|null                         $completePasskeyRegistration,
        #[SensitiveParameter] BeginPasskeyAuthentication|null    $beginPasskeyAuthentication,
        #[SensitiveParameter] CompletePasskeyAuthentication|null $completePasskeyAuthentication,
        ListPasskeys|null                                        $readPasskeys,
        RenamePasskey|null                                       $renamePasskey,
        RevokePasskeyFlow|null                                   $revokePasskey,
        RegisterFederationConnection|null                        $registerFederationConnection,
        ReadFederationConnections|null                           $readFederationConnections,
        VerifyFederationDomain|null                              $verifyFederationDomain,
        SyncFederationMetadata|null                              $syncFederationMetadata,
        CheckFederationConnectionHealth|null                     $checkFederationConnectionHealth,
        EvaluateFederationBreakGlassBypass|null                  $evaluateFederationBreakGlassBypass,
        DiscoverFederationConnection|null                        $discoverFederationConnection,
        StartFederatedLogin|null                                 $startFederatedLogin,
        CompleteFederatedLogin|null                              $completeFederatedLogin,
        RegisterScimDirectory|null                               $registerScimDirectory,
        ReadScimDirectories|null                                 $readScimDirectories,
        #[SensitiveParameter] RotateScimToken|null               $rotateScimToken,
        MarkScimDirectoryOutage|null                             $markScimDirectoryOutage,
        RecoverScimDirectoryOutage|null                          $recoverScimDirectoryOutage,
        ProvisionScimUser|null                                   $provisionScimUser,
        DeleteScimUser|null                                      $deleteScimUser,
        ReadScimUsers|null                                       $readScimUsers,
        ReadScimGroups|null                                      $readScimGroups,
        SyncScimGroups|null                                      $syncScimGroups,
        RunScimBulk|null                                         $runScimBulk,
        CreateTenant|null                                        $createTenant,
        ReadTenants|null                                         $readTenants,
        InviteTenantMember|null                                  $inviteTenantMember,
        AcceptTenantInvite|null                                  $acceptTenantInvite,
        ReadTenantMembers|null                                   $readTenantMembers,
        RemoveTenantMember|null                                  $removeTenantMember,
        SuspendTenantMember|null                                 $suspendTenantMember,
        TransferTenantOwnership|null                             $transferTenantOwnership,
        #[SensitiveParameter] ReadTenantSecurityConfiguration    $readTenantSecurityConfiguration,
        #[SensitiveParameter] ReadTenantSecurityChangeRequest    $readTenantSecurityChangeRequest,
        #[SensitiveParameter] ReadTenantSecurityChangeRequests   $readTenantSecurityChangeRequests,
        #[SensitiveParameter] BeginTenantSecurityChange          $beginTenantSecurityChange,
        #[SensitiveParameter] ApproveTenantSecurityChange        $approveTenantSecurityChange,
        #[SensitiveParameter] ApplyTenantSecurityChange          $applyTenantSecurityChange,
        #[SensitiveParameter] RollbackTenantSecurityChange       $rollbackTenantSecurityChange,
        AssessCurrentRisk                                        $assessCurrentRisk,
        ReadRiskSignals                                          $readRiskSignals,
        #[SensitiveParameter] BeginPasswordReset                 $beginPasswordReset,
        #[SensitiveParameter] ResetPassword                      $resetPassword,
        #[SensitiveParameter] BeginEmailVerification             $beginEmailVerification,
        #[SensitiveParameter] VerifyEmail                        $verifyEmail,
        StartMfaEnrollment                                       $startMfaEnrollment,
        ConfirmMfaEnrollment                                     $confirmMfaEnrollment,
        CancelMfaEnrollment                                      $cancelMfaEnrollment,
        StartMfaChallenge                                        $startMfaChallenge,
        VerifyMfaChallenge                                       $verifyMfaChallenge,
        #[SensitiveParameter] RegenerateBackupCodes              $regenerateBackupCodes,
        DisableMfa                                               $disableMfa,
        StartMfaRecovery                                         $startMfaRecovery,
        ConfirmMfaRecovery                                       $confirmMfaRecovery
    )
    {
        $this->login                              = $login;
        $this->authenticateRequest                = $authenticateRequest;
        $this->logout                             = $logout;
        $this->logoutAllSessions                  = $logoutAllSessions;
        $this->checkAuthentication                = $checkAuthentication;
        $this->readCurrentUser                    = $readCurrentUser;
        $this->readActiveSessions                 = $readActiveSessions;
        $this->revokeSession                      = $revokeSession;
        $this->currentAuthentication              = $currentAuthentication;
        $this->access                             = $access;
        $this->authIssueExplainer                 = $authIssueExplainer;
        $this->changePassword                     = $changePassword;
        $this->beginEmailChange                   = $beginEmailChange;
        $this->confirmEmailChange                 = $confirmEmailChange;
        $this->register                           = $register;
        $this->refreshAuthentication              = $refreshAuthentication;
        $this->registerOAuthClient                = $registerOAuthClient;
        $this->approveOAuthClientRegistration     = $approveOAuthClientRegistration;
        $this->updateOAuthClient                  = $updateOAuthClient;
        $this->disableOAuthClient                 = $disableOAuthClient;
        $this->rotateOAuthClientSecret            = $rotateOAuthClientSecret;
        $this->readOAuthClients                   = $readOAuthClients;
        $this->readWorkloadIdentities             = $readWorkloadIdentities;
        $this->readOidcProviderMetadata           = $readOidcProviderMetadata;
        $this->readOidcJsonWebKeySet              = $readOidcJsonWebKeySet;
        $this->readOidcUserInfo                   = $readOidcUserInfo;
        $this->pushOidcAuthorizationRequest       = $pushOidcAuthorizationRequest;
        $this->oidcLogout                         = $oidcLogout;
        $this->buildOidcJarmResponse              = $buildOidcJarmResponse;
        $this->authorizeOAuthCode                 = $authorizeOAuthCode;
        $this->exchangeOAuthCode                  = $exchangeOAuthCode;
        $this->exchangeOAuthClientCredentials     = $exchangeOAuthClientCredentials;
        $this->exchangeOAuthRefreshToken          = $exchangeOAuthRefreshToken;
        $this->revokeOAuthToken                   = $revokeOAuthToken;
        $this->introspectOAuthToken               = $introspectOAuthToken;
        $this->beginAdminElevation                = $beginAdminElevation;
        $this->endAdminElevation                  = $endAdminElevation;
        $this->requireAdminElevation              = $requireAdminElevation;
        $this->suspendUser                        = $suspendUser;
        $this->reactivateUser                     = $reactivateUser;
        $this->deprovisionUser                    = $deprovisionUser;
        $this->beginPasskeyRegistration           = $beginPasskeyRegistration;
        $this->completePasskeyRegistration        = $completePasskeyRegistration;
        $this->beginPasskeyAuthentication         = $beginPasskeyAuthentication;
        $this->completePasskeyAuthentication      = $completePasskeyAuthentication;
        $this->readPasskeys                       = $readPasskeys;
        $this->renamePasskey                      = $renamePasskey;
        $this->revokePasskey                      = $revokePasskey;
        $this->registerFederationConnection       = $registerFederationConnection;
        $this->readFederationConnections          = $readFederationConnections;
        $this->verifyFederationDomain             = $verifyFederationDomain;
        $this->syncFederationMetadata             = $syncFederationMetadata;
        $this->checkFederationConnectionHealth    = $checkFederationConnectionHealth;
        $this->evaluateFederationBreakGlassBypass = $evaluateFederationBreakGlassBypass;
        $this->discoverFederationConnection       = $discoverFederationConnection;
        $this->startFederatedLogin                = $startFederatedLogin;
        $this->completeFederatedLogin             = $completeFederatedLogin;
        $this->registerScimDirectory              = $registerScimDirectory;
        $this->readScimDirectories                = $readScimDirectories;
        $this->rotateScimToken                    = $rotateScimToken;
        $this->markScimDirectoryOutage            = $markScimDirectoryOutage;
        $this->recoverScimDirectoryOutage         = $recoverScimDirectoryOutage;
        $this->provisionScimUser                  = $provisionScimUser;
        $this->deleteScimUser                     = $deleteScimUser;
        $this->readScimUsers                      = $readScimUsers;
        $this->readScimGroups                     = $readScimGroups;
        $this->syncScimGroups                     = $syncScimGroups;
        $this->runScimBulk                        = $runScimBulk;
        $this->createTenant                       = $createTenant;
        $this->readTenants                        = $readTenants;
        $this->inviteTenantMember                 = $inviteTenantMember;
        $this->acceptTenantInvite                 = $acceptTenantInvite;
        $this->readTenantMembers                  = $readTenantMembers;
        $this->removeTenantMember                 = $removeTenantMember;
        $this->suspendTenantMember                = $suspendTenantMember;
        $this->transferTenantOwnership            = $transferTenantOwnership;
        $this->readTenantSecurityConfiguration    = $readTenantSecurityConfiguration;
        $this->readTenantSecurityChangeRequest    = $readTenantSecurityChangeRequest;
        $this->readTenantSecurityChangeRequests   = $readTenantSecurityChangeRequests;
        $this->beginTenantSecurityChange          = $beginTenantSecurityChange;
        $this->approveTenantSecurityChange        = $approveTenantSecurityChange;
        $this->applyTenantSecurityChange          = $applyTenantSecurityChange;
        $this->rollbackTenantSecurityChange       = $rollbackTenantSecurityChange;
        $this->assessCurrentRisk                  = $assessCurrentRisk;
        $this->readRiskSignals                    = $readRiskSignals;
        $this->beginPasswordReset                 = $beginPasswordReset;
        $this->resetPassword                      = $resetPassword;
        $this->beginEmailVerification             = $beginEmailVerification;
        $this->verifyEmail                        = $verifyEmail;
        $this->startMfaEnrollment                 = $startMfaEnrollment;
        $this->confirmMfaEnrollment               = $confirmMfaEnrollment;
        $this->cancelMfaEnrollment                = $cancelMfaEnrollment;
        $this->startMfaChallenge                  = $startMfaChallenge;
        $this->verifyMfaChallenge                 = $verifyMfaChallenge;
        $this->regenerateBackupCodes              = $regenerateBackupCodes;
        $this->disableMfa                         = $disableMfa;
        $this->startMfaRecovery                   = $startMfaRecovery;
        $this->confirmMfaRecovery                 = $confirmMfaRecovery;
    }

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

    public function explainAccessDenied(
        string      $resource,
        string|null $requiredPermission = null,
        string|null $tenant = null,
        string|null $resourceTenant = null
    ) : AuthIssueExplanation
    {
        return $this->authIssueExplainer->explainAccessDenied(
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
        return $this->authIssueExplainer->explainStepUpRequired(
            action                   : $action,
            phishingResistantRequired: $phishingResistantRequired,
            freshAfterSeconds        : $freshAfterSeconds
        );
    }

    public function explainSenderConstraintFailure(string $reason, string|null $requiredConstraint = null) : AuthIssueExplanation
    {
        return $this->authIssueExplainer->explainSenderConstraintFailure(
            reason            : $reason,
            requiredConstraint: $requiredConstraint
        );
    }

    public function explainSessionRevocation(string $status, #[\SensitiveParameter] string|null $sessionId = null) : AuthIssueExplanation
    {
        return $this->authIssueExplainer->explainSessionRevocation(status: $status, sessionId: $sessionId);
    }

    public function explainTrustedDeviceDecision(string|null $deviceId = null) : AuthIssueExplanation
    {
        return $this->authIssueExplainer->explainTrustedDeviceDecision(deviceId: $deviceId);
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

    private function beginEmailChangeOrFail() : BeginEmailChange
    {
        return $this->beginEmailChange ?? throw new RuntimeException(message: 'Email change flow is not configured.');
    }

    public function confirmEmailChange(ConfirmEmailChangeData $data) : bool
    {
        return $this->confirmEmailChangeOrFail()->execute(data: $data);
    }

    private function confirmEmailChangeOrFail() : ConfirmEmailChange
    {
        return $this->confirmEmailChange ?? throw new RuntimeException(message: 'Email change flow is not configured.');
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

    private function registerOAuthClientOrFail() : RegisterClient
    {
        return $this->registerOAuthClient ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    public function approveOAuthClientRegistration(ApproveClientRegistrationData $data) : OAuthClient
    {
        return $this->approveOAuthClientRegistrationOrFail()->execute(data: $data);
    }

    private function approveOAuthClientRegistrationOrFail() : ApproveClientRegistration
    {
        return $this->approveOAuthClientRegistration ?? throw new RuntimeException(message: 'OAuth client approval is not configured.');
    }

    public function updateOAuthClient(UpdateClientData $data) : OAuthClient
    {
        return $this->updateOAuthClientOrFail()->execute(data: $data);
    }

    private function updateOAuthClientOrFail() : UpdateClient
    {
        return $this->updateOAuthClient ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    public function disableOAuthClient(string $clientId) : OAuthClient
    {
        return $this->disableOAuthClientOrFail()->execute(clientId: $clientId);
    }

    private function disableOAuthClientOrFail() : DisableClient
    {
        return $this->disableOAuthClient ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    public function rotateOAuthClientSecret(string $clientId) : RegisteredOAuthClient
    {
        return $this->rotateOAuthClientSecretOrFail()->execute(clientId: $clientId);
    }

    private function rotateOAuthClientSecretOrFail() : RotateClientSecret
    {
        return $this->rotateOAuthClientSecret ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    public function readOAuthClients() : array
    {
        return $this->readOAuthClientsOrFail()->execute();
    }

    private function readOAuthClientsOrFail() : ReadClients
    {
        return $this->readOAuthClients ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    /**
     * @return list<WorkloadIdentityProfile>
     */
    public function readWorkloadIdentities() : array
    {
        return $this->readWorkloadIdentitiesOrFail()->execute();
    }

    private function readWorkloadIdentitiesOrFail() : ReadWorkloadIdentities
    {
        return $this->readWorkloadIdentities ?? throw new RuntimeException(message: 'OAuth client registry is not configured.');
    }

    public function readOidcProviderMetadata() : OidcProviderMetadata
    {
        return $this->readOidcProviderMetadataOrFail()->execute();
    }

    private function readOidcProviderMetadataOrFail() : ReadOidcProviderMetadata
    {
        return $this->readOidcProviderMetadata ?? throw new RuntimeException(message: 'OIDC provider is not configured.');
    }

    public function readOidcJsonWebKeySet() : OidcJsonWebKeySet
    {
        return $this->readOidcJsonWebKeySetOrFail()->execute();
    }

    private function readOidcJsonWebKeySetOrFail() : ReadOidcJsonWebKeySet
    {
        return $this->readOidcJsonWebKeySet ?? throw new RuntimeException(message: 'OIDC provider is not configured.');
    }

    public function readOidcUserInfo(#[SensitiveParameter] string $accessToken) : OidcUserInfo
    {
        return $this->readOidcUserInfoOrFail()->execute(accessToken: $accessToken);
    }

    private function readOidcUserInfoOrFail() : ReadOidcUserInfo
    {
        return $this->readOidcUserInfo ?? throw new RuntimeException(message: 'OIDC provider is not configured.');
    }

    public function pushOidcAuthorizationRequest(PushAuthorizationRequestData $data) : PushedAuthorizationRequest
    {
        return $this->pushOidcAuthorizationRequestOrFail()->execute(data: $data);
    }

    private function pushOidcAuthorizationRequestOrFail() : PushAuthorizationRequest
    {
        return $this->pushOidcAuthorizationRequest ?? throw new RuntimeException(message: 'OIDC PAR support is not configured.');
    }

    public function oidcLogout(OidcLogoutData $data) : OidcLogoutResult
    {
        return $this->oidcLogoutOrFail()->execute(data: $data);
    }

    private function oidcLogoutOrFail() : OidcLogout
    {
        return $this->oidcLogout ?? throw new RuntimeException(message: 'OIDC logout support is not configured.');
    }

    public function buildOidcJarmResponse(BuildJarmResponseData $data) : JarmResponse
    {
        return $this->buildOidcJarmResponseOrFail()->execute(data: $data);
    }

    private function buildOidcJarmResponseOrFail() : BuildJarmResponse
    {
        return $this->buildOidcJarmResponse ?? throw new RuntimeException(message: 'OIDC JARM support is not configured.');
    }

    public function registerScimDirectory(RegisterScimDirectoryData $data) : RegisteredScimDirectory
    {
        return $this->registerScimDirectoryOrFail()->execute(data: $data);
    }

    private function registerScimDirectoryOrFail() : RegisterScimDirectory
    {
        return $this->registerScimDirectory ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    public function readScimDirectories(string|null $tenantSlug = null) : array
    {
        return $this->readScimDirectoriesOrFail()->execute(tenantSlug: $tenantSlug);
    }

    private function readScimDirectoriesOrFail() : ReadScimDirectories
    {
        return $this->readScimDirectories ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    public function rotateScimToken(string $directoryId) : RotatedScimToken
    {
        return $this->rotateScimTokenOrFail()->execute(directoryId: $directoryId);
    }

    private function rotateScimTokenOrFail() : RotateScimToken
    {
        return $this->rotateScimToken ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    public function markScimDirectoryOutage(MarkScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->markScimDirectoryOutageOrFail()->execute(data: $data);
    }

    private function markScimDirectoryOutageOrFail() : MarkScimDirectoryOutage
    {
        return $this->markScimDirectoryOutage ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    public function recoverScimDirectoryOutage(RecoverScimDirectoryOutageData $data) : ScimDirectory
    {
        return $this->recoverScimDirectoryOutageOrFail()->execute(data: $data);
    }

    private function recoverScimDirectoryOutageOrFail() : RecoverScimDirectoryOutage
    {
        return $this->recoverScimDirectoryOutage ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    public function provisionScimUser(ProvisionScimUserData $data) : ScimProvisioningResult
    {
        return $this->provisionScimUserOrFail()->execute(data: $data);
    }

    private function provisionScimUserOrFail() : ProvisionScimUser
    {
        return $this->provisionScimUser ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    public function deleteScimUser(DeleteScimUserData $data) : void
    {
        $this->deleteScimUserOrFail()->execute(data: $data);
    }

    private function deleteScimUserOrFail() : DeleteScimUser
    {
        return $this->deleteScimUser ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    public function readScimUsers(string $directoryId) : array
    {
        return $this->readScimUsersOrFail()->execute(directoryId: $directoryId);
    }

    private function readScimUsersOrFail() : ReadScimUsers
    {
        return $this->readScimUsers ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    public function readScimGroups(string $directoryId) : array
    {
        return $this->readScimGroupsOrFail()->execute(directoryId: $directoryId);
    }

    private function readScimGroupsOrFail() : ReadScimGroups
    {
        return $this->readScimGroups ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    public function syncScimGroups(SyncScimGroupsData $data) : ScimProvisioningResult
    {
        return $this->syncScimGroupsOrFail()->execute(data: $data);
    }

    private function syncScimGroupsOrFail() : SyncScimGroups
    {
        return $this->syncScimGroups ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    public function runScimBulk(ScimBulkRequest $data) : ScimBulkResponse
    {
        return $this->runScimBulkOrFail()->execute(request: $data);
    }

    private function runScimBulkOrFail() : RunScimBulk
    {
        return $this->runScimBulk ?? throw new RuntimeException(message: 'SCIM runtime is not configured.');
    }

    public function createTenant(CreateTenantData $data) : Tenant
    {
        return $this->createTenantOrFail()->execute(data: $data);
    }

    private function createTenantOrFail() : CreateTenant
    {
        return $this->createTenant ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    public function readTenants() : array
    {
        return $this->readTenantsOrFail()->execute();
    }

    private function readTenantsOrFail() : ReadTenants
    {
        return $this->readTenants ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    public function inviteTenantMember(InviteTenantMemberData $data) : IssuedTenantInvite
    {
        return $this->inviteTenantMemberOrFail()->execute(data: $data);
    }

    private function inviteTenantMemberOrFail() : InviteTenantMember
    {
        return $this->inviteTenantMember ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    public function acceptTenantInvite(AcceptTenantInviteData $data) : TenantMember
    {
        return $this->acceptTenantInviteOrFail()->execute(data: $data);
    }

    private function acceptTenantInviteOrFail() : AcceptTenantInvite
    {
        return $this->acceptTenantInvite ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    public function readTenantMembers(string $tenantSlug) : array
    {
        return $this->readTenantMembersOrFail()->execute(tenantSlug: $tenantSlug);
    }

    private function readTenantMembersOrFail() : ReadTenantMembers
    {
        return $this->readTenantMembers ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    public function removeTenantMember(RemoveTenantMemberData $data) : void
    {
        $this->removeTenantMemberOrFail()->execute(data: $data);
    }

    private function removeTenantMemberOrFail() : RemoveTenantMember
    {
        return $this->removeTenantMember ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    public function suspendTenantMember(SuspendTenantMemberData $data) : TenantMember
    {
        return $this->suspendTenantMemberOrFail()->execute(data: $data);
    }

    private function suspendTenantMemberOrFail() : SuspendTenantMember
    {
        return $this->suspendTenantMember ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
    }

    public function transferTenantOwnership(TransferTenantOwnershipData $data) : Tenant
    {
        return $this->transferTenantOwnershipOrFail()->execute(data: $data);
    }

    private function transferTenantOwnershipOrFail() : TransferTenantOwnership
    {
        return $this->transferTenantOwnership ?? throw new RuntimeException(message: 'Tenant product flows are not configured.');
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

    private function authorizeOAuthCodeOrFail() : AuthorizeCode
    {
        return $this->authorizeOAuthCode ?? throw new RuntimeException(message: 'OAuth authorization code flow is not configured.');
    }

    public function exchangeOAuthCode(ExchangeAuthorizationCodeData $data) : OAuthTokenGrant
    {
        return $this->exchangeOAuthCodeOrFail()->execute(data: $data);
    }

    private function exchangeOAuthCodeOrFail() : ExchangeAuthorizationCode
    {
        return $this->exchangeOAuthCode ?? throw new RuntimeException(message: 'OAuth token exchange is not configured.');
    }

    public function exchangeOAuthClientCredentials(ExchangeClientCredentialsData $data) : OAuthTokenGrant
    {
        return $this->exchangeOAuthClientCredentialsOrFail()->execute(data: $data);
    }

    private function exchangeOAuthClientCredentialsOrFail() : ExchangeClientCredentials
    {
        return $this->exchangeOAuthClientCredentials ?? throw new RuntimeException(message: 'OAuth client credentials flow is not configured.');
    }

    public function exchangeOAuthRefreshToken(ExchangeRefreshTokenData $data) : OAuthTokenGrant
    {
        return $this->exchangeOAuthRefreshTokenOrFail()->execute(data: $data);
    }

    private function exchangeOAuthRefreshTokenOrFail() : ExchangeRefreshToken
    {
        return $this->exchangeOAuthRefreshToken ?? throw new RuntimeException(message: 'OAuth refresh flow is not configured.');
    }

    public function revokeOAuthToken(RevokeTokenData $data) : void
    {
        $this->revokeOAuthTokenOrFail()->execute(data: $data);
    }

    private function revokeOAuthTokenOrFail() : RevokeToken
    {
        return $this->revokeOAuthToken ?? throw new RuntimeException(message: 'OAuth revoke flow is not configured.');
    }

    public function introspectOAuthToken(IntrospectTokenData $data) : TokenIntrospection
    {
        return $this->introspectOAuthTokenOrFail()->execute(data: $data);
    }

    private function introspectOAuthTokenOrFail() : IntrospectToken
    {
        return $this->introspectOAuthToken ?? throw new RuntimeException(message: 'OAuth introspection is not configured.');
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

    private function suspendUserOrFail() : SuspendUser
    {
        return $this->suspendUser ?? throw new RuntimeException(message: 'Provisioning lifecycle is not configured.');
    }

    public function reactivateUser(int $userId) : void
    {
        $this->reactivateUserOrFail()->execute(userId: $userId);
    }

    private function reactivateUserOrFail() : ReactivateUser
    {
        return $this->reactivateUser ?? throw new RuntimeException(message: 'Provisioning lifecycle is not configured.');
    }

    public function deprovisionUser(int $userId) : void
    {
        $this->deprovisionUserOrFail()->execute(userId: $userId);
    }

    private function deprovisionUserOrFail() : DeprovisionUser
    {
        return $this->deprovisionUser ?? throw new RuntimeException(message: 'Provisioning lifecycle is not configured.');
    }

    public function beginPasskeyRegistration() : PasskeyRegistration
    {
        return $this->beginPasskeyRegistrationOrFail()->execute();
    }

    private function beginPasskeyRegistrationOrFail() : BeginPasskeyRegistration
    {
        return $this->beginPasskeyRegistration ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    public function completePasskeyRegistration(CompletePasskeyRegistrationData $data) : PasskeyCredential
    {
        return $this->completePasskeyRegistrationOrFail()->execute(data: $data);
    }

    private function completePasskeyRegistrationOrFail() : CompletePasskeyRegistration
    {
        return $this->completePasskeyRegistration ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    public function beginPasskeyAuthentication(BeginPasskeyAuthenticationData $data) : PasskeyAuthenticationChallenge
    {
        return $this->beginPasskeyAuthenticationOrFail()->execute(data: $data);
    }

    private function beginPasskeyAuthenticationOrFail() : BeginPasskeyAuthentication
    {
        return $this->beginPasskeyAuthentication ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    public function completePasskeyAuthentication(CompletePasskeyAuthenticationData $data) : AuthenticationResult
    {
        return $this->completePasskeyAuthenticationOrFail()->execute(data: $data);
    }

    private function completePasskeyAuthenticationOrFail() : CompletePasskeyAuthentication
    {
        return $this->completePasskeyAuthentication ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    public function readPasskeys() : array
    {
        return $this->readPasskeysOrFail()->execute();
    }

    private function readPasskeysOrFail() : ListPasskeys
    {
        return $this->readPasskeys ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    public function renamePasskey(RenamePasskeyData $data) : PasskeyCredential
    {
        return $this->renamePasskeyOrFail()->execute(data: $data);
    }

    private function renamePasskeyOrFail() : RenamePasskey
    {
        return $this->renamePasskey ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    public function revokePasskey(#[SensitiveParameter] string $credentialId) : void
    {
        $this->revokePasskeyOrFail()->execute(credentialId: $credentialId);
    }

    private function revokePasskeyOrFail() : RevokePasskeyFlow
    {
        return $this->revokePasskey ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    public function registerFederationConnection(RegisterFederationConnectionData $data) : FederationConnection
    {
        return $this->registerFederationConnectionOrFail()->execute(data: $data);
    }

    private function registerFederationConnectionOrFail() : RegisterFederationConnection
    {
        return $this->registerFederationConnection ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    public function readFederationConnections() : array
    {
        return $this->readFederationConnectionsOrFail()->execute();
    }

    private function readFederationConnectionsOrFail() : ReadFederationConnections
    {
        return $this->readFederationConnections ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    public function verifyFederationDomain(VerifyFederationDomainData $data) : FederationConnection
    {
        return $this->verifyFederationDomainOrFail()->execute(data: $data);
    }

    private function verifyFederationDomainOrFail() : VerifyFederationDomain
    {
        return $this->verifyFederationDomain ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    public function syncFederationMetadata(string $connectionId) : FederationConnection
    {
        return $this->syncFederationMetadataOrFail()->execute(connectionId: $connectionId);
    }

    private function syncFederationMetadataOrFail() : SyncFederationMetadata
    {
        return $this->syncFederationMetadata ?? throw new RuntimeException(message: 'Federation metadata runtime is not configured.');
    }

    public function checkFederationConnectionHealth(string $connectionId) : FederationConnectionHealth
    {
        return $this->checkFederationConnectionHealthOrFail()->execute(connectionId: $connectionId);
    }

    private function checkFederationConnectionHealthOrFail() : CheckFederationConnectionHealth
    {
        return $this->checkFederationConnectionHealth ?? throw new RuntimeException(message: 'Federation health checks are not configured.');
    }

    public function evaluateFederationBreakGlassBypass(string $connectionId) : bool
    {
        return $this->evaluateFederationBreakGlassBypassOrFail()->execute(connectionId: $connectionId);
    }

    private function evaluateFederationBreakGlassBypassOrFail() : EvaluateFederationBreakGlassBypass
    {
        return $this->evaluateFederationBreakGlassBypass ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    public function discoverFederationConnection(#[SensitiveParameter] string $email) : FederationConnection|null
    {
        return $this->discoverFederationConnectionOrFail()->execute(email: $email);
    }

    private function discoverFederationConnectionOrFail() : DiscoverFederationConnection
    {
        return $this->discoverFederationConnection ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    public function startFederatedLogin(StartFederatedLoginData $data) : StartedFederatedLogin
    {
        return $this->startFederatedLoginOrFail()->execute(data: $data);
    }

    private function startFederatedLoginOrFail() : StartFederatedLogin
    {
        return $this->startFederatedLogin ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
    }

    public function completeFederatedLogin(CompleteFederatedLoginData $data) : AuthenticationResult
    {
        return $this->completeFederatedLoginOrFail()->execute(data: $data);
    }

    private function completeFederatedLoginOrFail() : CompleteFederatedLogin
    {
        return $this->completeFederatedLogin ?? throw new RuntimeException(message: 'Federation runtime is not configured.');
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
}
