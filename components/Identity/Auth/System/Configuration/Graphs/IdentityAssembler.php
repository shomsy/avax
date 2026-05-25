<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Graphs;

use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Account;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Authentication;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Recovery;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityOwners\Verification;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Sessions;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\LogoutAllSessions\LogoutAllSessions;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\ReadActiveSessions\ReadActiveSessions;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\RevokeSession\RevokeSession;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Configuration\IdentityConfiguration;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\BeginEmailChange;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\ConfirmEmailChange;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\EmailChangeStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\InMemoryEmailChangeStore;
use Avax\Components\Identity\Auth\System\Flows\ChangePassword\ChangePassword;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
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
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationStateStoreInterface;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStateStore;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmail;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Foundation\IdGenerator;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Mfa;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\GenerateBackupCodes;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\RegenerateBackupCodes;
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
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp\Totp;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\InMemoryMfaChallengeStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\MfaChallengeStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\VerifyMfaChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Passkey;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyChallengeStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthentication;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\BeginRegistration\BeginPasskeyRegistration;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthentication;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistration;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\ListPasskeys\ListPasskeys;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\RenamePasskey\RenamePasskey;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\RevokePasskey\RevokePasskey;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Flow\RefreshAuthentication;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\TokenRevocationStoreInterface;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;

/**
 * IdentityAssembler — assembles the Identity capability coordinator from DI-registered services.
 *
 * This is a Configuration/Assembly class. It resolves the circular dependency
 * between Identity and its sub-capabilities by using lazy container resolution.
 */
final class IdentityAssembler
{
    /**
     * Assemble the Identity coordinator from container-registered services.
     */
    public static function assemble(
        ContainerInterface $container,
        SessionIdentityInterface $sessionIdentity,
        JwtIdentityInterface $jwtIdentity,
    ) : Identity {
        $clock = $container->get(Clock::class);
        $auditLog = $container->get(AuditLogInterface::class);
        $userSource = $container->get(UserSourceInterface::class);
        $passwordHasher = $container->get(PasswordHasher::class);

        // CurrentAuthentication — shared context for session + JWT identity
        $currentAuth = new CurrentAuthentication();

        // RequireFreshMfa — step-up MFA guard
        $requireFreshMfa = new RequireFreshMfa(
            currentAuthentication: $currentAuth,
            clock                : $clock,
        );

        // === Authentication owner ===
        $authentication = self::assembleAuthentication($container, $currentAuth, $jwtIdentity, $requireFreshMfa);

        // === Sessions owner ===
        $sessions = self::assembleSessions($container, $currentAuth);

        // === Account owner ===
        $account = self::assembleAccount($container, $currentAuth, $requireFreshMfa);

        // === Recovery owner ===
        $recovery = self::assembleRecovery($container, $currentAuth);

        // === Verification owner ===
        $verification = self::assembleVerification($container, $currentAuth);

        // === Mfa owner ===
        $mfa = self::assembleMfa($container, $currentAuth, $requireFreshMfa);

        // === Passkey owner ===
        $passkey = self::assemblePasskey($container, $currentAuth, $requireFreshMfa);

        return Identity::create(
            authentication : $authentication,
            sessions       : $sessions,
            account        : $account,
            recovery       : $recovery,
            verification   : $verification,
            mfa            : $mfa,
            passkey        : $passkey,
            sessionIdentity: $sessionIdentity,
            jwtIdentity    : $jwtIdentity,
        );
    }

    private static function assembleAuthentication(
        ContainerInterface $container,
        CurrentAuthentication $currentAuth,
        JwtIdentityInterface $jwtIdentity,
        RequireFreshMfa $requireFreshMfa,
    ) : Authentication {
        $userSource = $container->get(UserSourceInterface::class);
        $passwordHasher = $container->get(PasswordHasher::class);

        // Lazy IdentityInterface for Login/Logout flows (circular dep resolution)
        $identityResolver = static fn () : Identity => $container->get(Identity::class);

        return new Authentication(
            login: new Login(
                findUserByCredentials    : new FindUserByCredentials($userSource),
                verifyPassword           : new VerifyPassword($passwordHasher),
                startAuthenticatedSession: new StartAuthenticatedSession($container->get(Sessions::class)),
                identity                 : $container->get(Identity::class),
            ),
            logout: new Logout(
                clearAuthenticatedIdentity: new ClearAuthenticatedIdentity($container->get(Sessions::class)),
                identity                  : $container->get(Identity::class),
            ),
            refreshAuthentication: new RefreshAuthentication(
                userSource             : $userSource,
                projectAuthenticatedUser: new \Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser(
                    emailVerificationStateStore: new InMemoryEmailVerificationStateStore(),
                    mfaStore                   : new InMemoryMfaStore(),
                ),
                currentAuthentication  : $currentAuth,
                auditLog               : $container->get(AuditLogInterface::class),
                clock                  : $container->get(Clock::class),
                refreshTokenStore      : $container->has(RefreshTokenStoreInterface::class) ? $container->get(RefreshTokenStoreInterface::class) : null,
                jwtIdentity            : $jwtIdentity,
                deterministicRiskEngine: $container->has(DeterministicRiskEngine::class) ? $container->get(DeterministicRiskEngine::class) : null,
            ),
        );
    }

    private static function assembleSessions(
        ContainerInterface $container,
        CurrentAuthentication $currentAuth,
    ) : Sessions {
        $sessionRegistry = $container->has(SessionRegistryInterface::class)
            ? $container->get(SessionRegistryInterface::class)
            : null;
        $refreshTokenStore = $container->has(RefreshTokenStoreInterface::class)
            ? $container->get(RefreshTokenStoreInterface::class)
            : null;

        return new Sessions(
            logoutAllSessions: new LogoutAllSessions(
                identity           : $container->get(Identity::class),
                currentAuthentication: $currentAuth,
                auditLog           : $container->get(AuditLogInterface::class),
                clock              : $container->get(Clock::class),
                sessionRegistry    : $sessionRegistry,
                refreshTokenStore  : $refreshTokenStore,
            ),
            readActiveSessions: new ReadActiveSessions(
                currentAuthentication: $currentAuth,
                clock              : $container->get(Clock::class),
                sessionRegistry    : $sessionRegistry,
            ),
            revokeSession: new RevokeSession(
                identity           : $container->get(Identity::class),
                currentAuthentication: $currentAuth,
                auditLog           : $container->get(AuditLogInterface::class),
                clock              : $container->get(Clock::class),
                sessionRegistry    : $sessionRegistry,
            ),
        );
    }

    private static function assembleAccount(
        ContainerInterface $container,
        CurrentAuthentication $currentAuth,
        RequireFreshMfa $requireFreshMfa,
    ) : Account {
        $passwordHasher = $container->get(PasswordHasher::class);
        $userSource = $container->get(UserSourceInterface::class);
        $auditLog = $container->get(AuditLogInterface::class);
        $clock = $container->get(Clock::class);
        $sessionRegistry = $container->has(SessionRegistryInterface::class) ? $container->get(SessionRegistryInterface::class) : null;
        $mfaChallengeStore = $container->has(MfaChallengeStoreInterface::class) ? $container->get(MfaChallengeStoreInterface::class) : null;
        $refreshTokenStore = $container->has(RefreshTokenStoreInterface::class) ? $container->get(RefreshTokenStoreInterface::class) : null;

        // Lazy identity for circular dep resolution
        $identity = $container->get(Identity::class);

        // ChangePassword
        $changePassword = new ChangePassword(
            userSource           : $userSource,
            passwordHasher       : $passwordHasher,
            identity             : $identity,
            currentAuthentication: $currentAuth,
            auditLog             : $auditLog,
            clock                : $clock,
            sessionRegistry      : $sessionRegistry,
            mfaChallengeStore    : $mfaChallengeStore,
            refreshTokenStore    : $refreshTokenStore,
            loginRateLimit       : null,
            requireFreshMfa      : $requireFreshMfa,
        );

        // BeginEmailChange
        $emailChangeStore = $container->has(EmailChangeStoreInterface::class)
            ? $container->get(EmailChangeStoreInterface::class)
            : new InMemoryEmailChangeStore();

        $beginEmailChange = new BeginEmailChange(
            currentAuthentication: $currentAuth,
            userSource         : $userSource,
            passwordHasher     : $passwordHasher,
            emailChangeStore   : $emailChangeStore,
            requireFreshMfa    : $requireFreshMfa,
            auditLog           : $auditLog,
            clock              : $clock,
        );

        // ConfirmEmailChange
        $emailVerificationStateStore = $container->has(EmailVerificationStateStoreInterface::class)
            ? $container->get(EmailVerificationStateStoreInterface::class)
            : new InMemoryEmailVerificationStateStore();

        $provisionableSource = $userSource instanceof ProvisionableUserSourceInterface ? $userSource : null;
        $confirmEmailChange = $provisionableSource !== null
            ? new ConfirmEmailChange(
                provisionableUserSource     : $provisionableSource,
                emailChangeStore            : $emailChangeStore,
                emailVerificationStateStore : $emailVerificationStateStore,
                auditLog                    : $auditLog,
                clock                       : $clock,
                currentAuthentication       : $currentAuth,
                identity                    : $identity,
                sessionRegistry             : $sessionRegistry,
                mfaChallengeStore           : $mfaChallengeStore,
                refreshTokenStore           : $refreshTokenStore,
            )
            : null;

        // Register
        $register = new Register(
            validateRegistrationData: new ValidateRegistrationData(),
            hashRegisteredPassword  : new HashRegisteredPassword($passwordHasher),
            createRegisteredUser    : new CreateRegisteredUser($userSource, new IdGenerator()),
            identity                : $identity,
        );

        return new Account($changePassword, $beginEmailChange, $confirmEmailChange, $register);
    }

    private static function assembleRecovery(
        ContainerInterface $container,
        CurrentAuthentication $currentAuth,
    ) : Recovery {
        $passwordResetStore = $container->has(PasswordResetStoreInterface::class)
            ? $container->get(PasswordResetStoreInterface::class)
            : new InMemoryPasswordResetStore();

        return new Recovery(
            beginPasswordReset: new BeginPasswordReset(
                userSource       : $container->get(UserSourceInterface::class),
                passwordResetStore: $passwordResetStore,
                auditLog         : $container->get(AuditLogInterface::class),
                clock            : $container->get(Clock::class),
                attemptThrottle  : $container->has(AttemptThrottle::class) ? $container->get(AttemptThrottle::class) : null,
            ),
            resetPassword: new ResetPassword(
                userSource       : $container->get(UserSourceInterface::class),
                passwordHasher   : $container->get(PasswordHasher::class),
                passwordResetStore: $passwordResetStore,
                auditLog         : $container->get(AuditLogInterface::class),
                clock            : $container->get(Clock::class),
                sessionRegistry  : $container->has(SessionRegistryInterface::class) ? $container->get(SessionRegistryInterface::class) : null,
                mfaChallengeStore: $container->has(MfaChallengeStoreInterface::class) ? $container->get(MfaChallengeStoreInterface::class) : null,
                refreshTokenStore: $container->has(RefreshTokenStoreInterface::class) ? $container->get(RefreshTokenStoreInterface::class) : null,
            ),
        );
    }

    private static function assembleVerification(
        ContainerInterface $container,
        CurrentAuthentication $currentAuth,
    ) : Verification {
        $emailVerificationStore = $container->has(EmailVerificationStoreInterface::class)
            ? $container->get(EmailVerificationStoreInterface::class)
            : new \Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStore();
        $emailVerificationStateStore = $container->has(EmailVerificationStateStoreInterface::class)
            ? $container->get(EmailVerificationStateStoreInterface::class)
            : new InMemoryEmailVerificationStateStore();

        return new Verification(
            beginEmailVerification: new BeginEmailVerification(
                userSource           : $container->get(UserSourceInterface::class),
                emailVerificationStore: $emailVerificationStore,
                auditLog             : $container->get(AuditLogInterface::class),
                clock                : $container->get(Clock::class),
            ),
            verifyEmail: new VerifyEmail(
                emailVerificationStore   : $emailVerificationStore,
                emailVerificationStateStore: $emailVerificationStateStore,
                auditLog                 : $container->get(AuditLogInterface::class),
                clock                    : $container->get(Clock::class),
            ),
        );
    }

    private static function assembleMfa(
        ContainerInterface $container,
        CurrentAuthentication $currentAuth,
        RequireFreshMfa $requireFreshMfa,
    ) : Mfa {
        $mfaStore = new InMemoryMfaStore();
        $mfaChallengeStore = $container->has(MfaChallengeStoreInterface::class)
            ? $container->get(MfaChallengeStoreInterface::class)
            : new InMemoryMfaChallengeStore();
        $clock = $container->get(Clock::class);
        $auditLog = $container->get(AuditLogInterface::class);
        $userSource = $container->get(UserSourceInterface::class);
        $passwordHasher = $container->get(PasswordHasher::class);

        $attemptLimitStorage = new InMemoryAttemptLimitStorage();
        $limitMfaAttempts = new LimitMfaAttempts(
            attemptLimitStorage: $attemptLimitStorage,
            clock              : $clock,
        );

        $totp = new Totp();
        $generateBackupCodes = new GenerateBackupCodes($passwordHasher, $clock);
        $emailVerificationStateStore = new InMemoryEmailVerificationStateStore();

        return new Mfa(
            startMfaEnrollment   : new StartMfaEnrollment(
                currentAuthentication: $currentAuth,
                mfaStore           : $mfaStore,
                totp               : $totp,
                auditLog           : $auditLog,
                clock              : $clock,
            ),
            confirmMfaEnrollment : new ConfirmMfaEnrollment(
                currentAuthentication: $currentAuth,
                mfaStore           : $mfaStore,
                totp               : $totp,
                generateBackupCodes: $generateBackupCodes,
                auditLog           : $auditLog,
                clock              : $clock,
            ),
            cancelMfaEnrollment  : new CancelMfaEnrollment(
                currentAuthentication: $currentAuth,
                mfaStore           : $mfaStore,
                auditLog           : $auditLog,
                clock              : $clock,
            ),
            startMfaChallenge    : new \Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\StartMfaChallenge(
                currentAuthentication: $currentAuth,
                generalMfaStore    : $mfaStore,
                mfaChallengeStore  : $mfaChallengeStore,
                auditLog           : $auditLog,
                clock              : $clock,
            ),
            verifyMfaChallenge   : new VerifyMfaChallenge(
                mfaChallengeStore  : $mfaChallengeStore,
                mfaStore           : $mfaStore,
                totp               : $totp,
                verifyBackupCode   : new \Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\VerifyBackupCode(
                    mfaStore       : $mfaStore,
                    passwordHasher : $passwordHasher,
                    auditLog       : $auditLog,
                    clock          : $clock,
                ),
                userSource         : $userSource,
                identity           : $container->get(Identity::class),
                projectAuthenticatedUser: new \Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser(
                    emailVerificationStateStore: $emailVerificationStateStore,
                    mfaStore                   : $mfaStore,
                ),
                currentAuthentication: $currentAuth,
                auditLog           : $auditLog,
                clock              : $clock,
                limitMfaAttempts   : $limitMfaAttempts,
                deterministicRiskEngine: null,
            ),
            regenerateBackupCodes: new RegenerateBackupCodes(
                currentAuthentication: $currentAuth,
                requireFreshMfa    : $requireFreshMfa,
                mfaStore           : $mfaStore,
                generateBackupCodes: $generateBackupCodes,
                auditLog           : $auditLog,
                clock              : $clock,
            ),
            disableMfa           : new DisableMfa(
                currentAuthentication: $currentAuth,
                requireFreshMfa    : $requireFreshMfa,
                mfaStore           : $mfaStore,
                mfaChallengeStore  : $mfaChallengeStore,
                auditLog           : $auditLog,
                clock              : $clock,
                refreshTokenStore  : null,
            ),
            startMfaRecovery     : new StartMfaRecovery(
                userSource         : $userSource,
                mfaStore           : $mfaStore,
                auditLog           : $auditLog,
                clock              : $clock,
            ),
            confirmMfaRecovery   : new ConfirmMfaRecovery(
                mfaStore           : $mfaStore,
                mfaChallengeStore  : $mfaChallengeStore,
                auditLog           : $auditLog,
                clock              : $clock,
                sessionRegistry    : null,
                refreshTokenStore  : null,
                currentAuthentication: null,
                identity           : null,
            ),
        );
    }

    private static function assemblePasskey(
        ContainerInterface $container,
        CurrentAuthentication $currentAuth,
        RequireFreshMfa $requireFreshMfa,
    ) : Passkey {
        $passkeyChallengeStore = $container->has(PasskeyChallengeStoreInterface::class)
            ? $container->get(PasskeyChallengeStoreInterface::class) : null;
        $passkeyCredentialStore = $container->has(PasskeyCredentialStoreInterface::class)
            ? $container->get(PasskeyCredentialStoreInterface::class) : null;
        $passkeyRuntime = $container->has(\Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyRuntimeInterface::class)
            ? $container->get(\Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyRuntimeInterface::class) : null;

        // Passkey requires PasskeyRuntimeInterface which needs a WebAuthn library.
        // Without it, passkey capabilities stay disabled.
        if ($passkeyChallengeStore === null || $passkeyCredentialStore === null || $passkeyRuntime === null) {
            return new Passkey(
                beginPasskeyRegistration     : null,
                completePasskeyRegistration  : null,
                beginPasskeyAuthentication   : null,
                completePasskeyAuthentication: null,
                listPasskeys                 : null,
                renamePasskey                : null,
                revokePasskey                : null,
            );
        }

        $clock = $container->get(Clock::class);
        $auditLog = $container->get(AuditLogInterface::class);
        $userSource = $container->get(UserSourceInterface::class);
        $emailVerificationStateStore = new InMemoryEmailVerificationStateStore();
        $mfaStore = new InMemoryMfaStore();

        // RP configuration — defaults for now, should come from config in production
        $rpId = 'localhost';
        $rpName = 'AvaX';

        return new Passkey(
            beginPasskeyRegistration    : new BeginPasskeyRegistration(
                currentAuthentication  : $currentAuth,
                requireFreshMfa        : $requireFreshMfa,
                passkeyRuntime         : $passkeyRuntime,
                passkeyCredentialStore : $passkeyCredentialStore,
                passkeyChallengeStore  : $passkeyChallengeStore,
                auditLog               : $auditLog,
                clock                  : $clock,
                rpId                   : $rpId,
                rpName                 : $rpName,
            ),
            completePasskeyRegistration : new CompletePasskeyRegistration(
                currentAuthentication  : $currentAuth,
                passkeyRuntime         : $passkeyRuntime,
                passkeyCredentialStore : $passkeyCredentialStore,
                passkeyChallengeStore  : $passkeyChallengeStore,
                auditLog               : $auditLog,
                clock                  : $clock,
                rpId                   : $rpId,
            ),
            beginPasskeyAuthentication  : new BeginPasskeyAuthentication(
                userSource             : $userSource,
                passkeyRuntime         : $passkeyRuntime,
                passkeyCredentialStore : $passkeyCredentialStore,
                passkeyChallengeStore  : $passkeyChallengeStore,
                auditLog               : $auditLog,
                clock                  : $clock,
                rpId                   : $rpId,
            ),
            completePasskeyAuthentication: new CompletePasskeyAuthentication(
                passkeyRuntime         : $passkeyRuntime,
                passkeyChallengeStore  : $passkeyChallengeStore,
                passkeyCredentialStore : $passkeyCredentialStore,
                userSource             : $userSource,
                identity               : $container->get(Identity::class),
                projectAuthenticatedUser: new \Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser(
                    emailVerificationStateStore: $emailVerificationStateStore,
                    mfaStore                   : $mfaStore,
                ),
                currentAuthentication  : $currentAuth,
                auditLog               : $auditLog,
                clock                  : $clock,
                rpId                   : $rpId,
            ),
            listPasskeys              : new ListPasskeys(
                currentAuthentication  : $currentAuth,
                passkeyCredentialStore : $passkeyCredentialStore,
            ),
            renamePasskey             : new RenamePasskey(
                currentAuthentication  : $currentAuth,
                passkeyCredentialStore : $passkeyCredentialStore,
            ),
            revokePasskey             : new RevokePasskey(
                currentAuthentication  : $currentAuth,
                requireFreshMfa        : $requireFreshMfa,
                passkeyCredentialStore : $passkeyCredentialStore,
                auditLog               : $auditLog,
                clock                  : $clock,
            ),
        );
    }
}
