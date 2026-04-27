<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Arrays;

final readonly class ArrayReader
{
    public function has(array $data, string $key): bool
    {
        return array_key_exists($key, $data);
    }

    public function get(array $data, string $key, mixed $default = null): mixed
    {
        return $data[$key] ?? $default;
    }

    public function getNested(array $data, string $path, mixed $default = null): mixed
    {
        $keys = explode(separator: '.', string: $path);
        $current = $data;

        foreach ($keys as $key) {
            if (! is_array($current) || ! array_key_exists($key, $current)) {
                return $default;
            }

            $current = $current[$key];
        }

        return $current;
    }

    public function only(array $data, array $keys): array
    {
        return array_intersect_key($data, array_flip($keys));
    }

    public function except(array $data, array $keys): array
    {
        return array_diff_key($data, array_flip($keys));
    }
}