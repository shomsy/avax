<?php

declare(strict_types=1);

namespace Avax\Container\Providers\Core;

use Avax\Container\Providers\ServiceProvider;
use Avax\Contracts\FilesystemInterface;
use Avax\Filesystem\Storage\FileStorageInterface;
use Avax\Filesystem\Storage\Filesystem;
use Avax\Filesystem\Storage\LocalFileStorage;

/**
 * Service Provider for filesystem and storage services.
 *
 * @see docs/Providers/Core/FilesystemServiceProvider.md#quick-summary
 */
class FilesystemServiceProvider extends ServiceProvider
{
    /**
     * Register filesystem/storage bindings into the container.
     *
     * @see docs/Providers/Core/FilesystemServiceProvider.md#method-register
     */
    public function register() : void
    {
        // Bind the file storage interface to its local implementation
        $this->app->singleton(abstract: FileStorageInterface::class, concrete: LocalFileStorage::class);

        // Register the filesystem service
        $this->app->singleton(abstract: Filesystem::class, concrete: Filesystem::class);

        // Bind the filesystem contract interface
        $this->app->singleton(abstract: FilesystemInterface::class, concrete: Filesystem::class);

        // Establish an alias 'Storage'
        $this->app->singleton(abstract: 'Storage', concrete: function () {
            return $this->app->get(id: Filesystem::class);
        });
    }
}