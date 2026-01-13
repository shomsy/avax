<?php

declare(strict_types=1);

namespace Avax\Container\Providers\Auth;

use Avax\Container\Providers\ServiceProvider;
use Avax\HTTP\Security\CsrfTokenManager;
use Avax\HTTP\Session\Session;
use Psr\Log\LoggerInterface;

/**
 * Service provider for security-related services.
 *
 * @see docs/Providers/Auth/SecurityServiceProvider.md#quick-summary
 */
class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register CSRF token manager with session and logger dependencies.
     *
     * @see docs/Providers/Auth/SecurityServiceProvider.md#method-register
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
