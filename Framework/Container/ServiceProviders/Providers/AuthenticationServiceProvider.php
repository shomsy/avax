<?php

declare(strict_types=1);

namespace Gemini\Container\ServiceProviders\Providers;

use Gemini\Auth\Actions\Check;
use Gemini\Auth\Actions\GetUser;
use Gemini\Auth\Actions\Login;
use Gemini\Auth\Actions\Logout;
use Gemini\Auth\Actions\Register;
use Gemini\Auth\Authenticator;
use Gemini\Auth\Contracts\AuthInterface;
use Gemini\Auth\Contracts\CredentialsInterface;
use Gemini\Auth\Contracts\IdentityInterface;
use Gemini\Auth\Contracts\UserSourceInterface;
use Gemini\Auth\Data\Credentials;
use Gemini\Auth\Http\AccessMiddleware;
use Gemini\Auth\Http\AuthMiddleware;
use Gemini\Auth\Adapters\SessionIdentity;
use Gemini\Auth\Adapters\RateLimiter;
use Gemini\Auth\Adapters\PasswordHasher;
use Gemini\Auth\Adapters\UserDataSource;
use Gemini\Container\ServiceProviders\ServiceProvider;
use Gemini\Database\QueryBuilder\QueryBuilder;
use Gemini\HTTP\Session\Session;

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