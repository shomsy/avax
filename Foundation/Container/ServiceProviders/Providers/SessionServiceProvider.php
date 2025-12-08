<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Container\ServiceProviders\ServiceProvider;
use Avax\HTTP\Session\Contracts\{Factories\BagRegistryFactoryInterface,
    SessionBuilderInterface,
    SessionInterface,
    SessionManagerInterface,
    SessionStoreInterface};
use Avax\HTTP\Session\Drivers\NativeSession;
use Avax\HTTP\Session\SessionBuilder;
use Avax\HTTP\Session\SessionContext;
use Avax\HTTP\Session\SessionManager;
use Avax\HTTP\Session\Stores\NativeSessionStore;
use Avax\HTTP\Session\Support\Factories\BagRegistryFactory;

final class SessionServiceProvider extends ServiceProvider
{
    /**
     * @throws \Random\RandomException
     */
    public function register() : void
    {
        $this->bindSingleton(abstract: SessionStoreInterface::class, concrete: NativeSessionStore::class);
        $this->bindSingleton(abstract: BagRegistryFactoryInterface::class, concrete: BagRegistryFactory::class);

        // Main SessionInterface (deferred registry via closure)
        $this->dependencyInjector->bind(
            abstract: SessionInterface::class,
            concrete: static fn($c) => new NativeSession(
                store          : $c->get(SessionStoreInterface::class),
                registryFactory: static fn(SessionInterface $session) => $c
                    ->get(BagRegistryFactoryInterface::class)
                    ->create($session)
            )
        );

        // Use session->getRegistry() directly wherever needed
        $this->dependencyInjector->singleton(
            abstract: SessionBuilderInterface::class,
            concrete: fn() => new SessionBuilder(
                session : $this->resolve(abstract: SessionInterface::class),
                registry: $this->resolve(abstract: SessionInterface::class)->getRegistry(),
                context : new SessionContext(namespace: 'default')
            )
        );

        $this->dependencyInjector->singleton(
            abstract: SessionManagerInterface::class,
            concrete: fn() => new SessionManager(
                session: $this->resolve(abstract: SessionInterface::class),
                bags   : $this->resolve(abstract: SessionInterface::class)->getRegistry()
            )
        );
    }

    private function bindSingleton(string $abstract, string $concrete) : void
    {
        $this->dependencyInjector->singleton(
            abstract: $abstract,
            concrete: static fn() => new $concrete()
        );
    }

    private function resolve(string $abstract) : mixed
    {
        return $this->dependencyInjector->get(id: $abstract);
    }

    public function boot() : void {}
}
