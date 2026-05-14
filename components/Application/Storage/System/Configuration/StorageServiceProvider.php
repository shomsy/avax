<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Application\Storage\System\Capabilities\Disks\RegisteredDisks;
use Avax\Components\Application\Storage\System\PublicSurface\Storage;

/**
 * StorageServiceProvider — registers storage component dependencies.
 */
final class StorageServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Registered disks — mutable disk registry
        $container->singleton(RegisteredDisks::class, static fn () : RegisteredDisks => new RegisteredDisks());
    }

    public function boot(ContainerInterface $container) : void
    {
        // Wire static state for backward compatibility
        Storage::reset();
    }
}
