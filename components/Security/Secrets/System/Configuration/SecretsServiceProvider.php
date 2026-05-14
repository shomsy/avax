<?php

declare(strict_types=1);

namespace Avax\Components\Security\Secrets\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Security\Secrets\System\Capabilities\Stores\InMemorySecretStore;
use Avax\Components\Security\Secrets\System\Capabilities\Stores\SecretStore;
use Avax\Components\Security\Secrets\System\Configuration\SecretsConfiguration;

/**
 * SecretsServiceProvider — registers secrets component dependencies.
 */
final class SecretsServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Secrets configuration
        $container->singleton(SecretsConfiguration::class, static fn () : SecretsConfiguration => new SecretsConfiguration());

        // Secret store — in-memory implementation
        $container->singleton(SecretStore::class, static fn () : SecretStore => new InMemorySecretStore());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed — Secrets uses static store internally
    }
}
