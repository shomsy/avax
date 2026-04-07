<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Providers\Runtime\Auth;

use Avax\Container\DependencyInjection\Capability\Providers\Runtime\ServiceProvider;
use Avax\HTTP\Security\CsrfTokenManager;
use Avax\HTTP\Session\Session;
use Psr\Log\LoggerInterface;

/**
 * Service provider for security-related services.
 *
 */
class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register CSRF token manager with session and logger dependencies.
     *
     */
    public function register() : void
    {
        $this->app->singleton(abstract: CsrfTokenManager::class, concrete: function () {
            return new CsrfTokenManager(
                session: $this->app->get(id: Session::class),
                logger : $this->app->get(id: LoggerInterface::class)
            );
        });
    }
}
