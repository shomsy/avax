<?php

declare(strict_types=1);

namespace Gemini\Config\Configurator\FileLoader;

use RuntimeException;

/**
 * Implementation of ConfigLoaderInterface for loading configuration files.
 *
 * This class supports loading configurations from PHP and JSON files.
 * It throws exceptions for unsupported file formats and non-existent files.
 */
class ConfigFileLoader implements ConfigLoaderInterface
{
    /**
     * Load and parse the configuration from the specified file path.
     *
     * @param string $filePath The path to the configuration file.
     *
     * @return array Parsed configuration data as an associative array.
     *
     * The method determines the file extension to decide which loader method to use.
     * Throws exceptions for unsupported file formats and invalid configurations.
     */
    public function loadConfigFile(string $filePath) : array
    {
        // Ensure the file exists before attempting to load it.
        $this->ensureFileExists(filePath: $filePath);

        // Determine the file extension to identify the appropriate loading method.
        $extension = $this->getFileExtension(filePath: $filePath);

        // Use the appropriate method to load the file based on its extension.
        $config = match ($extension) {
            'php'   => $this->loadPhpFile(filePath: $filePath),
            'json'  => $this->loadJsonFile(filePath: $filePath),
            default => throw new RuntimeException(message: 'Unsupported configuration file format: ' . $extension),
        };

        // Ensure the loaded content is an array.
        $this->ensureIsArray(config: $config, filePath: $filePath);

        return $config;
    }

    /**
     * Ensure the given file path exists.
     *
     * @param string $filePath The path to the configuration file.
     *
     * @throws RuntimeException if the file does not exist.
     */
    private function ensureFileExists(string $filePath) : void
    {
        if (! file_exists($filePath)) {
            throw new RuntimeException(message: 'Configuration file not found: ' . $filePath);
        }
    }

    /**
     * Get the file extension of the provided file path.
     *
     * @param string $filePath The path to the configuration file.
     *
     * @return string The file extension.
     */
    private function getFileExtension(string $filePath) : string
    {
        return pathinfo($filePath, PATHINFO_EXTENSION);
    }

    /**
     * Load a configuration from a PHP file.
     *
     * @param string $filePath The path to the PHP configuration file.
     *
     * @return array The configuration as an associative array.
     *
     * Assumes the PHP file returns an array.
     */
    private function loadPhpFile(string $filePath) : array
    {
        return require $filePath;
    }

    /**
     * Load a configuration from a JSON file.
     *
     * @param string $filePath The path to the JSON configuration file.
     *
     * @return array The configuration as an associative array.
     * @throws RuntimeException if the JSON is invalid.
     */
    private function loadJsonFile(string $filePath) : array
    {
        $config = json_decode(file_get_contents($filePath), true);

        // Check for and handle JSON decoding errors.
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(message: 'Invalid JSON format in file: ' . $filePath);
        }

        return $config;
    }

    /**
     * Ensure the given configuration is an array.
     *
     * @param mixed  $config   The loaded configuration data.
     * @param string $filePath The path to the configuration file.
     *
     * @throws RuntimeException if the configuration is not an array.
     */
    private function ensureIsArray(mixed $config, string $filePath) : void
    {
        if (! is_array($config)) {
            throw new RuntimeException(message: 'Invalid configuration format in file: ' . $filePath);
        }
    }
}
