<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\AvaxContainer;

use Avax\Auth\System\Auth;
use Avax\Auth\System\AuthInterface;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Flow\Login\RateLimit\LoginRateLimitStorageInterface;
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
    public function register() : void
    {
        $this->registerFoundation();
        $this->registerIdentity();
        $this->registerAuth();
    }

    private function registerFoundation() : void
    {
        if (! $this->app->has(PasswordHasher::class)) {
            $this->app->singleton(PasswordHasher::class, PasswordHasher::class);
        }

        if (! $this->app->has(Clock::class)) {
            $this->app->singleton(Clock::class, Clock::class);
        }

        if (! $this->app->has(IdGeneratorInterface::class)) {
            $this->app->singleton(IdGeneratorInterface::class, IdGenerator::class);
        }

        if (! $this->app->has(IdGenerator::class)) {
            $this->app->singleton(IdGenerator::class, IdGenerator::class);
        }
    }

    private function registerIdentity() : void
    {
        if (! $this->app->has(IdentityInterface::class)) {
            $this->app->singleton(IdentityInterface::class, function () {
                $sessionIdentity = $this->app->has(SessionIdentityInterface::class)
                    ? $this->app->get(SessionIdentityInterface::class)
                    : null;

                $jwtIdentity = $this->app->has(JwtIdentityInterface::class)
                    ? $this->app->get(JwtIdentityInterface::class)
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
        if (! $this->app->has(AuthInterface::class)) {
            $this->app->singleton(AuthInterface::class, function () {
                $builder = Auth::configuration()
                    ->forUser(userSource: $this->app->get(UserSourceInterface::class))
                    ->withIdentity(identity: $this->app->get(IdentityInterface::class))
                    ->usingHasher(passwordHasher: $this->app->get(PasswordHasher::class))
                    ->usingIdGenerator(idGenerator: $this->app->get(IdGeneratorInterface::class));

                $rateLimit = $this->resolveLoginRateLimit();

                if ($rateLimit !== null) {
                    $builder->protectFromBruteForce(rateLimit: $rateLimit);
                }

                return $builder->ready();
            });
        }

        if (! $this->app->has(Auth::class)) {
            $this->app->singleton(Auth::class, function () {
                return $this->app->get(AuthInterface::class);
            });
        }
    }

    private function resolveLoginRateLimit() : LoginRateLimit|null
    {
        if ($this->app->has(LoginRateLimit::class)) {
            return $this->app->get(LoginRateLimit::class);
        }

        if (! $this->app->has(LoginRateLimitStorageInterface::class)) {
            return null;
        }

        return new LoginRateLimit(
            storage: $this->app->get(LoginRateLimitStorageInterface::class),
            clock  : $this->app->get(Clock::class)
        );
    }
}
