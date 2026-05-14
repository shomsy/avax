<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration;

use Avax\Framework\System\Configuration\Foundation\ApplicationConfiguration;
use Avax\Framework\System\Configuration\Foundation\ConfigurationLoadFailed;
use Avax\Framework\System\Configuration\Foundation\InvalidConfiguration;

/**
 * LoadApplicationConfiguration — Loads and validates config/app.php.
 *
 * Falls back to defaults if the file does not exist.
 * Config is loaded once at boot and stays warm as immutable state.
 */
final readonly class LoadApplicationConfiguration
{
    public function __construct(
        private string $configPath = '',
    ) {
    }

    /**
     * @throws ConfigurationLoadFailed When configuration file fails to load
     * @throws InvalidConfiguration When configuration file does not return an array
     */
    public function load(): ApplicationConfiguration
    {
        $file = $this->resolveConfigFile();

        if ($file === null) {
            return new ApplicationConfiguration();
        }

        /** @var mixed $config */
        $config = @include $file;
        if ($config === false) {
            throw new ConfigurationLoadFailed("Failed to load configuration file: {$file}");
        }

        if (! is_array($config)) {
            throw new InvalidConfiguration("Configuration file must return an array: {$file}");
        }

        return ApplicationConfiguration::fromArray($config);
    }

    private function resolveConfigFile() : string|null
    {
        $path = $this->configPath !== '' ? $this->configPath : $this->defaultProjectPath();
        $file = $path . '/config/app.php';

        if (is_file($file)) {
            return $file;
        }

        return null;
    }

    private function defaultProjectPath(): string
    {
        return dirname(__DIR__, 4);
    }
}
