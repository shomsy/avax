<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Container\ServiceProviders\ServiceProvider;
use Avax\Database\Migration\Runner\Manifest\ManifestDB;
use Avax\Database\Migration\Runner\Manifest\ManifestDBInterface;
use Avax\Database\Migration\Runner\Manifest\ManifestStore;
use Avax\Database\Migration\Runner\Manifest\ManifestStoreInterface;
use Avax\Filesystem\Storage\FileStorageInterface;
use Avax\Filesystem\Storage\Filesystem;
use Avax\Filesystem\Storage\LocalFileStorage;

/**
 * Service Provider responsible for filesystem-related dependency registrations.
 *
 * This provider manages the registration of filesystem services and their dependencies,
 * including local storage implementations, filesystem abstractions, and migration manifest
 * storage components. It follows the Repository pattern for data access abstraction and
 * supports the Domain-Driven Design principles through clear boundary definitions.
 *
 * @package Avax\Container\ServiceProviders\Providers
 * @final   This class is not intended for inheritance
 */
final class FilesystemServiceProvider extends ServiceProvider
{
    /**
     * Registers filesystem-related services in the dependency injection container.
     *
     * This method implements the registration of core filesystem services following
     * the Interface Segregation Principle (ISP) and Dependency Inversion Principle (DIP).
     * It establishes the necessary bindings for:
     * - File storage implementations
     * - Filesystem abstraction layer
     * - Migration manifest storage system
     *
     * @return void
     *
     */
    #[\Override]
    public function register() : void
    {
        // Bind the file storage interface to its local implementation
        // This provides the foundational storage capabilities
        $this->dependencyInjector->singleton(
            abstract: FileStorageInterface::class,
            concrete: LocalFileStorage::class
        );

        // Register the filesystem service with its storage dependency
        // Creates a new Filesystem instance with proper storage injection
        $this->dependencyInjector->singleton(
            abstract: Filesystem::class,
            concrete: fn() : Filesystem => new Filesystem(
                fileStorage: $this->dependencyInjector->get(id: FileStorageInterface::class)
            )
        );

        // Establish an alias 'Storage' for easier access to the Filesystem service
        // This maintains backward compatibility and provides a convenient access point
        $this->dependencyInjector->singleton(
            abstract: 'Storage',
            concrete: fn() : mixed => $this->dependencyInjector->get(
                id: Filesystem::class
            )
        );

        // Register-manifest-related services for migration tracking
        $this->registerManifestServices();
    }

    /**
     * Registers manifest-related services for migration management.
     *
     * Creates and configures the manifest storage system used for tracking
     * database migrations. This includes both the low-level storage implementation
     * and the higher-level manifest management interface.
     *
     * @return void
     *
     */
    private function registerManifestServices() : void
    {
        // Bind the manifest database interface to its concrete implementation
        // Configures the storage location for migration manifests
        $this->dependencyInjector->singleton(
            abstract: ManifestDBInterface::class,
            concrete: static fn() : ManifestDB => new ManifestDB(
                storagePath: storage_path(path: 'manifest')
            )
        );

        // Register the manifest store service with its database dependency
        // Provides the high-level interface for manifest operations
        $this->dependencyInjector->singleton(
            abstract: ManifestStoreInterface::class,
            concrete: fn() : ManifestStore => new ManifestStore(
                db: $this->dependencyInjector->get(id: ManifestDBInterface::class)
            )
        );
    }

    /**
     * Performs any post-registration boot operations.
     *
     * Currently, this method has no implementation as no boot-time operations
     * are required for filesystem services. Reserved for future use.
     *
     * @return void
     */
    #[\Override]
    public function boot() : void
    {
        // No boot operations required for filesystem services
    }
}