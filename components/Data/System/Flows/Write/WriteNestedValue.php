<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Flows\Write;

/**
 * Flow to write a value into a nested array using dot-notation.
 */
final class WriteNestedValue
{
    public function execute(array &$data, string $key, mixed $value) : void
    {
        $keys    = explode('.', $key);
        $current = &$data;

        while ( count($keys) > 1 ) {
            $segment = array_shift($keys);
            if (! isset($current[$segment]) || ! is_array($current[$segment])) {
                $current[$segment] = [];
            }
            $current = &$current[$segment];
        }

        $current[array_shift($keys)] = $value;
    }
}
