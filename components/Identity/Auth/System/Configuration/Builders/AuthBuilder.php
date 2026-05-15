<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Builders;

use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Access\System\Capabilities\Access;
use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\InMemoryAttemptThrottleStore;
use Avax\Components\Identity\Access\System\Capabilities\Facades\Authorization;
use Avax\Components\Identity\Access\System\Capabilities\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\RequireAuthentication;
use Avax\Components\Identity\Access\System\Capabilities\RequirePermission\RequirePermission;
use Avax\Components\Identity\Access\System\Capabilities\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Components\Identity\Access\System\Capabilities\RequireResourceOwner\RequireResourceOwner;
use Avax\Components\Identity\Access\System\Capabilities\RequireRole\RequireRole;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Runtime\AssessCurrentRisk\AssessCurrentRisk;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Runtime\ReadRiskSignals\ReadRiskSignals;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\InMemoryKnownAuthenticationEnvironmentStore;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\InMemoryRiskSignalStore;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\CorrelatingAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\NullAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Diagnostics;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Explainability\AuthIssueExplainer;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Account;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Authentication;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Recovery;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Verification;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\LogoutAllSessions\LogoutAllSessions;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\ReadActiveSessions\ReadActiveSessions;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\RevokeSession\RevokeSession;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Sessions;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\IdentitySync;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\InMemoryLifecycleStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Provisioning\Provisioning;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\InMemoryScimDirectoryStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\InMemoryScimProvisionedIdentityStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimProvisionedIdentityStoreInterface;
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
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Mfa;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\GenerateBackupCodes;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\RegenerateBackupCodes;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\VerifyBackupCode;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Disable\DisableMfa;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\CancelMfaEnrollment;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\ConfirmMfaEnrollment;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\StartMfaEnrollment;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\InMemoryAttemptLimitStorage;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\LimitMfaAttempts;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\ConfirmMfaRecovery;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\StartMfaRecovery;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\InMemoryMfaStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp\Totp;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp\TotpInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\InMemoryMfaChallengeStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\MfaChallengeStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\StartMfaChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\VerifyMfaChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Passkey;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\InMemoryPasskeyChallengeStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\InMemoryPasskeyCredentialStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyChallengeStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyRuntimeInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthentication;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\BeginRegistration\BeginPasskeyRegistration;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthentication;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistration;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\ListPasskeys\ListPasskeys;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\RenamePasskey\RenamePasskey;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\RevokePasskey\RevokePasskey;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentity;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\AuthorizationCodeStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\InMemoryAuthorizationCodeStore;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\InMemoryOAuthClientRegistry;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\OAuth;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ApproveClientRegistration\ApproveClientRegistration;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\AuthorizeCode\AuthorizeCode;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\DisableClient\DisableClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeAuthorizationCode\ExchangeAuthorizationCode;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeClientCredentials\ExchangeClientCredentials;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeRefreshToken\ExchangeRefreshToken;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\IntrospectToken\IntrospectToken;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ReadClients\ReadClients;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ReadWorkloadIdentities\ReadWorkloadIdentities;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RegisterClient\RegisterClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RevokeToken\RevokeToken;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RotateClientSecret\RotateClientSecret;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\UpdateClient\UpdateClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\OpenIDConnect;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\InMemoryOidcRequestObjectStore;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcRequestObjectStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\BackChannelLogout\BackChannelLogout;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\FrontChannelLogout\FrontChannelLogout;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\JarmResponse\BuildJarmResponse;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout\Logout as OidcLogout;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\PushAuthorizationRequest\PushAuthorizationRequest;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadJsonWebKeySet\ReadOidcJsonWebKeySet;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadProviderMetadata\ReadOidcProviderMetadata;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadUserInfo\ReadOidcUserInfo;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ValidateRequestObject\ValidateRequestObject;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederatedIdentityLinkStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationHealthCheckInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationMetadataRuntimeInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationRuntimeInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\GroupRoleMappingValidator;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\InMemoryFederatedIdentityLinkStore;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\InMemoryFederationConnectionStore;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\CheckHealth\CheckFederationConnectionHealth;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\CompleteFederatedLogin\CompleteFederatedLogin;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\DiscoverConnection\DiscoverFederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\EvaluateBreakGlass\EvaluateFederationBreakGlassBypass;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\ReadConnections\ReadFederationConnections;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\RegisterConnection\RegisterFederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\StartFederatedLogin\StartFederatedLogin;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\SyncMetadata\SyncFederationMetadata;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\VerifyDomain\VerifyFederationDomain;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\SingleSignOn;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\InMemoryAdminElevationStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\BeginAdminElevation\BeginAdminElevation;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\EndAdminElevation\EndAdminElevation;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\InMemoryTenantStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\AcceptInvite\AcceptTenantInvite;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\CreateTenant\CreateTenant;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\InviteMember\InviteTenantMember;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\ReadMembers\ReadTenantMembers;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\ReadTenants\ReadTenants;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\RemoveMember\RemoveTenantMember;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\SuspendMember\SuspendTenantMember;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\TransferOwnership\TransferTenantOwnership;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ApplyChange\ApplyTenantSecurityChange;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ApproveChange\ApproveTenantSecurityChange;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\BeginChange\BeginTenantSecurityChange;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ReadChangeRequest\ReadTenantSecurityChangeRequest;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ReadChangeRequests\ReadTenantSecurityChangeRequests;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\ReadConfiguration\ReadTenantSecurityConfiguration;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\RollbackChange\RollbackTenantSecurityChange;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\InMemoryTenantSecurityChangeRequestStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\InMemoryTenantSecurityConfigurationStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\Security;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequestStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityConfigurationStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Tenancy;
use Avax\Components\Identity\Tenancy\System\Capabilities\Tenants\Tenants;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Flow\RefreshAuthentication;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use SensitiveParameter;

/**
 * Fluent builder for creating Auth system instances.
 *
 * Capability: Composition root for the system.
 */
final class AuthBuilder
{
    private UserSourceInterface|null $userSource = null;

    private IdentityInterface|null $identity = null;

    private LoginRateLimit|null $loginRateLimit = null;

    private PasswordHasher|null $passwordHasher = null;

    private IdGeneratorInterface|null $idGenerator = null;

    private AuditLogInterface|null $auditLog = null;

    private string|null $auditCorrelationId = null;

    private EmailVerificationStateStoreInterface|null $emailVerificationStateStore = null;

    private MfaStoreInterface|null $mfaStore = null;

    private RefreshTokenStoreInterface|null $refreshTokenStore = null;

    private PasswordResetStoreInterface|null $passwordResetStore = null;

    private EmailVerificationStoreInterface|null $emailVerificationStore = null;

    private EmailChangeStoreInterface|null $emailChangeStore = null;

    private MfaChallengeStoreInterface|null $mfaChallengeStore = null;

    private TotpInterface|null $totp = null;

    private LimitMfaAttempts|null $limitMfaAttempts = null;

    private AttemptThrottle|null $passwordResetThrottle = null;

    private AttemptThrottle|null $mfaRecoveryThrottle = null;

    private AttemptThrottle|null $scimThrottle = null;

    private Clock|null $clock = null;

    private SessionRegistryInterface|null $sessionRegistry = null;

    private OAuthClientRegistryInterface|null $oAuthClientRegistry = null;

    private AuthorizationCodeStoreInterface|null $authorizationCodeStore = null;

    private LifecycleStoreInterface|null $lifecycleStore = null;

    private AdminElevationStoreInterface|null $adminElevationStore = null;

    private DeterministicRiskEngine|null $deterministicRiskEngine = null;

    private PasskeyRuntimeInterface|null $passkeyRuntime = null;

    private PasskeyCredentialStoreInterface|null $passkeyCredentialStore = null;

    private PasskeyChallengeStoreInterface|null $passkeyChallengeStore = null;

    private FederationRuntimeInterface|null $federationRuntime = null;

    private FederationConnectionStoreInterface|null $federationConnectionStore = null;

    private FederatedIdentityLinkStoreInterface|null $federatedIdentityLinkStore = null;

    private OidcProviderInterface|null $oidcProvider = null;

    private OidcRequestObjectStoreInterface|null $oidcRequestObjectStore = null;

    private ScimDirectoryStoreInterface|null $scimDirectoryStore = null;

    private ScimProvisionedIdentityStoreInterface|null $scimProvisionedIdentityStore = null;

    private TenantStoreInterface|null $tenantStore = null;

    private TenantSecurityConfigurationStoreInterface|null $tenantSecurityConfigurationStore = null;

    private TenantSecurityChangeRequestStoreInterface|null $tenantSecurityChangeRequestStore = null;

    private string $mfaIssuer = 'Avax Auth';

    private string $passkeyRpId = 'localhost';

    private string $passkeyRpName = 'Avax Auth';

    private bool $adminPhishingResistantRequired = false;

    private bool $enterpriseMode = false;

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
        #[SensitiveParameter]
        ?SessionIdentityInterface $sessionIdentity = null,
        #[SensitiveParameter]
        ?JwtIdentityInterface     $jwtIdentity = null,
    ) : self
    {
        $this->identity = Identity::fromBackends(
            sessionIdentity: $sessionIdentity,
            jwtIdentity    : $jwtIdentity,
        );

        return $this;
    }

    /**
     * Enable rate limiting for login.
     */
    public function protectFromBruteForce(LoginRateLimit $loginRateLimit) : self
    {
        $this->loginRateLimit = $loginRateLimit;

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

    public function withEmailVerificationState(#[SensitiveParameter] EmailVerificationStateStoreInterface $emailVerificationStateStore) : self
    {
        $this->emailVerificationStateStore = $emailVerificationStateStore;

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

    public function withPasswordResetThrottle(#[SensitiveParameter] AttemptThrottle $attemptThrottle) : self
    {
        $this->passwordResetThrottle = $attemptThrottle;

        return $this;
    }

    public function withMfaRecoveryThrottle(AttemptThrottle $attemptThrottle) : self
    {
        $this->mfaRecoveryThrottle = $attemptThrottle;

        return $this;
    }

    public function withScimThrottle(AttemptThrottle $attemptThrottle) : self
    {
        $this->scimThrottle = $attemptThrottle;

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
        $this->oAuthClientRegistry = $oauthClientRegistry;

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

    public function withRiskEngine(DeterministicRiskEngine $deterministicRiskEngine) : self
    {
        $this->deterministicRiskEngine = $deterministicRiskEngine;

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

    public function withMfaAttemptLimit(LimitMfaAttempts $limitMfaAttempts) : self
    {
        $this->limitMfaAttempts = $limitMfaAttempts;

        return $this;
    }

    public function withMfaIssuer(string $mfaIssuer) : self
    {
        $this->mfaIssuer = $mfaIssuer;

        return $this;
    }

    /**
     * Configure the builder with default dependencies from the DI container.
     *
     * This eliminates ?? new fallback patterns by resolving pre-registered
     * default implementations from the container. If a dependency has already
     * been configured via a withXxx() method, the container value is ignored
     * (explicit configuration always wins).
     */
    public function withContainer(ContainerInterface $container) : self
    {
        $this->clock ??= $container->get(Clock::class);
        $this->idGenerator ??= $container->get(IdGenerator::class);
        $this->passwordHasher ??= $container->get(PasswordHasher::class);
        $this->auditLog ??= $container->get(NullAuditLog::class);
        $this->oAuthClientRegistry ??= $container->get(InMemoryOAuthClientRegistry::class);
        $this->authorizationCodeStore ??= $container->get(InMemoryAuthorizationCodeStore::class);
        $this->lifecycleStore ??= $container->get(InMemoryLifecycleStore::class);
        $this->adminElevationStore ??= $container->get(InMemoryAdminElevationStore::class);
        $this->deterministicRiskEngine ??= $container->get(DeterministicRiskEngine::class);
        $this->passkeyCredentialStore ??= $container->get(InMemoryPasskeyCredentialStore::class);
        $this->passkeyChallengeStore ??= $container->get(InMemoryPasskeyChallengeStore::class);
        $this->federationConnectionStore ??= $container->get(InMemoryFederationConnectionStore::class);
        $this->federatedIdentityLinkStore ??= $container->get(InMemoryFederatedIdentityLinkStore::class);
        $this->passwordResetStore ??= $container->get(InMemoryPasswordResetStore::class);
        $this->emailVerificationStore ??= $container->get(InMemoryEmailVerificationStore::class);
        $this->emailChangeStore ??= $container->get(InMemoryEmailChangeStore::class);
        $this->emailVerificationStateStore ??= $container->get(InMemoryEmailVerificationStateStore::class);
        $this->mfaStore ??= $container->get(InMemoryMfaStore::class);
        $this->mfaChallengeStore ??= $container->get(InMemoryMfaChallengeStore::class);
        $this->totp ??= $container->get(Totp::class);
        $this->limitMfaAttempts ??= $container->get(LimitMfaAttempts::class);
        $this->oidcRequestObjectStore ??= $container->get(InMemoryOidcRequestObjectStore::class);
        $this->scimDirectoryStore ??= $container->get(InMemoryScimDirectoryStore::class);
        $this->scimProvisionedIdentityStore ??= $container->get(InMemoryScimProvisionedIdentityStore::class);
        $this->tenantStore ??= $container->get(InMemoryTenantStore::class);
        $this->tenantSecurityConfigurationStore ??= $container->get(InMemoryTenantSecurityConfigurationStore::class);
        $this->tenantSecurityChangeRequestStore ??= $container->get(InMemoryTenantSecurityChangeRequestStore::class);

        // Named throttle bindings
        $this->passwordResetThrottle ??= $container->get('auth.throttle.password_reset');
        $this->mfaRecoveryThrottle ??= $container->get('auth.throttle.mfa_recovery');
        $this->scimThrottle ??= $container->get('auth.throttle.scim');

        return $this;
    }

    /**
     * Build the final Auth instance.
     */
    public function ready() : Auth
    {
        $sessionRegistry       = $this->sessionRegistry;
        $refreshTokenStore     = $this->refreshTokenStore;
        AuthBootstrapValidator::validate(
            userSource: $this->userSource ?? throw ConfigurationException::missingDependency("UserSource", "forUser()"),
            identity         : $this->identity,
            sessionRegistry  : $this->sessionRegistry,
            refreshTokenStore: $this->refreshTokenStore,
            passkeyRuntime   : $this->passkeyRuntime,
            federationRuntime: $this->federationRuntime,
            oidcProvider     : $this->oidcProvider,
            requests         : $this->capabilityRequests(),
        );

        $identity       = $this->identity;
        $passwordHasher = $this->passwordHasher ?? throw ConfigurationException::missingDependency('PasswordHasher', 'withHasher() or AuthServiceProvider');
        $auditLog       = $this->auditLog ?? throw ConfigurationException::missingDependency('AuditLog', 'withAuditLog() or AuthServiceProvider');

        if ($this->auditCorrelationId !== null) {
            $auditLog = new CorrelatingAuditLog(
                correlationId: $this->auditCorrelationId,
                inner        : $auditLog,
            );
        }

        $clock                                  = $this->clock ?? throw ConfigurationException::missingDependency('Clock', 'withClock() or AuthServiceProvider');
        $oauthClientRegistry                    = $this->oAuthClientRegistry ?? throw ConfigurationException::missingDependency('OAuthClientRegistry', 'withOAuthClientRegistry() or AuthServiceProvider');
        $authorizationCodeStore                 = $this->authorizationCodeStore ?? throw ConfigurationException::missingDependency('AuthorizationCodeStore', 'withAuthorizationCodeStore() or AuthServiceProvider');
        $lifecycleStore                         = $this->lifecycleStore ?? throw ConfigurationException::missingDependency('LifecycleStore', 'withLifecycleStore() or AuthServiceProvider');
        $adminElevationStore                    = $this->adminElevationStore ?? throw ConfigurationException::missingDependency('AdminElevationStore', 'withAdminElevationStore() or AuthServiceProvider');
        $riskEngine                             = $this->deterministicRiskEngine ?? throw ConfigurationException::missingDependency('DeterministicRiskEngine', 'withRiskEngine() or AuthServiceProvider');
        $passkeyCredentialStore                 = $this->passkeyCredentialStore ?? throw ConfigurationException::missingDependency('PasskeyCredentialStore', 'withPasskeyCredentialStore() or AuthServiceProvider');
        $passkeyChallengeStore                  = $this->passkeyChallengeStore ?? throw ConfigurationException::missingDependency('PasskeyChallengeStore', 'withPasskeyChallengeStore() or AuthServiceProvider');
        $federationConnectionStore              = $this->federationConnectionStore ?? throw ConfigurationException::missingDependency('FederationConnectionStore', 'withFederationConnectionStore() or AuthServiceProvider');
        $federatedIdentityLinkStore             = $this->federatedIdentityLinkStore ?? throw ConfigurationException::missingDependency('FederatedIdentityLinkStore', 'withFederatedIdentityLinkStore() or AuthServiceProvider');
        $groupRoleMappingValidator              = new GroupRoleMappingValidator();
        $passwordResetStore                     = $this->passwordResetStore ?? throw ConfigurationException::missingDependency('PasswordResetStore', 'withPasswordResetStore() or AuthServiceProvider');
        $emailVerificationStore                 = $this->emailVerificationStore ?? throw ConfigurationException::missingDependency('EmailVerificationStore', 'withEmailVerificationStore() or AuthServiceProvider');
        $emailChangeStore                       = $this->emailChangeStore ?? throw ConfigurationException::missingDependency('EmailChangeStore', 'withEmailChangeStore() or AuthServiceProvider');
        $emailVerificationState                 = $this->emailVerificationStateStore ?? throw ConfigurationException::missingDependency('EmailVerificationStateStore', 'withEmailVerificationState() or AuthServiceProvider');
        $mfaStore                               = $this->mfaStore ?? throw ConfigurationException::missingDependency('MfaStore', 'withMfaStore() or AuthServiceProvider');
        $mfaChallengeStore                      = $this->mfaChallengeStore ?? throw ConfigurationException::missingDependency('MfaChallengeStore', 'withMfaChallengeStore() or AuthServiceProvider');
        $totp                                   = $this->totp ?? throw ConfigurationException::missingDependency('Totp', 'usingTotp() or AuthServiceProvider');
        $mfaAttemptLimit                        = $this->limitMfaAttempts ?? throw ConfigurationException::missingDependency('LimitMfaAttempts', 'withMfaAttemptLimit() or AuthServiceProvider');
        $passwordResetThrottle                  = $this->passwordResetThrottle ?? throw ConfigurationException::missingDependency('PasswordResetThrottle', 'withPasswordResetThrottle() or AuthServiceProvider');
        $mfaRecoveryThrottle                    = $this->mfaRecoveryThrottle ?? throw ConfigurationException::missingDependency('MfaRecoveryThrottle', 'withMfaRecoveryThrottle() or AuthServiceProvider');
        $scimThrottle                           = $this->scimThrottle ?? throw ConfigurationException::missingDependency('ScimThrottle', 'withScimThrottle() or AuthServiceProvider');
        $projectAuthenticatedUser               = new ProjectAuthenticatedUser(
            mfaStore              : $mfaStore,
            emailVerificationState: $emailVerificationState,
        );
        $currentAuthentication                  = new CurrentAuthentication();
        $requireFreshMfa                        = new RequireFreshMfa(
            currentAuthentication: $currentAuthentication,
            clock                : $clock,
        );
        $generateBackupCodes                    = new GenerateBackupCodes(
            passwordHasher: $passwordHasher,
            clock         : $clock,
        );
        $verifyBackupCode                       = new VerifyBackupCode(
            mfaStore      : $mfaStore,
            passwordHasher: $passwordHasher,
            auditLog      : $auditLog,
            clock         : $clock,
        );
        $startMfaChallenge                      = new StartMfaChallenge(
            currentAuthentication: $currentAuthentication,
            auditLog             : $auditLog,
            clock                : $clock,
            mfaStore             : $mfaStore,
            challengeStore       : $mfaChallengeStore,
        );
        $authenticateRequest                    = new AuthenticateRequest(
            currentAuthentication   : $currentAuthentication,
            projectAuthenticatedUser: $projectAuthenticatedUser,
            userSource              : $this->userSource,
            auditLog                : $auditLog,
            clock                   : $clock,
            sessionIdentity         : $identity->sessionIdentity(),
            jwtIdentity             : $identity->jwtIdentity(),
        );
        $readCurrentUser                        = new ReadCurrentUser(
            currentAuthentication: $currentAuthentication,
        );
        $checkAuthentication                    = new CheckAuthentication(
            currentAuthentication: $currentAuthentication,
        );
        $registerClient                         = new RegisterClient(
            auditLog      : $auditLog,
            clock         : $clock,
            clientRegistry: $oauthClientRegistry,
        );
        $approveClientRegistration              = new ApproveClientRegistration(
            auditLog      : $auditLog,
            clock         : $clock,
            clientRegistry: $oauthClientRegistry,
        );
        $updateClient                           = new UpdateClient(
            auditLog      : $auditLog,
            clock         : $clock,
            clientRegistry: $oauthClientRegistry,
        );
        $disableClient                          = new DisableClient(
            auditLog      : $auditLog,
            clock         : $clock,
            clientRegistry: $oauthClientRegistry,
        );
        $rotateClientSecret                     = new RotateClientSecret(
            auditLog      : $auditLog,
            clock         : $clock,
            clientRegistry: $oauthClientRegistry,
        );
        $readClients                            = new ReadClients(
            clientRegistry: $oauthClientRegistry,
        );
        $readWorkloadIdentities                 = new ReadWorkloadIdentities(
            clientRegistry: $oauthClientRegistry,
        );
        $readOidcProviderMetadata               = $this->oidcProvider instanceof OidcProviderInterface
            ? new ReadOidcProviderMetadata(oidcProvider: $this->oidcProvider)
            : null;
        $readOidcJsonWebKeySet                  = $this->oidcProvider instanceof OidcProviderInterface
            ? new ReadOidcJsonWebKeySet(oidcProvider: $this->oidcProvider)
            : null;
        $jwtIdentity                            = $identity->jwtIdentity();
        $readOidcUserInfo                       = $jwtIdentity instanceof JwtIdentityInterface && $this->oidcProvider instanceof OidcProviderInterface
            ? new ReadOidcUserInfo(jwtIdentity: $jwtIdentity, oidcProvider: $this->oidcProvider)
            : null;
        $oidcRequestObjectStore                 = $this->oidcProvider instanceof OidcProviderInterface
            ? ($this->oidcRequestObjectStore ?? throw ConfigurationException::missingDependency('OidcRequestObjectStore', 'withOidcRequestObjectStore() or AuthServiceProvider'))
            : null;
        $pushOidcAuthorizationRequest           = $oidcRequestObjectStore instanceof OidcRequestObjectStoreInterface
            ? new PushAuthorizationRequest(
                auditLog          : $auditLog,
                clock             : $clock,
                oidcProvider      : $this->oidcProvider,
                requestObjectStore: $oidcRequestObjectStore,
                clientRegistry    : $oauthClientRegistry,
            )
            : null;
        $validateRequestObject                  = $oidcRequestObjectStore instanceof OidcRequestObjectStoreInterface
            ? new ValidateRequestObject(requestObjectStore: $oidcRequestObjectStore, clientRegistry: $oauthClientRegistry)
            : null;
        $oidcLogout                             = $this->oidcProvider instanceof OidcProviderInterface
            ? new OidcLogout(
                frontChannelLogout: new FrontChannelLogout(
                                        currentAuthentication: $currentAuthentication,
                                        identity             : $identity,
                                        auditLog             : $auditLog,
                                        clock                : $clock,
                                        sessionRegistry      : $this->sessionRegistry,
                                        refreshTokenStore    : $this->refreshTokenStore,
                                        oidcProvider         : $this->oidcProvider,
                                        clientRegistry       : $oauthClientRegistry,
                                    ),
                backChannelLogout : new BackChannelLogout(
                                        currentAuthentication: $currentAuthentication,
                                        identity             : $identity,
                                        auditLog             : $auditLog,
                                        clock                : $clock,
                                        sessionRegistry      : $this->sessionRegistry,
                                        refreshTokenStore    : $this->refreshTokenStore,
                                        oidcProvider         : $this->oidcProvider,
                                        clientRegistry       : $oauthClientRegistry,
                                    ),
            )
            : null;
        $buildOidcJarmResponse                  = $this->oidcProvider instanceof OidcProviderInterface
            ? new BuildJarmResponse(
                oidcProvider: $this->oidcProvider,
                clock       : $clock,
            )
            : null;
        $authorizeCode                          = new AuthorizeCode(
            currentAuthentication : $currentAuthentication,
            userSource            : $this->userSource,
            auditLog              : $auditLog,
            clock                 : $clock,
            oidcProvider          : $this->oidcProvider,
            clientRegistry        : $oauthClientRegistry,
            codeStore             : $authorizationCodeStore,
            requestObjectValidator: $validateRequestObject,
        );
        $requireAdminElevation                  = new RequireAdminElevation(
            currentAuthentication: $currentAuthentication,
            clock                : $clock,
            elevationStore       : $adminElevationStore,
        );
        $requireAuthentication                  = new RequireAuthentication(
            currentAuthentication: $currentAuthentication,
        );
        $requireRole                            = new RequireRole(
            currentAuthentication: $currentAuthentication,
        );
        $requirePermission                      = new RequirePermission(
            currentAuthentication: $currentAuthentication,
        );
        $requireResourceOwner                   = new RequireResourceOwner(
            currentAuthentication: $currentAuthentication,
        );
        $requirePhishingResistantAuthentication = new RequirePhishingResistantAuthentication(
            currentAuthentication: $currentAuthentication,
        );
        $requireAccessPolicy                    = new RequireAccessPolicy(
            requireAuthentication                 : $requireAuthentication,
            requireRole                           : $requireRole,
            requirePermission                     : $requirePermission,
            requireResourceOwner                  : $requireResourceOwner,
            requirePhishingResistantAuthentication: $requirePhishingResistantAuthentication,
            requireFreshMfa                       : $requireFreshMfa,
            requireAdminElevation                 : $requireAdminElevation,
        );
        $authorization                          = new Authorization(
            requireAuthentication: $requireAuthentication,
            requireRole          : $requireRole,
            requirePermission    : $requirePermission,
            requireAccessPolicy  : $requireAccessPolicy,
        );
        $provisionableUserSource                = $this->userSource instanceof ProvisionableUserSourceInterface
            ? $this->userSource
            : null;
        $lifecycle                              = $provisionableUserSource instanceof ProvisionableUserSourceInterface
            ? new LifecycleOrchestrator(
                auditLog  : $auditLog,
                clock     : $clock,
                userSource: $provisionableUserSource,
                store     : $lifecycleStore,
            )
            : null;
        $scimDirectoryStore                     = $this->scimDirectoryStore ?? throw ConfigurationException::missingDependency('ScimDirectoryStore', 'withScimDirectoryStore() or AuthServiceProvider');
        $scimProvisionedIdentityStore           = $this->scimProvisionedIdentityStore ?? throw ConfigurationException::missingDependency('ScimProvisionedIdentityStore', 'withScimProvisionedIdentityStore() or AuthServiceProvider');
        $tenantStore                            = $this->tenantStore ?? throw ConfigurationException::missingDependency('TenantStore', 'withTenantStore() or AuthServiceProvider');
        $tenantSecurityConfigurationStore       = $this->tenantSecurityConfigurationStore ?? throw ConfigurationException::missingDependency('TenantSecurityConfigurationStore', 'withTenantSecurityConfigurationStore() or AuthServiceProvider');
        $tenantSecurityChangeRequestStore       = $this->tenantSecurityChangeRequestStore ?? throw ConfigurationException::missingDependency('TenantSecurityChangeRequestStore', 'withTenantSecurityChangeRequestStore() or AuthServiceProvider');
        $readTenantSecurityConfiguration        = new ReadTenantSecurityConfiguration(configurationStore: $tenantSecurityConfigurationStore);
        $beginTenantSecurityChange              = new BeginTenantSecurityChange(
            federationConnectionStore: $federationConnectionStore,
            scimDirectoryStore       : $scimDirectoryStore,
            auditLog                 : $auditLog,
            clock                    : $clock,
            configurationStore       : $tenantSecurityConfigurationStore,
            changeRequestStore       : $tenantSecurityChangeRequestStore,
        );
        $approveTenantSecurityChange            = new ApproveTenantSecurityChange(
            auditLog          : $auditLog,
            clock             : $clock,
            changeRequestStore: $tenantSecurityChangeRequestStore,
        );
        $applyTenantSecurityChange              = new ApplyTenantSecurityChange(
            auditLog          : $auditLog,
            clock             : $clock,
            configurationStore: $tenantSecurityConfigurationStore,
            changeRequestStore: $tenantSecurityChangeRequestStore,
        );
        $rollbackTenantSecurityChange           = new RollbackTenantSecurityChange(
            auditLog          : $auditLog,
            clock             : $clock,
            configurationStore: $tenantSecurityConfigurationStore,
            changeRequestStore: $tenantSecurityChangeRequestStore,
        );
        $readTenantSecurityChangeRequest        = new ReadTenantSecurityChangeRequest(changeRequestStore: $tenantSecurityChangeRequestStore);
        $readTenantSecurityChangeRequests       = new ReadTenantSecurityChangeRequests(changeRequestStore: $tenantSecurityChangeRequestStore);
        $createTenant                           = new CreateTenant(
            tenantStore: $tenantStore,
            userSource : $this->userSource,
            auditLog   : $auditLog,
            clock      : $clock,
        );
        $readTenants                            = new ReadTenants(tenantStore: $tenantStore);
        $inviteTenantMember                     = new InviteTenantMember(
            tenantStore: $tenantStore,
            auditLog   : $auditLog,
            clock      : $clock,
        );
        $acceptTenantInvite                     = new AcceptTenantInvite(
            tenantStore: $tenantStore,
            userSource : $this->userSource,
            auditLog   : $auditLog,
            clock      : $clock,
        );
        $readTenantMembers                      = new ReadTenantMembers(tenantStore: $tenantStore);
        $removeTenantMember                     = new RemoveTenantMember(
            tenantStore: $tenantStore,
            auditLog   : $auditLog,
            clock      : $clock,
        );
        $suspendTenantMember                    = new SuspendTenantMember(
            tenantStore: $tenantStore,
            auditLog   : $auditLog,
            clock      : $clock,
        );
        $transferTenantOwnership                = new TransferTenantOwnership(
            tenantStore: $tenantStore,
            auditLog   : $auditLog,
            clock      : $clock,
        );
        $authCapabilityReadiness                = AuthCapabilityReadiness::from(
            jwtIdentity            : $jwtIdentity,
            refreshTokenStore      : $this->refreshTokenStore,
            passkeyRuntime         : $this->passkeyRuntime,
            federationRuntime      : $this->federationRuntime,
            provisionableUserSource: $provisionableUserSource,
        );
        $provisionScimUser                      = $authCapabilityReadiness->scim()
            ? new ProvisionScimUser(
                passwordHasher : $passwordHasher,
                idGenerator    : $this->idGenerator ?? throw ConfigurationException::missingDependency('IdGenerator', 'usingIdGenerator() or AuthServiceProvider'),
                auditLog       : $auditLog,
                clock          : $clock,
                attemptThrottle: $scimThrottle,
                provisionableUserSource: throw ConfigurationException::missingDependency("ProvisionableUserSource", "forUser()"),
                scimDirectoryStore: $scimDirectoryStore,
                scimProvisionedIdentityStore: $scimProvisionedIdentityStore,
                lifecycleOrchestrator: $lifecycle,
            )
            : null;
        $readScimUsers                          = $authCapabilityReadiness->scim()
            ? new ReadScimUsers(
                userSource   : $this->userSource,
                scimProvisionedIdentityStore: $scimProvisionedIdentityStore,
            )
            : null;
        $readScimGroups                         = $authCapabilityReadiness->scim() && $readScimUsers instanceof ReadScimUsers
            ? new ReadScimGroups(readScimUsers: $readScimUsers)
            : null;
        $runScimBulk                            = $authCapabilityReadiness->scim() && $provisionScimUser instanceof ProvisionScimUser
            ? new RunScimBulk(
                provisionScimUser: $provisionScimUser,
                deleteScimUser   : new DeleteScimUser(
                                       auditLog      : $auditLog,
                                       clock         : $clock,
                                       userSource    : $provisionableUserSource,
                                       scimDirectoryStore: $scimDirectoryStore,
                                       identityStore : $scimProvisionedIdentityStore,
                                       lifecycle     : $lifecycle,
                                   ),
            )
            : null;

        $assessCurrentRisk = new AssessCurrentRisk(
            currentAuthentication: $currentAuthentication,
            userSource           : $this->userSource,
            riskEngine           : $riskEngine,
        );
        $readRiskSignals   = new ReadRiskSignals(
            currentAuthentication: $currentAuthentication,
            riskEngine           : $riskEngine,
        );

        $authentication = new Authentication(
            login                : new Login(
                                       identity                : $identity,
                                       userSource              : $this->userSource,
                                       passwordHasher          : $passwordHasher,
                                       projectAuthenticatedUser: $projectAuthenticatedUser,
                                       currentAuthentication   : $currentAuthentication,
                                       auditLog                : $auditLog,
                                       mfaStore                : $mfaStore,
                                       startMfaChallenge       : $startMfaChallenge,
                                       clock                   : $clock,
                                       rateLimit               : $this->loginRateLimit,
                                       deterministicRiskEngine : $riskEngine,
                                   ),
            logout               : new Logout(
                                       identity             : $identity,
                                       currentAuthentication: $currentAuthentication,
                                       auditLog             : $auditLog,
                                       clock                : $clock,
                                       sessionRegistry      : $this->sessionRegistry,
                                       refreshTokenStore    : $this->refreshTokenStore,
                                   ),
            refreshAuthentication: new RefreshAuthentication(
                                       userSource              : $this->userSource,
                                       projectAuthenticatedUser: $projectAuthenticatedUser,
                                       currentAuthentication   : $currentAuthentication,
                                       auditLog                : $auditLog,
                                       clock                   : $clock,
                                       refreshTokenStore       : $this->refreshTokenStore,
                                       jwtIdentity             : $identity->jwtIdentity(),
                                       deterministicRiskEngine : $riskEngine,
                                   ),
        );

        $sessions = new Sessions(
            logoutAllSessions : new LogoutAllSessions(
                                    identity             : $identity,
                                    currentAuthentication: $currentAuthentication,
                                    auditLog             : $auditLog,
                                    clock                : $clock,
                                    sessionRegistry      : $this->sessionRegistry,
                                    refreshTokenStore    : $this->refreshTokenStore,
                                ),
            readActiveSessions: new ReadActiveSessions(
                                    currentAuthentication: $currentAuthentication,
                                    clock                : $clock,
                                    sessionRegistry      : $this->sessionRegistry,
                                ),
            revokeSession     : new RevokeSession(
                                    identity             : $identity,
                                    currentAuthentication: $currentAuthentication,
                                    auditLog             : $auditLog,
                                    clock                : $clock,
                                    sessionRegistry      : $this->sessionRegistry,
                                ),
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
                                    requireFreshMfa      : $requireFreshMfa,
                                    rateLimit            : $this->loginRateLimit,
                                ),
            beginEmailChange  : new BeginEmailChange(
                                    currentAuthentication: $currentAuthentication,
                                    userSource           : $this->userSource,
                                    passwordHasher       : $passwordHasher,
                                    emailChangeStore     : $emailChangeStore,
                                    requireFreshMfa      : $requireFreshMfa,
                                    auditLog             : $auditLog,
                                    clock                : $clock,
                                ),
            confirmEmailChange: $provisionableUserSource instanceof ProvisionableUserSourceInterface
                                    ? new ConfirmEmailChange(
                                        emailChangeStore      : $emailChangeStore,
                                        auditLog              : $auditLog,
                                        clock                 : $clock,
                                        currentAuthentication : $currentAuthentication,
                                        identity              : $identity,
                                        sessionRegistry       : $this->sessionRegistry,
                                        mfaChallengeStore     : $mfaChallengeStore,
                                        refreshTokenStore     : $this->refreshTokenStore,
                                        userSource            : $provisionableUserSource,
                                        emailVerificationState: $emailVerificationState,
                                    )
                                    : null,
            register          : new Register(
                                    userSource               : $this->userSource,
                                    passwordHasher           : $passwordHasher,
                                    idGenerator              : $this->idGenerator ?? throw ConfigurationException::missingDependency('IdGenerator', 'usingIdGenerator() or AuthServiceProvider'),
                                    projectAuthenticatedUser : $projectAuthenticatedUser,
                                    auditLog                 : $auditLog,
                                    clock                    : $clock,
                                    emailVerificationRequired: $this->emailVerificationStateStore instanceof EmailVerificationStateStoreInterface || $this->emailVerificationStore instanceof EmailVerificationStoreInterface,
                                    rateLimit                : $this->loginRateLimit,
                                ),
        );

        $recovery = new Recovery(
            beginPasswordReset: new BeginPasswordReset(
                                    userSource        : $this->userSource,
                                    passwordResetStore: $passwordResetStore,
                                    auditLog          : $auditLog,
                                    clock             : $clock,
                                    attemptThrottle   : $passwordResetThrottle,
                                ),
            resetPassword     : new ResetPassword(
                                    userSource        : $this->userSource,
                                    passwordHasher    : $passwordHasher,
                                    passwordResetStore: $passwordResetStore,
                                    auditLog          : $auditLog,
                                    clock             : $clock,
                                    sessionRegistry   : $this->sessionRegistry,
                                    mfaChallengeStore : $mfaChallengeStore,
                                    refreshTokenStore : $this->refreshTokenStore,
                                ),
        );

        $verification = new Verification(
            beginEmailVerification: new BeginEmailVerification(
                                        userSource            : $this->userSource,
                                        emailVerificationStore: $emailVerificationStore,
                                        auditLog              : $auditLog,
                                        clock                 : $clock,
                                    ),
            verifyEmail           : new VerifyEmail(
                                        emailVerificationStore: $emailVerificationStore,
                                        auditLog              : $auditLog,
                                        clock                 : $clock,
                                        emailVerificationState: $emailVerificationState,
                                    ),
        );

        $mfa = new Mfa(
            startMfaEnrollment   : new StartMfaEnrollment(
                                       currentAuthentication: $currentAuthentication,
                                       mfaStore             : $mfaStore,
                                       totp                 : $totp,
                                       auditLog             : $auditLog,
                                       clock                : $clock,
                                       issuer               : $this->mfaIssuer,
                                   ),
            confirmMfaEnrollment : new ConfirmMfaEnrollment(
                                       currentAuthentication: $currentAuthentication,
                                       mfaStore             : $mfaStore,
                                       totp                 : $totp,
                                       generateBackupCodes  : $generateBackupCodes,
                                       auditLog             : $auditLog,
                                       clock                : $clock,
                                   ),
            cancelMfaEnrollment  : new CancelMfaEnrollment(
                                       currentAuthentication: $currentAuthentication,
                                       mfaStore             : $mfaStore,
                                       auditLog             : $auditLog,
                                       clock                : $clock,
                                   ),
            startMfaChallenge    : $startMfaChallenge,
            verifyMfaChallenge   : new VerifyMfaChallenge(
                                       mfaStore                : $mfaStore,
                                       totp                    : $totp,
                                       verifyBackupCode        : $verifyBackupCode,
                                       userSource              : $this->userSource,
                                       identity                : $identity,
                                       projectAuthenticatedUser: $projectAuthenticatedUser,
                                       currentAuthentication   : $currentAuthentication,
                                       auditLog                : $auditLog,
                                       clock                   : $clock,
                                       challengeStore          : $mfaChallengeStore,
                                       attemptLimit            : $mfaAttemptLimit,
                                       deterministicRiskEngine : $riskEngine,
                                   ),
            regenerateBackupCodes: new RegenerateBackupCodes(
                                       currentAuthentication: $currentAuthentication,
                                       requireFreshMfa      : $requireFreshMfa,
                                       mfaStore             : $mfaStore,
                                       generateBackupCodes  : $generateBackupCodes,
                                       auditLog             : $auditLog,
                                       clock                : $clock,
                                   ),
            disableMfa           : new DisableMfa(
                                       currentAuthentication: $currentAuthentication,
                                       requireFreshMfa      : $requireFreshMfa,
                                       mfaStore             : $mfaStore,
                                       mfaChallengeStore    : $mfaChallengeStore,
                                       auditLog             : $auditLog,
                                       clock                : $clock,
                                       refreshTokenStore    : $this->refreshTokenStore,
                                   ),
            startMfaRecovery     : new StartMfaRecovery(
                                       userSource     : $this->userSource,
                                       mfaStore       : $mfaStore,
                                       auditLog       : $auditLog,
                                       clock          : $clock,
                                       attemptThrottle: $mfaRecoveryThrottle,
                                   ),
            confirmMfaRecovery   : new ConfirmMfaRecovery(
                                       mfaStore             : $mfaStore,
                                       mfaChallengeStore    : $mfaChallengeStore,
                                       auditLog             : $auditLog,
                                       clock                : $clock,
                                       sessionRegistry      : $this->sessionRegistry,
                                       refreshTokenStore    : $this->refreshTokenStore,
                                       currentAuthentication: $currentAuthentication,
                                       identity             : $identity,
                                   ),
        );

        $passkey = new Passkey(
            beginPasskeyRegistration     : $authCapabilityReadiness->passkey()
                                               ? new BeginPasskeyRegistration(
                                                 currentAuthentication: $currentAuthentication,
                                                 requireFreshMfa      : $requireFreshMfa,
                                                 auditLog             : $auditLog,
                                                 clock                : $clock,
                                                 rpId                 : $this->passkeyRpId,
                                                 rpName               : $this->passkeyRpName,
                                                 runtime              : $this->passkeyRuntime ?? throw ConfigurationException::missingCapabilityDependency(
                                                 capability : 'passkey',
                                                 requirement: 'runtime',
                                                 buildPath  : 'AuthBuilder::ready()',
                                                 option     : 'withPasskeyRuntime()',
                                                 cause      : 'Passkey capability assembly was attempted.',
                                             ),
                                                 credentialStore      : $passkeyCredentialStore,
                                                 challengeStore       : $passkeyChallengeStore,
                                             )
                                               : null,
            completePasskeyRegistration  : $authCapabilityReadiness->passkey()
                                               ? new CompletePasskeyRegistration(
                                                   currentAuthentication: $currentAuthentication,
                                                   auditLog             : $auditLog,
                                                   clock                : $clock,
                                                   rpId                 : $this->passkeyRpId,
                                                   runtime              : $this->passkeyRuntime ?? throw ConfigurationException::missingCapabilityDependency(
                                                   capability : 'passkey',
                                                   requirement: 'runtime',
                                                   buildPath  : 'AuthBuilder::ready()',
                                                   option     : 'withPasskeyRuntime()',
                                                   cause      : 'Passkey capability assembly was attempted.',
                                               ),
                                                   credentialStore      : $passkeyCredentialStore,
                                                   challengeStore       : $passkeyChallengeStore,
                                               )
                                               : null,
            beginPasskeyAuthentication   : $authCapabilityReadiness->passkey()
                                               ? new BeginPasskeyAuthentication(
                                                   userSource     : $this->userSource,
                                                   auditLog       : $auditLog,
                                                   clock          : $clock,
                                                   rpId           : $this->passkeyRpId,
                                                   runtime        : $this->passkeyRuntime ?? throw ConfigurationException::missingCapabilityDependency(
                                                   capability : 'passkey',
                                                   requirement: 'runtime',
                                                   buildPath  : 'AuthBuilder::ready()',
                                                   option     : 'withPasskeyRuntime()',
                                                   cause      : 'Passkey capability assembly was attempted.',
                                               ),
                                                   credentialStore: $passkeyCredentialStore,
                                                   challengeStore : $passkeyChallengeStore,
                                               )
                                               : null,
            completePasskeyAuthentication: $authCapabilityReadiness->passkey()
                                               ? new CompletePasskeyAuthentication(
                                                   userSource              : $this->userSource,
                                                   identity                : $identity,
                                                   projectAuthenticatedUser: $projectAuthenticatedUser,
                                                   currentAuthentication   : $currentAuthentication,
                                                   auditLog                : $auditLog,
                                                   clock                   : $clock,
                                                   rpId                    : $this->passkeyRpId,
                                                   runtime                 : $this->passkeyRuntime ?? throw ConfigurationException::missingCapabilityDependency(
                                                   capability : 'passkey',
                                                   requirement: 'runtime',
                                                   buildPath  : 'AuthBuilder::ready()',
                                                   option     : 'withPasskeyRuntime()',
                                                   cause      : 'Passkey capability assembly was attempted.',
                                               ),
                                                   challengeStore          : $passkeyChallengeStore,
                                                   credentialStore         : $passkeyCredentialStore,
                                               )
                                               : null,
            renamePasskey                : $authCapabilityReadiness->passkey()
                                               ? new RenamePasskey(
                                                   currentAuthentication: $currentAuthentication,
                                                   credentialStore      : $passkeyCredentialStore,
                                               )
                                               : null,
            revokePasskey                : $authCapabilityReadiness->passkey()
                                               ? new RevokePasskey(
                                                   currentAuthentication: $currentAuthentication,
                                                   requireFreshMfa      : $requireFreshMfa,
                                                   auditLog             : $auditLog,
                                                   clock                : $clock,
                                                   credentialStore      : $passkeyCredentialStore,
                                               )
                                               : null,
            readPasskeys                 : $authCapabilityReadiness->passkey()
                                               ? new ListPasskeys(
                                                   currentAuthentication: $currentAuthentication,
                                                   credentialStore      : $passkeyCredentialStore,
                                               )
                                               : null,
        );

        $identity = new Identity(
            authentication : $authentication,
            sessions       : $sessions,
            account        : $account,
            recovery       : $recovery,
            verification   : $verification,
            mfa            : $mfa,
            passkey        : $passkey,
            sessionIdentity: $identity->sessionIdentity(),
            jwtIdentity    : $identity->jwtIdentity(),
        );

        $oauth = new OAuth(
            registerClient           : $authCapabilityReadiness->oauth() ? $registerClient : null,
            approveClientRegistration: $authCapabilityReadiness->oauth() ? $approveClientRegistration : null,
            updateClient             : $authCapabilityReadiness->oauth() ? $updateClient : null,
            disableClient            : $authCapabilityReadiness->oauth() ? $disableClient : null,
            rotateClientSecret       : $authCapabilityReadiness->oauth() ? $rotateClientSecret : null,
            readClients              : $authCapabilityReadiness->oauth() ? $readClients : null,
            readWorkloadIdentities   : $authCapabilityReadiness->oauth() ? $readWorkloadIdentities : null,
            authorizeCode            : $authCapabilityReadiness->oauth() ? $authorizeCode : null,
            exchangeAuthorizationCode: $authCapabilityReadiness->oauth()
                                           ? new ExchangeAuthorizationCode(
                                               userSource           : $this->userSource,
                                               jwtIdentity          : $jwtIdentity ?? throw ConfigurationException::missingCapabilityDependency(
                                               capability : 'oauth',
                                               requirement: 'jwt_identity',
                                               buildPath  : 'AuthBuilder::ready()',
                                               option     : 'withIdentityBackends(jwtIdentity: ...) or withIdentity(new Identity(jwtIdentity: ...))',
                                               cause      : 'OAuth capability assembly was attempted.',
                                           ),
                                               refreshTokenStore    : $this->refreshTokenStore ?? throw ConfigurationException::missingCapabilityDependency(
                                               capability : 'oauth',
                                               requirement: 'refresh_token_store',
                                               buildPath  : 'AuthBuilder::ready()',
                                               option     : 'withRefreshTokenStore()',
                                               cause      : 'OAuth capability assembly was attempted.',
                                           ),
                                               auditLog             : $auditLog,
                                               clock                : $clock,
                                               currentAuthentication: $currentAuthentication,
                                               oidcProvider         : $this->oidcProvider,
                                               clientRegistry       : $oauthClientRegistry,
                                               codeStore            : $authorizationCodeStore,
                                           )
                                           : null,
            exchangeClientCredentials: $authCapabilityReadiness->oauth()
                                           ? new ExchangeClientCredentials(
                                               jwtIdentity   : $jwtIdentity,
                                               auditLog      : $auditLog,
                                               clock         : $clock,
                                               clientRegistry: $oauthClientRegistry,
                                           )
                                           : null,
            exchangeRefreshToken     : $authCapabilityReadiness->oauth()
                                           ? new ExchangeRefreshToken(
                                               refreshTokenStore: $this->refreshTokenStore ?? throw ConfigurationException::missingCapabilityDependency(
                                               capability : 'oauth',
                                               requirement: 'refresh_token_store',
                                               buildPath  : 'AuthBuilder::ready()',
                                               option     : 'withRefreshTokenStore()',
                                               cause      : 'OAuth capability assembly was attempted.',
                                           ),
                                               userSource: $this->userSource ?? throw ConfigurationException::missingDependency("UserSource", "forUser()"),
                                               jwtIdentity      : $jwtIdentity,
                                               auditLog         : $auditLog,
                                               clock            : $clock,
                                               clientRegistry   : $oauthClientRegistry,
                                               riskEngine       : $riskEngine,
                                           )
                                           : null,
            revokeToken              : $authCapabilityReadiness->oauth()
                                           ? new RevokeToken(
                                               oAuthClientRegistry: $oauthClientRegistry,
                                               refreshTokenStore  : $this->refreshTokenStore ?? throw ConfigurationException::missingCapabilityDependency(
                                                   capability : 'oauth',
                                                   requirement: 'refresh_token_store',
                                                   buildPath  : 'AuthBuilder::ready()',
                                                   option     : 'withRefreshTokenStore()',
                                                   cause      : 'OAuth capability assembly was attempted.',
                                               ),
                                               jwtIdentity        : $jwtIdentity,
                                               auditLog           : $auditLog,
                                               clock              : $clock,
                                           )
                                           : null,
            introspectToken          : $authCapabilityReadiness->oauth()
                                           ? new IntrospectToken(
                                               oAuthClientRegistry: $oauthClientRegistry,
                                               jwtIdentity        : $jwtIdentity,
                                               auditLog           : $auditLog,
                                               clock              : $clock,
                                           )
                                           : null,
        );

        $openIDConnect = new OpenIDConnect(
            pushAuthorizationRequest: $authCapabilityReadiness->oauth() ? $pushOidcAuthorizationRequest : null,
            logout                  : $authCapabilityReadiness->oauth() ? $oidcLogout : null,
            buildJarmResponse       : $authCapabilityReadiness->oauth() ? $buildOidcJarmResponse : null,
            readOidcProviderMetadata: $authCapabilityReadiness->oauth() ? $readOidcProviderMetadata : null,
            readOidcJsonWebKeySet   : $authCapabilityReadiness->oauth() ? $readOidcJsonWebKeySet : null,
            readOidcUserInfo        : $authCapabilityReadiness->oauth() ? $readOidcUserInfo : null,
        );

        $singleSignOn = new SingleSignOn(
            startFederatedLogin     : $authCapabilityReadiness->federation()
                                          ? new StartFederatedLogin(
                                            auditLog       : $auditLog,
                                            clock          : $clock,
                                            federationConnectionStore: $federationConnectionStore,
                                            federationRuntime: $this->federationRuntime ?? throw ConfigurationException::missingCapabilityDependency(
                                            capability : 'federation',
                                            requirement: 'runtime',
                                            buildPath  : 'AuthBuilder::ready()',
                                            option     : 'withFederationRuntime()',
                                            cause      : 'Federation capability assembly was attempted.',
                                        ),
                                        )
                                          : null,
            completeFederatedLogin  : $authCapabilityReadiness->federation()
                                          ? new CompleteFederatedLogin(
                                              userSource              : $this->userSource,
                                              identity                : $identity,
                                              projectAuthenticatedUser: $projectAuthenticatedUser,
                                              currentAuthentication   : $currentAuthentication,
                                              passwordHasher          : $passwordHasher,
                                              idGenerator             : $this->idGenerator ?? throw ConfigurationException::missingDependency('IdGenerator', 'usingIdGenerator() or AuthServiceProvider'),
                                              auditLog                : $auditLog,
                                              clock                   : $clock,
                                              federationConnectionStore: $federationConnectionStore,
                                              federationRuntime: $this->federationRuntime ?? throw ConfigurationException::missingCapabilityDependency(
                                              capability : 'federation',
                                              requirement: 'runtime',
                                              buildPath  : 'AuthBuilder::ready()',
                                              option     : 'withFederationRuntime()',
                                              cause      : 'Federation capability assembly was attempted.',
                                          ),
                                              federatedIdentityLinkStore: $federatedIdentityLinkStore,
                                              deterministicRiskEngine: $riskEngine,
                                              lifecycleOrchestrator: $lifecycle,
                                          )
                                          : null,
            registerFederationConnection: $authCapabilityReadiness->federation()
                                          ? new RegisterFederationConnection(
                                              groupRoleMappingValidator: $groupRoleMappingValidator,
                                              auditLog                 : $auditLog,
                                              clock                    : $clock,
                                              federationConnectionStore: $federationConnectionStore,
                                          )
                                          : null,
            readFederationConnections: $authCapabilityReadiness->federation()
                                          ? new ReadFederationConnections(federationConnectionStore: $federationConnectionStore)
                                          : null,
            verifyFederationDomain: $authCapabilityReadiness->federation()
                                          ? new VerifyFederationDomain(
                                              auditLog       : $auditLog,
                                              clock          : $clock,
                                              federationConnectionStore: $federationConnectionStore,
                                          )
                                          : null,
            syncFederationMetadata      : $authCapabilityReadiness->federation() && $this->federationRuntime instanceof FederationMetadataRuntimeInterface
                                          ? new SyncFederationMetadata(
                                              auditLog       : $auditLog,
                                              clock          : $clock,
                                              federationConnectionStore: $federationConnectionStore,
                                              federationMetadataRuntime: $this->federationRuntime,
                                          )
                                          : null,
            checkFederationConnectionHealth: $authCapabilityReadiness->federation() && $this->federationRuntime instanceof FederationHealthCheckInterface
                                          ? new CheckFederationConnectionHealth(
                                              auditLog       : $auditLog,
                                              clock          : $clock,
                                              federationConnectionStore: $federationConnectionStore,
                                              federationHealthCheck: $this->federationRuntime,
                                          )
                                          : null,
            evaluateFederationBreakGlassBypass: $authCapabilityReadiness->federation()
                                          ? new EvaluateFederationBreakGlassBypass(
                                              auditLog       : $auditLog,
                                              clock          : $clock,
                                              federationConnectionStore: $federationConnectionStore,
                                          )
                                          : null,
            discoverFederationConnection: $authCapabilityReadiness->federation()
                                          ? new DiscoverFederationConnection(federationConnectionStore: $federationConnectionStore)
                                          : null,
        );

        $externalIdentity = new ExternalIdentity(
            oauth: $oauth,
            openIDConnect: $openIDConnect,
            singleSignOn: $singleSignOn,
        );

        $scim = new SCIM(
            registerScimDirectory     : $authCapabilityReadiness->scim()
                                            ? new RegisterScimDirectory(
                                              passwordHasher           : $passwordHasher,
                                              groupRoleMappingValidator: new GroupRoleMappingValidator(),
                                              auditLog                 : $auditLog,
                                              clock                    : $clock,
                                              scimDirectoryStore      : $scimDirectoryStore,
                                          )
                                            : null,
            readScimDirectories       : $authCapabilityReadiness->scim() ? new ReadScimDirectories(scimDirectoryStore: $scimDirectoryStore) : null,
            rotateScimToken           : $authCapabilityReadiness->scim()
                                            ? new RotateScimToken(
                                                passwordHasher : $passwordHasher,
                                                auditLog       : $auditLog,
                                                clock          : $clock,
                                                attemptThrottle: $scimThrottle,
                                                scimDirectoryStore: $scimDirectoryStore,
                                            )
                                            : null,
            markScimDirectoryOutage   : $authCapabilityReadiness->scim()
                                            ? new MarkScimDirectoryOutage(
                                                auditLog      : $auditLog,
                                                clock         : $clock,
                                                scimDirectoryStore: $scimDirectoryStore,
                                            )
                                            : null,
            recoverScimDirectoryOutage: $authCapabilityReadiness->scim()
                                            ? new RecoverScimDirectoryOutage(
                                                auditLog      : $auditLog,
                                                clock         : $clock,
                                                scimDirectoryStore: $scimDirectoryStore,
                                            )
                                            : null,
            provisionScimUser         : $provisionScimUser,
            deleteScimUser            : $authCapabilityReadiness->scim()
                                            ? new DeleteScimUser(
                                                auditLog       : $auditLog,
                                                clock          : $clock,
                                                attemptThrottle: $scimThrottle,
                                                provisionableUserSource: throw ConfigurationException::missingDependency("ProvisionableUserSource", "forUser()"),
                                                scimDirectoryStore: $scimDirectoryStore,
                                                scimProvisionedIdentityStore: $scimProvisionedIdentityStore,
                                                lifecycleOrchestrator: $lifecycle,
                                            )
                                            : null,
            readScimUsers             : $readScimUsers,
            readScimGroups            : $readScimGroups,
            syncScimGroups            : $authCapabilityReadiness->scim() && $provisionScimUser instanceof ProvisionScimUser
                                            ? new SyncScimGroups(
                                                userSource: $this->userSource ?? throw ConfigurationException::missingDependency("UserSource", "forUser()"),
                                                provisionScimUser: $provisionScimUser,
                                                scimDirectoryStore: $scimDirectoryStore,
                                                scimProvisionedIdentityStore: $scimProvisionedIdentityStore,
                                            )
                                            : null,
            runScimBulk               : $runScimBulk,
        );

        $provisioning = new Provisioning(
            suspendUser    : $provisionableUserSource instanceof ProvisionableUserSourceInterface
                                 ? new SuspendUser(
                                   provisionableUserSource             : throw ConfigurationException::missingDependency("ProvisionableUserSource", "forUser()"),
                                                requireAdminElevation  : $requireAdminElevation,
                                                auditLog               : $auditLog,
                                                clock                  : $clock,
                                                lifecycleOrchestrator  : $lifecycle,
                                            )
                                 : null,
            reactivateUser : $provisionableUserSource instanceof ProvisionableUserSourceInterface
                                 ? new ReactivateUser(
                                     provisionableUserSource           : throw ConfigurationException::missingDependency("ProvisionableUserSource", "forUser()"),
                                                requireAdminElevation  : $requireAdminElevation,
                                                auditLog               : $auditLog,
                                                clock                  : $clock,
                                                lifecycleOrchestrator  : $lifecycle,
                                            )
                                 : null,
            deprovisionUser: $provisionableUserSource instanceof ProvisionableUserSourceInterface
                                 ? new DeprovisionUser(
                                     provisionableUserSource           : throw ConfigurationException::missingDependency("ProvisionableUserSource", "forUser()"),
                                                requireAdminElevation  : $requireAdminElevation,
                                                auditLog               : $auditLog,
                                                clock                  : $clock,
                                                sessionRegistry        : $sessionRegistry,
                                                refreshTokenStore      : $refreshTokenStore,
                                                adminElevationStore    : $adminElevationStore,
                                                lifecycleOrchestrator  : $lifecycle,
                                            )
                                 : null,
        );

        $identitySync = new IdentitySync(
            scim        : $scim,
            provisioning: $provisioning,
        );

        $tenants = new Tenants(
            createTenant           : $createTenant,
            readTenants            : $readTenants,
            inviteTenantMember     : $inviteTenantMember,
            acceptTenantInvite     : $acceptTenantInvite,
            readTenantMembers      : $readTenantMembers,
            removeTenantMember     : $removeTenantMember,
            suspendTenantMember    : $suspendTenantMember,
            transferTenantOwnership: $transferTenantOwnership,
        );

        $security = new Security(
            readTenantSecurityConfiguration : $readTenantSecurityConfiguration,
            readTenantSecurityChangeRequest : $readTenantSecurityChangeRequest,
            readTenantSecurityChangeRequests: $readTenantSecurityChangeRequests,
            beginTenantSecurityChange       : $beginTenantSecurityChange,
            approveTenantSecurityChange     : $approveTenantSecurityChange,
            applyTenantSecurityChange       : $applyTenantSecurityChange,
            rollbackTenantSecurityChange    : $rollbackTenantSecurityChange,
        );

        $tenancy = new Tenancy(
            tenants : $tenants,
            security: $security,
        );

        $diagnostics = new Diagnostics(
            authIssueExplainer: new AuthIssueExplainer(),
        );

                return new Auth(
            identity: $identity,
        );
    }

    private function capabilityRequests() : AuthCapabilityRequests
    {
        return AuthCapabilityRequests::from(
            enterpriseMode                        : $this->enterpriseMode,
            oauthClientRegistryConfigured         : $this->oAuthClientRegistry instanceof OAuthClientRegistryInterface,
            authorizationCodeStoreConfigured      : $this->authorizationCodeStore instanceof AuthorizationCodeStoreInterface,
            oidcProviderConfigured                : $this->oidcProvider instanceof OidcProviderInterface,
            oidcRequestObjectStoreConfigured      : $this->oidcRequestObjectStore instanceof OidcRequestObjectStoreInterface,
            passkeyCredentialStoreConfigured      : $this->passkeyCredentialStore instanceof PasskeyCredentialStoreInterface,
            passkeyChallengeStoreConfigured       : $this->passkeyChallengeStore instanceof PasskeyChallengeStoreInterface,
            passkeyRelyingPartyCustomized         : $this->passkeyRpId !== 'localhost' || $this->passkeyRpName !== 'Avax Auth',
            federationConnectionStoreConfigured   : $this->federationConnectionStore instanceof FederationConnectionStoreInterface,
            federatedIdentityLinkStoreConfigured  : $this->federatedIdentityLinkStore instanceof FederatedIdentityLinkStoreInterface,
            scimDirectoryStoreConfigured          : $this->scimDirectoryStore instanceof ScimDirectoryStoreInterface,
            scimProvisionedIdentityStoreConfigured: $this->scimProvisionedIdentityStore instanceof ScimProvisionedIdentityStoreInterface,
        );
    }
}
