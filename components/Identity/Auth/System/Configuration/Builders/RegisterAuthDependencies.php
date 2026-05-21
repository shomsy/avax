<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Builders;

use Avax\Components\Application\Container\System\Capabilities\Providers\BaseRegisterDependency;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\NullAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle\InMemoryLifecycleStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\InMemoryScimDirectoryStore;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\InMemoryScimProvisionedIdentityStore;
use Avax\Components\Identity\Auth\System\Flows\ChangeEmail\InMemoryEmailChangeStore;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\LoginRateLimitStorageInterface;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\InMemoryPasswordResetStore;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStateStore;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStore;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Foundation\Exceptions\ConfigurationException;
use Avax\Components\Identity\Auth\System\Foundation\IdGenerator;
use Avax\Components\Identity\Auth\System\Foundation\IdGeneratorInterface;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Auth\System\PublicSurface\AuthInterface;
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
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use Override;
use ReflectionException;

/**
 * Optional Avax Container adapter for assembling the auth kernel.
 */
final class RegisterAuthDependencies extends BaseRegisterDependency
{
    #[Override]
    public function register() : void
    {
        $this->registerFoundation();
        $this->registerIdentity();
        $this->registerAuth();
    }

    private function registerFoundation() : void
    {
        if (! $this->container->has(id: PasswordHasher::class)) {
            $this->container->singleton(abstract: PasswordHasher::class, concrete: PasswordHasher::class);
        }

        if (! $this->container->has(id: Clock::class)) {
            $this->container->singleton(abstract: Clock::class, concrete: Clock::class);
        }

        if (! $this->container->has(id: IdGeneratorInterface::class)) {
            $this->container->singleton(abstract: IdGeneratorInterface::class, concrete: IdGenerator::class);
        }

        if (! $this->container->has(id: IdGenerator::class)) {
            $this->container->singleton(abstract: IdGenerator::class, concrete: IdGenerator::class);
        }
    }

    private function registerIdentity() : void
    {
        if (! $this->container->has(id: IdentityInterface::class)) {
            $this->container->singleton(abstract: IdentityInterface::class, concrete: function () : Identity {
                $sessionIdentity = $this->container->has(id: SessionIdentityInterface::class)
                    ? $this->container->get(id: SessionIdentityInterface::class)
                    : null;

                $jwtIdentity = $this->container->has(id: JwtIdentityInterface::class)
                    ? $this->container->get(id: JwtIdentityInterface::class)
                    : null;

                if ($sessionIdentity === null && $jwtIdentity === null) {
                    throw ConfigurationException::missingIdentityBackend(
                        buildPath: 'RegisterAuthDependencies::registerIdentity()',
                        hint     : 'Provide a SessionIdentityInterface or JwtIdentityInterface binding.',
                    );
                }

                return Identity::fromBackends(
                    sessionIdentity: $sessionIdentity,
                    jwtIdentity    : $jwtIdentity,
                );
            });
        }
    }

    private function registerAuth() : void
    {
        if (! $this->container->has(id: AuthInterface::class)) {
            $this->container->singleton(abstract: AuthInterface::class, concrete: function () : Auth {
                $authBuilder = $this->authBuilderFromContainer()
                    ->forUser(userSource: $this->container->get(id: UserSourceInterface::class))
                    ->withIdentity(identity: $this->container->get(id: IdentityInterface::class))
                    ->usingHasher(passwordHasher: $this->container->get(id: PasswordHasher::class))
                    ->usingIdGenerator(idGenerator: $this->container->get(id: IdGeneratorInterface::class));

                $rateLimit = $this->resolveLoginRateLimit();

                if ($rateLimit instanceof LoginRateLimit) {
                    $authBuilder->protectFromBruteForce(loginRateLimit: $rateLimit);
                }

                $auth = $authBuilder->ready();
                $this->container->alias(abstract: AuthInterface::class, alias: Auth::class);

                return $auth;
            });
        }
    }

    private function authBuilderFromContainer() : AuthBuilder
    {
        $authBuilder = new AuthBuilder();

        $authBuilder->withClock(clock: $this->container->get(id: Clock::class));
        $authBuilder->usingIdGenerator(idGenerator: $this->container->get(id: IdGenerator::class));
        $authBuilder->usingHasher(passwordHasher: $this->container->get(id: PasswordHasher::class));
        $authBuilder->withAuditLog(auditLog: $this->container->get(id: NullAuditLog::class));

        $authBuilder->withPasswordResetStore(passwordResetStore: $this->container->get(id: InMemoryPasswordResetStore::class));
        $authBuilder->withEmailVerificationStore(emailVerificationStore: $this->container->get(id: InMemoryEmailVerificationStore::class));
        $authBuilder->withEmailChangeStore(emailChangeStore: $this->container->get(id: InMemoryEmailChangeStore::class));
        $authBuilder->withEmailVerificationState(emailVerificationStateStore: $this->container->get(id: InMemoryEmailVerificationStateStore::class));

        $authBuilder->withMfaStore(mfaStore: $this->container->get(id: InMemoryMfaStore::class));
        $authBuilder->withMfaChallengeStore(mfaChallengeStore: $this->container->get(id: InMemoryMfaChallengeStore::class));
        $authBuilder->usingTotp(totp: $this->container->get(id: Totp::class));
        $authBuilder->withMfaAttemptLimit(limitMfaAttempts: $this->container->get(id: LimitMfaAttempts::class));
        $authBuilder->withPasskeyCredentialStore(passkeyCredentialStore: $this->container->get(id: InMemoryPasskeyCredentialStore::class));
        $authBuilder->withPasskeyChallengeStore(passkeyChallengeStore: $this->container->get(id: InMemoryPasskeyChallengeStore::class));

        $authBuilder->withOAuthClientRegistry(oauthClientRegistry: $this->container->get(id: InMemoryOAuthClientRegistry::class));
        $authBuilder->withAuthorizationCodeStore(authorizationCodeStore: $this->container->get(id: InMemoryAuthorizationCodeStore::class));
        $authBuilder->withOidcRequestObjectStore(oidcRequestObjectStore: $this->container->get(id: InMemoryOidcRequestObjectStore::class));

        $authBuilder->withFederationConnectionStore(federationConnectionStore: $this->container->get(id: InMemoryFederationConnectionStore::class));
        $authBuilder->withFederatedIdentityLinkStore(federatedIdentityLinkStore: $this->container->get(id: InMemoryFederatedIdentityLinkStore::class));

        $authBuilder->withLifecycleStore(lifecycleStore: $this->container->get(id: InMemoryLifecycleStore::class));
        $authBuilder->withScimDirectoryStore(scimDirectoryStore: $this->container->get(id: InMemoryScimDirectoryStore::class));
        $authBuilder->withScimProvisionedIdentityStore(scimProvisionedIdentityStore: $this->container->get(id: InMemoryScimProvisionedIdentityStore::class));

        $authBuilder->withAdminElevationStore(adminElevationStore: $this->container->get(id: InMemoryAdminElevationStore::class));
        $authBuilder->withTenantStore(tenantStore: $this->container->get(id: InMemoryTenantStore::class));
        $authBuilder->withTenantSecurityConfigurationStore(tenantSecurityConfigurationStore: $this->container->get(id: InMemoryTenantSecurityConfigurationStore::class));
        $authBuilder->withTenantSecurityChangeRequestStore(tenantSecurityChangeRequestStore: $this->container->get(id: InMemoryTenantSecurityChangeRequestStore::class));
        $authBuilder->withRiskEngine(deterministicRiskEngine: $this->container->get(id: DeterministicRiskEngine::class));

        $authBuilder->withPasswordResetThrottle(attemptThrottle: $this->container->get(id: 'auth.throttle.password_reset'));
        $authBuilder->withMfaRecoveryThrottle(attemptThrottle: $this->container->get(id: 'auth.throttle.mfa_recovery'));
        $authBuilder->withScimThrottle(attemptThrottle: $this->container->get(id: 'auth.throttle.scim'));

        return $authBuilder;
    }

    /**
     * @throws ReflectionException
     */
    private function resolveLoginRateLimit() : LoginRateLimit|null
    {
        if ($this->container->has(id: LoginRateLimit::class)) {
            return $this->container->get(id: LoginRateLimit::class);
        }

        if (! $this->container->has(id: LoginRateLimitStorageInterface::class)) {
            return null;
        }

        return new LoginRateLimit(
            clock  : $this->container->get(id: Clock::class),
            loginRateLimitStorage: $this->container->get(id: LoginRateLimitStorageInterface::class),
        );
    }
}
