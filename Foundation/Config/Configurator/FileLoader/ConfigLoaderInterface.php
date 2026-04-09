<?php

declare(strict_types=1);

namespace Avax\Config\Configurator\FileLoader;

/**
 * This interface defines the contract for loading configuration files.
 * Classes that implement this interface should provide a mechanism to
 * load and parse configuration data from a given file path.
 */
interface ConfigLoaderInterface
{
    /**
     * Load and parse the configuration from the specified file path.
     *
     * @param string $filePath The path to the configuration file.
     *
     * @return array Parsed configuration data as an associative array.
     *
     * The method signature implies that the implementation should:
     * - Handle potential file reading errors.
     * - Parse the file content appropriately (e.g., JSON, YAML).
     * - Return an empty array if the file is empty or parsing fails gracefully.
     */
    public function loadConfigFile(string $filePath) : array;
}
