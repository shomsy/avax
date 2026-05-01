<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\Aggregate;

/**
 * Flow to calculate the sum of values in a collection/array.
 * Supports property extraction via dot-notation.
 */
final class SumValues
{
    public function execute(iterable $items, string|callable|null $key = null): int|float
    {
        $sum = 0;
        foreach ($items as $item) {
            $value = $this->extract($item, $key);
            if (is_numeric($value)) {
                $sum += $value;
            }
        }

        return $sum;
    }

    private function extract(mixed $item, string|callable|null $key): mixed
    {
        if ($key === null) {
            return $item;
        }

        if (is_callable($key)) {
            return $key($item);
        }

        if (is_array($item)) {
            return $item[$key] ?? 0;
        }

        return $item->{$key} ?? 0;
    }
}
