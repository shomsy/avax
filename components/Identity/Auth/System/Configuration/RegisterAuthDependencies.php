<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\Providers\BaseRegisterDependency;
use Avax\Components\Identity\Auth\System\Auth;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\DefaultAuth;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\LoginRateLimitStorageInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Foundation\Exceptions\ConfigurationException;
use Avax\Components\Identity\Auth\System\Foundation\IdGenerator;
use Avax\Components\Identity\Auth\System\Foundation\IdGeneratorInterface;
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
            $this->container->singleton(abstract: IdentityInterface::class, concrete: function () {
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
        if (! $this->container->has(id: Auth::class)) {
            $this->container->singleton(abstract: Auth::class, concrete: function () {
                $builder = $this->applyOptionalBindings(builder: DefaultAuth::configuration()
                                                                     ->forUser(userSource: $this->container->get(id: UserSourceInterface::class))
                                                                     ->withIdentity(identity: $this->container->get(id: IdentityInterface::class))
                                                                     ->usingHasher(passwordHasher: $this->container->get(id: PasswordHasher::class))
                                                                     ->usingIdGenerator(idGenerator: $this->container->get(id: IdGeneratorInterface::class)));

                $rateLimit = $this->resolveLoginRateLimit();

                if ($rateLimit !== null) {
                    $builder->protectFromBruteForce(rateLimit: $rateLimit);
                }

                return $builder->ready();
            });
        }
    }

    /**
     * @throws ReflectionException
     */
    private function applyOptionalBindings(AuthBuilder $builder) : AuthBuilder
    {
        if ($this->container->has(id: Clock::class)) {
            $builder->withClock(clock: $this->container->get(id: Clock::class));
        }

        if ($this->container->has(id: AuditLogInterface::class)) {
            $builder->withAuditLog(auditLog: $this->container->get(id: AuditLogInterface::class));
        }

        return $builder;
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
            storage: $this->container->get(id: LoginRateLimitStorageInterface::class),
            clock  : $this->container->get(id: Clock::class),
        );
    }
}
