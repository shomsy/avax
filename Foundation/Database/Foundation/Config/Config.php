<?php

declare(strict_types=1);

namespace Avax\Database\Config;

/**
 * In-memory configuration repository supporting dot-notation access.
 *
 * @see docs/Concepts/Architecture.md
 */
final class Config
{
    /** @var array<string, mixed> The actual list of settings stored in memory. */
    private array $items = [];

    /**
     * Start the Settings Book with an initial list of items.
     *
     * @param array<string, mixed> $items The starting dictionary of settings.
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Retrieve a setting by key with optional dot-notation.
     *
     * @param string $key     Setting key or path.
     * @param mixed  $default Default value if not found.
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists(key: $key, array: $this->items)) {
            return $this->items[$key];
        }

        if (! str_contains(haystack: $key, needle: '.')) {
            return $default;
        }

        $array = $this->items;
        foreach (explode(separator: '.', string: $key) as $segment) {
            if (is_array(value: $array) && array_key_exists(key: $segment, array: $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Add or change a setting during runtime.
     *
     * @param string $key   The name of the setting.
     * @param mixed  $value The new information to store.
     */
    public function set(string $key, mixed $value): void
    {
        $this->items[$key] = $value;
    }

    /**
     * Get the entire dictionary of all settings at once.
     *
     * @return array<string, mixed> The raw list of everything inside.
     */
    public function all(): array
    {
        return $this->items;
    }
}
