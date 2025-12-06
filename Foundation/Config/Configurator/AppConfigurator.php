<?php

declare(strict_types=1);

namespace Avax\Config\Configurator;

use Avax\Config\Configurator\FileLoader\ConfigLoaderInterface;
use Avax\DataHandling\ObjectHandling\Collections\Collection;
use RuntimeException;
use WeakMap;

/**
 * Abstract base class for managing application configuration.
 *
 * This class provides a foundational structure for configuration management,
 * using a `WeakMap` for in-memory caching, which allows for lightweight and
 * automatic cleanup of configuration data when no longer needed.
 */
abstract class AppConfigurator implements ConfiguratorInterface
{
    // WeakMap used to cache configuration data by instance of the configurator
    private static WeakMap $weakMap;

    // Holds the loaded configuration data as a Collection instance
    protected Collection $configuration;

    /**
     * Constructor to initialize the configurator with a config loader.
     *
     * @param ConfigLoaderInterface $configLoader An instance responsible for loading config files.
     */
    public function __construct(
        protected ConfigLoaderInterface $configLoader,
    ) {
        // Initialize WeakMap if not already set
        self::$weakMap ??= new WeakMap();
        // Load configuration, either from cache or fresh data
        $this->initializeConfiguration();
    }

    /**
     * Initializes configuration data.
     *
     * This method first checks if configuration data is cached for the current instance
     * in `WeakMap`. If available, it uses the cached data; otherwise, it loads a fresh
     * configuration and caches it.
     */
    private function initializeConfiguration() : void
    {
        // Check if configuration is already cached for this instance
        $this->configuration = self::$weakMap[$this] ?? $this->loadFreshConfigAndCache();
    }

    /**
     * Loads fresh configuration data from source files and caches it.
     *
     * This method loads configuration data from source files using the config loader,
     * and then caches the result in the `WeakMap` for this instance.
     *
     * @return Collection The newly loaded configuration data.
     */
    private function loadFreshConfigAndCache() : Collection
    {
        $collection = $this->loadConfigurationFiles();
        // Cache the configuration for the current instance in WeakMap
        self::$weakMap[$this] = $collection;

        return $collection;
    }

    /**
     * Loads configuration data from specified files through the config loader.
     *
     * This method iterates through each defined configuration path, loading the configuration
     * file and storing it under the appropriate namespace. The configuration data is returned
     * as a `Collection` instance for further processing.
     *
     * @return Collection The loaded configuration data.
     */
    protected function loadConfigurationFiles() : Collection
    {
        $configData = [];
        foreach ($this->getConfigurationPaths() as $namespace => $filePath) {
            // Load the configuration file and store it under the associated namespace
            $configData[$namespace] = $this->configLoader->loadConfigFile(filePath: $filePath);
        }

        return new Collection(items: $configData);
    }

    /**
     * Define paths to configuration files.
     *
     * Subclasses should implement this method to provide an associative array
     * mapping configuration namespaces to their respective file paths.
     *
     * @return array<string, string> Associative array of configuration namespaces and file paths.
     */
    abstract protected function getConfigurationPaths() : array;

    /**
     * Retrieve a configuration value by dot-notated key.
     *
     * This method supports dot notation for nested keys, allowing access to deeply nested
     * configuration values (e.g., "database.mysql.dsn"). It uses `data_get` to efficiently
     * resolve nested paths.
     *
     * @param string $key     The configuration key, supporting dot notation for nested access.
     * @param mixed  $default Default value if the key does not exist.
     *
     * @return mixed The configuration value or the default value if not found.
     * @throws RuntimeException if the configuration key does not exist and no default is provided.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        // Access the base data from the collection
        $items = $this->configuration->getItems();

        // Use data_get for dot-notated configuration access
        $value = data_get(target: $items, key: $key, default: $default);

        if ($value === $default && $default === null) {
            throw new RuntimeException(message: "Configuration key [" . $key . "] does not exist.");
        }

        return $value;
    }

    /**
     * Check if a specific configuration key exists.
     *
     * @param string $key The configuration key, potentially in dot notation.
     *
     * @return bool Returns true if the key exists, false otherwise.
     */
    public function has(string $key) : bool
    {
        return $this->configuration->contains($key);
    }

    /**
     * Retrieve all configuration data.
     *
     * @return Collection The entire configuration data as a Collection.
     */
    public function all() : Collection
    {
        return $this->configuration;
    }

    /**
     * Refresh the configuration data by reloading it from the source files.
     *
     * This method discards any cached configuration data for the current instance
     * and loads fresh data from the configuration files.
     *
     * @return Collection The newly loaded configuration data.
     */
    public function refresh() : Collection
    {
        return $this->configuration = $this->loadFreshConfigAndCache();
    }
}
