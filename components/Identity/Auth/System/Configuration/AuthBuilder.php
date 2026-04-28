<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration;

use Avax\Components\Identity\Auth\System\Auth;
use Avax\Components\Identity\Auth\System\Capabilities\Access\Access;
use Avax\Components\Identity\Auth\System\Capabilities\Access\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Auth\System\Capabilities\Access\Authentication\Throttle\InMemoryAttemptThrottleStore;
use Avax\Components\Identity\Auth\System\Capabilities\Access\Facades\Authorization;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication\RequireAuthentication;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequirePermission\RequirePermission;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireResourceOwner\RequireResourceOwner;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireRole\RequireRole;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RiskBasedAccess\Runtime\AssessCurrentRisk\AssessCurrentRisk;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RiskBasedAccess\Runtime\ReadRiskSignals\ReadRiskSignals;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RiskBasedAccess\Support\DeterministicRiskEngine;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RiskBasedAccess\Support\InMemoryKnownAuthenticationEnvironmentStore;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RiskBasedAccess\Support\InMemoryRiskSignalStore;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\CorrelatingAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\NullAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Diagnostics;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplainer;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\ExternalIdentity;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\OAuth;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistration;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\AuthorizeCode\AuthorizeCode;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\DisableClient\DisableClient;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeAuthorizationCode\ExchangeAuthorizationCode;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeClientCredentials\ExchangeClientCredentials;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeRefreshToken\ExchangeRefreshToken;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\IntrospectToken\IntrospectToken;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ReadClients\ReadClients;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ReadWorkloadIdentities\ReadWorkloadIdentities;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RegisterClient\RegisterClient;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RevokeToken\RevokeToken;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RotateClientSecret\RotateClientSecret;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\UpdateClient\UpdateClient;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\AuthorizationCodeStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\InMemoryAuthorizationCodeStore;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\InMemoryOAuthClientRegistry;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\OpenIDConnect;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\BackChannelLogout\BackChannelLogout;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\FrontChannelLogout\FrontChannelLogout;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponse;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout\Logout as OidcLogout;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequest;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadJsonWebKeySet\ReadOidcJsonWebKeySet;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadProviderMetadata\ReadOidcProviderMetadata;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ReadUserInfo\ReadOidcUserInfo;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\ValidateRequestObject\ValidateRequestObject;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\InMemoryOidcRequestObjectStore;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcProviderInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Support\OidcRequestObjectStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\CheckHealth\CheckFederationConnectionHealth;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\CompleteFederatedLogin\CompleteFederatedLogin;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\DiscoverConnection\DiscoverFederationConnection;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\EvaluateBreakGlass\EvaluateFederationBreakGlassBypass;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\ReadConnections\ReadFederationConnections;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\RegisterConnection\RegisterFederationConnection;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\StartFederatedLogin\StartFederatedLogin;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\SyncMetadata\SyncFederationMetadata;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\VerifyDomain\VerifyFederationDomain;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederatedIdentityLinkStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationHealthCheckInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationMetadataRuntimeInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationRuntimeInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\GroupRoleMappingValidator;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\InMemoryFederatedIdentityLinkStore;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\InMemoryFederationConnectionStore;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\SingleSignOn;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Account;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Authentication;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Recovery;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Verification;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Mfa;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\GenerateBackupCodes;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\RegenerateBackupCodes;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\VerifyBackupCode;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Disable\DisableMfa;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\CancelMfaEnrollment;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollment;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\StartMfaEnrollment;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Limit\InMemoryAttemptLimitStorage;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Limit\LimitMfaAttempts;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\ConfirmMfaRecovery;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\StartMfaRecovery;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\InMemoryMfaStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Totp\Totp;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Totp\TotpInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\InMemoryMfaChallengeStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\MfaChallengeStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\StartMfaChallenge;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\VerifyMfaChallenge;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Passkey;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthentication;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginRegistration\BeginPasskeyRegistration;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthentication;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistration;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\ListPasskeys\ListPasskeys;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey\RenamePasskey;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Runtime\RevokePasskey\RevokePasskey;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support\InMemoryPasskeyChallengeStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support\InMemoryPasskeyCredentialStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyChallengeStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredentialStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyRuntimeInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\LogoutAllSessions\LogoutAllSessions;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\ReadActiveSessions\ReadActiveSessions;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\RevokeSession\RevokeSession;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Sessions;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow\RefreshAuthentication;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\IdentitySync;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\InMemoryLifecycleStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Provisioning\Provisioning;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\DeprovisionUser\DeprovisionUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\ReactivateUser\ReactivateUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\SuspendUser\SuspendUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\RunScimBulk;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutage;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUser;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadDirectories\ReadScimDirectories;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups\ReadScimGroups;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers\ReadScimUsers;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RecoverOutage\RecoverScimDirectoryOutage;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RotateToken\RotateScimToken;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups\SyncScimGroups;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\SCIM;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\InMemoryScimDirectoryStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\InMemoryScimProvisionedIdentityStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimProvisionedIdentityStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\BeginAdminElevation\BeginAdminElevation;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\EndAdminElevation\EndAdminElevation;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\AdminRealmSupport\AdminElevationStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\AdminRealmSupport\InMemoryAdminElevationStore;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\InMemoryTenantStore;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\TenantStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\AcceptInvite\AcceptTenantInvite;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\CreateTenant\CreateTenant;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember\InviteTenantMember;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\ReadMembers\ReadTenantMembers;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\ReadTenants\ReadTenants;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\RemoveMember\RemoveTenantMember;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\SuspendMember\SuspendTenantMember;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\TransferOwnership\TransferTenantOwnership;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\ApplyChange\ApplyTenantSecurityChange;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\ApproveChange\ApproveTenantSecurityChange;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\BeginChange\BeginTenantSecurityChange;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\ReadChangeRequest\ReadTenantSecurityChangeRequest;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\ReadChangeRequests\ReadTenantSecurityChangeRequests;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\ReadConfiguration\ReadTenantSecurityConfiguration;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\RollbackChange\RollbackTenantSecurityChange;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\InMemoryTenantSecurityChangeRequestStore;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\InMemoryTenantSecurityConfigurationStore;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\Security;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfigurationStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Tenancy;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Tenants\Tenants;
use Avax\Components\Identity\Auth\System\Configuration\Readiness\AuthBootstrapValidator;
use Avax\Components\Identity\Auth\System\Configuration\Readiness\AuthCapabilityReadiness;
use Avax\Components\Identity\Auth\System\Configuration\Readiness\AuthCapabilityRequests;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\BeginEmailChange;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\ConfirmEmailChange;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\EmailChangeStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\InMemoryEmailChangeStore;
use Avax\Components\Identity\Auth\System\Flows\ChangePassword\ChangePassword;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticateRequest;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\CheckAuthentication;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\ReadCurrentUser\ReadCurrentUser;
use Avax\Components\Identity\Auth\System\Flows\Login\Login;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Components\Identity\Auth\System\Flows\Logout\Logout;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordReset;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\InMemoryPasswordResetStore;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\PasswordResetStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPassword;
use Avax\Components\Identity\Auth\System\Flows\Register\Register;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerification;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationStateStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStateStore;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStore;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmail;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Foundation\Exceptions\ConfigurationException;
use Avax\Components\Identity\Auth\System\Foundation\IdGenerator;
use Avax\Components\Identity\Auth\System\Foundation\IdGeneratorInterface;
use SensitiveParameter;

/**
 * Fluent builder for creating Auth system instances.
 *
 * Capability: Composition root for the system.
 */
final class AuthBuilder
{
    private UserSourceInterface|null                       $userSource                       = null;
    private IdentityInterface|null                         $identity                         = null;
    private LoginRateLimit|null                            $rateLimit                        = null;
    private PasswordHasher|null                            $passwordHasher                   = null;
    private IdGeneratorInterface|null                      $idGenerator                      = null;
    private AuditLogInterface|null                         $auditLog                         = null;
    private string|null                                    $auditCorrelationId               = null;
    private EmailVerificationStateStoreInterface|null      $emailVerificationState           = null;
    private MfaStoreInterface|null                         $mfaStore                         = null;
    private RefreshTokenStoreInterface|null                $refreshTokenStore                = null;
    private PasswordResetStoreInterface|null               $passwordResetStore               = null;
    private EmailVerificationStoreInterface|null           $emailVerificationStore           = null;
    private EmailChangeStoreInterface|null                 $emailChangeStore                 = null;
    private MfaChallengeStoreInterface|null                $mfaChallengeStore                = null;
    private TotpInterface|null                             $totp                             = null;
    private LimitMfaAttempts|null                          $mfaAttemptLimit                  = null;
    private AttemptThrottle|null                           $passwordResetThrottle            = null;
    private AttemptThrottle|null                           $mfaRecoveryThrottle              = null;
    private AttemptThrottle|null                           $scimThrottle                     = null;
    private Clock|null                                     $clock                            = null;
    private SessionRegistryInterface|null                  $sessionRegistry                  = null;
    private OAuthClientRegistryInterface|null              $oauthClientRegistry              = null;
    private AuthorizationCodeStoreInterface|null           $authorizationCodeStore           = null;
    private LifecycleStoreInterface|null                   $lifecycleStore                   = null;
    private AdminElevationStoreInterface|null              $adminElevationStore              = null;
    private DeterministicRiskEngine|null                   $riskEngine                       = null;
    private PasskeyRuntimeInterface|null                   $passkeyRuntime                   = null;
    private PasskeyCredentialStoreInterface|null           $passkeyCredentialStore           = null;
    private PasskeyChallengeStoreInterface|null            $passkeyChallengeStore            = null;
    private FederationRuntimeInterface|null                $federationRuntime                = null;
    private FederationConnectionStoreInterface|null        $federationConnectionStore        = null;
    private FederatedIdentityLinkStoreInterface|null       $federatedIdentityLinkStore       = null;
    private OidcProviderInterface|null                     $oidcProvider                     = null;
    private OidcRequestObjectStoreInterface|null           $oidcRequestObjectStore           = null;
    private ScimDirectoryStoreInterface|null               $scimDirectoryStore               = null;
    private ScimProvisionedIdentityStoreInterface|null     $scimProvisionedIdentityStore     = null;
    private TenantStoreInterface|null                      $tenantStore                      = null;
    private TenantSecurityConfigurationStoreInterface|null $tenantSecurityConfigurationStore = null;
    private TenantSecurityChangeRequestStoreInterface|null $tenantSecurityChangeRequestStore = null;
    private string                                         $mfaIssuer                        = 'Avax Auth';
    private string                                         $passkeyRpId                      = 'localhost';
    private string                                         $passkeyRpName                    = 'Avax Auth';
    private bool                                           $adminPhishingResistantRequired   = false;
    private bool                                           $enterpriseMode                   = false;

    /**
     * Enable enterprise mode.
     * In enterprise mode, session registry becomes a required first-class runtime dependency.
     * The builder will fail-fast at build time if no durable session registry is configured.
     */
    public function enterprise() : self
    {
        $this->enterpriseMode = true;

        return $this;
    }

    /**
     * Define the data source for users.
     */
    public function forUser(UserSourceInterface $userSource) : self
    {
        $this->userSource = $userSource;

        return $this;
    }

    /**
     * Define the composed identity façade for authentication state.
     */
    public function withIdentity(IdentityInterface $identity) : self
    {
        $this->identity = $identity;

        return $this;
    }

    public function withIdentityBackends(
        #[SensitiveParameter] SessionIdentityInterface|null $sessionIdentity = null,
        #[SensitiveParameter] JwtIdentityInterface|null     $jwtIdentity = null
    ) : self
    {
        $this->identity = Identity::fromBackends(
            sessionIdentity: $sessionIdentity,
            jwtIdentity    : $jwtIdentity
        );

        return $this;
    }

    /**
     * Enable rate limiting for login.
     */
    public function protectFromBruteForce(LoginRateLimit $rateLimit) : self
    {
        $this->rateLimit = $rateLimit;

        return $this;
    }

    /**
     * Configure a custom password hasher.
     */
    public function usingHasher(#[SensitiveParameter] PasswordHasher $passwordHasher) : self
    {
        $this->passwordHasher = $passwordHasher;

        return $this;
    }

    /**
     * Configure a custom identifier generator for registrations.
     */
    public function usingIdGenerator(IdGeneratorInterface $idGenerator) : self
    {
        $this->idGenerator = $idGenerator;

        return $this;
    }

    public function withAuditLog(AuditLogInterface $auditLog) : self
    {
        $this->auditLog = $auditLog;

        return $this;
    }

    public function withAuditCorrelationId(string $correlationId) : self
    {
        $this->auditCorrelationId = trim(string: $correlationId) !== '' ? trim(string: $correlationId) : null;

        return $this;
    }

    public function withEmailVerificationState(#[SensitiveParameter] EmailVerificationStateStoreInterface $emailVerificationState) : self
    {
        $this->emailVerificationState = $emailVerificationState;

        return $this;
    }

    public function withMfaStore(MfaStoreInterface $mfaStore) : self
    {
        $this->mfaStore = $mfaStore;

        return $this;
    }

    public function withRefreshTokenStore(#[SensitiveParameter] RefreshTokenStoreInterface $refreshTokenStore) : self
    {
        $this->refreshTokenStore = $refreshTokenStore;

        return $this;
    }

    public function withPasswordResetStore(#[SensitiveParameter] PasswordResetStoreInterface $passwordResetStore) : self
    {
        $this->passwordResetStore = $passwordResetStore;

        return $this;
    }

    public function withEmailVerificationStore(#[SensitiveParameter] EmailVerificationStoreInterface $emailVerificationStore) : self
    {
        $this->emailVerificationStore = $emailVerificationStore;

        return $this;
    }

    public function withEmailChangeStore(#[SensitiveParameter] EmailChangeStoreInterface $emailChangeStore) : self
    {
        $this->emailChangeStore = $emailChangeStore;

        return $this;
    }

    public function withMfaChallengeStore(MfaChallengeStoreInterface $mfaChallengeStore) : self
    {
        $this->mfaChallengeStore = $mfaChallengeStore;

        return $this;
    }

    public function usingTotp(TotpInterface $totp) : self
    {
        $this->totp = $totp;

        return $this;
    }

    public function withPasswordResetThrottle(#[SensitiveParameter] AttemptThrottle $passwordResetThrottle) : self
    {
        $this->passwordResetThrottle = $passwordResetThrottle;

        return $this;
    }

    public function withMfaRecoveryThrottle(AttemptThrottle $mfaRecoveryThrottle) : self
    {
        $this->mfaRecoveryThrottle = $mfaRecoveryThrottle;

        return $this;
    }

    public function withScimThrottle(AttemptThrottle $scimThrottle) : self
    {
        $this->scimThrottle = $scimThrottle;

        return $this;
    }

    public function withClock(Clock $clock) : self
    {
        $this->clock = $clock;

        return $this;
    }

    public function withSessionRegistry(#[SensitiveParameter] SessionRegistryInterface $sessionRegistry) : self
    {
        $this->sessionRegistry = $sessionRegistry;

        return $this;
    }

    public function withOAuthClientRegistry(#[SensitiveParameter] OAuthClientRegistryInterface $oauthClientRegistry) : self
    {
        $this->oauthClientRegistry = $oauthClientRegistry;

        return $this;
    }

    public function withAuthorizationCodeStore(#[SensitiveParameter] AuthorizationCodeStoreInterface $authorizationCodeStore) : self
    {
        $this->authorizationCodeStore = $authorizationCodeStore;

        return $this;
    }

    public function withLifecycleStore(#[SensitiveParameter] LifecycleStoreInterface $lifecycleStore) : self
    {
        $this->lifecycleStore = $lifecycleStore;

        return $this;
    }

    public function withAdminElevationStore(AdminElevationStoreInterface $adminElevationStore) : self
    {
        $this->adminElevationStore = $adminElevationStore;

        return $this;
    }

    public function withRiskEngine(DeterministicRiskEngine $riskEngine) : self
    {
        $this->riskEngine = $riskEngine;

        return $this;
    }

    public function withPasskeyRuntime(PasskeyRuntimeInterface $passkeyRuntime) : self
    {
        $this->passkeyRuntime = $passkeyRuntime;

        return $this;
    }

    public function withPasskeyCredentialStore(#[SensitiveParameter] PasskeyCredentialStoreInterface $passkeyCredentialStore) : self
    {
        $this->passkeyCredentialStore = $passkeyCredentialStore;

        return $this;
    }

    public function withPasskeyChallengeStore(PasskeyChallengeStoreInterface $passkeyChallengeStore) : self
    {
        $this->passkeyChallengeStore = $passkeyChallengeStore;

        return $this;
    }

    public function withPasskeyRelyingParty(string $rpId, string $rpName) : self
    {
        $this->passkeyRpId   = $rpId;
        $this->passkeyRpName = $rpName;

        return $this;
    }

    public function requirePhishingResistantAdminElevation(bool $required = true) : self
    {
        $this->adminPhishingResistantRequired = $required;

        return $this;
    }

    public function withFederationRuntime(FederationRuntimeInterface $federationRuntime) : self
    {
        $this->federationRuntime = $federationRuntime;

        return $this;
    }

    public function withFederationConnectionStore(FederationConnectionStoreInterface $federationConnectionStore) : self
    {
        $this->federationConnectionStore = $federationConnectionStore;

        return $this;
    }

    public function withFederatedIdentityLinkStore(FederatedIdentityLinkStoreInterface $federatedIdentityLinkStore) : self
    {
        $this->federatedIdentityLinkStore = $federatedIdentityLinkStore;

        return $this;
    }

    public function withOidcProvider(OidcProviderInterface $oidcProvider) : self
    {
        $this->oidcProvider = $oidcProvider;

        return $this;
    }

    public function withOidcRequestObjectStore(OidcRequestObjectStoreInterface $oidcRequestObjectStore) : self
    {
        $this->oidcRequestObjectStore = $oidcRequestObjectStore;

        return $this;
    }

    public function withScimDirectoryStore(ScimDirectoryStoreInterface $scimDirectoryStore) : self
    {
        $this->scimDirectoryStore = $scimDirectoryStore;

        return $this;
    }

    public function withScimProvisionedIdentityStore(ScimProvisionedIdentityStoreInterface $scimProvisionedIdentityStore) : self
    {
        $this->scimProvisionedIdentityStore = $scimProvisionedIdentityStore;

        return $this;
    }

    public function withTenantStore(#[SensitiveParameter] TenantStoreInterface $tenantStore) : self
    {
        $this->tenantStore = $tenantStore;

        return $this;
    }

    public function withTenantSecurityConfigurationStore(#[SensitiveParameter] TenantSecurityConfigurationStoreInterface $tenantSecurityConfigurationStore) : self
    {
        $this->tenantSecurityConfigurationStore = $tenantSecurityConfigurationStore;

        return $this;
    }

    public function withTenantSecurityChangeRequestStore(#[SensitiveParameter] TenantSecurityChangeRequestStoreInterface $tenantSecurityChangeRequestStore) : self
    {
        $this->tenantSecurityChangeRequestStore = $tenantSecurityChangeRequestStore;

        return $this;
    }

    public function withMfaAttemptLimit(LimitMfaAttempts $mfaAttemptLimit) : self
    {
        $this->mfaAttemptLimit = $mfaAttemptLimit;

        return $this;
    }

    public function withMfaIssuer(string $mfaIssuer) : self
    {
        $this->mfaIssuer = $mfaIssuer;

        return $this;
    }

    /**
     * Build the final Auth instance.
     */
    public function ready() : Auth
    {
        AuthBootstrapValidator::validate(
            userSource       : $this->userSource,
            identity         : $this->identity,
            requests         : $this->capabilityRequests(),
            sessionRegistry  : $this->sessionRegistry,
            refreshTokenStore: $this->refreshTokenStore,
            passkeyRuntime   : $this->passkeyRuntime,
            federationRuntime: $this->federationRuntime,
            oidcProvider     : $this->oidcProvider
        );

        $identity       = $this->identity;
        $passwordHasher = $this->passwordHasher ?? new PasswordHasher();
        $auditLog       = $this->auditLog ?? new NullAuditLog();

        if ($this->auditCorrelationId !== null) {
            $auditLog = new CorrelatingAuditLog(
                inner        : $auditLog,
                correlationId: $this->auditCorrelationId
            );
        }
        $clock                                  = $this->clock ?? new Clock();
        $oauthClientRegistry                    = $this->oauthClientRegistry ?? new InMemoryOAuthClientRegistry(passwordHasher: $passwordHasher);
        $authorizationCodeStore                 = $this->authorizationCodeStore ?? new InMemoryAuthorizationCodeStore();
        $lifecycleStore                         = $this->lifecycleStore ?? new InMemoryLifecycleStore();
        $adminElevationStore                    = $this->adminElevationStore ?? new InMemoryAdminElevationStore();
        $riskEngine                             = $this->riskEngine ?? new DeterministicRiskEngine(
            knownEnvironments: new InMemoryKnownAuthenticationEnvironmentStore(),
            signals          : new InMemoryRiskSignalStore(),
            clock            : $clock
        );
        $passkeyCredentialStore                 = $this->passkeyCredentialStore ?? new InMemoryPasskeyCredentialStore();
        $passkeyChallengeStore                  = $this->passkeyChallengeStore ?? new InMemoryPasskeyChallengeStore();
        $federationConnectionStore              = $this->federationConnectionStore ?? new InMemoryFederationConnectionStore();
        $federatedIdentityLinkStore             = $this->federatedIdentityLinkStore ?? new InMemoryFederatedIdentityLinkStore();
        $groupRoleMappingValidator              = new GroupRoleMappingValidator();
        $passwordResetStore                     = $this->passwordResetStore ?? new InMemoryPasswordResetStore();
        $emailVerificationStore                 = $this->emailVerificationStore ?? new InMemoryEmailVerificationStore();
        $emailChangeStore                       = $this->emailChangeStore ?? new InMemoryEmailChangeStore();
        $emailVerificationState                 = $this->emailVerificationState ?? new InMemoryEmailVerificationStateStore();
        $mfaStore                               = $this->mfaStore ?? new InMemoryMfaStore();
        $mfaChallengeStore                      = $this->mfaChallengeStore ?? new InMemoryMfaChallengeStore();
        $totp                                   = $this->totp ?? new Totp();
        $mfaAttemptLimit                        = $this->mfaAttemptLimit ?? new LimitMfaAttempts(
            storage: new InMemoryAttemptLimitStorage(),
            clock  : $clock
        );
        $passwordResetThrottle                  = $this->passwordResetThrottle ?? new AttemptThrottle(
            store       : new InMemoryAttemptThrottleStore(),
            clock       : $clock,
            maxAttempts : 5,
            decaySeconds: 900
        );
        $mfaRecoveryThrottle                    = $this->mfaRecoveryThrottle ?? new AttemptThrottle(
            store       : new InMemoryAttemptThrottleStore(),
            clock       : $clock,
            maxAttempts : 3,
            decaySeconds: 1800
        );
        $scimThrottle                           = $this->scimThrottle ?? new AttemptThrottle(
            store       : new InMemoryAttemptThrottleStore(),
            clock       : $clock,
            maxAttempts : 60,
            decaySeconds: 60
        );
        $projectAuthenticatedUser               = new ProjectAuthenticatedUser(
            emailVerificationState: $emailVerificationState,
            mfaStore              : $mfaStore
        );
        $currentAuthentication                  = new CurrentAuthentication();
        $requireFreshMfa                        = new RequireFreshMfa(
            currentAuthentication: $currentAuthentication,
            clock                : $clock
        );
        $generateBackupCodes                    = new GenerateBackupCodes(
            passwordHasher: $passwordHasher,
            clock         : $clock
        );
        $verifyBackupCode                       = new VerifyBackupCode(
            mfaStore      : $mfaStore,
            passwordHasher: $passwordHasher,
            auditLog      : $auditLog,
            clock         : $clock
        );
        $startMfaChallenge                      = new StartMfaChallenge(
            currentAuthentication: $currentAuthentication,
            mfaStore             : $mfaStore,
            challengeStore       : $mfaChallengeStore,
            auditLog             : $auditLog,
            clock                : $clock
        );
        $authenticateRequest                    = new AuthenticateRequest(
            currentAuthentication   : $currentAuthentication,
            projectAuthenticatedUser: $projectAuthenticatedUser,
            userSource              : $this->userSource,
            auditLog                : $auditLog,
            sessionIdentity         : $identity->sessionIdentity(),
            jwtIdentity             : $identity->jwtIdentity(),
            clock                   : $clock
        );
        $readCurrentUser                        = new ReadCurrentUser(
            currentAuthentication: $currentAuthentication
        );
        $checkAuthentication                    = new CheckAuthentication(
            currentAuthentication: $currentAuthentication
        );
        $registerOAuthClient                    = new RegisterClient(
            clientRegistry: $oauthClientRegistry,
            auditLog      : $auditLog,
            clock         : $clock
        );
        $approveOAuthClientRegistration         = new ApproveClientRegistration(
            clientRegistry: $oauthClientRegistry,
            auditLog      : $auditLog,
            clock         : $clock
        );
        $updateOAuthClient                      = new UpdateClient(
            clientRegistry: $oauthClientRegistry,
            auditLog      : $auditLog,
            clock         : $clock
        );
        $disableOAuthClient                     = new DisableClient(
            clientRegistry: $oauthClientRegistry,
            auditLog      : $auditLog,
            clock         : $clock
        );
        $rotateOAuthClientSecret                = new RotateClientSecret(
            clientRegistry: $oauthClientRegistry,
            auditLog      : $auditLog,
            clock         : $clock
        );
        $readOAuthClients                       = new ReadClients(
            clientRegistry: $oauthClientRegistry
        );
        $readWorkloadIdentities                 = new ReadWorkloadIdentities(
            clientRegistry: $oauthClientRegistry
        );
        $readOidcProviderMetadata               = $this->oidcProvider !== null
            ? new ReadOidcProviderMetadata(oidcProvider: $this->oidcProvider)
            : null;
        $readOidcJsonWebKeySet                  = $this->oidcProvider !== null
            ? new ReadOidcJsonWebKeySet(oidcProvider: $this->oidcProvider)
            : null;
        $jwtIdentity                            = $identity->jwtIdentity();
        $readOidcUserInfo                       = $jwtIdentity !== null && $this->oidcProvider !== null
            ? new ReadOidcUserInfo(jwtIdentity: $jwtIdentity, oidcProvider: $this->oidcProvider)
            : null;
        $oidcRequestObjectStore                 = $this->oidcProvider !== null
            ? ($this->oidcRequestObjectStore ?? new InMemoryOidcRequestObjectStore())
            : null;
        $pushOidcAuthorizationRequest           = $oidcRequestObjectStore !== null
            ? new PushAuthorizationRequest(
                requestObjectStore: $oidcRequestObjectStore,
                auditLog          : $auditLog,
                clock             : $clock,
                clientRegistry    : $oauthClientRegistry,
                oidcProvider      : $this->oidcProvider
            )
            : null;
        $validateRequestObject                  = $oidcRequestObjectStore !== null
            ? new ValidateRequestObject(requestObjectStore: $oidcRequestObjectStore, clientRegistry: $oauthClientRegistry)
            : null;
        $oidcLogout                             = $this->oidcProvider !== null
            ? new OidcLogout(
                frontChannelLogout: new FrontChannelLogout(
                                        currentAuthentication: $currentAuthentication,
                                        identity             : $identity,
                                        auditLog             : $auditLog,
                                        clock                : $clock,
                                        sessionRegistry      : $this->sessionRegistry,
                                        refreshTokenStore    : $this->refreshTokenStore,
                                        oidcProvider         : $this->oidcProvider,
                                        clientRegistry       : $oauthClientRegistry
                                    ),
                backChannelLogout : new BackChannelLogout(
                                        currentAuthentication: $currentAuthentication,
                                        identity             : $identity,
                                        auditLog             : $auditLog,
                                        clock                : $clock,
                                        sessionRegistry      : $this->sessionRegistry,
                                        refreshTokenStore    : $this->refreshTokenStore,
                                        oidcProvider         : $this->oidcProvider,
                                        clientRegistry       : $oauthClientRegistry
                                    )
            )
            : null;
        $buildOidcJarmResponse                  = $this->oidcProvider !== null
            ? new BuildJarmResponse(
                oidcProvider: $this->oidcProvider,
                clock       : $clock
            )
            : null;
        $authorizeOAuthCode                     = new AuthorizeCode(
            currentAuthentication : $currentAuthentication,
            userSource            : $this->userSource,
            clientRegistry        : $oauthClientRegistry,
            codeStore             : $authorizationCodeStore,
            auditLog              : $auditLog,
            clock                 : $clock,
            oidcProvider          : $this->oidcProvider,
            requestObjectValidator: $validateRequestObject
        );
        $requireAdminElevation                  = new RequireAdminElevation(
            currentAuthentication: $currentAuthentication,
            elevationStore       : $adminElevationStore,
            clock                : $clock
        );
        $requireAuthentication                  = new RequireAuthentication(
            currentAuthentication: $currentAuthentication
        );
        $requireRole                            = new RequireRole(
            currentAuthentication: $currentAuthentication
        );
        $requirePermission                      = new RequirePermission(
            currentAuthentication: $currentAuthentication
        );
        $requireResourceOwner                   = new RequireResourceOwner(
            currentAuthentication: $currentAuthentication
        );
        $requirePhishingResistantAuthentication = new RequirePhishingResistantAuthentication(
            currentAuthentication: $currentAuthentication
        );
        $requireAccessPolicy                    = new RequireAccessPolicy(
            requireAuthentication                 : $requireAuthentication,
            requireRole                           : $requireRole,
            requirePermission                     : $requirePermission,
            requireResourceOwner                  : $requireResourceOwner,
            requirePhishingResistantAuthentication: $requirePhishingResistantAuthentication,
            requireFreshMfa                       : $requireFreshMfa,
            requireAdminElevation                 : $requireAdminElevation
        );
        $authorization                          = new Authorization(
            requireAuthentication: $requireAuthentication,
            requireRole          : $requireRole,
            requirePermission    : $requirePermission,
            requireAccessPolicy  : $requireAccessPolicy
        );
        $provisionableUserSource                = $this->userSource instanceof ProvisionableUserSourceInterface
            ? $this->userSource
            : null;
        $lifecycle                              = $provisionableUserSource !== null
            ? new LifecycleOrchestrator(
                userSource: $provisionableUserSource,
                store     : $lifecycleStore,
                auditLog  : $auditLog,
                clock     : $clock
            )
            : null;
        $scimDirectoryStore                     = $this->scimDirectoryStore ?? new InMemoryScimDirectoryStore(passwordHasher: $passwordHasher);
        $scimProvisionedIdentityStore           = $this->scimProvisionedIdentityStore ?? new InMemoryScimProvisionedIdentityStore();
        $tenantStore                            = $this->tenantStore ?? new InMemoryTenantStore();
        $tenantSecurityConfigurationStore       = $this->tenantSecurityConfigurationStore ?? new InMemoryTenantSecurityConfigurationStore();
        $tenantSecurityChangeRequestStore       = $this->tenantSecurityChangeRequestStore ?? new InMemoryTenantSecurityChangeRequestStore();
        $readTenantSecurityConfiguration        = new ReadTenantSecurityConfiguration(configurationStore: $tenantSecurityConfigurationStore);
        $beginTenantSecurityChange              = new BeginTenantSecurityChange(
            configurationStore       : $tenantSecurityConfigurationStore,
            changeRequestStore       : $tenantSecurityChangeRequestStore,
            federationConnectionStore: $federationConnectionStore,
            scimDirectoryStore       : $scimDirectoryStore,
            auditLog                 : $auditLog,
            clock                    : $clock
        );
        $approveTenantSecurityChange            = new ApproveTenantSecurityChange(
            changeRequestStore: $tenantSecurityChangeRequestStore,
            auditLog          : $auditLog,
            clock             : $clock
        );
        $applyTenantSecurityChange              = new ApplyTenantSecurityChange(
            configurationStore: $tenantSecurityConfigurationStore,
            changeRequestStore: $tenantSecurityChangeRequestStore,
            auditLog          : $auditLog,
            clock             : $clock
        );
        $rollbackTenantSecurityChange           = new RollbackTenantSecurityChange(
            configurationStore: $tenantSecurityConfigurationStore,
            changeRequestStore: $tenantSecurityChangeRequestStore,
            auditLog          : $auditLog,
            clock             : $clock
        );
        $readTenantSecurityChangeRequest        = new ReadTenantSecurityChangeRequest(changeRequestStore: $tenantSecurityChangeRequestStore);
        $readTenantSecurityChangeRequests       = new ReadTenantSecurityChangeRequests(changeRequestStore: $tenantSecurityChangeRequestStore);
        $createTenant                           = new CreateTenant(
            tenantStore: $tenantStore,
            userSource : $this->userSource,
            auditLog   : $auditLog,
            clock      : $clock
        );
        $readTenants                            = new ReadTenants(tenantStore: $tenantStore);
        $inviteTenantMember                     = new InviteTenantMember(
            tenantStore: $tenantStore,
            auditLog   : $auditLog,
            clock      : $clock
        );
        $acceptTenantInvite                     = new AcceptTenantInvite(
            tenantStore: $tenantStore,
            userSource : $this->userSource,
            auditLog   : $auditLog,
            clock      : $clock
        );
        $readTenantMembers                      = new ReadTenantMembers(tenantStore: $tenantStore);
        $removeTenantMember                     = new RemoveTenantMember(
            tenantStore: $tenantStore,
            auditLog   : $auditLog,
            clock      : $clock
        );
        $suspendTenantMember                    = new SuspendTenantMember(
            tenantStore: $tenantStore,
            auditLog   : $auditLog,
            clock      : $clock
        );
        $transferTenantOwnership                = new TransferTenantOwnership(
            tenantStore: $tenantStore,
            auditLog   : $auditLog,
            clock      : $clock
        );
        $capabilityReadiness                    = AuthCapabilityReadiness::from(
            jwtIdentity            : $jwtIdentity,
            refreshTokenStore      : $this->refreshTokenStore,
            passkeyRuntime         : $this->passkeyRuntime,
            federationRuntime      : $this->federationRuntime,
            provisionableUserSource: $provisionableUserSource
        );
        $provisionScimUser                      = $capabilityReadiness->scim()
            ? new ProvisionScimUser(
                userSource     : $provisionableUserSource,
                directoryStore : $scimDirectoryStore,
                identityStore  : $scimProvisionedIdentityStore,
                passwordHasher : $passwordHasher,
                idGenerator    : $this->idGenerator ?? new IdGenerator(),
                auditLog       : $auditLog,
                clock          : $clock,
                lifecycle      : $lifecycle,
                attemptThrottle: $scimThrottle
            )
            : null;
        $readScimUsers                          = $capabilityReadiness->scim()
            ? new ReadScimUsers(
                identityStore: $scimProvisionedIdentityStore,
                userSource   : $this->userSource
            )
            : null;
        $readScimGroups                         = $capabilityReadiness->scim() && $readScimUsers !== null
            ? new ReadScimGroups(readScimUsers: $readScimUsers)
            : null;
        $runScimBulk                            = $capabilityReadiness->scim() && $provisionScimUser !== null
            ? new RunScimBulk(
                provisionScimUser: $provisionScimUser,
                deleteScimUser   : new DeleteScimUser(
                                       userSource    : $provisionableUserSource,
                                       directoryStore: $scimDirectoryStore,
                                       identityStore : $scimProvisionedIdentityStore,
                                       auditLog      : $auditLog,
                                       clock         : $clock,
                                       lifecycle     : $lifecycle
                                   )
            )
            : null;

        $assessCurrentRisk = new AssessCurrentRisk(
            currentAuthentication: $currentAuthentication,
            userSource           : $this->userSource,
            riskEngine           : $riskEngine
        );
        $readRiskSignals   = new ReadRiskSignals(
            currentAuthentication: $currentAuthentication,
            riskEngine           : $riskEngine
        );

        $authentication = new Authentication(
            login                : new Login(
                                       userSource              : $this->userSource,
                                       passwordHasher          : $passwordHasher,
                                       identity                : $identity,
                                       projectAuthenticatedUser: $projectAuthenticatedUser,
                                       currentAuthentication   : $currentAuthentication,
                                       auditLog                : $auditLog,
                                       mfaStore                : $mfaStore,
                                       startMfaChallenge       : $startMfaChallenge,
                                       clock                   : $clock,
                                       rateLimit               : $this->rateLimit,
                                       riskEngine              : $riskEngine
                                   ),
            logout               : new Logout(
                                       identity             : $identity,
                                       currentAuthentication: $currentAuthentication,
                                       auditLog             : $auditLog,
                                       clock                : $clock,
                                       sessionRegistry      : $this->sessionRegistry,
                                       refreshTokenStore    : $this->refreshTokenStore
                                   ),
            refreshAuthentication: new RefreshAuthentication(
                                       userSource              : $this->userSource,
                                       projectAuthenticatedUser: $projectAuthenticatedUser,
                                       currentAuthentication   : $currentAuthentication,
                                       auditLog                : $auditLog,
                                       clock                   : $clock,
                                       refreshTokenStore       : $this->refreshTokenStore,
                                       jwtIdentity             : $identity->jwtIdentity(),
                                       riskEngine              : $riskEngine
                                   )
        );

        $session = new Sessions(
            logoutAllSessions : new LogoutAllSessions(
                                    identity             : $identity,
                                    currentAuthentication: $currentAuthentication,
                                    auditLog             : $auditLog,
                                    clock                : $clock,
                                    sessionRegistry      : $this->sessionRegistry,
                                    refreshTokenStore    : $this->refreshTokenStore
                                ),
            readActiveSessions: new ReadActiveSessions(
                                    currentAuthentication: $currentAuthentication,
                                    clock                : $clock,
                                    sessionRegistry      : $this->sessionRegistry
                                ),
            revokeSession     : new RevokeSession(
                                    identity             : $identity,
                                    currentAuthentication: $currentAuthentication,
                                    auditLog             : $auditLog,
                                    clock                : $clock,
                                    sessionRegistry      : $this->sessionRegistry
                                )
        );

        $account = new Account(
            changePassword    : new ChangePassword(
                                    userSource           : $this->userSource,
                                    passwordHasher       : $passwordHasher,
                                    identity             : $identity,
                                    currentAuthentication: $currentAuthentication,
                                    auditLog             : $auditLog,
                                    clock                : $clock,
                                    sessionRegistry      : $this->sessionRegistry,
                                    mfaChallengeStore    : $mfaChallengeStore,
                                    refreshTokenStore    : $this->refreshTokenStore,
                                    rateLimit            : $this->rateLimit,
                                    requireFreshMfa      : $requireFreshMfa
                                ),
            beginEmailChange  : new BeginEmailChange(
                                    currentAuthentication: $currentAuthentication,
                                    userSource           : $this->userSource,
                                    passwordHasher       : $passwordHasher,
                                    emailChangeStore     : $emailChangeStore,
                                    requireFreshMfa      : $requireFreshMfa,
                                    auditLog             : $auditLog,
                                    clock                : $clock
                                ),
            confirmEmailChange: $provisionableUserSource !== null
                                    ? new ConfirmEmailChange(
                                        userSource            : $provisionableUserSource,
                                        emailChangeStore      : $emailChangeStore,
                                        emailVerificationState: $emailVerificationState,
                                        auditLog              : $auditLog,
                                        clock                 : $clock,
                                        currentAuthentication : $currentAuthentication,
                                        identity              : $identity,
                                        sessionRegistry       : $this->sessionRegistry,
                                        mfaChallengeStore     : $mfaChallengeStore,
                                        refreshTokenStore     : $this->refreshTokenStore
                                    )
                                    : null,
            register          : new Register(
                                    userSource               : $this->userSource,
                                    passwordHasher           : $passwordHasher,
                                    idGenerator              : $this->idGenerator ?? new IdGenerator(),
                                    projectAuthenticatedUser : $projectAuthenticatedUser,
                                    auditLog                 : $auditLog,
                                    clock                    : $clock,
                                    emailVerificationRequired: $this->emailVerificationState !== null || $this->emailVerificationStore !== null,
                                    rateLimit                : $this->rateLimit
                                )
        );

        $recovery = new Recovery(
            beginPasswordReset: new BeginPasswordReset(
                                    userSource        : $this->userSource,
                                    passwordResetStore: $passwordResetStore,
                                    auditLog          : $auditLog,
                                    clock             : $clock,
                                    attemptThrottle   : $passwordResetThrottle
                                ),
            resetPassword     : new ResetPassword(
                                    userSource        : $this->userSource,
                                    passwordHasher    : $passwordHasher,
                                    passwordResetStore: $passwordResetStore,
                                    auditLog          : $auditLog,
                                    clock             : $clock,
                                    sessionRegistry   : $this->sessionRegistry,
                                    mfaChallengeStore : $mfaChallengeStore,
                                    refreshTokenStore : $this->refreshTokenStore
                                )
        );

        $verification = new Verification(
            beginEmailVerification: new BeginEmailVerification(
                                        userSource            : $this->userSource,
                                        emailVerificationStore: $emailVerificationStore,
                                        auditLog              : $auditLog,
                                        clock                 : $clock
                                    ),
            verifyEmail           : new VerifyEmail(
                                        emailVerificationStore: $emailVerificationStore,
                                        emailVerificationState: $emailVerificationState,
                                        auditLog              : $auditLog,
                                        clock                 : $clock
                                    )
        );

        $mfa = new Mfa(
            startMfaEnrollment   : new StartMfaEnrollment(
                                       currentAuthentication: $currentAuthentication,
                                       mfaStore             : $mfaStore,
                                       totp                 : $totp,
                                       auditLog             : $auditLog,
                                       clock                : $clock,
                                       issuer               : $this->mfaIssuer
                                   ),
            confirmMfaEnrollment : new ConfirmMfaEnrollment(
                                       currentAuthentication: $currentAuthentication,
                                       mfaStore             : $mfaStore,
                                       totp                 : $totp,
                                       generateBackupCodes  : $generateBackupCodes,
                                       auditLog             : $auditLog,
                                       clock                : $clock
                                   ),
            cancelMfaEnrollment  : new CancelMfaEnrollment(
                                       currentAuthentication: $currentAuthentication,
                                       mfaStore             : $mfaStore,
                                       auditLog             : $auditLog,
                                       clock                : $clock
                                   ),
            startMfaChallenge    : $startMfaChallenge,
            verifyMfaChallenge   : new VerifyMfaChallenge(
                                       challengeStore          : $mfaChallengeStore,
                                       mfaStore                : $mfaStore,
                                       totp                    : $totp,
                                       verifyBackupCode        : $verifyBackupCode,
                                       userSource              : $this->userSource,
                                       identity                : $identity,
                                       projectAuthenticatedUser: $projectAuthenticatedUser,
                                       currentAuthentication   : $currentAuthentication,
                                       auditLog                : $auditLog,
                                       clock                   : $clock,
                                       attemptLimit            : $mfaAttemptLimit,
                                       riskEngine              : $riskEngine
                                   ),
            regenerateBackupCodes: new RegenerateBackupCodes(
                                       currentAuthentication: $currentAuthentication,
                                       requireFreshMfa      : $requireFreshMfa,
                                       mfaStore             : $mfaStore,
                                       generateBackupCodes  : $generateBackupCodes,
                                       auditLog             : $auditLog,
                                       clock                : $clock
                                   ),
            disableMfa           : new DisableMfa(
                                       currentAuthentication: $currentAuthentication,
                                       requireFreshMfa      : $requireFreshMfa,
                                       mfaStore             : $mfaStore,
                                       mfaChallengeStore    : $mfaChallengeStore,
                                       auditLog             : $auditLog,
                                       clock                : $clock,
                                       refreshTokenStore    : $this->refreshTokenStore
                                   ),
            startMfaRecovery     : new StartMfaRecovery(
                                       userSource     : $this->userSource,
                                       mfaStore       : $mfaStore,
                                       auditLog       : $auditLog,
                                       clock          : $clock,
                                       attemptThrottle: $mfaRecoveryThrottle
                                   ),
            confirmMfaRecovery   : new ConfirmMfaRecovery(
                                       mfaStore             : $mfaStore,
                                       mfaChallengeStore    : $mfaChallengeStore,
                                       auditLog             : $auditLog,
                                       clock                : $clock,
                                       sessionRegistry      : $this->sessionRegistry,
                                       refreshTokenStore    : $this->refreshTokenStore,
                                       currentAuthentication: $currentAuthentication,
                                       identity             : $identity
                                   )
        );

        $passkey = new Passkey(
            beginPasskeyRegistration     : $capabilityReadiness->passkey()
                                               ? new BeginPasskeyRegistration(
                                                 currentAuthentication: $currentAuthentication,
                                                 requireFreshMfa      : $requireFreshMfa,
                                                 runtime              : $this->passkeyRuntime ?? throw ConfigurationException::missingCapabilityDependency(
                                                 capability : 'passkey',
                                                 requirement: 'runtime',
                                                 buildPath  : 'AuthBuilder::ready()',
                                                 option     : 'withPasskeyRuntime()',
                                                 cause      : 'Passkey capability assembly was attempted.'
                                             ),
                                                 credentialStore      : $passkeyCredentialStore,
                                                 challengeStore       : $passkeyChallengeStore,
                                                 auditLog             : $auditLog,
                                                 clock                : $clock,
                                                 rpId                 : $this->passkeyRpId,
                                                 rpName               : $this->passkeyRpName
                                             )
                                               : null,
            completePasskeyRegistration  : $capabilityReadiness->passkey()
                                               ? new CompletePasskeyRegistration(
                                                   currentAuthentication: $currentAuthentication,
                                                   runtime              : $this->passkeyRuntime ?? throw ConfigurationException::missingCapabilityDependency(
                                                   capability : 'passkey',
                                                   requirement: 'runtime',
                                                   buildPath  : 'AuthBuilder::ready()',
                                                   option     : 'withPasskeyRuntime()',
                                                   cause      : 'Passkey capability assembly was attempted.'
                                               ),
                                                   credentialStore      : $passkeyCredentialStore,
                                                   challengeStore       : $passkeyChallengeStore,
                                                   auditLog             : $auditLog,
                                                   clock                : $clock,
                                                   rpId                 : $this->passkeyRpId
                                               )
                                               : null,
            beginPasskeyAuthentication   : $capabilityReadiness->passkey()
                                               ? new BeginPasskeyAuthentication(
                                                   userSource     : $this->userSource,
                                                   runtime        : $this->passkeyRuntime ?? throw ConfigurationException::missingCapabilityDependency(
                                                   capability : 'passkey',
                                                   requirement: 'runtime',
                                                   buildPath  : 'AuthBuilder::ready()',
                                                   option     : 'withPasskeyRuntime()',
                                                   cause      : 'Passkey capability assembly was attempted.'
                                               ),
                                                   credentialStore: $passkeyCredentialStore,
                                                   challengeStore : $passkeyChallengeStore,
                                                   auditLog       : $auditLog,
                                                   clock          : $clock,
                                                   rpId           : $this->passkeyRpId
                                               )
                                               : null,
            completePasskeyAuthentication: $capabilityReadiness->passkey()
                                               ? new CompletePasskeyAuthentication(
                                                   runtime                 : $this->passkeyRuntime ?? throw ConfigurationException::missingCapabilityDependency(
                                                   capability : 'passkey',
                                                   requirement: 'runtime',
                                                   buildPath  : 'AuthBuilder::ready()',
                                                   option     : 'withPasskeyRuntime()',
                                                   cause      : 'Passkey capability assembly was attempted.'
                                               ),
                                                   challengeStore          : $passkeyChallengeStore,
                                                   credentialStore         : $passkeyCredentialStore,
                                                   userSource              : $this->userSource,
                                                   identity                : $identity,
                                                   projectAuthenticatedUser: $projectAuthenticatedUser,
                                                   currentAuthentication   : $currentAuthentication,
                                                   auditLog                : $auditLog,
                                                   clock                   : $clock,
                                                   rpId                    : $this->passkeyRpId
                                               )
                                               : null,
            readPasskeys                 : $capabilityReadiness->passkey()
                                               ? new ListPasskeys(
                                                   currentAuthentication: $currentAuthentication,
                                                   credentialStore      : $passkeyCredentialStore
                                               )
                                               : null,
            renamePasskey                : $capabilityReadiness->passkey()
                                               ? new RenamePasskey(
                                                   currentAuthentication: $currentAuthentication,
                                                   credentialStore      : $passkeyCredentialStore
                                               )
                                               : null,
            revokePasskey                : $capabilityReadiness->passkey()
                                               ? new RevokePasskey(
                                                   currentAuthentication: $currentAuthentication,
                                                   requireFreshMfa      : $requireFreshMfa,
                                                   credentialStore      : $passkeyCredentialStore,
                                                   auditLog             : $auditLog,
                                                   clock                : $clock
                                               )
                                               : null
        );

        $identity = new Identity(
            authentication : $authentication,
            sessions       : $session,
            account        : $account,
            recovery       : $recovery,
            verification   : $verification,
            mfa            : $mfa,
            passkey        : $passkey,
            sessionIdentity: $identity->sessionIdentity(),
            jwtIdentity    : $identity->jwtIdentity()
        );

        $oauth = new OAuth(
            registerClient           : $capabilityReadiness->oauth() ? $registerOAuthClient : null,
            approveClientRegistration: $capabilityReadiness->oauth() ? $approveOAuthClientRegistration : null,
            updateClient             : $capabilityReadiness->oauth() ? $updateOAuthClient : null,
            disableClient            : $capabilityReadiness->oauth() ? $disableOAuthClient : null,
            rotateClientSecret       : $capabilityReadiness->oauth() ? $rotateOAuthClientSecret : null,
            readClients              : $capabilityReadiness->oauth() ? $readOAuthClients : null,
            readWorkloadIdentities   : $capabilityReadiness->oauth() ? $readWorkloadIdentities : null,
            authorizeCode            : $capabilityReadiness->oauth() ? $authorizeOAuthCode : null,
            exchangeAuthorizationCode: $capabilityReadiness->oauth()
                                           ? new ExchangeAuthorizationCode(
                                               clientRegistry       : $oauthClientRegistry,
                                               codeStore            : $authorizationCodeStore,
                                               userSource           : $this->userSource,
                                               jwtIdentity          : $jwtIdentity ?? throw ConfigurationException::missingCapabilityDependency(
                                               capability : 'oauth',
                                               requirement: 'jwt_identity',
                                               buildPath  : 'AuthBuilder::ready()',
                                               option     : 'withIdentityBackends(jwtIdentity: ...) or withIdentity(new Identity(jwtIdentity: ...))',
                                               cause      : 'OAuth capability assembly was attempted.'
                                           ),
                                               refreshTokenStore    : $this->refreshTokenStore ?? throw ConfigurationException::missingCapabilityDependency(
                                               capability : 'oauth',
                                               requirement: 'refresh_token_store',
                                               buildPath  : 'AuthBuilder::ready()',
                                               option     : 'withRefreshTokenStore()',
                                               cause      : 'OAuth capability assembly was attempted.'
                                           ),
                                               auditLog             : $auditLog,
                                               clock                : $clock,
                                               currentAuthentication: $currentAuthentication,
                                               oidcProvider         : $this->oidcProvider
                                           )
                                           : null,
            exchangeClientCredentials: $capabilityReadiness->oauth()
                                           ? new ExchangeClientCredentials(
                                               clientRegistry: $oauthClientRegistry,
                                               jwtIdentity   : $jwtIdentity,
                                               auditLog      : $auditLog,
                                               clock         : $clock
                                           )
                                           : null,
            exchangeRefreshToken     : $capabilityReadiness->oauth()
                                           ? new ExchangeRefreshToken(
                                               clientRegistry   : $oauthClientRegistry,
                                               refreshTokenStore: $this->refreshTokenStore ?? throw ConfigurationException::missingCapabilityDependency(
                                               capability : 'oauth',
                                               requirement: 'refresh_token_store',
                                               buildPath  : 'AuthBuilder::ready()',
                                               option     : 'withRefreshTokenStore()',
                                               cause      : 'OAuth capability assembly was attempted.'
                                           ),
                                               userSource       : $this->userSource,
                                               jwtIdentity      : $jwtIdentity,
                                               auditLog         : $auditLog,
                                               clock            : $clock,
                                               riskEngine       : $riskEngine
                                           )
                                           : null,
            revokeToken              : $capabilityReadiness->oauth()
                                           ? new RevokeToken(
                                               clientRegistry   : $oauthClientRegistry,
                                               refreshTokenStore: $this->refreshTokenStore ?? throw ConfigurationException::missingCapabilityDependency(
                                               capability : 'oauth',
                                               requirement: 'refresh_token_store',
                                               buildPath  : 'AuthBuilder::ready()',
                                               option     : 'withRefreshTokenStore()',
                                               cause      : 'OAuth capability assembly was attempted.'
                                           ),
                                               jwtIdentity      : $jwtIdentity,
                                               auditLog         : $auditLog,
                                               clock            : $clock
                                           )
                                           : null,
            introspectToken          : $capabilityReadiness->oauth()
                                           ? new IntrospectToken(
                                               clientRegistry: $oauthClientRegistry,
                                               jwtIdentity   : $jwtIdentity,
                                               auditLog      : $auditLog,
                                               clock         : $clock
                                           )
                                           : null
        );

        $oidc = new OpenIDConnect(
            readProviderMetadata    : $capabilityReadiness->oauth() ? $readOidcProviderMetadata : null,
            readJsonWebKeySet       : $capabilityReadiness->oauth() ? $readOidcJsonWebKeySet : null,
            readUserInfo            : $capabilityReadiness->oauth() ? $readOidcUserInfo : null,
            pushAuthorizationRequest: $capabilityReadiness->oauth() ? $pushOidcAuthorizationRequest : null,
            logout                  : $capabilityReadiness->oauth() ? $oidcLogout : null,
            buildJarmResponse       : $capabilityReadiness->oauth() ? $buildOidcJarmResponse : null
        );

        $sso = new SingleSignOn(
            registerConnection      : $capabilityReadiness->federation()
                                          ? new RegisterFederationConnection(
                                            connectionStore          : $federationConnectionStore,
                                            groupRoleMappingValidator: $groupRoleMappingValidator,
                                            auditLog                 : $auditLog,
                                            clock                    : $clock
                                        )
                                          : null,
            readConnections         : $capabilityReadiness->federation()
                                          ? new ReadFederationConnections(connectionStore: $federationConnectionStore)
                                          : null,
            verifyDomain            : $capabilityReadiness->federation()
                                          ? new VerifyFederationDomain(
                                              connectionStore: $federationConnectionStore,
                                              auditLog       : $auditLog,
                                              clock          : $clock
                                          )
                                          : null,
            syncMetadata            : $capabilityReadiness->federation() && $this->federationRuntime instanceof FederationMetadataRuntimeInterface
                                          ? new SyncFederationMetadata(
                                              connectionStore: $federationConnectionStore,
                                              runtime        : $this->federationRuntime,
                                              auditLog       : $auditLog,
                                              clock          : $clock
                                          )
                                          : null,
            checkConnectionHealth   : $capabilityReadiness->federation() && $this->federationRuntime instanceof FederationHealthCheckInterface
                                          ? new CheckFederationConnectionHealth(
                                              connectionStore: $federationConnectionStore,
                                              runtime        : $this->federationRuntime,
                                              auditLog       : $auditLog,
                                              clock          : $clock
                                          )
                                          : null,
            evaluateBreakGlassBypass: $capabilityReadiness->federation()
                                          ? new EvaluateFederationBreakGlassBypass(
                                              connectionStore: $federationConnectionStore,
                                              auditLog       : $auditLog,
                                              clock          : $clock
                                          )
                                          : null,
            discoverConnection      : $capabilityReadiness->federation()
                                          ? new DiscoverFederationConnection(connectionStore: $federationConnectionStore)
                                          : null,
            startFederatedLogin     : $capabilityReadiness->federation()
                                          ? new StartFederatedLogin(
                                              connectionStore: $federationConnectionStore,
                                              runtime        : $this->federationRuntime ?? throw ConfigurationException::missingCapabilityDependency(
                                              capability : 'federation',
                                              requirement: 'runtime',
                                              buildPath  : 'AuthBuilder::ready()',
                                              option     : 'withFederationRuntime()',
                                              cause      : 'Federation capability assembly was attempted.'
                                          ),
                                              auditLog       : $auditLog,
                                              clock          : $clock
                                          )
                                          : null,
            completeFederatedLogin  : $capabilityReadiness->federation()
                                          ? new CompleteFederatedLogin(
                                              connectionStore         : $federationConnectionStore,
                                              runtime                 : $this->federationRuntime ?? throw ConfigurationException::missingCapabilityDependency(
                                              capability : 'federation',
                                              requirement: 'runtime',
                                              buildPath  : 'AuthBuilder::ready()',
                                              option     : 'withFederationRuntime()',
                                              cause      : 'Federation capability assembly was attempted.'
                                          ),
                                              linkStore               : $federatedIdentityLinkStore,
                                              userSource              : $this->userSource,
                                              identity                : $identity,
                                              projectAuthenticatedUser: $projectAuthenticatedUser,
                                              currentAuthentication   : $currentAuthentication,
                                              passwordHasher          : $passwordHasher,
                                              idGenerator             : $this->idGenerator ?? new IdGenerator(),
                                              auditLog                : $auditLog,
                                              clock                   : $clock,
                                              riskEngine              : $riskEngine,
                                              lifecycle               : $lifecycle
                                          )
                                          : null
        );

        $externalIdentity = new ExternalIdentity(
            oauth: $oauth,
            oidc : $oidc,
            sso  : $sso
        );

        $scim = new SCIM(
            registerScimDirectory     : $capabilityReadiness->scim()
                                            ? new RegisterScimDirectory(
                                              directoryStore           : $scimDirectoryStore,
                                              passwordHasher           : $passwordHasher,
                                              groupRoleMappingValidator: new GroupRoleMappingValidator(),
                                              auditLog                 : $auditLog,
                                              clock                    : $clock
                                          )
                                            : null,
            readScimDirectories       : $capabilityReadiness->scim() ? new ReadScimDirectories(directoryStore: $scimDirectoryStore) : null,
            rotateScimToken           : $capabilityReadiness->scim()
                                            ? new RotateScimToken(
                                                directoryStore : $scimDirectoryStore,
                                                passwordHasher : $passwordHasher,
                                                auditLog       : $auditLog,
                                                clock          : $clock,
                                                attemptThrottle: $scimThrottle
                                            )
                                            : null,
            markScimDirectoryOutage   : $capabilityReadiness->scim()
                                            ? new MarkScimDirectoryOutage(
                                                directoryStore: $scimDirectoryStore,
                                                auditLog      : $auditLog,
                                                clock         : $clock
                                            )
                                            : null,
            recoverScimDirectoryOutage: $capabilityReadiness->scim()
                                            ? new RecoverScimDirectoryOutage(
                                                directoryStore: $scimDirectoryStore,
                                                auditLog      : $auditLog,
                                                clock         : $clock
                                            )
                                            : null,
            provisionScimUser         : $provisionScimUser,
            deleteScimUser            : $capabilityReadiness->scim()
                                            ? new DeleteScimUser(
                                                userSource     : $provisionableUserSource,
                                                directoryStore : $scimDirectoryStore,
                                                identityStore  : $scimProvisionedIdentityStore,
                                                auditLog       : $auditLog,
                                                clock          : $clock,
                                                lifecycle      : $lifecycle,
                                                attemptThrottle: $scimThrottle
                                            )
                                            : null,
            readScimUsers             : $readScimUsers,
            readScimGroups            : $readScimGroups,
            syncScimGroups            : $capabilityReadiness->scim() && $provisionScimUser !== null
                                            ? new SyncScimGroups(
                                                directoryStore   : $scimDirectoryStore,
                                                identityStore    : $scimProvisionedIdentityStore,
                                                userSource       : $this->userSource,
                                                provisionScimUser: $provisionScimUser
                                            )
                                            : null,
            runScimBulk               : $runScimBulk
        );

        $provisioning = new Provisioning(
            suspendUser    : $provisionableUserSource !== null
                                 ? new SuspendUser(
                                   userSource           : $provisionableUserSource,
                                   requireAdminElevation: $requireAdminElevation,
                                   auditLog             : $auditLog,
                                   clock                : $clock,
                                   sessionRegistry      : $this->sessionRegistry,
                                   refreshTokenStore    : $this->refreshTokenStore,
                                   adminElevationStore  : $adminElevationStore,
                                   lifecycle            : $lifecycle
                               )
                                 : null,
            reactivateUser : $provisionableUserSource !== null
                                 ? new ReactivateUser(
                                     userSource           : $provisionableUserSource,
                                     requireAdminElevation: $requireAdminElevation,
                                     auditLog             : $auditLog,
                                     clock                : $clock,
                                     lifecycle            : $lifecycle
                                 )
                                 : null,
            deprovisionUser: $provisionableUserSource !== null
                                 ? new DeprovisionUser(
                                     userSource           : $provisionableUserSource,
                                     requireAdminElevation: $requireAdminElevation,
                                     auditLog             : $auditLog,
                                     clock                : $clock,
                                     sessionRegistry      : $this->sessionRegistry,
                                     refreshTokenStore    : $this->refreshTokenStore,
                                     adminElevationStore  : $adminElevationStore,
                                     lifecycle            : $lifecycle
                                 )
                                 : null
        );

        $identitySync = new IdentitySync(
            scim        : $scim,
            provisioning: $provisioning
        );

        $tenants = new Tenants(
            createTenant           : $createTenant,
            readTenants            : $readTenants,
            inviteTenantMember     : $inviteTenantMember,
            acceptTenantInvite     : $acceptTenantInvite,
            readTenantMembers      : $readTenantMembers,
            removeTenantMember     : $removeTenantMember,
            suspendTenantMember    : $suspendTenantMember,
            transferTenantOwnership: $transferTenantOwnership
        );

        $security = new Security(
            readTenantSecurityConfiguration : $readTenantSecurityConfiguration,
            readTenantSecurityChangeRequest : $readTenantSecurityChangeRequest,
            readTenantSecurityChangeRequests: $readTenantSecurityChangeRequests,
            beginTenantSecurityChange       : $beginTenantSecurityChange,
            approveTenantSecurityChange     : $approveTenantSecurityChange,
            applyTenantSecurityChange       : $applyTenantSecurityChange,
            rollbackTenantSecurityChange    : $rollbackTenantSecurityChange
        );

        $tenancy = new Tenancy(
            tenants : $tenants,
            security: $security
        );

        $diagnostics = new Diagnostics(
            authIssueExplainer: new AuthIssueExplainer()
        );

        return new Auth(
            access          : new Access(
                                  authenticateRequest  : $authenticateRequest,
                                  currentAuthentication: $currentAuthentication,
                                  checkAuthentication  : $checkAuthentication,
                                  readCurrentUser      : $readCurrentUser,
                                  access               : $authorization,
                                  beginAdminElevation  : new BeginAdminElevation(
                                                             currentAuthentication    : $currentAuthentication,
                                                             requireFreshMfa          : $requireFreshMfa,
                                                             elevationStore           : $adminElevationStore,
                                                             auditLog                 : $auditLog,
                                                             clock                    : $clock,
                                                             phishingResistantRequired: $this->adminPhishingResistantRequired
                                                         ),
                                  endAdminElevation    : new EndAdminElevation(
                                                             currentAuthentication: $currentAuthentication,
                                                             elevationStore       : $adminElevationStore,
                                                             auditLog             : $auditLog,
                                                             clock                : $clock
                                                         ),
                                  requireAdminElevation: $requireAdminElevation,
                                  assessCurrentRisk    : $assessCurrentRisk,
                                  readRiskSignals      : $readRiskSignals
                              ),
            diagnostics     : $diagnostics,
            identity        : $identity,
            externalIdentity: $externalIdentity,
            identitySync    : $identitySync,
            tenancy         : $tenancy
        );
    }

    private function capabilityRequests() : AuthCapabilityRequests
    {
        return AuthCapabilityRequests::from(
            enterpriseMode                        : $this->enterpriseMode,
            oauthClientRegistryConfigured         : $this->oauthClientRegistry !== null,
            authorizationCodeStoreConfigured      : $this->authorizationCodeStore !== null,
            oidcProviderConfigured                : $this->oidcProvider !== null,
            oidcRequestObjectStoreConfigured      : $this->oidcRequestObjectStore !== null,
            passkeyCredentialStoreConfigured      : $this->passkeyCredentialStore !== null,
            passkeyChallengeStoreConfigured       : $this->passkeyChallengeStore !== null,
            passkeyRelyingPartyCustomized         : $this->passkeyRpId !== 'localhost' || $this->passkeyRpName !== 'Avax Auth',
            federationConnectionStoreConfigured   : $this->federationConnectionStore !== null,
            federatedIdentityLinkStoreConfigured  : $this->federatedIdentityLinkStore !== null,
            scimDirectoryStoreConfigured          : $this->scimDirectoryStore !== null,
            scimProvisionedIdentityStoreConfigured: $this->scimProvisionedIdentityStore !== null
        );
    }
}
