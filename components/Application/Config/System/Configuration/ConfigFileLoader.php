<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Configuration;

use Avax\Components\Operations\Filesystem\System\PublicSurface\Filesystem;
use RuntimeException;

class ConfigFileLoader implements ConfigLoaderInterface
{
    public function loadConfigFile(string $filePath): array
    {
        $this->ensureFileExists(filePath: $filePath);

        $extension = $this->getFileExtension(filePath: $filePath);

        $config = match ($extension) {
            'php' => $this->loadPhpFile(filePath: $filePath),
            'json' => $this->loadJsonFile(filePath: $filePath),
            default => throw new RuntimeException(message: 'Unsupported configuration file format: '.$extension),
        };

        $this->ensureIsArray(config: $config, filePath: $filePath);

        return $config;
    }

    private function ensureFileExists(string $filePath): void
    {
        if (! file_exists(filename: $filePath)) {
            throw new RuntimeException(message: 'Configuration file not found: '.$filePath);
        }
    }

    private function getFileExtension(string $filePath): string
    {
        return pathinfo(path: $filePath, flags: PATHINFO_EXTENSION);
    }

    private function loadPhpFile(string $filePath): array
    {
        return require $filePath;
    }

    private function loadJsonFile(string $filePath): array
    {
        $content = Filesystem::read($filePath);
        $config  = json_decode(json: $content, associative: true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(message: 'Invalid JSON format in file: '.$filePath);
        }

        return $config;
    }

    private function ensureIsArray(mixed $config, string $filePath): void
    {
        if (! is_array(value: $config)) {
            throw new RuntimeException(message: 'Invalid configuration format in file: '.$filePath);
        }
    }
}
