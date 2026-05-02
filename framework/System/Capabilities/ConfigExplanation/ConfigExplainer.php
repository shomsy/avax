<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ConfigExplanation;

/**
 * Tracks and explains where config values come from.
 */
final class ConfigExplainer
{
    /**
     * @var array<string, ConfigSource>
     */
    private array $sources = [];

    /**
     * Record that a config value came from a file.
     */
    public function recordFileSource(string $key, mixed $value, string $filePath): void
    {
        $this->sources[$key] = new ConfigSource(
            key     : $key,
            source  : ConfigSource::SOURCE_FILE,
            value   : $value,
            filePath: $filePath,
        );
    }

    /**
     * Record that a config value came from an environment variable.
     */
    public function recordEnvSource(string $key, mixed $value, string $envVar): void
    {
        $this->sources[$key] = new ConfigSource(
            key   : $key,
            source: ConfigSource::SOURCE_ENV,
            value : $value,
            envVar: $envVar,
        );
    }

    /**
     * Record that a config value was set as a runtime override.
     */
    public function recordOverrideSource(string $key, mixed $value): void
    {
        $this->sources[$key] = new ConfigSource(
            key   : $key,
            source: ConfigSource::SOURCE_OVERRIDE,
            value : $value,
        );
    }

    /**
     * Get the source of a config value.
     */
    public function getSource(string $key): ?ConfigSource
    {
        return $this->sources[$key] ?? null;
    }

    /**
     * @return array<string, ConfigSource>
     */
    public function allSources(): array
    {
        return $this->sources;
    }
}
