<?php

declare(strict_types=1);

namespace Avax\Container\Providers\Auth;

use Avax\Auth\Adapters\PasswordHasher;
use Avax\Auth\Adapters\RateLimiter;
use Avax\Auth\Adapters\SessionIdentity;
use Avax\Auth\Authenticator;
use Avax\Auth\Contracts\AuthInterface;
use Avax\Auth\Contracts\IdentityInterface;
use Avax\Container\Providers\ServiceProvider;

/**
 * Service Provider for authentication services.
 *
 * @see docs/Providers/Auth/AuthenticationServiceProvider.md#quick-summary
 */
class AuthenticationServiceProvider extends ServiceProvider
{
    /**
     * Register authentication services: hasher, rate limiter, identity, authenticator, and alias.
     *
     * @see docs/Providers/Auth/AuthenticationServiceProvider.md#method-register
     */
    public function register() : void
    {
        $this->app->singleton(abstract: PasswordHasher::class, concrete: PasswordHasher::class);
        $this->app->singleton(abstract: RateLimiter::class, concrete: RateLimiter::class);

        // Use SessionIdentity as the default identity implementation
        $this->app->singleton(abstract: IdentityInterface::class, concrete: SessionIdentity::class);

        $this->app->singleton(abstract: AuthInterface::class, concrete: Authenticator::class);
        $this->app->singleton(abstract: Authenticator::class, concrete: Authenticator::class);

        // Bind 'auth' alias
        $this->app->singleton(abstract: 'auth', concrete: function () {
            return $this->app->get(id: AuthInterface::class);
        });
    }
}
