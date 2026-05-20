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
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
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
use Avax\Components\Identity\Auth\System\Configuration\Readiness\AuthBootstrapValidator;
use Avax\Components\Identity\Auth\System\Configuration\Readiness\AuthCapabilityReadiness;
use Avax\Components\Identity\Auth\System\Configuration\Readiness\AuthCapabilityRequests;
use Avax\Components\Identity\Auth\System\Configuration\Assembly\AssembleAuthIdentityGraph;
use Avax\Components\Identity\Auth\System\Configuration\Assembly\AssembleAuthExternalIdentityGraph;
use Avax\Components\Identity\Auth\System\Configuration\Assembly\BuildCredentialGraph;
use Avax\Components\Identity\Auth\System\Configuration\Assembly\BuildOAuthGraph;
use Avax\Components\Identity\Auth\System\Configuration\Assembly\BuildFederationGraph;
use Avax\Components\Identity\Auth\System\Configuration\Assembly\BuildScimGraph;
use Avax\Components\Identity\Auth\System\Configuration\Assembly\BuildTenancyGraph;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimProvisionedIdentityStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\AuthorizationCodeStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcRequestObjectStoreInterface;
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
use Avax\Components\Identity\Auth\System\Flows\Login\FindUserByCredentials;
use Avax\Components\Identity\Auth\System\Flows\Login\Login;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Components\Identity\Auth\System\Flows\Login\StartAuthenticatedSession;
use Avax\Components\Identity\Auth\System\Flows\Login\VerifyPassword;
use Avax\Components\Identity\Auth\System\Flows\Logout\ClearAuthenticatedIdentity;
use Avax\Components\Identity\Auth\System\Flows\Logout\Logout;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordReset;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\InMemoryPasswordResetStore;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\PasswordResetStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPassword;
use Avax\Components\Identity\Auth\System\Flows\Register\CreateRegisteredUser;
use Avax\Components\Identity\Auth\System\Flows\Register\HashRegisteredPassword;
use Avax\Components\Identity\Auth\System\Flows\Register\Register;
use Avax\Components\Identity\Auth\System\Flows\Register\ValidateRegistrationData;
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
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\LimitMfaAttempts;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\InMemoryAttemptLimitStorage;
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
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederatedIdentityLinkStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationRuntimeInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationMetadataRuntimeInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationHealthCheckInterface;
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
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityConfiguration;
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
 * Delegates bounded configuration responsibilities to focused sub-builders:
 * BuildCredentialGraph (MFA/Passkey), BuildOAuthGraph (OAuth/OIDC),
 * BuildFederationGraph (Federation), BuildScimGraph (SCIM),
 * BuildTenancyGraph (Tenancy).
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
    private EmailVerificationStoreInterface|null $emailVerificationStore = null;
    private EmailChangeStoreInterface|null $emailChangeStore = null;
    private PasswordResetStoreInterface|null $passwordResetStore = null;
    private Clock|null $clock = null;
    private SessionRegistryInterface|null $sessionRegistry = null;

    private bool $enterpriseMode = false;

    private BuildCredentialGraph $credentialBuilder;
    private BuildOAuthGraph $oAuthBuilder;
    private BuildFederationGraph $federationBuilder;
    private BuildScimGraph $scimBuilder;
    private BuildTenancyGraph $tenancyBuilder;

    public function __construct()
    {
        $this->credentialBuilder   = new BuildCredentialGraph();
        $this->oAuthBuilder        = new BuildOAuthGraph();
        $this->federationBuilder   = new BuildFederationGraph();
        $this->scimBuilder         = new BuildScimGraph();
        $this->tenancyBuilder      = new BuildTenancyGraph();
    }

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
    ) : self {
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

    public function withPasswordResetStore(#[SensitiveParameter] PasswordResetStoreInterface $passwordResetStore) : self
    {
        $this->passwordResetStore = $passwordResetStore;

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

    // Delegate to credential sub-builder
    public function withMfaStore(MfaStoreInterface $mfaStore) : self { $this->credentialBuilder->withMfaStore($mfaStore); return $this; }
    public function withMfaChallengeStore(MfaChallengeStoreInterface $mfaChallengeStore) : self { $this->credentialBuilder->withMfaChallengeStore($mfaChallengeStore); return $this; }
    public function usingTotp(TotpInterface $totp) : self { $this->credentialBuilder->usingTotp($totp); return $this; }
    public function withMfaAttemptLimit(LimitMfaAttempts $limitMfaAttempts) : self { $this->credentialBuilder->withMfaAttemptLimit($limitMfaAttempts); return $this; }
    public function withPasswordResetThrottle(#[SensitiveParameter] AttemptThrottle $attemptThrottle) : self { $this->credentialBuilder->withPasswordResetThrottle($attemptThrottle); return $this; }
    public function withMfaRecoveryThrottle(AttemptThrottle $attemptThrottle) : self { $this->credentialBuilder->withMfaRecoveryThrottle($attemptThrottle); return $this; }
    public function withScimThrottle(AttemptThrottle $attemptThrottle) : self { $this->scimBuilder->withScimThrottle($attemptThrottle); return $this; }
    public function withPasskeyRuntime(PasskeyRuntimeInterface $passkeyRuntime) : self { $this->credentialBuilder->withPasskeyRuntime($passkeyRuntime); return $this; }
    public function withPasskeyCredentialStore(#[SensitiveParameter] PasskeyCredentialStoreInterface $passkeyCredentialStore) : self { $this->credentialBuilder->withPasskeyCredentialStore($passkeyCredentialStore); return $this; }
    public function withPasskeyChallengeStore(PasskeyChallengeStoreInterface $passkeyChallengeStore) : self { $this->credentialBuilder->withPasskeyChallengeStore($passkeyChallengeStore); return $this; }
    public function withPasskeyRelyingParty(string $rpId, string $rpName) : self { $this->credentialBuilder->withPasskeyRelyingParty($rpId, $rpName); return $this; }
    public function withMfaIssuer(string $mfaIssuer) : self { $this->credentialBuilder->withMfaIssuer($mfaIssuer); return $this; }

    // Delegate to OAuth sub-builder
    public function withRefreshTokenStore(#[SensitiveParameter] RefreshTokenStoreInterface $refreshTokenStore) : self { $this->oAuthBuilder->withRefreshTokenStore($refreshTokenStore); return $this; }
    public function withOAuthClientRegistry(#[SensitiveParameter] OAuthClientRegistryInterface $oauthClientRegistry) : self { $this->oAuthBuilder->withOAuthClientRegistry($oauthClientRegistry); return $this; }
    public function withAuthorizationCodeStore(#[SensitiveParameter] AuthorizationCodeStoreInterface $authorizationCodeStore) : self { $this->oAuthBuilder->withAuthorizationCodeStore($authorizationCodeStore); return $this; }
    public function withOidcProvider(OidcProviderInterface $oidcProvider) : self { $this->oAuthBuilder->withOidcProvider($oidcProvider); return $this; }
    public function withOidcRequestObjectStore(OidcRequestObjectStoreInterface $oidcRequestObjectStore) : self { $this->oAuthBuilder->withOidcRequestObjectStore($oidcRequestObjectStore); return $this; }

    // Delegate to Federation sub-builder
    public function withFederationRuntime(FederationRuntimeInterface $federationRuntime) : self { $this->federationBuilder->withFederationRuntime($federationRuntime); return $this; }
    public function withFederationConnectionStore(FederationConnectionStoreInterface $federationConnectionStore) : self { $this->federationBuilder->withFederationConnectionStore($federationConnectionStore); return $this; }
    public function withFederatedIdentityLinkStore(FederatedIdentityLinkStoreInterface $federatedIdentityLinkStore) : self { $this->federationBuilder->withFederatedIdentityLinkStore($federatedIdentityLinkStore); return $this; }

    // Delegate to SCIM sub-builder
    public function withScimDirectoryStore(ScimDirectoryStoreInterface $scimDirectoryStore) : self { $this->scimBuilder->withScimDirectoryStore($scimDirectoryStore); return $this; }
    public function withScimProvisionedIdentityStore(ScimProvisionedIdentityStoreInterface $scimProvisionedIdentityStore) : self { $this->scimBuilder->withScimProvisionedIdentityStore($scimProvisionedIdentityStore); return $this; }
    public function withLifecycleStore(#[SensitiveParameter] LifecycleStoreInterface $lifecycleStore) : self { $this->scimBuilder->withLifecycleStore($lifecycleStore); return $this; }

    // Delegate to Tenancy sub-builder
    public function withTenantStore(#[SensitiveParameter] TenantStoreInterface $tenantStore) : self { $this->tenancyBuilder->withTenantStore($tenantStore); return $this; }
    public function withTenantSecurityConfigurationStore(#[SensitiveParameter] TenantSecurityConfigurationStoreInterface $tenantSecurityConfigurationStore) : self { $this->tenancyBuilder->withTenantSecurityConfigurationStore($tenantSecurityConfigurationStore); return $this; }
    public function withTenantSecurityChangeRequestStore(#[SensitiveParameter] TenantSecurityChangeRequestStoreInterface $tenantSecurityChangeRequestStore) : self { $this->tenancyBuilder->withTenantSecurityChangeRequestStore($tenantSecurityChangeRequestStore); return $this; }
    public function withAdminElevationStore(AdminElevationStoreInterface $adminElevationStore) : self { $this->tenancyBuilder->withAdminElevationStore($adminElevationStore); return $this; }
    public function withRiskEngine(DeterministicRiskEngine $deterministicRiskEngine) : self { $this->tenancyBuilder->withRiskEngine($deterministicRiskEngine); return $this; }
    public function requirePhishingResistantAdminElevation(bool $required = true) : self { $this->tenancyBuilder->requirePhishingResistantAdminElevation($required); return $this; }

    /**
     * Configure the builder with default dependencies from the DI container.
     */
    public function withContainer(ContainerInterface $container) : self
    {
        $this->clock ??= $container->get(Clock::class);
        $this->idGenerator ??= $container->get(IdGenerator::class);
        $this->passwordHasher ??= $container->get(PasswordHasher::class);
        $this->auditLog ??= $container->get(NullAuditLog::class);

        $this->passwordResetStore ??= $container->get(InMemoryPasswordResetStore::class);
        $this->emailVerificationStore ??= $container->get(InMemoryEmailVerificationStore::class);
        $this->emailChangeStore ??= $container->get(InMemoryEmailChangeStore::class);
        $this->emailVerificationStateStore ??= $container->get(InMemoryEmailVerificationStateStore::class);

        // Credential defaults
        $this->credentialBuilder->withMfaStore($container->get(InMemoryMfaStore::class));
        $this->credentialBuilder->withMfaChallengeStore($container->get(InMemoryMfaChallengeStore::class));
        $this->credentialBuilder->usingTotp($container->get(Totp::class));
        $this->credentialBuilder->withMfaAttemptLimit($container->get(LimitMfaAttempts::class));
        $this->credentialBuilder->withPasskeyCredentialStore($container->get(InMemoryPasskeyCredentialStore::class));
        $this->credentialBuilder->withPasskeyChallengeStore($container->get(InMemoryPasskeyChallengeStore::class));

        // OAuth defaults
        $this->oAuthBuilder->withOAuthClientRegistry($container->get(\Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\InMemoryOAuthClientRegistry::class));
        $this->oAuthBuilder->withAuthorizationCodeStore($container->get(\Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\InMemoryAuthorizationCodeStore::class));

        // Federation defaults
        $this->federationBuilder->withFederationConnectionStore($container->get(\Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\InMemoryFederationConnectionStore::class));
        $this->federationBuilder->withFederatedIdentityLinkStore($container->get(\Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\InMemoryFederatedIdentityLinkStore::class));

        // SCIM defaults
        $this->scimBuilder->withLifecycleStore($container->get(\Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\InMemoryLifecycleStore::class));
        $this->scimBuilder->withScimDirectoryStore($container->get(\Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\InMemoryScimDirectoryStore::class));
        $this->scimBuilder->withScimProvisionedIdentityStore($container->get(\Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\InMemoryScimProvisionedIdentityStore::class));

        // Tenancy defaults
        $this->tenancyBuilder->withAdminElevationStore($container->get(InMemoryAdminElevationStore::class));
        $this->tenancyBuilder->withTenantStore($container->get(InMemoryTenantStore::class));
        $this->tenancyBuilder->withTenantSecurityConfigurationStore($container->get(\Avax\Components\Identity\Tenancy\System\Capabilities\Security\InMemoryTenantSecurityConfigurationStore::class));
        $this->tenancyBuilder->withTenantSecurityChangeRequestStore($container->get(\Avax\Components\Identity\Tenancy\System\Capabilities\Security\InMemoryTenantSecurityChangeRequestStore::class));
        $this->tenancyBuilder->withRiskEngine($container->get(DeterministicRiskEngine::class));

        // OIDC defaults
        $this->oAuthBuilder->withOidcRequestObjectStore($container->get(\Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\InMemoryOidcRequestObjectStore::class));

        // Named throttle bindings
        $this->credentialBuilder->withPasswordResetThrottle($container->get('auth.throttle.password_reset'));
        $this->credentialBuilder->withMfaRecoveryThrottle($container->get('auth.throttle.mfa_recovery'));
        $this->scimBuilder->withScimThrottle($container->get('auth.throttle.scim'));

        return $this;
    }

    /**
     * Build the final Auth instance.
     */
    public function ready() : Auth
    {
        $credential   = $this->credentialBuilder->build();
        $oAuth        = $this->oAuthBuilder->build();
        $federation   = $this->federationBuilder->build();
        $scim         = $this->scimBuilder->build();
        $tenancy      = $this->tenancyBuilder->build();

        AuthBootstrapValidator::validate(
            userSource: $this->userSource ?? throw ConfigurationException::missingDependency("UserSource", "forUser()"),
            identity: $this->identity,
            authCapabilityRequests: $this->capabilityRequests(),
            sessionRegistry: $this->sessionRegistry,
            refreshTokenStore: $oAuth['refreshTokenStore'],
            passkeyRuntime: $credential['passkeyRuntime'],
            federationRuntime: $federation['federationRuntime'],
            oidcProvider: $oAuth['oidcProvider'],
        );

        $identity       = $this->identity ?? throw ConfigurationException::missingDependency('Identity', 'withIdentity() or withIdentityBackends()');
        $passwordHasher = $this->passwordHasher ?? throw ConfigurationException::missingDependency('PasswordHasher', 'withHasher() or AuthServiceProvider');
        $auditLog       = $this->auditLog ?? throw ConfigurationException::missingDependency('AuditLog', 'withAuditLog() or AuthServiceProvider');
        if ($this->auditCorrelationId !== null) {
            $auditLog = new CorrelatingAuditLog(auditLog: $auditLog, correlationId: $this->auditCorrelationId);
        }
        $clock       = $this->clock ?? throw ConfigurationException::missingDependency('Clock', 'withClock() or AuthServiceProvider');
        $idGenerator = $this->idGenerator ?? throw ConfigurationException::missingDependency('IdGenerator', 'usingIdGenerator() or AuthServiceProvider');
        $sessionRegistry = $this->sessionRegistry ?? throw ConfigurationException::missingDependency('SessionRegistry', 'withSessionRegistry() or AuthServiceProvider');

        $graph = (new AssembleAuthIdentityGraph(
            userSource: $this->userSource, identity: $identity, auditLog: $auditLog, clock: $clock,
            passwordHasher: $passwordHasher, idGenerator: $idGenerator,
            sessionRegistry: $sessionRegistry,
            refreshTokenStore: $oAuth['refreshTokenStore'] ?? throw ConfigurationException::missingDependency('RefreshTokenStore', 'withRefreshTokenStore() or AuthServiceProvider'),
            mfaStore: $credential['mfaStore'] ?? throw ConfigurationException::missingDependency('MfaStore', 'withMfaStore() or AuthServiceProvider'),
            mfaChallengeStore: $credential['mfaChallengeStore'] ?? throw ConfigurationException::missingDependency('MfaChallengeStore', 'withMfaChallengeStore() or AuthServiceProvider'),
            totp: $credential['totp'] ?? throw ConfigurationException::missingDependency('Totp', 'usingTotp() or AuthServiceProvider'),
            limitMfaAttempts: $credential['limitMfaAttempts'] ?? throw ConfigurationException::missingDependency('LimitMfaAttempts', 'withMfaAttemptLimit() or AuthServiceProvider'),
            passwordResetThrottle: $credential['passwordResetThrottle'] ?? throw ConfigurationException::missingDependency('PasswordResetThrottle', 'withPasswordResetThrottle() or AuthServiceProvider'),
            mfaRecoveryThrottle: $credential['mfaRecoveryThrottle'] ?? throw ConfigurationException::missingDependency('MfaRecoveryThrottle', 'withMfaRecoveryThrottle() or AuthServiceProvider'),
            scimThrottle: $scim['scimThrottle'] ?? throw ConfigurationException::missingDependency('ScimThrottle', 'withScimThrottle() or AuthServiceProvider'),
            passwordResetStore: $this->passwordResetStore ?? throw ConfigurationException::missingDependency('PasswordResetStore', 'withPasswordResetStore() or AuthServiceProvider'),
            emailVerificationStore: $this->emailVerificationStore ?? throw ConfigurationException::missingDependency('EmailVerificationStore', 'withEmailVerificationStore() or AuthServiceProvider'),
            emailVerificationStateStore: $this->emailVerificationStateStore ?? throw ConfigurationException::missingDependency('EmailVerificationStateStore', 'withEmailVerificationState() or AuthServiceProvider'),
            emailChangeStore: $this->emailChangeStore ?? throw ConfigurationException::missingDependency('EmailChangeStore', 'withEmailChangeStore() or AuthServiceProvider'),
            adminElevationStore: $tenancy['adminElevationStore'] ?? throw ConfigurationException::missingDependency('AdminElevationStore', 'withAdminElevationStore() or AuthServiceProvider'),
            riskEngine: $tenancy['deterministicRiskEngine'] ?? throw ConfigurationException::missingDependency('DeterministicRiskEngine', 'withRiskEngine() or AuthServiceProvider'),
            passkeyRuntime: $credential['passkeyRuntime'] ?? throw ConfigurationException::missingDependency('PasskeyRuntime', 'withPasskeyRuntime() or AuthServiceProvider'),
            passkeyCredentialStore: $credential['passkeyCredentialStore'] ?? throw ConfigurationException::missingDependency('PasskeyCredentialStore', 'withPasskeyCredentialStore() or AuthServiceProvider'),
            passkeyChallengeStore: $credential['passkeyChallengeStore'] ?? throw ConfigurationException::missingDependency('PasskeyChallengeStore', 'withPasskeyChallengeStore() or AuthServiceProvider'),
            lifecycleStore: $scim['lifecycleStore'] ?? throw ConfigurationException::missingDependency('LifecycleStore', 'withLifecycleStore() or AuthServiceProvider'),
            scimDirectoryStore: $scim['scimDirectoryStore'] ?? throw ConfigurationException::missingDependency('ScimDirectoryStore', 'withScimDirectoryStore() or AuthServiceProvider'),
            scimProvisionedIdentityStore: $scim['scimProvisionedIdentityStore'] ?? throw ConfigurationException::missingDependency('ScimProvisionedIdentityStore', 'withScimProvisionedIdentityStore() or AuthServiceProvider'),
            tenantStore: $tenancy['tenantStore'] ?? throw ConfigurationException::missingDependency('TenantStore', 'withTenantStore() or AuthServiceProvider'),
            tenantSecurityConfigurationStore: $tenancy['tenantSecurityConfigurationStore'] ?? throw ConfigurationException::missingDependency('TenantSecurityConfigurationStore', 'withTenantSecurityConfigurationStore() or AuthServiceProvider'),
            tenantSecurityChangeRequestStore: $tenancy['tenantSecurityChangeRequestStore'] ?? throw ConfigurationException::missingDependency('TenantSecurityChangeRequestStore', 'withTenantSecurityChangeRequestStore() or AuthServiceProvider'),
            federationConnectionStore: $federation['federationConnectionStore'] ?? throw ConfigurationException::missingDependency('FederationConnectionStore', 'withFederationConnectionStore() or AuthServiceProvider'),
            federationRuntime: $federation['federationRuntime'],
            loginRateLimit: $this->loginRateLimit,
            mfaIssuer: $credential['mfaIssuer'], passkeyRpId: $credential['passkeyRpId'], passkeyRpName: $credential['passkeyRpName'],
        ))->assemble();

        $identity              = $graph['identity'];
        $currentAuthentication = $graph['currentAuthentication'];
        $projectAuthenticatedUser = $graph['projectAuthenticatedUser'];
        $authCapabilityReadiness  = $graph['authCapabilityReadiness'];
        $provisionableUserSource  = $graph['provisionableUserSource'];
        $lifecycle                = $graph['lifecycle'];
        $adminElevationStore = $tenancy['adminElevationStore'];

        (new AssembleAuthExternalIdentityGraph(
            oauthClientRegistry: $oAuth['oAuthClientRegistry'] ?? throw ConfigurationException::missingDependency('OAuthClientRegistry', 'withOAuthClientRegistry() or AuthServiceProvider'),
            authorizationCodeStore: $oAuth['authorizationCodeStore'] ?? throw ConfigurationException::missingDependency('AuthorizationCodeStore', 'withAuthorizationCodeStore() or AuthServiceProvider'),
            oidcProvider: $oAuth['oidcProvider'],
            oidcRequestObjectStore: $oAuth['oidcRequestObjectStore'],
            federationConnectionStore: $federation['federationConnectionStore'],
            federatedIdentityLinkStore: $federation['federatedIdentityLinkStore'] ?? throw ConfigurationException::missingDependency('FederatedIdentityLinkStore', 'withFederatedIdentityLinkStore() or AuthServiceProvider'),
            federationRuntime: $federation['federationRuntime'],
            federationMetadataRuntime: $federation['federationRuntime'] instanceof FederationMetadataRuntimeInterface ? $federation['federationRuntime'] : null,
            federationHealthCheck: $federation['federationRuntime'] instanceof FederationHealthCheckInterface ? $federation['federationRuntime'] : null,
            userSource: $this->userSource,
            identity: $identity,
            auditLog: $auditLog,
            clock: $clock,
            passwordHasher: $passwordHasher,
            idGenerator: $idGenerator,
            sessionRegistry: $sessionRegistry,
            refreshTokenStore: $oAuth['refreshTokenStore'],
            deterministicRiskEngine: $tenancy['deterministicRiskEngine'],
            currentAuthentication: $currentAuthentication,
            projectAuthenticatedUser: $projectAuthenticatedUser,
            authCapabilityReadiness: $authCapabilityReadiness,
            provisionableUserSource: $provisionableUserSource,
            lifecycle: $lifecycle,
            scim: $graph['scim'],
            requireAdminElevation: new RequireAdminElevation(currentAuthentication: $currentAuthentication, adminElevationStore: $adminElevationStore, clock: $clock),
            adminElevationStore: $adminElevationStore,
        ))->assemble();

        return new Auth(identity: $identity);
    }

    private function capabilityRequests() : AuthCapabilityRequests
    {
        $credential = $this->credentialBuilder->build();
        $oAuth      = $this->oAuthBuilder->build();
        $federation = $this->federationBuilder->build();
        $scim       = $this->scimBuilder->build();

        return AuthCapabilityRequests::from(
            enterpriseMode                        : $this->enterpriseMode,
            oauthClientRegistryConfigured         : $oAuth['oAuthClientRegistry'] instanceof OAuthClientRegistryInterface,
            authorizationCodeStoreConfigured      : $oAuth['authorizationCodeStore'] instanceof AuthorizationCodeStoreInterface,
            oidcProviderConfigured                : $oAuth['oidcProvider'] instanceof OidcProviderInterface,
            oidcRequestObjectStoreConfigured      : $oAuth['oidcRequestObjectStore'] instanceof OidcRequestObjectStoreInterface,
            passkeyCredentialStoreConfigured      : $credential['passkeyCredentialStore'] instanceof PasskeyCredentialStoreInterface,
            passkeyChallengeStoreConfigured       : $credential['passkeyChallengeStore'] instanceof PasskeyChallengeStoreInterface,
            passkeyRelyingPartyCustomized         : $credential['passkeyRpId'] !== 'localhost' || $credential['passkeyRpName'] !== 'Avax Auth',
            federationConnectionStoreConfigured   : $federation['federationConnectionStore'] instanceof FederationConnectionStoreInterface,
            federatedIdentityLinkStoreConfigured  : $federation['federatedIdentityLinkStore'] instanceof FederatedIdentityLinkStoreInterface,
            scimDirectoryStoreConfigured          : $scim['scimDirectoryStore'] instanceof ScimDirectoryStoreInterface,
            scimProvisionedIdentityStoreConfigured: $scim['scimProvisionedIdentityStore'] instanceof ScimProvisionedIdentityStoreInterface,
        );
    }
}
