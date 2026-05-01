<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\SizeCachedValues;

final readonly class EstimateCachedValueSize
{
    public function estimate(mixed $value): int
    {
        if (is_string($value)) {
            return strlen($value);
        }

        if (is_int($value) || is_float($value)) {
            return 8;
        }

        if (is_bool($value)) {
            return 1;
        }

        if (is_array($value)) {
            return $this->estimateArray(value: $value);
        }

        if (is_object($value)) {
            return $this->estimateObject(value: $value);
        }

        return 0;
    }

    private function estimateArray(array $value): int
    {
        $size = 0;

        foreach ($value as $key => $val) {
            $size += strlen((string) $key);
            $size += $this->estimate(value: $val);
        }

        return $size;
    }

    private function estimateObject(object $value): int
    {
        return strlen(serialize($value));
    }
}
