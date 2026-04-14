<?php

declare(strict_types=1);

namespace Avax\Auth\System\Configuration;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Access\Access;
use Avax\Auth\System\Capability\Access\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Auth\System\Capability\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capability\Access\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Auth\System\Capability\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capability\Access\RequireResourceOwner\RequireResourceOwner;
use Avax\Auth\System\Capability\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capability\AdminRealm\AdminElevationStoreInterface;
use Avax\Auth\System\Capability\AdminRealm\InMemoryAdminElevationStore;
use Avax\Auth\System\Capability\Federation\FederatedIdentityLinkStoreInterface;
use Avax\Auth\System\Capability\Federation\FederationConnectionStoreInterface;
use Avax\Auth\System\Capability\Federation\FederationHealthCheckInterface;
use Avax\Auth\System\Capability\Federation\FederationMetadataRuntimeInterface;
use Avax\Auth\System\Capability\Federation\FederationRuntimeInterface;
use Avax\Auth\System\Capability\Federation\GroupRoleMappingValidator;
use Avax\Auth\System\Capability\Federation\InMemoryFederatedIdentityLinkStore;
use Avax\Auth\System\Capability\Federation\InMemoryFederationConnectionStore;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Lifecycle\InMemoryLifecycleStore;
use Avax\Auth\System\Capability\Lifecycle\LifecycleOrchestrator;
use Avax\Auth\System\Capability\Lifecycle\LifecycleStoreInterface;
use Avax\Auth\System\Capability\Oidc\InMemoryOidcRequestObjectStore;
use Avax\Auth\System\Capability\Oidc\OidcProviderInterface;
use Avax\Auth\System\Capability\Oidc\OidcRequestObjectStoreInterface;
use Avax\Auth\System\Capability\OAuth\AuthorizationCodeStoreInterface;
use Avax\Auth\System\Capability\OAuth\InMemoryAuthorizationCodeStore;
use Avax\Auth\System\Capability\OAuth\InMemoryOAuthClientRegistry;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Capability\Passkey\InMemoryPasskeyChallengeStore;
use Avax\Auth\System\Capability\Passkey\InMemoryPasskeyCredentialStore;
use Avax\Auth\System\Capability\Passkey\PasskeyChallengeStoreInterface;
use Avax\Auth\System\Capability\Passkey\PasskeyCredentialStoreInterface;
use Avax\Auth\System\Capability\Passkey\PasskeyRuntimeInterface;
use Avax\Auth\System\Capability\Risk\DeterministicRiskEngine;
use Avax\Auth\System\Capability\Risk\InMemoryKnownAuthenticationEnvironmentStore;
use Avax\Auth\System\Capability\Risk\InMemoryRiskSignalStore;
use Avax\Auth\System\Capability\Scim\InMemoryScimDirectoryStore;
use Avax\Auth\System\Capability\Scim\InMemoryScimProvisionedIdentityStore;
use Avax\Auth\System\Capability\Scim\ScimDirectoryStoreInterface;
use Avax\Auth\System\Capability\Scim\ScimProvisionedIdentityStoreInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\Tenant\InMemoryTenantStore;
use Avax\Auth\System\Capability\Tenant\TenantStoreInterface;
use Avax\Auth\System\Capability\TenantSecurity\InMemoryTenantSecurityChangeRequestStore;
use Avax\Auth\System\Capability\TenantSecurity\InMemoryTenantSecurityConfigurationStore;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequestStoreInterface;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfigurationStoreInterface;
use Avax\Auth\System\Capability\Throttle\AttemptThrottle;
use Avax\Auth\System\Capability\Throttle\InMemoryAttemptThrottleStore;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\UserSource\ProvisionableUserSourceInterface;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AdminRealm\BeginAdminElevation\BeginAdminElevation;
use Avax\Auth\System\Flow\AdminRealm\EndAdminElevation\EndAdminElevation;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticateRequest;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flow\ChangeEmail\BeginEmailChange;
use Avax\Auth\System\Flow\ChangeEmail\ConfirmEmailChange;
use Avax\Auth\System\Flow\ChangeEmail\EmailChangeStoreInterface;
use Avax\Auth\System\Flow\ChangeEmail\InMemoryEmailChangeStore;
use Avax\Auth\System\Flow\ChangePassword\ChangePassword;
use Avax\Auth\System\Flow\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Diagnostics\CorrelatingAuditLog;
use Avax\Auth\System\Flow\Diagnostics\NullAuditLog;
use Avax\Auth\System\Flow\Federation\CompleteFederatedLogin\CompleteFederatedLogin;
use Avax\Auth\System\Flow\Federation\CheckHealth\CheckFederationConnectionHealth;
use Avax\Auth\System\Flow\Federation\DiscoverConnection\DiscoverFederationConnection;
use Avax\Auth\System\Flow\Federation\EvaluateBreakGlass\EvaluateFederationBreakGlassBypass;
use Avax\Auth\System\Flow\Federation\ReadConnections\ReadFederationConnections;
use Avax\Auth\System\Flow\Federation\RegisterConnection\RegisterFederationConnection;
use Avax\Auth\System\Flow\Federation\StartFederatedLogin\StartFederatedLogin;
use Avax\Auth\System\Flow\Federation\SyncMetadata\SyncFederationMetadata;
use Avax\Auth\System\Flow\Federation\VerifyDomain\VerifyFederationDomain;
use Avax\Auth\System\Flow\Login\Login;
use Avax\Auth\System\Flow\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Flow\Logout\Logout;
use Avax\Auth\System\Flow\Mfa\Backup\GenerateBackupCodes;
use Avax\Auth\System\Flow\Mfa\Backup\RegenerateBackupCodes;
use Avax\Auth\System\Flow\Mfa\Backup\VerifyBackupCode;
use Avax\Auth\System\Flow\Mfa\Challenge\InMemoryAttemptLimitStorage;
use Avax\Auth\System\Flow\Mfa\Challenge\InMemoryMfaChallengeStore;
use Avax\Auth\System\Flow\Mfa\Challenge\LimitMfaAttempts;
use Avax\Auth\System\Flow\Mfa\Challenge\MfaChallengeStoreInterface;
use Avax\Auth\System\Flow\Mfa\Challenge\StartMfaChallenge;
use Avax\Auth\System\Flow\Mfa\Challenge\VerifyMfaChallenge;
use Avax\Auth\System\Flow\Mfa\Disable\DisableMfa;
use Avax\Auth\System\Flow\Mfa\Enroll\CancelMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\Enroll\ConfirmMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\Enroll\StartMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\InMemoryMfaStore;
use Avax\Auth\System\Flow\Mfa\MfaStoreInterface;
use Avax\Auth\System\Flow\Mfa\Recover\ConfirmMfaRecovery;
use Avax\Auth\System\Flow\Mfa\Recover\StartMfaRecovery;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Flow\Mfa\Totp;
use Avax\Auth\System\Flow\Mfa\TotpInterface;
use Avax\Auth\System\Flow\OAuth\AuthorizeCode\AuthorizeCode;
use Avax\Auth\System\Flow\OAuth\ExchangeAuthorizationCode\ExchangeAuthorizationCode;
use Avax\Auth\System\Flow\OAuth\ExchangeClientCredentials\ExchangeClientCredentials;
use Avax\Auth\System\Flow\OAuth\ExchangeRefreshToken\ExchangeRefreshToken;
use Avax\Auth\System\Flow\OAuth\ApproveClientRegistration\ApproveClientRegistration;
use Avax\Auth\System\Flow\OAuth\IntrospectToken\IntrospectToken;
use Avax\Auth\System\Flow\OAuth\ReadClients\ReadClients;
use Avax\Auth\System\Flow\OAuth\ReadWorkloadIdentities\ReadWorkloadIdentities;
use Avax\Auth\System\Flow\OAuth\RotateClientSecret\RotateClientSecret;
use Avax\Auth\System\Flow\OAuth\RegisterClient\RegisterClient;
use Avax\Auth\System\Flow\OAuth\RevokeToken\RevokeToken;
use Avax\Auth\System\Flow\OAuth\DisableClient\DisableClient;
use Avax\Auth\System\Flow\OAuth\UpdateClient\UpdateClient;
use Avax\Auth\System\Flow\Oidc\ReadJsonWebKeySet\ReadOidcJsonWebKeySet;
use Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest\PushAuthorizationRequest;
use Avax\Auth\System\Flow\Oidc\ValidateRequestObject\ValidateRequestObject;
use Avax\Auth\System\Flow\Oidc\JarmResponse\BuildJarmResponse;
use Avax\Auth\System\Flow\Oidc\Logout\Logout as OidcLogout;
use Avax\Auth\System\Flow\Oidc\FrontChannelLogout\FrontChannelLogout;
use Avax\Auth\System\Flow\Oidc\BackChannelLogout\BackChannelLogout;
use Avax\Auth\System\Flow\Oidc\ReadProviderMetadata\ReadOidcProviderMetadata;
use Avax\Auth\System\Flow\Oidc\ReadUserInfo\ReadOidcUserInfo;
use Avax\Auth\System\Flow\Passkey\BeginAuthentication\BeginPasskeyAuthentication;
use Avax\Auth\System\Flow\Passkey\BeginRegistration\BeginPasskeyRegistration;
use Avax\Auth\System\Flow\Passkey\CompleteAuthentication\CompletePasskeyAuthentication;
use Avax\Auth\System\Flow\Passkey\CompleteRegistration\CompletePasskeyRegistration;
use Avax\Auth\System\Flow\Passkey\ListPasskeys\ListPasskeys;
use Avax\Auth\System\Flow\Passkey\RenamePasskey\RenamePasskey;
use Avax\Auth\System\Flow\Passkey\RevokePasskey\RevokePasskey;
use Avax\Auth\System\Flow\Provisioning\DeprovisionUser\DeprovisionUser;
use Avax\Auth\System\Flow\Provisioning\ReactivateUser\ReactivateUser;
use Avax\Auth\System\Flow\Provisioning\SuspendUser\SuspendUser;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Flow\Recover\BeginPasswordReset;
use Avax\Auth\System\Flow\Recover\InMemoryPasswordResetStore;
use Avax\Auth\System\Flow\Recover\PasswordResetStoreInterface;
use Avax\Auth\System\Flow\Recover\ResetPassword;
use Avax\Auth\System\Flow\Register\Register;
use Avax\Auth\System\Flow\Risk\AssessCurrentRisk\AssessCurrentRisk;
use Avax\Auth\System\Flow\Risk\ReadRiskSignals\ReadRiskSignals;
use Avax\Auth\System\Flow\Scim\Bulk\RunScimBulk;
use Avax\Auth\System\Flow\Scim\DeleteUser\DeleteScimUser;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ProvisionScimUser;
use Avax\Auth\System\Flow\Scim\ReadDirectories\ReadScimDirectories;
use Avax\Auth\System\Flow\Scim\ReadGroups\ReadScimGroups;
use Avax\Auth\System\Flow\Scim\ReadUsers\ReadScimUsers;
use Avax\Auth\System\Flow\Scim\RegisterDirectory\RegisterScimDirectory;
use Avax\Auth\System\Flow\Scim\RotateToken\RotateScimToken;
use Avax\Auth\System\Flow\Scim\MarkOutage\MarkScimDirectoryOutage;
use Avax\Auth\System\Flow\Scim\RecoverOutage\RecoverScimDirectoryOutage;
use Avax\Auth\System\Flow\Scim\SyncGroups\SyncScimGroups;
use Avax\Auth\System\Flow\Session\LogoutAllSessions\LogoutAllSessions;
use Avax\Auth\System\Flow\Session\ReadActiveSessions\ReadActiveSessions;
use Avax\Auth\System\Flow\Session\RevokeSession\RevokeSession;
use Avax\Auth\System\Flow\Tenant\AcceptInvite\AcceptTenantInvite;
use Avax\Auth\System\Flow\Tenant\CreateTenant\CreateTenant;
use Avax\Auth\System\Flow\Tenant\InviteMember\InviteTenantMember;
use Avax\Auth\System\Flow\Tenant\ReadMembers\ReadTenantMembers;
use Avax\Auth\System\Flow\Tenant\ReadTenants\ReadTenants;
use Avax\Auth\System\Flow\Tenant\RemoveMember\RemoveTenantMember;
use Avax\Auth\System\Flow\Tenant\SuspendMember\SuspendTenantMember;
use Avax\Auth\System\Flow\Tenant\TransferOwnership\TransferTenantOwnership;
use Avax\Auth\System\Flow\TenantSecurity\ApplyChange\ApplyTenantSecurityChange;
use Avax\Auth\System\Flow\TenantSecurity\ApproveChange\ApproveTenantSecurityChange;
use Avax\Auth\System\Flow\TenantSecurity\BeginChange\BeginTenantSecurityChange;
use Avax\Auth\System\Flow\TenantSecurity\ReadChangeRequest\ReadTenantSecurityChangeRequest;
use Avax\Auth\System\Flow\TenantSecurity\ReadChangeRequests\ReadTenantSecurityChangeRequests;
use Avax\Auth\System\Flow\TenantSecurity\ReadConfiguration\ReadTenantSecurityConfiguration;
use Avax\Auth\System\Flow\TenantSecurity\RollbackChange\RollbackTenantSecurityChange;
use Avax\Auth\System\Flow\Token\RefreshAuthentication;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Flow\Verify\BeginEmailVerification;
use Avax\Auth\System\Flow\Verify\EmailVerificationStateStoreInterface;
use Avax\Auth\System\Flow\Verify\EmailVerificationStoreInterface;
use Avax\Auth\System\Flow\Verify\InMemoryEmailVerificationStateStore;
use Avax\Auth\System\Flow\Verify\InMemoryEmailVerificationStore;
use Avax\Auth\System\Flow\Verify\VerifyEmail;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\System\Foundation\IdGenerator;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use RuntimeException;
use SensitiveParameter;

/**
 * Fluent builder for creating Auth system instances.
 *
 * Capability: Composition root for the system.
 */
final class AuthBuilder
{
    private UserSourceInterface|null                  $userSource             = null;
    private IdentityInterface|null                    $identity               = null;
    private LoginRateLimit|null                       $rateLimit              = null;
    private PasswordHasher|null                       $passwordHasher         = null;
    private IdGeneratorInterface|null                 $idGenerator            = null;
    private AuditLogInterface|null                    $auditLog               = null;
    private string|null                               $auditCorrelationId     = null;
    private EmailVerificationStateStoreInterface|null $emailVerificationState = null;
    private MfaStoreInterface|null                    $mfaStore               = null;
    private RefreshTokenStoreInterface|null           $refreshTokenStore      = null;
    private PasswordResetStoreInterface|null          $passwordResetStore     = null;
    private EmailVerificationStoreInterface|null      $emailVerificationStore = null;
    private EmailChangeStoreInterface|null            $emailChangeStore       = null;
    private MfaChallengeStoreInterface|null           $mfaChallengeStore      = null;
    private TotpInterface|null                        $totp                   = null;
    private LimitMfaAttempts|null                     $mfaAttemptLimit        = null;
    private AttemptThrottle|null                      $passwordResetThrottle  = null;
    private AttemptThrottle|null                      $mfaRecoveryThrottle    = null;
    private AttemptThrottle|null                      $scimThrottle           = null;
    private Clock|null                                $clock                  = null;
    private SessionRegistryInterface|null             $sessionRegistry        = null;
    private OAuthClientRegistryInterface|null         $oauthClientRegistry    = null;
    private AuthorizationCodeStoreInterface|null      $authorizationCodeStore = null;
    private LifecycleStoreInterface|null              $lifecycleStore         = null;
    private AdminElevationStoreInterface|null         $adminElevationStore    = null;
    private DeterministicRiskEngine|null              $riskEngine             = null;
    private PasskeyRuntimeInterface|null              $passkeyRuntime         = null;
    private PasskeyCredentialStoreInterface|null      $passkeyCredentialStore = null;
    private PasskeyChallengeStoreInterface|null       $passkeyChallengeStore  = null;
    private FederationRuntimeInterface|null           $federationRuntime      = null;
    private FederationConnectionStoreInterface|null   $federationConnectionStore = null;
    private FederatedIdentityLinkStoreInterface|null  $federatedIdentityLinkStore = null;
    private OidcProviderInterface|null                $oidcProvider           = null;
    private OidcRequestObjectStoreInterface|null      $oidcRequestObjectStore = null;
    private ScimDirectoryStoreInterface|null          $scimDirectoryStore     = null;
    private ScimProvisionedIdentityStoreInterface|null $scimProvisionedIdentityStore = null;
    private TenantStoreInterface|null                 $tenantStore            = null;
    private TenantSecurityConfigurationStoreInterface|null $tenantSecurityConfigurationStore = null;
    private TenantSecurityChangeRequestStoreInterface|null $tenantSecurityChangeRequestStore = null;
    private string                                    $mfaIssuer              = 'Avax Auth';
    private string                                    $passkeyRpId            = 'localhost';
    private string                                    $passkeyRpName          = 'Avax Auth';
    private bool                                      $adminPhishingResistantRequired = false;

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
        $this->auditCorrelationId = trim($correlationId) !== '' ? trim($correlationId) : null;

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
        $this->passkeyRpId = $rpId;
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
        if ($this->userSource === null) {
            throw new RuntimeException(message: 'Data source is required (forUser).');
        }

        if ($this->identity === null) {
            throw new RuntimeException(message: 'Identity is required (withIdentity).');
        }

        $identity                 = $this->identity;
        $passwordHasher           = $this->passwordHasher ?? new PasswordHasher();
        $auditLog                 = $this->auditLog ?? new NullAuditLog();

        if ($this->auditCorrelationId !== null) {
            $auditLog = new CorrelatingAuditLog(
                inner         : $auditLog,
                correlationId : $this->auditCorrelationId
            );
        }
        $clock                    = $this->clock ?? new Clock();
        $oauthClientRegistry      = $this->oauthClientRegistry ?? new InMemoryOAuthClientRegistry(passwordHasher: $passwordHasher);
        $authorizationCodeStore   = $this->authorizationCodeStore ?? new InMemoryAuthorizationCodeStore();
        $lifecycleStore           = $this->lifecycleStore ?? new InMemoryLifecycleStore();
        $adminElevationStore      = $this->adminElevationStore ?? new InMemoryAdminElevationStore();
        $riskEngine               = $this->riskEngine ?? new DeterministicRiskEngine(
            knownEnvironments: new InMemoryKnownAuthenticationEnvironmentStore(),
            signals          : new InMemoryRiskSignalStore(),
            clock            : $clock
        );
        $passkeyCredentialStore   = $this->passkeyCredentialStore ?? new InMemoryPasskeyCredentialStore();
        $passkeyChallengeStore    = $this->passkeyChallengeStore ?? new InMemoryPasskeyChallengeStore();
        $federationConnectionStore = $this->federationConnectionStore ?? new InMemoryFederationConnectionStore();
        $federatedIdentityLinkStore = $this->federatedIdentityLinkStore ?? new InMemoryFederatedIdentityLinkStore();
        $groupRoleMappingValidator = new GroupRoleMappingValidator();
        $passwordResetStore       = $this->passwordResetStore ?? new InMemoryPasswordResetStore();
        $emailVerificationStore   = $this->emailVerificationStore ?? new InMemoryEmailVerificationStore();
        $emailChangeStore         = $this->emailChangeStore ?? new InMemoryEmailChangeStore();
        $emailVerificationState   = $this->emailVerificationState ?? new InMemoryEmailVerificationStateStore();
        $mfaStore                 = $this->mfaStore ?? new InMemoryMfaStore();
        $mfaChallengeStore        = $this->mfaChallengeStore ?? new InMemoryMfaChallengeStore();
        $totp                     = $this->totp ?? new Totp();
        $mfaAttemptLimit          = $this->mfaAttemptLimit ?? new LimitMfaAttempts(
            storage: new InMemoryAttemptLimitStorage(),
            clock  : $clock
        );
        $passwordResetThrottle    = $this->passwordResetThrottle ?? new AttemptThrottle(
            store       : new InMemoryAttemptThrottleStore(),
            clock       : $clock,
            maxAttempts : 5,
            decaySeconds: 900
        );
        $mfaRecoveryThrottle      = $this->mfaRecoveryThrottle ?? new AttemptThrottle(
            store       : new InMemoryAttemptThrottleStore(),
            clock       : $clock,
            maxAttempts : 3,
            decaySeconds: 1800
        );
        $scimThrottle             = $this->scimThrottle ?? new AttemptThrottle(
            store       : new InMemoryAttemptThrottleStore(),
            clock       : $clock,
            maxAttempts : 60,
            decaySeconds: 60
        );
        $projectAuthenticatedUser = new ProjectAuthenticatedUser(
            emailVerificationState: $emailVerificationState,
            mfaStore              : $mfaStore
        );
        $currentAuthentication    = new CurrentAuthentication();
        $requireFreshMfa          = new RequireFreshMfa(
            currentAuthentication: $currentAuthentication,
            clock                : $clock
        );
        $generateBackupCodes      = new GenerateBackupCodes(
            passwordHasher: $passwordHasher,
            clock         : $clock
        );
        $verifyBackupCode         = new VerifyBackupCode(
            mfaStore      : $mfaStore,
            passwordHasher: $passwordHasher,
            auditLog      : $auditLog,
            clock         : $clock
        );
        $startMfaChallenge        = new StartMfaChallenge(
            currentAuthentication: $currentAuthentication,
            mfaStore             : $mfaStore,
            challengeStore       : $mfaChallengeStore,
            auditLog             : $auditLog,
            clock                : $clock
        );
        $authenticateRequest      = new AuthenticateRequest(
            currentAuthentication   : $currentAuthentication,
            projectAuthenticatedUser: $projectAuthenticatedUser,
            userSource              : $this->userSource,
            auditLog                : $auditLog,
            sessionIdentity         : $identity->sessionIdentity(),
            jwtIdentity             : $identity->jwtIdentity()
        );
        $readCurrentUser          = new ReadCurrentUser(
            currentAuthentication: $currentAuthentication
        );
        $checkAuthentication      = new CheckAuthentication(
            currentAuthentication: $currentAuthentication
        );
        $registerOAuthClient      = new RegisterClient(
            clientRegistry: $oauthClientRegistry,
            auditLog      : $auditLog,
            clock         : $clock
        );
        $approveOAuthClientRegistration = new ApproveClientRegistration(
            clientRegistry: $oauthClientRegistry,
            auditLog      : $auditLog,
            clock         : $clock
        );
        $updateOAuthClient        = new UpdateClient(
            clientRegistry: $oauthClientRegistry,
            auditLog      : $auditLog,
            clock         : $clock
        );
        $disableOAuthClient       = new DisableClient(
            clientRegistry: $oauthClientRegistry,
            auditLog      : $auditLog,
            clock         : $clock
        );
        $rotateOAuthClientSecret  = new RotateClientSecret(
            clientRegistry: $oauthClientRegistry,
            auditLog      : $auditLog,
            clock         : $clock
        );
        $readOAuthClients         = new ReadClients(
            clientRegistry: $oauthClientRegistry
        );
        $readWorkloadIdentities   = new ReadWorkloadIdentities(
            clientRegistry: $oauthClientRegistry
        );
        $readOidcProviderMetadata = $this->oidcProvider !== null
            ? new ReadOidcProviderMetadata(oidcProvider: $this->oidcProvider)
            : null;
        $readOidcJsonWebKeySet    = $this->oidcProvider !== null
            ? new ReadOidcJsonWebKeySet(oidcProvider: $this->oidcProvider)
            : null;
        $jwtIdentity              = $identity->jwtIdentity();
        $readOidcUserInfo         = $jwtIdentity !== null && $this->oidcProvider !== null
            ? new ReadOidcUserInfo(jwtIdentity: $jwtIdentity, oidcProvider: $this->oidcProvider)
            : null;
        $oidcRequestObjectStore   = $this->oidcProvider !== null
            ? ($this->oidcRequestObjectStore ?? new InMemoryOidcRequestObjectStore())
            : null;
        $pushOidcAuthorizationRequest = $oidcRequestObjectStore !== null
            ? new PushAuthorizationRequest(
                requestObjectStore: $oidcRequestObjectStore,
                auditLog          : $auditLog,
                clock             : $clock
            )
            : null;
        $validateRequestObject = $oidcRequestObjectStore !== null
            ? new ValidateRequestObject(requestObjectStore: $oidcRequestObjectStore)
            : null;
        $oidcLogout = $this->oidcProvider !== null
            ? new OidcLogout(
                frontChannelLogout: new FrontChannelLogout(
                    currentAuthentication: $currentAuthentication,
                    identity            : $identity,
                    auditLog            : $auditLog,
                    clock               : $clock,
                    sessionRegistry     : $this->sessionRegistry,
                    refreshTokenStore   : $this->refreshTokenStore,
                    oidcProvider        : $this->oidcProvider,
                    clientRegistry      : $oauthClientRegistry
                ),
                backChannelLogout : new BackChannelLogout(
                    currentAuthentication: $currentAuthentication,
                    identity            : $identity,
                    auditLog            : $auditLog,
                    clock               : $clock,
                    sessionRegistry     : $this->sessionRegistry,
                    refreshTokenStore   : $this->refreshTokenStore,
                    oidcProvider        : $this->oidcProvider,
                    clientRegistry      : $oauthClientRegistry
                )
            )
            : null;
        $buildOidcJarmResponse = $this->oidcProvider !== null
            ? new BuildJarmResponse(
                oidcProvider: $this->oidcProvider,
                clock       : $clock
            )
            : null;
        $authorizeOAuthCode       = new AuthorizeCode(
            currentAuthentication: $currentAuthentication,
            userSource           : $this->userSource,
            clientRegistry       : $oauthClientRegistry,
            codeStore            : $authorizationCodeStore,
            auditLog             : $auditLog,
            clock                : $clock,
            oidcProvider         : $this->oidcProvider,
            requestObjectValidator: $validateRequestObject
        );
        $oauthReady               = $identity->jwtIdentity() !== null && $this->refreshTokenStore !== null;
        $requireAdminElevation    = new RequireAdminElevation(
            currentAuthentication: $currentAuthentication,
            elevationStore       : $adminElevationStore,
            clock                : $clock
        );
        $requireAuthentication    = new RequireAuthentication(
            currentAuthentication: $currentAuthentication
        );
        $requireRole             = new RequireRole(
            currentAuthentication: $currentAuthentication
        );
        $requirePermission       = new RequirePermission(
            currentAuthentication: $currentAuthentication
        );
        $requireResourceOwner    = new RequireResourceOwner(
            currentAuthentication: $currentAuthentication
        );
        $requirePhishingResistantAuthentication = new RequirePhishingResistantAuthentication(
            currentAuthentication: $currentAuthentication
        );
        $access                  = new Access(
            requireAuthentication: $requireAuthentication,
            requireRole          : $requireRole,
            requirePermission    : $requirePermission,
            requireAccessPolicy  : new RequireAccessPolicy(
                requireAuthentication: $requireAuthentication,
                requireRole          : $requireRole,
                requirePermission    : $requirePermission,
                requireResourceOwner : $requireResourceOwner,
                requirePhishingResistantAuthentication: $requirePhishingResistantAuthentication,
                requireFreshMfa      : $requireFreshMfa,
                requireAdminElevation: $requireAdminElevation
            )
        );
        $provisionableUserSource  = $this->userSource instanceof ProvisionableUserSourceInterface
            ? $this->userSource
            : null;
        $lifecycle                = $provisionableUserSource !== null
            ? new LifecycleOrchestrator(
                userSource: $provisionableUserSource,
                store     : $lifecycleStore,
                auditLog  : $auditLog,
                clock     : $clock
            )
            : null;
        $scimDirectoryStore       = $this->scimDirectoryStore ?? new InMemoryScimDirectoryStore(passwordHasher: $passwordHasher);
        $scimProvisionedIdentityStore = $this->scimProvisionedIdentityStore ?? new InMemoryScimProvisionedIdentityStore();
        $tenantStore              = $this->tenantStore ?? new InMemoryTenantStore();
        $tenantSecurityConfigurationStore = $this->tenantSecurityConfigurationStore ?? new InMemoryTenantSecurityConfigurationStore();
        $tenantSecurityChangeRequestStore = $this->tenantSecurityChangeRequestStore ?? new InMemoryTenantSecurityChangeRequestStore();
        $readTenantSecurityConfiguration = new ReadTenantSecurityConfiguration(configurationStore: $tenantSecurityConfigurationStore);
        $beginTenantSecurityChange = new BeginTenantSecurityChange(
            configurationStore      : $tenantSecurityConfigurationStore,
            changeRequestStore      : $tenantSecurityChangeRequestStore,
            federationConnectionStore: $federationConnectionStore,
            scimDirectoryStore      : $scimDirectoryStore,
            auditLog                : $auditLog,
            clock                   : $clock
        );
        $approveTenantSecurityChange = new ApproveTenantSecurityChange(
            changeRequestStore : $tenantSecurityChangeRequestStore,
            auditLog           : $auditLog,
            clock              : $clock
        );
        $applyTenantSecurityChange = new ApplyTenantSecurityChange(
            configurationStore : $tenantSecurityConfigurationStore,
            changeRequestStore : $tenantSecurityChangeRequestStore,
            auditLog           : $auditLog,
            clock              : $clock
        );
        $rollbackTenantSecurityChange = new RollbackTenantSecurityChange(
            configurationStore : $tenantSecurityConfigurationStore,
            changeRequestStore : $tenantSecurityChangeRequestStore,
            auditLog           : $auditLog,
            clock              : $clock
        );
        $readTenantSecurityChangeRequest = new ReadTenantSecurityChangeRequest(changeRequestStore: $tenantSecurityChangeRequestStore);
        $readTenantSecurityChangeRequests = new ReadTenantSecurityChangeRequests(changeRequestStore: $tenantSecurityChangeRequestStore);
        $createTenant            = new CreateTenant(
            tenantStore: $tenantStore,
            userSource : $this->userSource,
            auditLog   : $auditLog,
            clock      : $clock
        );
        $readTenants             = new ReadTenants(tenantStore: $tenantStore);
        $inviteTenantMember      = new InviteTenantMember(
            tenantStore: $tenantStore,
            auditLog   : $auditLog,
            clock      : $clock
        );
        $acceptTenantInvite      = new AcceptTenantInvite(
            tenantStore: $tenantStore,
            userSource : $this->userSource,
            auditLog   : $auditLog,
            clock      : $clock
        );
        $readTenantMembers       = new ReadTenantMembers(tenantStore: $tenantStore);
        $removeTenantMember      = new RemoveTenantMember(
            tenantStore: $tenantStore,
            auditLog   : $auditLog,
            clock      : $clock
        );
        $suspendTenantMember     = new SuspendTenantMember(
            tenantStore: $tenantStore,
            auditLog   : $auditLog,
            clock      : $clock
        );
        $transferTenantOwnership = new TransferTenantOwnership(
            tenantStore: $tenantStore,
            auditLog   : $auditLog,
            clock      : $clock
        );
        $passkeyReady             = $this->passkeyRuntime !== null;
        $federationReady          = $this->federationRuntime !== null;
        $scimReady                = $provisionableUserSource !== null;
        $provisionScimUser        = $scimReady
            ? new ProvisionScimUser(
                userSource      : $provisionableUserSource,
                directoryStore  : $scimDirectoryStore,
                identityStore   : $scimProvisionedIdentityStore,
                passwordHasher  : $passwordHasher,
                idGenerator     : $this->idGenerator ?? new IdGenerator(),
                auditLog        : $auditLog,
                clock           : $clock,
                lifecycle       : $lifecycle,
                attemptThrottle : $scimThrottle
            )
            : null;
        $readScimUsers            = $scimReady
            ? new ReadScimUsers(
                identityStore : $scimProvisionedIdentityStore,
                userSource    : $this->userSource
            )
            : null;
        $readScimGroups           = $scimReady && $readScimUsers !== null
            ? new ReadScimGroups(readScimUsers: $readScimUsers)
            : null;
        $runScimBulk              = $scimReady && $provisionScimUser !== null
            ? new RunScimBulk(
                provisionScimUser: $provisionScimUser,
                deleteScimUser   : new DeleteScimUser(
                    userSource     : $provisionableUserSource,
                    directoryStore : $scimDirectoryStore,
                    identityStore  : $scimProvisionedIdentityStore,
                    auditLog       : $auditLog,
                    clock          : $clock,
                    lifecycle      : $lifecycle
                )
            )
            : null;

        return new Auth(
            login                 : new Login(
                                        userSource              : $this->userSource,
                                        passwordHasher          : $passwordHasher,
                                        identity                : $identity,
                                        projectAuthenticatedUser: $projectAuthenticatedUser,
                                        currentAuthentication   : $currentAuthentication,
                                        auditLog                : $auditLog,
                                        mfaStore                : $mfaStore,
                                        startMfaChallenge       : $startMfaChallenge,
                                        rateLimit               : $this->rateLimit,
                                        riskEngine              : $riskEngine
                                    ),
            authenticateRequest   : $authenticateRequest,
            logout                : new Logout(
                                        identity             : $identity,
                                        currentAuthentication: $currentAuthentication,
                                        auditLog             : $auditLog,
                                        clock                : $clock,
                                        sessionRegistry      : $this->sessionRegistry,
                                        refreshTokenStore    : $this->refreshTokenStore
                                    ),
            logoutAllSessions     : new LogoutAllSessions(
                                        identity             : $identity,
                                        currentAuthentication: $currentAuthentication,
                                        auditLog             : $auditLog,
                                        clock                : $clock,
                                        sessionRegistry      : $this->sessionRegistry,
                                        refreshTokenStore    : $this->refreshTokenStore
                                    ),
            checkAuthentication   : $checkAuthentication,
            readCurrentUser       : $readCurrentUser,
            readActiveSessions    : new ReadActiveSessions(
                                        currentAuthentication: $currentAuthentication,
                                        clock                : $clock,
                                        sessionRegistry      : $this->sessionRegistry
                                    ),
            revokeSession         : new RevokeSession(
                                        identity             : $identity,
                                        currentAuthentication: $currentAuthentication,
                                        auditLog             : $auditLog,
                                        clock                : $clock,
                                        sessionRegistry      : $this->sessionRegistry
                                    ),
            currentAuthentication : $currentAuthentication,
            access                : $access,
            changePassword        : new ChangePassword(
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
            beginEmailChange      : new BeginEmailChange(
                currentAuthentication: $currentAuthentication,
                userSource           : $this->userSource,
                passwordHasher       : $passwordHasher,
                emailChangeStore     : $emailChangeStore,
                requireFreshMfa      : $requireFreshMfa,
                auditLog             : $auditLog,
                clock                : $clock
            ),
            confirmEmailChange    : $provisionableUserSource !== null
                ? new ConfirmEmailChange(
                    userSource           : $provisionableUserSource,
                    emailChangeStore     : $emailChangeStore,
                    emailVerificationState: $emailVerificationState,
                    auditLog             : $auditLog,
                    clock                : $clock,
                    currentAuthentication: $currentAuthentication,
                    identity             : $identity,
                    sessionRegistry      : $this->sessionRegistry,
                    mfaChallengeStore    : $mfaChallengeStore,
                    refreshTokenStore    : $this->refreshTokenStore
                )
                : null,
            register              : new Register(
                                        userSource               : $this->userSource,
                                        passwordHasher           : $passwordHasher,
                                        idGenerator              : $this->idGenerator ?? new IdGenerator(),
                                        projectAuthenticatedUser : $projectAuthenticatedUser,
                                        auditLog                 : $auditLog,
                                        emailVerificationRequired: $this->emailVerificationState !== null || $this->emailVerificationStore !== null,
                                        rateLimit                : $this->rateLimit
                                    ),
            refreshAuthentication : new RefreshAuthentication(
                                        userSource              : $this->userSource,
                                        projectAuthenticatedUser: $projectAuthenticatedUser,
                                        currentAuthentication   : $currentAuthentication,
                                        auditLog                : $auditLog,
                                        clock                   : $clock,
                                        refreshTokenStore       : $this->refreshTokenStore,
                                        jwtIdentity             : $identity->jwtIdentity(),
                                        riskEngine              : $riskEngine
                                    ),
            registerOAuthClient   : $oauthReady ? $registerOAuthClient : null,
            approveOAuthClientRegistration: $oauthReady ? $approveOAuthClientRegistration : null,
            updateOAuthClient     : $oauthReady ? $updateOAuthClient : null,
            disableOAuthClient    : $oauthReady ? $disableOAuthClient : null,
            rotateOAuthClientSecret: $oauthReady ? $rotateOAuthClientSecret : null,
            readOAuthClients      : $oauthReady ? $readOAuthClients : null,
            readWorkloadIdentities: $oauthReady ? $readWorkloadIdentities : null,
            readOidcProviderMetadata: $oauthReady ? $readOidcProviderMetadata : null,
            readOidcJsonWebKeySet : $oauthReady ? $readOidcJsonWebKeySet : null,
            readOidcUserInfo      : $oauthReady ? $readOidcUserInfo : null,
            pushOidcAuthorizationRequest: $oauthReady ? $pushOidcAuthorizationRequest : null,
            oidcLogout            : $oauthReady ? $oidcLogout : null,
            buildOidcJarmResponse : $oauthReady ? $buildOidcJarmResponse : null,
            authorizeOAuthCode    : $oauthReady ? $authorizeOAuthCode : null,
            exchangeOAuthCode     : $oauthReady
                ? new ExchangeAuthorizationCode(
                    clientRegistry   : $oauthClientRegistry,
                    codeStore        : $authorizationCodeStore,
                    userSource       : $this->userSource,
                    jwtIdentity      : $identity->jwtIdentity() ?? throw new RuntimeException(message: 'JWT identity is required.'),
                    refreshTokenStore: $this->refreshTokenStore ?? throw new RuntimeException(message: 'Refresh token store is required.'),
                    auditLog         : $auditLog,
                    clock            : $clock,
                    currentAuthentication: $currentAuthentication,
                    oidcProvider     : $this->oidcProvider
                )
                : null,
            exchangeOAuthClientCredentials: $oauthReady
                ? new ExchangeClientCredentials(
                    clientRegistry: $oauthClientRegistry,
                    jwtIdentity   : $identity->jwtIdentity() ?? throw new RuntimeException(message: 'JWT identity is required.'),
                    auditLog      : $auditLog,
                    clock         : $clock
                )
                : null,
            exchangeOAuthRefreshToken: $oauthReady
                ? new ExchangeRefreshToken(
                    clientRegistry   : $oauthClientRegistry,
                    refreshTokenStore: $this->refreshTokenStore ?? throw new RuntimeException(message: 'Refresh token store is required.'),
                    userSource       : $this->userSource,
                    jwtIdentity      : $identity->jwtIdentity() ?? throw new RuntimeException(message: 'JWT identity is required.'),
                    auditLog         : $auditLog,
                    clock            : $clock,
                    riskEngine       : $riskEngine
                )
                : null,
            revokeOAuthToken      : $oauthReady
                ? new RevokeToken(
                    clientRegistry   : $oauthClientRegistry,
                    refreshTokenStore: $this->refreshTokenStore ?? throw new RuntimeException(message: 'Refresh token store is required.'),
                    jwtIdentity      : $identity->jwtIdentity() ?? throw new RuntimeException(message: 'JWT identity is required.'),
                    auditLog         : $auditLog,
                    clock            : $clock
                )
                : null,
            introspectOAuthToken  : $oauthReady
                ? new IntrospectToken(
                    clientRegistry: $oauthClientRegistry,
                    jwtIdentity   : $identity->jwtIdentity() ?? throw new RuntimeException(message: 'JWT identity is required.'),
                    auditLog      : $auditLog,
                    clock         : $clock
                )
                : null,
            beginAdminElevation   : new BeginAdminElevation(
                                        currentAuthentication: $currentAuthentication,
                                        requireFreshMfa      : $requireFreshMfa,
                                        elevationStore       : $adminElevationStore,
                                        auditLog             : $auditLog,
                                        clock                : $clock,
                                        phishingResistantRequired: $this->adminPhishingResistantRequired
                                    ),
            endAdminElevation     : new EndAdminElevation(
                                        currentAuthentication: $currentAuthentication,
                                        elevationStore       : $adminElevationStore,
                                        auditLog             : $auditLog,
                                        clock                : $clock
                                    ),
            requireAdminElevation : $requireAdminElevation,
            suspendUser           : $provisionableUserSource !== null
                ? new SuspendUser(
                    userSource         : $provisionableUserSource,
                    requireAdminElevation: $requireAdminElevation,
                    auditLog           : $auditLog,
                    clock              : $clock,
                    sessionRegistry    : $this->sessionRegistry,
                    refreshTokenStore  : $this->refreshTokenStore,
                    adminElevationStore: $adminElevationStore,
                    lifecycle          : $lifecycle
                )
                : null,
            reactivateUser        : $provisionableUserSource !== null
                ? new ReactivateUser(
                    userSource         : $provisionableUserSource,
                    requireAdminElevation: $requireAdminElevation,
                    auditLog           : $auditLog,
                    clock              : $clock,
                    lifecycle          : $lifecycle
                )
                : null,
            deprovisionUser       : $provisionableUserSource !== null
                ? new DeprovisionUser(
                    userSource         : $provisionableUserSource,
                    requireAdminElevation: $requireAdminElevation,
                    auditLog           : $auditLog,
                    clock              : $clock,
                    sessionRegistry    : $this->sessionRegistry,
                    refreshTokenStore  : $this->refreshTokenStore,
                    adminElevationStore: $adminElevationStore,
                    lifecycle          : $lifecycle
                )
                : null,
            beginPasskeyRegistration: $passkeyReady
                ? new BeginPasskeyRegistration(
                    currentAuthentication: $currentAuthentication,
                    requireFreshMfa      : $requireFreshMfa,
                    runtime              : $this->passkeyRuntime ?? throw new RuntimeException(message: 'Passkey runtime is required.'),
                    credentialStore      : $passkeyCredentialStore,
                    challengeStore       : $passkeyChallengeStore,
                    auditLog             : $auditLog,
                    clock                : $clock,
                    rpId                 : $this->passkeyRpId,
                    rpName               : $this->passkeyRpName
                )
                : null,
            completePasskeyRegistration: $passkeyReady
                ? new CompletePasskeyRegistration(
                    currentAuthentication: $currentAuthentication,
                    runtime              : $this->passkeyRuntime ?? throw new RuntimeException(message: 'Passkey runtime is required.'),
                    credentialStore      : $passkeyCredentialStore,
                    challengeStore       : $passkeyChallengeStore,
                    auditLog             : $auditLog,
                    clock                : $clock,
                    rpId                 : $this->passkeyRpId
                )
                : null,
            beginPasskeyAuthentication: $passkeyReady
                ? new BeginPasskeyAuthentication(
                    userSource      : $this->userSource,
                    runtime         : $this->passkeyRuntime ?? throw new RuntimeException(message: 'Passkey runtime is required.'),
                    credentialStore : $passkeyCredentialStore,
                    challengeStore  : $passkeyChallengeStore,
                    auditLog        : $auditLog,
                    clock           : $clock,
                    rpId            : $this->passkeyRpId
                )
                : null,
            completePasskeyAuthentication: $passkeyReady
                ? new CompletePasskeyAuthentication(
                    runtime              : $this->passkeyRuntime ?? throw new RuntimeException(message: 'Passkey runtime is required.'),
                    challengeStore       : $passkeyChallengeStore,
                    credentialStore      : $passkeyCredentialStore,
                    userSource           : $this->userSource,
                    identity             : $identity,
                    projectAuthenticatedUser: $projectAuthenticatedUser,
                    currentAuthentication: $currentAuthentication,
                    auditLog             : $auditLog,
                    clock                : $clock,
                    rpId                 : $this->passkeyRpId
                )
                : null,
            readPasskeys          : $passkeyReady
                ? new ListPasskeys(
                    currentAuthentication: $currentAuthentication,
                    credentialStore      : $passkeyCredentialStore
                )
                : null,
            renamePasskey         : $passkeyReady
                ? new RenamePasskey(
                    currentAuthentication: $currentAuthentication,
                    credentialStore      : $passkeyCredentialStore
                )
                : null,
            revokePasskey         : $passkeyReady
                ? new RevokePasskey(
                    currentAuthentication: $currentAuthentication,
                    requireFreshMfa      : $requireFreshMfa,
                    credentialStore      : $passkeyCredentialStore,
                    auditLog             : $auditLog,
                    clock                : $clock
                )
                : null,
            registerFederationConnection: $federationReady
                ? new RegisterFederationConnection(
                    connectionStore: $federationConnectionStore,
                    groupRoleMappingValidator: $groupRoleMappingValidator,
                    auditLog      : $auditLog,
                    clock         : $clock
                )
                : null,
            readFederationConnections: $federationReady
                ? new ReadFederationConnections(
                    connectionStore: $federationConnectionStore
                )
                : null,
            verifyFederationDomain: $federationReady
                ? new VerifyFederationDomain(
                    connectionStore: $federationConnectionStore,
                    auditLog      : $auditLog,
                    clock         : $clock
                )
                : null,
            syncFederationMetadata: $federationReady && $this->federationRuntime instanceof FederationMetadataRuntimeInterface
                ? new SyncFederationMetadata(
                    connectionStore: $federationConnectionStore,
                    runtime        : $this->federationRuntime,
                    auditLog       : $auditLog,
                    clock          : $clock
                )
                : null,
            checkFederationConnectionHealth: $federationReady && $this->federationRuntime instanceof FederationHealthCheckInterface
                ? new CheckFederationConnectionHealth(
                    connectionStore: $federationConnectionStore,
                    runtime        : $this->federationRuntime,
                    auditLog       : $auditLog,
                    clock          : $clock
                )
                : null,
            evaluateFederationBreakGlassBypass: $federationReady
                ? new EvaluateFederationBreakGlassBypass(
                    connectionStore: $federationConnectionStore,
                    auditLog       : $auditLog,
                    clock          : $clock
                )
                : null,
            discoverFederationConnection: $federationReady
                ? new DiscoverFederationConnection(
                    connectionStore: $federationConnectionStore
                )
                : null,
            startFederatedLogin   : $federationReady
                ? new StartFederatedLogin(
                    connectionStore: $federationConnectionStore,
                    runtime        : $this->federationRuntime ?? throw new RuntimeException(message: 'Federation runtime is required.'),
                    auditLog       : $auditLog,
                    clock          : $clock
                )
                : null,
            completeFederatedLogin: $federationReady
                ? new CompleteFederatedLogin(
                    connectionStore       : $federationConnectionStore,
                    runtime               : $this->federationRuntime ?? throw new RuntimeException(message: 'Federation runtime is required.'),
                    linkStore             : $federatedIdentityLinkStore,
                    userSource            : $this->userSource,
                    identity              : $identity,
                    projectAuthenticatedUser: $projectAuthenticatedUser,
                    currentAuthentication : $currentAuthentication,
                    passwordHasher        : $passwordHasher,
                    idGenerator           : $this->idGenerator ?? new IdGenerator(),
                    auditLog              : $auditLog,
                    clock                 : $clock,
                    riskEngine            : $riskEngine,
                    lifecycle             : $lifecycle
                )
                : null,
            registerScimDirectory : $scimReady
                ? new RegisterScimDirectory(
                    directoryStore          : $scimDirectoryStore,
                    passwordHasher          : $passwordHasher,
                    groupRoleMappingValidator: new GroupRoleMappingValidator(),
                    auditLog                : $auditLog,
                    clock                   : $clock
                )
                : null,
            readScimDirectories   : $scimReady
                ? new ReadScimDirectories(directoryStore: $scimDirectoryStore)
                : null,
            rotateScimToken       : $scimReady
                ? new RotateScimToken(
                    directoryStore : $scimDirectoryStore,
                    passwordHasher : $passwordHasher,
                    auditLog       : $auditLog,
                    clock          : $clock,
                    attemptThrottle: $scimThrottle
                )
                : null,
            markScimDirectoryOutage: $scimReady
                ? new MarkScimDirectoryOutage(
                    directoryStore : $scimDirectoryStore,
                    auditLog       : $auditLog,
                    clock          : $clock
                )
                : null,
            recoverScimDirectoryOutage: $scimReady
                ? new RecoverScimDirectoryOutage(
                    directoryStore : $scimDirectoryStore,
                    auditLog       : $auditLog,
                    clock          : $clock
                )
                : null,
            provisionScimUser     : $provisionScimUser,
            deleteScimUser        : $scimReady
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
            readScimUsers         : $readScimUsers,
            readScimGroups        : $readScimGroups,
            syncScimGroups        : $scimReady && $provisionScimUser !== null
                ? new SyncScimGroups(
                    directoryStore   : $scimDirectoryStore,
                    identityStore    : $scimProvisionedIdentityStore,
                    userSource       : $this->userSource,
                    provisionScimUser: $provisionScimUser
                )
                : null,
            runScimBulk           : $runScimBulk,
            createTenant          : $createTenant,
            readTenants           : $readTenants,
            inviteTenantMember    : $inviteTenantMember,
            acceptTenantInvite    : $acceptTenantInvite,
            readTenantMembers     : $readTenantMembers,
            removeTenantMember    : $removeTenantMember,
            suspendTenantMember   : $suspendTenantMember,
            transferTenantOwnership: $transferTenantOwnership,
            readTenantSecurityConfiguration: $readTenantSecurityConfiguration,
            readTenantSecurityChangeRequest: $readTenantSecurityChangeRequest,
            readTenantSecurityChangeRequests: $readTenantSecurityChangeRequests,
            beginTenantSecurityChange: $beginTenantSecurityChange,
            approveTenantSecurityChange: $approveTenantSecurityChange,
            applyTenantSecurityChange: $applyTenantSecurityChange,
            rollbackTenantSecurityChange: $rollbackTenantSecurityChange,
            assessCurrentRisk     : new AssessCurrentRisk(
                                        currentAuthentication: $currentAuthentication,
                                        userSource           : $this->userSource,
                                        riskEngine           : $riskEngine
                                    ),
            readRiskSignals       : new ReadRiskSignals(
                                        currentAuthentication: $currentAuthentication,
                                        riskEngine           : $riskEngine
                                    ),
            beginPasswordReset    : new BeginPasswordReset(
                                        userSource        : $this->userSource,
                                        passwordResetStore: $passwordResetStore,
                                        auditLog          : $auditLog,
                                        clock             : $clock,
                                        attemptThrottle   : $passwordResetThrottle
                                    ),
            resetPassword         : new ResetPassword(
                                        userSource        : $this->userSource,
                                        passwordHasher    : $passwordHasher,
                                        passwordResetStore: $passwordResetStore,
                                        auditLog          : $auditLog,
                                        clock             : $clock,
                                        sessionRegistry   : $this->sessionRegistry,
                                        mfaChallengeStore : $mfaChallengeStore,
                                        refreshTokenStore : $this->refreshTokenStore
                                    ),
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
                                    ),
            startMfaEnrollment    : new StartMfaEnrollment(
                                        currentAuthentication: $currentAuthentication,
                                        mfaStore             : $mfaStore,
                                        totp                 : $totp,
                                        auditLog             : $auditLog,
                                        clock                : $clock,
                                        issuer               : $this->mfaIssuer
                                    ),
            confirmMfaEnrollment  : new ConfirmMfaEnrollment(
                                        currentAuthentication: $currentAuthentication,
                                        mfaStore             : $mfaStore,
                                        totp                 : $totp,
                                        generateBackupCodes  : $generateBackupCodes,
                                        auditLog             : $auditLog,
                                        clock                : $clock
                                    ),
            cancelMfaEnrollment   : new CancelMfaEnrollment(
                                        currentAuthentication: $currentAuthentication,
                                        mfaStore             : $mfaStore,
                                        auditLog             : $auditLog,
                                        clock                : $clock
                                    ),
            startMfaChallenge     : $startMfaChallenge,
            verifyMfaChallenge    : new VerifyMfaChallenge(
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
            regenerateBackupCodes : new RegenerateBackupCodes(
                                        currentAuthentication: $currentAuthentication,
                                        requireFreshMfa      : $requireFreshMfa,
                                        mfaStore             : $mfaStore,
                                        generateBackupCodes  : $generateBackupCodes,
                                        auditLog             : $auditLog,
                                        clock                : $clock
                                    ),
            disableMfa            : new DisableMfa(
                                        currentAuthentication: $currentAuthentication,
                                        requireFreshMfa      : $requireFreshMfa,
                                        mfaStore             : $mfaStore,
                                        mfaChallengeStore    : $mfaChallengeStore,
                                        auditLog             : $auditLog,
                                        clock                : $clock,
                                        refreshTokenStore    : $this->refreshTokenStore
                                    ),
            startMfaRecovery      : new StartMfaRecovery(
                                        userSource     : $this->userSource,
                                        mfaStore       : $mfaStore,
                                        auditLog       : $auditLog,
                                        clock          : $clock,
                                        attemptThrottle: $mfaRecoveryThrottle
                                    ),
            confirmMfaRecovery    : new ConfirmMfaRecovery(
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
    }
}
