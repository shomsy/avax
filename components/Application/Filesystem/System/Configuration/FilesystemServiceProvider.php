<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Application\Filesystem\System\Capabilities\HealthCheck\CheckFilesystemHealth;
use Avax\Components\Application\Filesystem\System\Flows\ReadFile\ReadFile;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

/**
 * FilesystemServiceProvider — registers filesystem component dependencies.
 */
final class FilesystemServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Filesystem facade — stateless operations (no constructor params)
        $container->singleton(Filesystem::class, static fn () : Filesystem => new Filesystem());

        // ReadFile flow — stateless, no constructor params
        $container->singleton(ReadFile::class, static fn () : ReadFile => new ReadFile());

        // Health check — requires Filesystem instance
        $container->singleton(
            CheckFilesystemHealth::class,
            static fn (ContainerInterface $c) : CheckFilesystemHealth => new CheckFilesystemHealth(
                filesystem: $c->get(Filesystem::class),
            ),
        );
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
