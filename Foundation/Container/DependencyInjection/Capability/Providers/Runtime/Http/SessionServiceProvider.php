<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Providers\Runtime\Http;

use Avax\Container\DependencyInjection\Capability\Providers\Runtime\ServiceProvider;
use Avax\HTTP\Session\Session;
use Avax\HTTP\Session\SessionAdapter;

/**
 * Service Provider for session management.
 *
 */
class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register session manager, adapter, and alias.
     *
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
