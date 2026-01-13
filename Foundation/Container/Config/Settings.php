<?php

declare(strict_types=1);

namespace Avax\Container\Config;

/**
 * Lightweight configuration for container-scoped settings.
 *
 * This class provides a simple key-value store for configuration data with dot notation support, allowing container
 * components to access and modify settings in a structured way. It serves as the foundational configuration storage
 * mechanism, enabling hierarchical organization of settings without external dependencies.
 *
 * @see docs/Config/Settings.md#quick-summary
 */
final class Settings
{
    /**
     * @var array<string, mixed> In-memory configuration store.
     */
    private array $items;

    /**
     * Creates a new Settings instance with optional initial configuration.
     *
     * @param array<string, mixed> $items Initial configuration values.
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Retrieves a configuration value using dot notation.
     *
     * This method allows accessing nested configuration values with dot-separated keys, providing a default value if
     * the key doesn't exist. Enables hierarchical configuration access like "database.host" or "app.cache.ttl".
     *
     * @param string $key     Configuration key (e.g., "app.name").
     * @param mixed  $default Fallback value when the key is missing.
     *
     * @return mixed The resolved configuration value or the default.
     *
     * @see docs/Config/Settings.md#method-get
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        if ($key === '') {
            return $default;
        }

        $segments = explode('.', $key);
        $value    = $this->items;

        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Sets a configuration value using dot notation.
     *
     * This method allows storing configuration values with hierarchical keys, automatically creating nested structure
     * as needed. Supports deep nesting like setting "database.connections.primary.host" to create the full path
     * structure.
     *
     * @param string $key   Configuration key (e.g., "app.name").
     * @param mixed  $value Value to store.
     *
     * @see docs/Config/Settings.md#method-set
     */
    public function set(string $key, mixed $value) : void
    {
        if ($key === '') {
            return;
        }

        $segments = explode('.', $key);
        $target   = &$this->items;

        foreach ($segments as $segment) {
            if (! isset($target[$segment]) || ! is_array($target[$segment])) {
                $target[$segment] = [];
            }

            $target = &$target[$segment];
        }

        $target = $value;
    }

    /**
     * Checks if a configuration key exists using dot notation.
     *
     * This method verifies whether a configuration key is present without retrieving its value,
     * enabling conditional logic based on configuration availability.
     *
     * @param string $key Configuration key to check.
     *
     * @return bool True when the key exists.
     *
     * @see docs/Config/Settings.md#method-has
     */
    public function has(string $key) : bool
    {
        if ($key === '') {
            return false;
        }

        $segments = explode('.', $key);
        $value    = $this->items;

        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return false;
            }

            $value = $value[$segment];
        }

        return true;
    }

    /**
     * Returns the full configuration array.
     *
     * This method provides access to all configuration data as a single array structure,
     * useful for debugging, serialization, or bulk operations.
     *
     * @return array<string, mixed> All configuration entries.
     *
     * @see docs/Config/Settings.md#method-all
     */
    public function all() : array
    {
        return $this->items;
    }
}
