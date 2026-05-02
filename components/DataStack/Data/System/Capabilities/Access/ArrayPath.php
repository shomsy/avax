<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Access;

/**
 * ArrayPath - Logic for accessing array values using dot-notation paths.
 */
final readonly class ArrayPath
{
    public function get(array $items, string $path, mixed $default = null): mixed
    {
        $keys = explode('.', $path);
        foreach ($keys as $key) {
            if (! is_array($items) || ! array_key_exists($key, $items)) {
                return $default;
            }

            $items = $items[$key];
        }

        return $items;
    }

    public function set(array &$items, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (! isset($items[$key]) || ! is_array($items[$key])) {
                $items[$key] = [];
            }

            $items = &$items[$key];
        }

        $items[array_shift($keys)] = $value;
    }
}
