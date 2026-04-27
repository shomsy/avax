<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Convert;

/**
 * Expands dot notation keys into nested array.
 */
final readonly class ExpandDotKeys
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke() : array
    {
        return $this->expand();
    }

    public function expand() : array
    {
        $result = [];

        foreach ($this->items as $key => $value) {
            if (str_contains(haystack: $key, needle: '.')) {
                $this->setDotKey(items: $result, key: $key, value: $value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function setDotKey(array &$items, string $key, mixed $value) : void
    {
        $keys    = explode(separator: '.', string: $key);
        $current = &$items;

        while ( count(value: $keys) > 1 ) {
            $segment = array_shift(array: $keys);

            if (! isset($current[$segment]) || ! is_array(value: $current[$segment])) {
                $current[$segment] = [];
            }

            $current = &$current[$segment];
        }

        $current[array_shift(array: $keys)] = $value;
    }

    public function getItems() : array
    {
        return $this->items;
    }
}