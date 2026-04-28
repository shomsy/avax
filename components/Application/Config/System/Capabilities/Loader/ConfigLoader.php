<?php

declare(strict_types=1);

namespace Avax\Components\Config\System\Capabilities\Loader;

use RuntimeException;

/**
 * Loads configuration files from various formats.
 */
final class ConfigLoader
{
    public function load(string $path) : array
    {
        if (! file_exists($path)) {
            throw new RuntimeException("Configuration file [{$path}] not found.");
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return match ($extension) {
            'php'   => $this->loadPhp($path),
            'json'  => $this->loadJson($path),
            default => throw new RuntimeException("Unsupported configuration format: [{$extension}]")
        };
    }

    private function loadPhp(string $path) : array
    {
        $config = require $path;

        if (! is_array($config)) {
            throw new RuntimeException("PHP configuration file [{$path}] must return an array.");
        }

        return $config;
    }

    private function loadJson(string $path) : array
    {
        $content = file_get_contents($path);
        $config  = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Failed to parse JSON configuration file [{$path}]: " . json_last_error_msg());
        }

        return $config;
    }
}
