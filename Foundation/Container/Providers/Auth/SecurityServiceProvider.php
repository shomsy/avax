<?php

declare(strict_types=1);

namespace Avax\Container\Providers\Auth;

use Avax\Container\Features\Operate\Boot\ServiceProvider;
use Avax\HTTP\Security\CsrfTokenManager;
use Avax\HTTP\Session\Session;
use Psr\Log\LoggerInterface;

/**
 * Service provider for security-related services.
 *
 * @see docs_md/Providers/Auth/SecurityServiceProvider.md#quick-summary
 */
class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register CSRF token manager with session and logger dependencies.
     *
     * @return void
     * @see docs_md/Providers/Auth/SecurityServiceProvider.md#method-register
     */
    public function register() : void
    {
        $this->app->singleton(abstract: CsrfTokenManager::class, concrete: function () {
            return new CsrfTokenManager(
                session: $this->app->get(Session::class),
                logger : $this->app->get(LoggerInterface::class)
            );
        });
    }
}
