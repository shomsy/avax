<?php

declare(strict_types=1);

namespace Avax\Container\Providers\Core;

use Avax\Container\Features\Operate\Boot\ServiceProvider;
use Avax\Filesystem\Storage\FileStorageInterface;
use Avax\Filesystem\Storage\Filesystem;
use Avax\Filesystem\Storage\LocalFileStorage;

/**
 * Service Provider for filesystem and storage services.
 *
 * @see docs_md/Providers/Core/FilesystemServiceProvider.md#quick-summary
 */
class FilesystemServiceProvider extends ServiceProvider
{
    /**
     * Register filesystem/storage bindings into the container.
     *
     * @return void
     * @see docs_md/Providers/Core/FilesystemServiceProvider.md#method-register
     */
    public function register() : void
    {
        // Bind the file storage interface to its local implementation
        $this->app->singleton(abstract: FileStorageInterface::class, concrete: LocalFileStorage::class);

        // Register the filesystem service
        $this->app->singleton(abstract: Filesystem::class, concrete: Filesystem::class);

        // Establish an alias 'Storage'
        $this->app->singleton(abstract: 'Storage', concrete: function () {
            return $this->app->get(Filesystem::class);
        });
    }
}
