<?php

declare(strict_types=1);

namespace Avax\Components\Integration\ObjectStorage\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Integration\ObjectStorage\System\Capabilities\StoreObjects\StoreObjectsInMemory;

/**
 * ObjectStorageServiceProvider — registers object storage component dependencies.
 */
final class ObjectStorageServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Object storage configuration interface — no concrete implementation exists yet.
        // Configuration is assembled at runtime by the application.

        // Default in-memory object store
        $container->singleton(StoreObjectsInMemory::class, static fn () : StoreObjectsInMemory => new StoreObjectsInMemory());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
