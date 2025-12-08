<?php

declare(strict_types=1);

namespace Avax\Config\Configurator;

use Avax\DataHandling\ObjectHandling\Collections\Collection;
use InvalidArgumentException;

/**
 * ConfiguratorInterface provides a contract for configuration management.
 *
 * This interface mandates methods for managing and retrieving configuration data.
 * It decouples configuration logic, enabling different components to interact with configurations
 * consistently, regardless of the specific implementation details.
 */
interface ConfiguratorInterface
{
    /**
     * Retrieves the paths to configuration files.
     *
     * By using a unified method for accessing configuration paths, the system can dynamically
     * load and manage configuration data based on varying contexts or environments.
     *
     * @return array<string, string> Associative array where the key is the configuration namespace
     *                               and the value is the path to the configuration file.
     */
    public function configurationFilePaths() : array;

    /**
     * Retrieves a configuration value by its key with an optional default.
     *
     * This method standardizes the retrieval of configuration values, reducing dependency on
     * hard-coded configuration keys and enhancing code consistency. If the key does not exist,
     * a default value can be returned.
     *
     * @param string $key     The configuration key to retrieve.
     * @param mixed  $default A default value to return if the key does not exist.
     *                        Use meaningful defaults relevant to the configuration context.
     *
     * @return mixed The configuration value, or the default if the key is missing.
     * @throws InvalidArgumentException if the configuration key does not exist and no default is provided.
     */
    public function get(string $key, mixed $default = null) : mixed;

    /**
     * Determines if a configuration key exists.
     *
     * Enables conditional logic based on the presence of specific configuration settings,
     * improving code robustness by ensuring configuration values are available when needed.
     *
     * @param string $key The configuration key to check.
     *
     * @return bool True if the key exists in the configuration, false otherwise.
     */
    public function has(string $key) : bool;

    /**
     * Returns all configuration settings as a collection.
     *
     * This provides a way to access all configurations for operations that
     * require bulk processing.
     *
     * @return Collection The complete set of configuration data.
     */
    public function all() : Collection;

    /**
     * Refreshes the configuration data by reloading from files and updating the cache.
     *
     * This method reloads the configuration from the original source files,
     * ensuring that the configuration data is current without reconstructing the object.
     *
     * @return Collection The refreshed configuration data.
     */
    public function refresh() : Collection;
}
