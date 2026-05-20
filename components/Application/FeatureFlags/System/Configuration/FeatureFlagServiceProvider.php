<?php

declare(strict_types=1);

namespace Avax\Components\Application\FeatureFlags\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Application\FeatureFlags\System\Capabilities\Flags\InMemoryFlagStore;
use Avax\Components\Application\FeatureFlags\System\PublicSurface\FlagStoreInterface;

/**
 * FeatureFlagServiceProvider — registers Application/FeatureFlags component dependencies.
 *
 * Registers the in-memory flag store and binds the interface to the implementation.
 */
final class FeatureFlagServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // InMemoryFlagStore — default flag store with no external dependencies
        $container->singleton(InMemoryFlagStore::class, static fn () : InMemoryFlagStore => new InMemoryFlagStore());

        // FlagStoreInterface -> InMemoryFlagStore
        $container->singleton(FlagStoreInterface::class, static fn (ContainerInterface $c) : FlagStoreInterface => $c->get(InMemoryFlagStore::class));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
