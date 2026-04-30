<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\PublicSurface;

use Avax\Components\Application\Config\System\Capabilities\Repository\ConfigurationRepository;
use RuntimeException;

/**
 * Configuration Public Surface.
 *
 * Provides a thin proxy to the configuration repository.
 * Follows Screaming Architecture by delegating all state and logic to internal capabilities.
 */
final readonly class Config
{
    public function __construct(
        private ConfigurationRepository $configurationRepository,
    ) {}

    /**
     * Retrieve a configuration value by key.
     *
     * @throws RuntimeException if key does not exist and no default is provided.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (! $this->configurationRepository->has($key) && $default === null) {
            throw new RuntimeException(sprintf('Configuration key [%s] does not exist.', $key));
        }

        return $this->configurationRepository->get($key, $default);
    }

    /**
     * Check if a configuration key exists.
     */
    public function has(string $key): bool
    {
        return $this->configurationRepository->has($key);
    }

    /**
     * Retrieve all configuration items.
     */
    public function all() : array
    {
        return $this->configurationRepository->all();
    }

    /**
     * Set a configuration value at runtime.
     *
     * -- boundary: this is for runtime overrides only.
     */
    public function set(string $key, mixed $value) : void
    {
        $this->configurationRepository->set($key, $value);
    }
}
