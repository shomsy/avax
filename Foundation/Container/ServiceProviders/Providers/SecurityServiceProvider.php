<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Auth\Application\Service\AccessControl\AccessControlService;
use Avax\Auth\Application\Service\RateLimiterService;
use Avax\Container\ServiceProviders\ServiceProvider;
use Avax\HTTP\Security\CsrfTokenManager;
use Avax\HTTP\Session\Contracts\SessionInterface;
use Psr\Log\LoggerInterface;

/**
 * Service provider for security-related services.
 *
 * Registers security services and components,
 * specifically the CsrfTokenManager, with the application container.
 */
class SecurityServiceProvider extends ServiceProvider
{

    /**
     * Register services into the container.
     *
     * This method registers the CsrfTokenManager as a singleton in the container.
     * It ensures a single instance is used throughout the application lifecycle.
     *
     */
    #[\Override]
    public function register() : void
    {
        $this->dependencyInjector->singleton(
            abstract: CsrfTokenManager::class,
            concrete: fn() : CsrfTokenManager => new CsrfTokenManager(
            // Injecting SessionInterface dependency into CsrfTokenManager,
            // indicating the necessity of session management for CSRF token handling.
                session: $this->dependencyInjector->get(id: SessionInterface::class),
                logger : $this->dependencyInjector->get(id: LoggerInterface::class),
            )
        );

        $this->dependencyInjector->singleton(
            abstract: AccessControlService::class,
            concrete: AccessControlService::class
        );

        $this->dependencyInjector->singleton(abstract: RateLimiterService::class, concrete: fn(
        ) : RateLimiterService => new RateLimiterService(
            session: $this->dependencyInjector->get(id: SessionInterface::class)
        ));
    }

    /**
     * Perform additional bootstrapping for security.
     *
     * Intended for any security-related initialization that
     * might be required after the service registration.
     */
    #[\Override]
    public function boot() : void
    {
        // Additional bootstrapping for security if necessary
    }
}