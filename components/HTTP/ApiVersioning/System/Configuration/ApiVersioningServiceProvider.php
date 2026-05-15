<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\ApiVersioning\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\HTTP\ApiVersioning\System\Capabilities\Lifecycle\VersionRegistry;
use Avax\Components\HTTP\ApiVersioning\System\PublicSurface\ApiVersion;

/**
 * ApiVersioningServiceProvider — registers and boots the API versioning component.
 *
 * Registers VersionRegistry as a singleton and wires it into the ApiVersion facade during boot.
 */
final class ApiVersioningServiceProvider implements ServiceProvider
{
    /**
     * Register API versioning services in the container.
     *
     * Registers VersionRegistry as a singleton configured from application config.
     * Uses currentVersion and supportedVersions from config, with sensible defaults.
     */
    public function register(ContainerInterface $container) : void
    {
        $container->singleton(
            VersionRegistry::class,
            static fn () : VersionRegistry => new VersionRegistry(
                currentVersion   : $container->has('config') ? ($container->get('config')['api.versioning.current'] ?? 1) : 1,
                supportedVersions: $container->has('config') ? ($container->get('config')['api.versioning.supported'] ?? [1]) : [1],
            ),
        );
    }

    /**
     * Boot the API versioning component by wiring the facade.
     *
     * Resolves the registered VersionRegistry singleton and injects it
     * into the ApiVersion static facade via setInstance().
     * This is the single source of truth for API version resolution.
     */
    public function boot(ContainerInterface $container) : void
    {
        ApiVersion::setInstance($container->make(VersionRegistry::class));
    }
}
