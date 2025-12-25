<?php

declare(strict_types=1);

namespace Avax\Database\Config;

use Avax\DataHandling\ArrayHandling\Arrhae;

/**
 * A pragmatic, nested configuration registry with dot-notation support.
 *
 * -- intent: manage database-specific configuration parameters with easy access.
 */
final class Config
{
    // Storage for configuration parameters
    private array $items = [];

    /**
     * Initialize the configuration registry with optional starting items.
     *
     * -- intent: hydrate the configuration storage.
     *
     * @param array $items Initial configuration data
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Retrieve a specific configuration value using an optional dot-notation key.
     *
     * -- intent: provide flexible, depth-aware access to configuration items.
     *
     * @param string $key     Property technical name
     * @param mixed  $default Fallback value if key is not located
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        return Arrhae::make(items: $this->items)->get(key: $key, default: $default);
    }

    /**
     * Assign a specific value to a configuration key.
     *
     * -- intent: allow runtime modification of the configuration Registry.
     *
     * @param string $key   Property identifier
     * @param mixed  $value Data to store
     *
     * @return void
     */
    public function set(string $key, mixed $value) : void
    {
        $this->items[$key] = $value;
    }

    /**
     * Retrieve the entire configuration registry as an array.
     *
     * -- intent: expose the raw configuration for mass-processing or debugging.
     *
     * @return array
     */
    public function all() : array
    {
        return $this->items;
    }
}
