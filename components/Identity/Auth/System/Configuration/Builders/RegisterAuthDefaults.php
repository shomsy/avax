<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Builders;

use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\InMemoryAttemptThrottleStore;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\InMemoryKnownAuthenticationEnvironmentStore;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\InMemoryRiskSignalStore;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\NullAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\SessionStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Configuration\Graphs\IdentityAssembler;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store\GenerateSessionId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store\InMemorySessionStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store\RandomSessionId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store\SessionStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\InMemoryLifecycleStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\InMemoryScimDirectoryStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\InMemoryScimProvisionedIdentityStore;
use Avax\Components\Identity\Auth\System\Configuration\Graphs\SessionGraph;
use Avax\Components\Identity\Auth\System\Configuration\Graphs\TokenGraph;
use Avax\Components\Identity\Auth\System\Configuration\IdentityConfiguration;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\InMemoryEmailChangeStore;
use Avax\Components\Identity\Auth\System\Flows\IssueAccessToken\IssueAccessToken;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\InMemoryPasswordResetStore;
use Avax\Components\Identity\Auth\System\Flows\StartSession\StartSession;
use Avax\Components\Identity\Auth\System\Flows\VerifyAccessToken\VerifyAccessToken;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStateStore;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStore;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Foundation\Time\ClockInterface;
use Avax\Components\Identity\Auth\System\Foundation\IdGenerator;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Auth\System\PublicSurface\AuthInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\InMemoryAttemptLimitStorage;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\LimitMfaAttempts;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\InMemoryMfaStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp\Totp;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\InMemoryMfaChallengeStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\InMemoryPasskeyChallengeStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\InMemoryPasskeyCredentialStore;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\InMemoryAuthorizationCodeStore;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\InMemoryOAuthClientRegistry;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\InMemoryOidcRequestObjectStore;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\InMemoryFederatedIdentityLinkStore;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\InMemoryFederationConnectionStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\InMemoryAdminElevationStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\Model\InMemoryTenantStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\InMemoryTenantSecurityChangeRequestStore;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\InMemoryTenantSecurityConfigurationStore;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Blacklist\InMemoryTokenBlacklist;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Blacklist\TokenBlacklist;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\TokenRevocationStoreInterface;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;

final readonly class RegisterAuthDefaults
{
    public function register(ContainerInterface $container) : void
    {
        // === Core Infrastructure ===

        // Clock — system time source
        $container->singleton(Clock::class, static fn () : Clock => new Clock());

        // IdGenerator — unique identifier generation
        $container->singleton(IdGenerator::class, static fn () : IdGenerator => new IdGenerator());

        // PasswordHasher — default password hashing
        $container->singleton(PasswordHasher::class, static fn () : PasswordHasher => new PasswordHasher());

        // === Audit & Diagnostics ===

        // NullAuditLog — no-op audit implementation (default when no real audit configured)
        $container->singleton(NullAuditLog::class, static fn () : NullAuditLog => new NullAuditLog());

        // === OAuth Infrastructure ===

        // OAuth client registry with default in-memory store
        $container->singleton(
            InMemoryOAuthClientRegistry::class,
            static fn (ContainerInterface $c) : InMemoryOAuthClientRegistry => new InMemoryOAuthClientRegistry(
                passwordHasher: $c->get(PasswordHasher::class),
            ),
        );

        // Authorization code store
        $container->singleton(InMemoryAuthorizationCodeStore::class, static fn () : InMemoryAuthorizationCodeStore => new InMemoryAuthorizationCodeStore());

        // === Lifecycle & Admin ===

        // Lifecycle store for user provisioning
        $container->singleton(InMemoryLifecycleStore::class, static fn () : InMemoryLifecycleStore => new InMemoryLifecycleStore());

        // Admin elevation store
        $container->singleton(InMemoryAdminElevationStore::class, static fn () : InMemoryAdminElevationStore => new InMemoryAdminElevationStore());

        // === Risk Engine ===

        // Known authentication environments store
        $container->singleton(
            InMemoryKnownAuthenticationEnvironmentStore::class,
            static fn () : InMemoryKnownAuthenticationEnvironmentStore => new InMemoryKnownAuthenticationEnvironmentStore(),
        );

        // Risk signal store
        $container->singleton(InMemoryRiskSignalStore::class, static fn () : InMemoryRiskSignalStore => new InMemoryRiskSignalStore());

        // Deterministic risk engine
        $container->singleton(
            DeterministicRiskEngine::class,
            static fn (ContainerInterface $c) : DeterministicRiskEngine => new DeterministicRiskEngine(
                clock            : $c->get(Clock::class),
                knownAuthenticationEnvironmentStore: $c->get(InMemoryKnownAuthenticationEnvironmentStore::class),
                riskSignalStore          : $c->get(InMemoryRiskSignalStore::class),
            ),
        );

        // === Access / Authorization Graph ===

        // Access component — registers AuthorizationEngine, Require* boundaries, PolicyEvaluator, Access facade
        \Avax\Components\Identity\Access\System\Configuration\Builders\RegisterAccessDependencies::register($container);

        // === Passkey Infrastructure ===

        $container->singleton(
            InMemoryPasskeyCredentialStore::class,
            static fn () : InMemoryPasskeyCredentialStore => new InMemoryPasskeyCredentialStore(),
        );

        $container->singleton(
            InMemoryPasskeyChallengeStore::class,
            static fn () : InMemoryPasskeyChallengeStore => new InMemoryPasskeyChallengeStore(),
        );

        // === Federation Infrastructure ===

        $container->singleton(
            InMemoryFederationConnectionStore::class,
            static fn () : InMemoryFederationConnectionStore => new InMemoryFederationConnectionStore(),
        );

        $container->singleton(
            InMemoryFederatedIdentityLinkStore::class,
            static fn () : InMemoryFederatedIdentityLinkStore => new InMemoryFederatedIdentityLinkStore(),
        );

        // === Password Reset & Email Verification ===

        $container->singleton(InMemoryPasswordResetStore::class, static fn () : InMemoryPasswordResetStore => new InMemoryPasswordResetStore());
        $container->singleton(InMemoryEmailVerificationStore::class, static fn () : InMemoryEmailVerificationStore => new InMemoryEmailVerificationStore());
        $container->singleton(InMemoryEmailChangeStore::class, static fn () : InMemoryEmailChangeStore => new InMemoryEmailChangeStore());
        $container->singleton(InMemoryEmailVerificationStateStore::class, static fn () : InMemoryEmailVerificationStateStore => new InMemoryEmailVerificationStateStore());

        // === MFA Infrastructure ===

        $container->singleton(InMemoryMfaStore::class, static fn () : InMemoryMfaStore => new InMemoryMfaStore());
        $container->singleton(InMemoryMfaChallengeStore::class, static fn () : InMemoryMfaChallengeStore => new InMemoryMfaChallengeStore());
        $container->singleton(Totp::class, static fn () : Totp => new Totp());

        // MFA attempt limit
        $container->singleton(
            InMemoryAttemptLimitStorage::class,
            static fn () : InMemoryAttemptLimitStorage => new InMemoryAttemptLimitStorage(),
        );

        $container->singleton(
            LimitMfaAttempts::class,
            static fn (ContainerInterface $c) : LimitMfaAttempts => new LimitMfaAttempts(
                clock  : $c->get(Clock::class),
                attemptLimitStorage: $c->get(InMemoryAttemptLimitStorage::class),
            ),
        );

        // === Throttling Infrastructure ===

        // Shared throttle store
        $container->singleton(InMemoryAttemptThrottleStore::class, static fn () : InMemoryAttemptThrottleStore => new InMemoryAttemptThrottleStore());

        // Password reset throttle (5 attempts per 15 minutes)
        $container->singleton(
            'auth.throttle.password_reset',
            static fn (ContainerInterface $c) : AttemptThrottle => new AttemptThrottle(
                clock       : $c->get(Clock::class),
                maxAttempts : 5,
                decaySeconds: 900,
                attemptThrottleStore: $c->get(InMemoryAttemptThrottleStore::class),
            ),
        );

        // MFA recovery throttle (3 attempts per 30 minutes)
        $container->singleton(
            'auth.throttle.mfa_recovery',
            static fn (ContainerInterface $c) : AttemptThrottle => new AttemptThrottle(
                clock       : $c->get(Clock::class),
                maxAttempts : 3,
                decaySeconds: 1800,
                attemptThrottleStore: $c->get(InMemoryAttemptThrottleStore::class),
            ),
        );

        // SCIM throttle (60 attempts per minute)
        $container->singleton(
            'auth.throttle.scim',
            static fn (ContainerInterface $c) : AttemptThrottle => new AttemptThrottle(
                clock       : $c->get(Clock::class),
                maxAttempts : 60,
                decaySeconds: 60,
                attemptThrottleStore: $c->get(InMemoryAttemptThrottleStore::class),
            ),
        );

        // === OIDC Infrastructure ===

        $container->singleton(
            InMemoryOidcRequestObjectStore::class,
            static fn () : InMemoryOidcRequestObjectStore => new InMemoryOidcRequestObjectStore(),
        );

        // === SCIM Infrastructure ===

        $container->singleton(
            InMemoryScimDirectoryStore::class,
            static fn (ContainerInterface $c) : InMemoryScimDirectoryStore => new InMemoryScimDirectoryStore(
                passwordHasher: $c->get(PasswordHasher::class),
            ),
        );

        $container->singleton(
            InMemoryScimProvisionedIdentityStore::class,
            static fn () : InMemoryScimProvisionedIdentityStore => new InMemoryScimProvisionedIdentityStore(),
        );

        // === Tenant Infrastructure ===

        $container->singleton(InMemoryTenantStore::class, static fn () : InMemoryTenantStore => new InMemoryTenantStore());

        $container->singleton(
            InMemoryTenantSecurityConfigurationStore::class,
            static fn () : InMemoryTenantSecurityConfigurationStore => new InMemoryTenantSecurityConfigurationStore(),
        );

        $container->singleton(
            InMemoryTenantSecurityChangeRequestStore::class,
            static fn () : InMemoryTenantSecurityChangeRequestStore => new InMemoryTenantSecurityChangeRequestStore(),
        );

        // === Slice 2: Token & Session Infrastructure ===

        // IdentityConfiguration — token secret and default TTLs
        $container->singleton(
            IdentityConfiguration::class,
            static fn () : IdentityConfiguration => new IdentityConfiguration(
                tokenSecret: $_ENV['IDENTITY_TOKEN_SECRET'] ?? $_SERVER['IDENTITY_TOKEN_SECRET'] ?? 'default-secret-must-be-overridden-in-production-at-least-32-chars!',
            ),
        );

        // ClockInterface — alias to concrete Clock
        $container->singleton(
            ClockInterface::class,
            static fn (ContainerInterface $c) : ClockInterface => $c->get(Clock::class),
        );

        // HmacTokenCodec — signs and verifies HMAC-signed JWTs (implements SignToken + VerifyToken)
        $container->singleton(
            HmacTokenCodec::class,
            static function (ContainerInterface $c) : HmacTokenCodec {
                /** @var IdentityConfiguration $config */
                $config = $c->get(IdentityConfiguration::class);
                return new HmacTokenCodec(secret: $config->tokenSecret());
            },
        );

        // TokenBlacklist — in-memory token revocation checks
        $container->singleton(InMemoryTokenBlacklist::class, static fn () : InMemoryTokenBlacklist => new InMemoryTokenBlacklist());
        $container->singleton(
            TokenBlacklist::class,
            static fn (ContainerInterface $c) : TokenBlacklist => $c->get(InMemoryTokenBlacklist::class),
        );

        // IssueAccessToken flow — issues signed access tokens
        $container->singleton(
            IssueAccessToken::class,
            static fn (ContainerInterface $c) : IssueAccessToken => new IssueAccessToken(
                tokens: $c->get(HmacTokenCodec::class),
                clock: $c->get(ClockInterface::class),
            ),
        );

        // VerifyAccessToken flow — verifies signed access tokens
        $container->singleton(
            VerifyAccessToken::class,
            static fn (ContainerInterface $c) : VerifyAccessToken => new VerifyAccessToken(
                tokens: $c->get(HmacTokenCodec::class),
                blacklist: $c->get(TokenBlacklist::class),
                clock: $c->get(ClockInterface::class),
            ),
        );

        // TokenGraph — composition root for token flows
        $container->singleton(
            TokenGraph::class,
            static fn (ContainerInterface $c) : TokenGraph => new TokenGraph(
                issueAccessToken: $c->get(IssueAccessToken::class),
                verifyAccessToken: $c->get(VerifyAccessToken::class),
                blacklist: $c->get(TokenBlacklist::class),
            ),
        );

        // SessionStore — in-memory session persistence
        $container->singleton(InMemorySessionStore::class, static fn () : InMemorySessionStore => new InMemorySessionStore());
        $container->singleton(
            SessionStore::class,
            static fn (ContainerInterface $c) : SessionStore => $c->get(InMemorySessionStore::class),
        );

        // GenerateSessionId — cryptographically secure session ID generation
        $container->singleton(GenerateSessionId::class, static fn () : GenerateSessionId => new RandomSessionId());

        // StartSession flow — starts authenticated user sessions
        $container->singleton(
            StartSession::class,
            static fn (ContainerInterface $c) : StartSession => new StartSession(
                sessionIds: $c->get(GenerateSessionId::class),
                sessions: $c->get(SessionStore::class),
                clock: $c->get(ClockInterface::class),
            ),
        );

        // SessionGraph — composition root for session flows
        $container->singleton(
            SessionGraph::class,
            static fn (ContainerInterface $c) : SessionGraph => new SessionGraph(
                startSession: $c->get(StartSession::class),
                sessions: $c->get(SessionStore::class),
                sessionIds: $c->get(GenerateSessionId::class),
            ),
        );

        // === User Source ===

        // InMemoryUserSource — default user source for dev/test (no constructor)
        $container->singleton(
            UserSourceInterface::class,
            static fn (ContainerInterface $c) : UserSourceInterface => new \Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource(),
        );

        // === Session Storage ===

        // NativeSessionStore — PHP $_SESSION wrapper for session-based identity
        $container->singleton(
            SessionStoreInterface::class,
            static fn (ContainerInterface $c) : SessionStoreInterface => new \Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\NativeSessionStore(
                $c->get(\Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\SessionCookieSettings::class),
            ),
        );

        // SessionCookieSettings — default cookie policy for native sessions
        $container->singleton(
            \Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\SessionCookieSettings::class,
            static fn () : \Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\SessionCookieSettings => new \Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\SessionCookieSettings(),
        );

        // === MFA Challenge Store ===

        // InMemoryMfaChallengeStore — stores MFA challenge lifecycle state
        $container->singleton(
            \Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\MfaChallengeStoreInterface::class,
            static fn () : \Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\MfaChallengeStoreInterface => new \Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\InMemoryMfaChallengeStore(),
        );

        // === Current Authentication Context ===

        // CurrentAuthentication — shared auth context required by all Require* boundaries
        $container->singleton(
            \Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication::class,
            static fn () : \Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication => new \Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication(),
        );

        // === Admin Elevation Store ===

        // InMemoryAdminElevationStore — stores admin elevation state for tenancy
        $container->singleton(
            \Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationStoreInterface::class,
            static fn () : \Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\AdminElevationStoreInterface => new \Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealm\InMemoryAdminElevationStore(),
        );

        // === Identity & Auth Facades ===

        // SessionIdentity — session-based identity backend
        $container->singleton(
            SessionIdentityInterface::class,
            static function (ContainerInterface $c) : SessionIdentityInterface {
                /** @var IdentityConfiguration $config */
                $config = $c->get(IdentityConfiguration::class);
                return new SessionIdentity(
                    sessionStore    : $c->get(SessionStoreInterface::class),
                    clock           : $c->get(Clock::class),
                    auditLog        : $c->get(NullAuditLog::class),
                    sessionLifetime : $config->sessionLifetime(),
                    sessionRegistry : $c->get(SessionRegistryInterface::class),
                );
            },
        );

        // JwtIdentity — JWT-based identity backend
        $container->singleton(
            JwtIdentityInterface::class,
            static function (ContainerInterface $c) : JwtIdentityInterface {
                /** @var IdentityConfiguration $config */
                $config = $c->get(IdentityConfiguration::class);
                $now = new \DateTimeImmutable();
                $tokenExpiry = (int) $now->diff($now->add($config->defaultTokenTtl()))->format('%s')
                    + (int) $config->defaultTokenTtl()->i * 60
                    + (int) $config->defaultTokenTtl()->h * 3600
                    + (int) $config->defaultTokenTtl()->d * 86400;
                $refreshTokenExpiry = (int) $now->diff($now->add($config->defaultRefreshTokenTtl()))->format('%s')
                    + (int) $config->defaultRefreshTokenTtl()->i * 60
                    + (int) $config->defaultRefreshTokenTtl()->h * 3600
                    + (int) $config->defaultRefreshTokenTtl()->d * 86400;

                return new JwtIdentity(
                    userSource          : $c->get(UserSourceInterface::class),
                    tokenCodec          : $c->get(HmacTokenCodec::class),
                    clock               : $c->get(Clock::class),
                    tokenRevocationStore: $c->has(TokenRevocationStoreInterface::class) ? $c->get(TokenRevocationStoreInterface::class) : null,
                    refreshTokenStore   : $c->has(RefreshTokenStoreInterface::class) ? $c->get(RefreshTokenStoreInterface::class) : null,
                    tokenExpiry         : $tokenExpiry,
                    refreshTokenExpiry  : $refreshTokenExpiry,
                    issuer              : $config->tokenIssuer(),
                );
            },
        );

        // Identity capability coordinator — assembled via factory to handle circular deps
        // Flows that need IdentityInterface get it via lazy container resolution
        $container->singleton(Identity::class, static function (ContainerInterface $c) : Identity {
            // Break circular dependency: Identity → Authentication → Login → IdentityInterface
            // by using lazy resolution through the container
            $sessionIdentity = $c->get(SessionIdentityInterface::class);
            $jwtIdentity     = $c->get(JwtIdentityInterface::class);

            // Build Identity using a factory that resolves circular references
            return IdentityAssembler::assemble($c, $sessionIdentity, $jwtIdentity);
        });

        // Auth facade — requires Identity
        $container->singleton(AuthInterface::class, static fn (ContainerInterface $c) : Auth => new Auth(
            identity: $c->get(Identity::class),
        ));
    }
}
