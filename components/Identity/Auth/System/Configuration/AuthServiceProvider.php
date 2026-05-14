<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\InMemoryAttemptThrottleStore;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\InMemoryKnownAuthenticationEnvironmentStore;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\InMemoryRiskSignalStore;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\NullAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\InMemoryLifecycleStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\InMemoryScimDirectoryStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\InMemoryScimProvisionedIdentityStore;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\InMemoryEmailChangeStore;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\InMemoryPasswordResetStore;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStateStore;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStore;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Foundation\IdGenerator;
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
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Auth\System\PublicSurface\AuthInterface;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;

/**
 * AuthServiceProvider — registers Identity/Auth component dependencies.
 *
 * Registers all default infrastructure dependencies that AuthBuilder previously
 * created with `?? new` fallback patterns. This eliminates the fallback anti-pattern
 * by pre-registering default implementations in the composition root.
 */
final class AuthServiceProvider implements ServiceProvider
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
                knownEnvironments: $c->get(InMemoryKnownAuthenticationEnvironmentStore::class),
                signals          : $c->get(InMemoryRiskSignalStore::class),
            ),
        );

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
                storage: $c->get(InMemoryAttemptLimitStorage::class),
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
                store       : $c->get(InMemoryAttemptThrottleStore::class),
            ),
        );

        // MFA recovery throttle (3 attempts per 30 minutes)
        $container->singleton(
            'auth.throttle.mfa_recovery',
            static fn (ContainerInterface $c) : AttemptThrottle => new AttemptThrottle(
                clock       : $c->get(Clock::class),
                maxAttempts : 3,
                decaySeconds: 1800,
                store       : $c->get(InMemoryAttemptThrottleStore::class),
            ),
        );

        // SCIM throttle (60 attempts per minute)
        $container->singleton(
            'auth.throttle.scim',
            static fn (ContainerInterface $c) : AttemptThrottle => new AttemptThrottle(
                clock       : $c->get(Clock::class),
                maxAttempts : 60,
                decaySeconds: 60,
                store       : $c->get(InMemoryAttemptThrottleStore::class),
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

        // === Identity & Auth Facades ===

        // Identity capability coordinator
        $container->singleton(Identity::class, static fn () : Identity => new Identity());

        // Auth facade — requires Identity
        $container->singleton(AuthInterface::class, static fn (ContainerInterface $c) : Auth => new Auth(
            identity: $c->get(Identity::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
