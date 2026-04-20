<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\AvaxContainer;

use Avax\Auth\System\Auth;
use Avax\Auth\System\AuthInterface;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Flows\Login\RateLimit\LoginRateLimitStorageInterface;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\System\Foundation\IdGenerator;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use Avax\Container\Providers\ServiceProvider;
use RuntimeException;

/**
 * Optional Avax Container adapter for assembling the auth kernel.
 */
final class AuthServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register() : void
    {
        $this->registerFoundation();
        $this->registerIdentity();
        $this->registerAuth();
    }

    private function registerFoundation() : void
    {
        if (! $this->app->has(id: PasswordHasher::class)) {
            $this->app->singleton(id: PasswordHasher::class, implementation: PasswordHasher::class);
        }

        if (! $this->app->has(id: Clock::class)) {
            $this->app->singleton(id: Clock::class, implementation: Clock::class);
        }

        if (! $this->app->has(id: IdGeneratorInterface::class)) {
            $this->app->singleton(id: IdGeneratorInterface::class, implementation: IdGenerator::class);
        }

        if (! $this->app->has(id: IdGenerator::class)) {
            $this->app->singleton(id: IdGenerator::class, implementation: IdGenerator::class);
        }
    }

    private function registerIdentity() : void
    {
        if (! $this->app->has(id: IdentityInterface::class)) {
            $this->app->singleton(id: IdentityInterface::class, implementation: function () {
                $sessionIdentity = $this->app->has(id: SessionIdentityInterface::class)
                    ? $this->app->get(id: SessionIdentityInterface::class)
                    : null;

                $jwtIdentity = $this->app->has(id: JwtIdentityInterface::class)
                    ? $this->app->get(id: JwtIdentityInterface::class)
                    : null;

                if ($sessionIdentity === null && $jwtIdentity === null) {
                    throw new RuntimeException(
                        message: 'AuthServiceProvider requires a SessionIdentityInterface or JwtIdentityInterface binding.'
                    );
                }

                return new Identity(
                    sessionIdentity: $sessionIdentity,
                    jwtIdentity    : $jwtIdentity
                );
            });
        }
    }

    private function registerAuth() : void
    {
        if (! $this->app->has(id: AuthInterface::class)) {
            $this->app->singleton(id: AuthInterface::class, implementation: function () {
                $builder = $this->applyOptionalBindings(builder: Auth::configuration()
                    ->forUser(userSource: $this->app->get(id: UserSourceInterface::class))
                    ->withIdentity(identity: $this->app->get(id: IdentityInterface::class))
                    ->usingHasher(passwordHasher: $this->app->get(id: PasswordHasher::class))
                    ->usingIdGenerator(idGenerator: $this->app->get(id: IdGeneratorInterface::class)));

                $rateLimit = $this->resolveLoginRateLimit();

                if ($rateLimit !== null) {
                    $builder->protectFromBruteForce(rateLimit: $rateLimit);
                }

                return $builder->ready();
            });
        }

        if (! $this->app->has(id: Auth::class)) {
            $this->app->singleton(id: Auth::class, implementation: function () {
                return $this->app->get(id: AuthInterface::class);
            });
        }
    }

    private function applyOptionalBindings(AuthBuilder $builder) : AuthBuilder
    {
        if ($this->app->has(id: Clock::class)) {
            $builder->withClock(clock: $this->app->get(id: Clock::class));
        }

        if ($this->app->has(id: AuditLogInterface::class)) {
            $builder->withAuditLog(auditLog: $this->app->get(id: AuditLogInterface::class));
        }

        return $builder;
    }

    private function resolveLoginRateLimit() : LoginRateLimit|null
    {
        if ($this->app->has(id: LoginRateLimit::class)) {
            return $this->app->get(id: LoginRateLimit::class);
        }

        if (! $this->app->has(id: LoginRateLimitStorageInterface::class)) {
            return null;
        }

        return new LoginRateLimit(
            storage: $this->app->get(id: LoginRateLimitStorageInterface::class),
            clock  : $this->app->get(id: Clock::class)
        );
    }
}
