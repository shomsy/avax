<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flows;

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Dependencies\Providers\ServiceProviderInterface;
use InvalidArgumentException;

/**
 * Deterministic provider lifecycle flow: register first, then boot.
 */
final readonly class BootProviders
{
    public function __construct(
        private ContainerInterface $container
    ) {}

    /** @param array<int, string|ServiceProviderInterface> $providers */
    public function boot(array $providers) : void
    {
        $instances = [];

        foreach ($providers as $provider) {
            if (is_string($provider) && ! class_exists($provider)) {
                throw new InvalidArgumentException(message: "Provider class [{$provider}] does not exist.");
            }

            $instance = is_string($provider) ? new $provider($this->container) : $provider;

            if (! $instance instanceof ServiceProviderInterface) {
                throw new InvalidArgumentException(message: 'Provider must implement ServiceProviderInterface.');
            }

            $instance->register();
            $instances[] = $instance;
        }

        foreach ($instances as $provider) {
            $provider->boot();
        }
    }
}
