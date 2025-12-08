<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Auth\Actions\Check;
use Avax\Auth\Actions\GetUser;
use Avax\Auth\Actions\Login;
use Avax\Auth\Actions\Logout;
use Avax\Auth\Actions\Register;
use Avax\Auth\Authenticator;
use Avax\Auth\Contracts\AuthInterface;
use Avax\Auth\Contracts\CredentialsInterface;
use Avax\Auth\Contracts\IdentityInterface;
use Avax\Auth\Contracts\UserSourceInterface;
use Avax\Auth\Data\Credentials;
use Avax\Auth\Http\AccessMiddleware;
use Avax\Auth\Http\AuthMiddleware;
use Avax\Auth\Adapters\SessionIdentity;
use Avax\Auth\Adapters\RateLimiter;
use Avax\Auth\Adapters\PasswordHasher;
use Avax\Auth\Adapters\UserDataSource;
use Avax\Container\ServiceProviders\ServiceProvider;
use Avax\Database\QueryBuilder\QueryBuilder;
use Avax\HTTP\Session\Session;

/**
 * Provider for Authentication services.
 */
class AuthenticationServiceProvider extends ServiceProvider
{
    /**
     * Register authentication-related services and their dependencies.
     */
    public function register() : void
    {
        // Core Dependencies
        $this->dependencyInjector->singleton(abstract: PasswordHasher::class, concrete: PasswordHasher::class);
        $this->dependencyInjector->singleton(abstract: CredentialsInterface::class, concrete: Credentials::class);

        // Identity Provider
        $this->dependencyInjector->singleton(
            abstract: IdentityInterface::class,
            concrete: fn() => new SessionIdentity(
                session: $this->dependencyInjector->get(Session::class),
                userProvider: $this->dependencyInjector->get(UserSourceInterface::class)
            )
        );

        // User Data Source
        $this->dependencyInjector->singleton(
            abstract: UserSourceInterface::class,
            concrete: fn() => new UserDataSource(
                queryBuilder: $this->dependencyInjector->get(QueryBuilder::class),
                passwordHasher: $this->dependencyInjector->get(PasswordHasher::class)
            )
        );

        // Authenticator Orchestrator (The "Facade" impl)
        $this->dependencyInjector->singleton(
            abstract: AuthInterface::class,
            concrete: fn() => new Authenticator(
                loginAction: new Login(identity: $this->dependencyInjector->get(IdentityInterface::class)),
                logoutAction: new Logout(identity: $this->dependencyInjector->get(IdentityInterface::class)),
                getUserAction: new GetUser(identity: $this->dependencyInjector->get(IdentityInterface::class)),
                checkAction: new Check(identity: $this->dependencyInjector->get(IdentityInterface::class))
            )
        );

        // Actions
        $this->dependencyInjector->singleton(abstract: Register::class, concrete: Register::class);

        // Infrastructure
        $this->dependencyInjector->singleton(abstract: RateLimiter::class, concrete: RateLimiter::class);

        // Middleware
        $this->dependencyInjector->singleton(abstract: AuthMiddleware::class, concrete: AuthMiddleware::class);
        $this->dependencyInjector->singleton(abstract: AccessMiddleware::class, concrete: AccessMiddleware::class);
    }

    public function boot() : void
    {
        // Boot logic
    }
}