<?php

declare(strict_types=1);

namespace Avax\Container\Providers\HTTP;

use Avax\Container\Providers\ServiceProvider;
use Avax\HTTP\Session\Session;
use Avax\HTTP\Session\SessionAdapter;

/**
 * Service Provider for session management.
 *
 * @see docs/Providers/HTTP/SessionServiceProvider.md#quick-summary
 */
class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register session manager, adapter, and alias.
     *
     * @see docs/Providers/HTTP/SessionServiceProvider.md#method-register
     */
    public function register() : void
    {
        $this->app->singleton(abstract: Session::class, concrete: static function () {
            // Configuration can be injected here
            return new Session;
        });

        $this->app->singleton(abstract: SessionAdapter::class, concrete: SessionAdapter::class);

        // Alias 'session'
        $this->app->singleton(abstract: 'session', concrete: function () {
            return $this->app->get(id: Session::class);
        });
    }
}
