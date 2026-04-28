<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Flows\Read;

/**
 * Flow to read a value from a nested array using dot-notation.
 * Recovered from legacy Arrhae/DataPath logic.
 */
final class ReadNestedValue
{
    public function execute(array $data, string $key, mixed $default = null) : mixed
    {
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (! is_array($data) || ! array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }

        return $data;
    }
}
