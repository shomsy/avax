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
use Avax\Auth\System\Capability\Federation\FederationRuntimeInterface;
use Avax\Auth\System\Capability\Federation\InMemoryFederatedIdentityLinkStore;
use Avax\Auth\System\Capability\Federation\InMemoryFederationConnectionStore;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
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
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
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
use Avax\Auth\System\Flow\Diagnostics\NullAuditLog;
use Avax\Auth\System\Flow\Federation\CompleteFederatedLogin\CompleteFederatedLogin;
use Avax\Auth\System\Flow\Federation\DiscoverConnection\DiscoverFederationConnection;
use Avax\Auth\System\Flow\Federation\ReadConnections\ReadFederationConnections;
use Avax\Auth\System\Flow\Federation\RegisterConnection\RegisterFederationConnection;
use Avax\Auth\System\Flow\Federation\StartFederatedLogin\StartFederatedLogin;
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
use Avax\Auth\System\Flow\OAuth\ExchangeRefreshToken\ExchangeRefreshToken;
use Avax\Auth\System\Flow\OAuth\IntrospectToken\IntrospectToken;
use Avax\Auth\System\Flow\OAuth\ReadClients\ReadClients;
use Avax\Auth\System\Flow\OAuth\RegisterClient\RegisterClient;
use Avax\Auth\System\Flow\OAuth\RevokeToken\RevokeToken;
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
use Avax\Auth\System\Flow\Session\LogoutAllSessions\LogoutAllSessions;
use Avax\Auth\System\Flow\Session\ReadActiveSessions\ReadActiveSessions;
use Avax\Auth\System\Flow\Session\RevokeSession\RevokeSession;
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
    private Clock|null                                $clock                  = null;
    private SessionRegistryInterface|null             $sessionRegistry        = null;
    private OAuthClientRegistryInterface|null         $oauthClientRegistry    = null;
    private AuthorizationCodeStoreInterface|null      $authorizationCodeStore = null;
    private AdminElevationStoreInterface|null         $adminElevationStore    = null;
    private DeterministicRiskEngine|null              $riskEngine             = null;
    private PasskeyRuntimeInterface|null              $passkeyRuntime         = null;
    private PasskeyCredentialStoreInterface|null      $passkeyCredentialStore = null;
    private PasskeyChallengeStoreInterface|null       $passkeyChallengeStore  = null;
    private FederationRuntimeInterface|null           $federationRuntime      = null;
    private FederationConnectionStoreInterface|null   $federationConnectionStore = null;
    private FederatedIdentityLinkStoreInterface|null  $federatedIdentityLinkStore = null;
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
    public function usingHasher(PasswordHasher $passwordHasher) : self
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

    public function withEmailVerificationState(EmailVerificationStateStoreInterface $emailVerificationState) : self
    {
        $this->emailVerificationState = $emailVerificationState;

        return $this;
    }

    public function withMfaStore(MfaStoreInterface $mfaStore) : self
    {
        $this->mfaStore = $mfaStore;

        return $this;
    }

    public function withRefreshTokenStore(RefreshTokenStoreInterface $refreshTokenStore) : self
    {
        $this->refreshTokenStore = $refreshTokenStore;

        return $this;
    }

    public function withPasswordResetStore(PasswordResetStoreInterface $passwordResetStore) : self
    {
        $this->passwordResetStore = $passwordResetStore;

        return $this;
    }

    public function withEmailVerificationStore(EmailVerificationStoreInterface $emailVerificationStore) : self
    {
        $this->emailVerificationStore = $emailVerificationStore;

        return $this;
    }

    public function withEmailChangeStore(EmailChangeStoreInterface $emailChangeStore) : self
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

    public function withPasswordResetThrottle(AttemptThrottle $passwordResetThrottle) : self
    {
        $this->passwordResetThrottle = $passwordResetThrottle;

        return $this;
    }

    public function withMfaRecoveryThrottle(AttemptThrottle $mfaRecoveryThrottle) : self
    {
        $this->mfaRecoveryThrottle = $mfaRecoveryThrottle;

        return $this;
    }

    public function withClock(Clock $clock) : self
    {
        $this->clock = $clock;

        return $this;
    }

    public function withSessionRegistry(SessionRegistryInterface $sessionRegistry) : self
    {
        $this->sessionRegistry = $sessionRegistry;

        return $this;
    }

    public function withOAuthClientRegistry(OAuthClientRegistryInterface $oauthClientRegistry) : self
    {
        $this->oauthClientRegistry = $oauthClientRegistry;

        return $this;
    }

    public function withAuthorizationCodeStore(AuthorizationCodeStoreInterface $authorizationCodeStore) : self
    {
        $this->authorizationCodeStore = $authorizationCodeStore;

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

    public function withPasskeyCredentialStore(PasskeyCredentialStoreInterface $passkeyCredentialStore) : self
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
        $clock                    = $this->clock ?? new Clock();
        $oauthClientRegistry      = $this->oauthClientRegistry ?? new InMemoryOAuthClientRegistry($passwordHasher);
        $authorizationCodeStore   = $this->authorizationCodeStore ?? new InMemoryAuthorizationCodeStore();
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
        $readOAuthClients         = new ReadClients(
            clientRegistry: $oauthClientRegistry
        );
        $authorizeOAuthCode       = new AuthorizeCode(
            currentAuthentication: $currentAuthentication,
            userSource           : $this->userSource,
            clientRegistry       : $oauthClientRegistry,
            codeStore            : $authorizationCodeStore,
            auditLog             : $auditLog,
            clock                : $clock
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
        $passkeyReady             = $this->passkeyRuntime !== null;
        $federationReady          = $this->federationRuntime !== null;

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
            readOAuthClients      : $oauthReady ? $readOAuthClients : null,
            authorizeOAuthCode    : $oauthReady ? $authorizeOAuthCode : null,
            exchangeOAuthCode     : $oauthReady
                ? new ExchangeAuthorizationCode(
                    clientRegistry   : $oauthClientRegistry,
                    codeStore        : $authorizationCodeStore,
                    userSource       : $this->userSource,
                    jwtIdentity      : $identity->jwtIdentity() ?? throw new RuntimeException('JWT identity is required.'),
                    refreshTokenStore: $this->refreshTokenStore ?? throw new RuntimeException('Refresh token store is required.'),
                    auditLog         : $auditLog,
                    clock            : $clock
                )
                : null,
            exchangeOAuthRefreshToken: $oauthReady
                ? new ExchangeRefreshToken(
                    clientRegistry   : $oauthClientRegistry,
                    refreshTokenStore: $this->refreshTokenStore ?? throw new RuntimeException('Refresh token store is required.'),
                    userSource       : $this->userSource,
                    jwtIdentity      : $identity->jwtIdentity() ?? throw new RuntimeException('JWT identity is required.'),
                    auditLog         : $auditLog,
                    clock            : $clock,
                    riskEngine       : $riskEngine
                )
                : null,
            revokeOAuthToken      : $oauthReady
                ? new RevokeToken(
                    clientRegistry   : $oauthClientRegistry,
                    refreshTokenStore: $this->refreshTokenStore ?? throw new RuntimeException('Refresh token store is required.'),
                    jwtIdentity      : $identity->jwtIdentity() ?? throw new RuntimeException('JWT identity is required.'),
                    auditLog         : $auditLog,
                    clock            : $clock
                )
                : null,
            introspectOAuthToken  : $oauthReady
                ? new IntrospectToken(
                    clientRegistry: $oauthClientRegistry,
                    jwtIdentity   : $identity->jwtIdentity() ?? throw new RuntimeException('JWT identity is required.'),
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
                    adminElevationStore: $adminElevationStore
                )
                : null,
            reactivateUser        : $provisionableUserSource !== null
                ? new ReactivateUser(
                    userSource         : $provisionableUserSource,
                    requireAdminElevation: $requireAdminElevation,
                    auditLog           : $auditLog,
                    clock              : $clock
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
                    adminElevationStore: $adminElevationStore
                )
                : null,
            beginPasskeyRegistration: $passkeyReady
                ? new BeginPasskeyRegistration(
                    currentAuthentication: $currentAuthentication,
                    requireFreshMfa      : $requireFreshMfa,
                    runtime              : $this->passkeyRuntime ?? throw new RuntimeException('Passkey runtime is required.'),
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
                    runtime              : $this->passkeyRuntime ?? throw new RuntimeException('Passkey runtime is required.'),
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
                    runtime         : $this->passkeyRuntime ?? throw new RuntimeException('Passkey runtime is required.'),
                    credentialStore : $passkeyCredentialStore,
                    challengeStore  : $passkeyChallengeStore,
                    auditLog        : $auditLog,
                    clock           : $clock,
                    rpId            : $this->passkeyRpId
                )
                : null,
            completePasskeyAuthentication: $passkeyReady
                ? new CompletePasskeyAuthentication(
                    runtime              : $this->passkeyRuntime ?? throw new RuntimeException('Passkey runtime is required.'),
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
                    auditLog      : $auditLog,
                    clock         : $clock
                )
                : null,
            readFederationConnections: $federationReady
                ? new ReadFederationConnections(
                    connectionStore: $federationConnectionStore
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
                    runtime        : $this->federationRuntime ?? throw new RuntimeException('Federation runtime is required.'),
                    auditLog       : $auditLog,
                    clock          : $clock
                )
                : null,
            completeFederatedLogin: $federationReady
                ? new CompleteFederatedLogin(
                    connectionStore       : $federationConnectionStore,
                    runtime               : $this->federationRuntime ?? throw new RuntimeException('Federation runtime is required.'),
                    linkStore             : $federatedIdentityLinkStore,
                    userSource            : $this->userSource,
                    identity              : $identity,
                    projectAuthenticatedUser: $projectAuthenticatedUser,
                    currentAuthentication : $currentAuthentication,
                    passwordHasher        : $passwordHasher,
                    idGenerator           : $this->idGenerator ?? new IdGenerator(),
                    auditLog              : $auditLog,
                    clock                 : $clock,
                    riskEngine            : $riskEngine
                )
                : null,
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
