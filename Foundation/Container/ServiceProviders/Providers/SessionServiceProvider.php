<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Container\ServiceProviders\ServiceProvider;
use Avax\HTTP\Session\Contracts\SessionInterface;
use Avax\HTTP\Session\Contracts\SessionContract;
use Avax\HTTP\Session\Contracts\Storage\Store;
use Avax\HTTP\Session\Providers\SessionProvider;
use Avax\HTTP\Session\Security\CookieManager;
use Avax\HTTP\Session\Adapters\SessionAdapter;
use Avax\HTTP\Session\Storage\NativeStore;

final class SessionServiceProvider extends ServiceProvider
{
    /**
     * @throws \Random\RandomException
     */
    public function register() : void
    {
        $this->dependencyInjector->singleton(
            abstract: CookieManager::class,
            concrete: static fn() => CookieManager::strict()
        );

        $this->dependencyInjector->singleton(
            abstract: SessionAdapter::class,
            concrete: fn() => new SessionAdapter(
                cookieManager: $this->dependencyInjector->get(id: CookieManager::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: Store::class,
            concrete: fn() => new NativeStore(
                cookieManager: $this->dependencyInjector->get(id: CookieManager::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: SessionProvider::class,
            concrete: fn() => new SessionProvider(
                store         : $this->dependencyInjector->get(id: Store::class),
                cookieManager : $this->dependencyInjector->get(id: CookieManager::class),
                sessionAdapter: $this->dependencyInjector->get(id: SessionAdapter::class)
            )
        );

        // Bind contracts/aliases
        $this->dependencyInjector->singleton(
            abstract: SessionInterface::class,
            concrete: fn() => $this->dependencyInjector->get(id: SessionProvider::class)
        );

        $this->dependencyInjector->singleton(
            abstract: SessionContract::class,
            concrete: fn() => $this->dependencyInjector->get(id: SessionProvider::class)
        );
    }

    public function boot() : void {}
}
