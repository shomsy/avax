<?php

declare(strict_types=1);

namespace Avax\Components\Container\System\Capabilities\Providers;

use Avax\Components\Container\System\PublicSurface\ContainerInterface;

/**
 * Manages the registration and booting of service providers.
 */
final class ProviderRegistry
{
    /** @var ServiceProvider[] */
    private array $providers = [];

    private bool $booted = false;

    public function __construct(
        private readonly ContainerInterface $container
    ) {}

    public function register(string $providerClass) : void
    {
        $provider = new $providerClass($this->container);
        $provider->register();
        $this->providers[] = $provider;

        if ($this->booted) {
            $provider->boot();
        }
    }

    public function boot() : void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->providers as $provider) {
            $provider->boot();
        }

        $this->booted = true;
    }
}
