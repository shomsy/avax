<?php

declare(strict_types=1);

namespace components\DataFoundation\Collections\Convert;

/**
 * Flattens nested array into dot notation keys.
 */
final readonly class FlattenIntoDotKeys
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke() : array
    {
        return $this->flatten();
    }

    public function flatten() : array
    {
        $result = [];

        foreach ($this->items as $key => $value) {
            if (is_array(value: $value)) {
                $result = $this->flattenRecursive(result: $result, prefix: $key, items: $value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function flattenRecursive(array $result, string $prefix, array $items, string $separator = '.') : array
    {
        foreach ($items as $key => $value) {
            $newKey = $prefix . $separator . $key;

            if (is_array(value: $value)) {
                $result = $this->flattenRecursive(result: $result, prefix: $newKey, items: $value, separator: $separator);
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    public function getItems() : array
    {
        return $this->items;
    }
}