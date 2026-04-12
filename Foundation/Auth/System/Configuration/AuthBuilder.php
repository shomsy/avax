<?php

declare(strict_types=1);

namespace Avax\Auth\System\Configuration;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Access\Access;
use Avax\Auth\System\Capability\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capability\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capability\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\Throttle\AttemptThrottle;
use Avax\Auth\System\Capability\Throttle\InMemoryAttemptThrottleStore;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticateRequest;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flow\ChangePassword\ChangePassword;
use Avax\Auth\System\Flow\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Diagnostics\NullAuditLog;
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
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Flow\Recover\BeginPasswordReset;
use Avax\Auth\System\Flow\Recover\InMemoryPasswordResetStore;
use Avax\Auth\System\Flow\Recover\PasswordResetStoreInterface;
use Avax\Auth\System\Flow\Recover\ResetPassword;
use Avax\Auth\System\Flow\Register\Register;
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
    private MfaChallengeStoreInterface|null           $mfaChallengeStore      = null;
    private TotpInterface|null                        $totp                   = null;
    private LimitMfaAttempts|null                     $mfaAttemptLimit        = null;
    private AttemptThrottle|null                      $passwordResetThrottle  = null;
    private AttemptThrottle|null                      $mfaRecoveryThrottle    = null;
    private Clock|null                                $clock                  = null;
    private SessionRegistryInterface|null             $sessionRegistry        = null;
    private string                                    $mfaIssuer              = 'Avax Auth';

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
        $passwordResetStore       = $this->passwordResetStore ?? new InMemoryPasswordResetStore();
        $emailVerificationStore   = $this->emailVerificationStore ?? new InMemoryEmailVerificationStore();
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
        $access                   = new Access(
            requireAuthentication: new RequireAuthentication(
                                       currentAuthentication: $currentAuthentication
                                   ),
            requireRole          : new RequireRole(currentAuthentication: $currentAuthentication),
            requirePermission    : new RequirePermission(
                                       currentAuthentication: $currentAuthentication
                                   )
        );

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
                                        rateLimit               : $this->rateLimit
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
                                        jwtIdentity             : $identity->jwtIdentity()
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
                                        attemptLimit            : $mfaAttemptLimit
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
