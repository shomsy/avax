<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Arrays;

use InvalidArgumentException;

/**
 * Type-safe, immutable array reading with dot-notation path support.
 *
 * This is the recovered and strengthened version that adds typed readers
 * (getInt, getString, getBool, getFloat, getArray) on top of the basic
 * get/has/getNested from the original implementation.
 */
final readonly class ArrayReader
{
    public function only(array $data, array $keys): array
    {
        return array_intersect_key($data, array_flip($keys));
    }

    public function except(array $data, array $keys): array
    {
        return array_diff_key($data, array_flip($keys));
    }

    public function getString(array $data, string $key, ?string $default = null): ?string
    {
        $value = $this->get($data, $key, $default);

        if ($value === null) {
            return $default;
        }

        return (string) $value;
    }

    public function get(array $data, string $key, mixed $default = null): mixed
    {
        return $data[$key] ?? $default;
    }

    public function getStringOrFail(array $data, string $key): string
    {
        $value = $this->get($data, $key);

        if ($value === null || ! is_string($value)) {
            throw new InvalidArgumentException(
                message: sprintf("Key '%s' is required and must be a string.", $key),
            );
        }

        return $value;
    }

    public function getInt(array $data, string $key, ?int $default = null): ?int
    {
        $value = $this->get($data, $key, $default);

        if ($value === null) {
            return $default;
        }

        return (int) $value;
    }

    // ──────────────────────────────────────────────
    // Type-safe readers
    // ──────────────────────────────────────────────

    public function getIntOrFail(array $data, string $key): int
    {
        $value = $this->get($data, $key);

        if ($value === null || (! is_int($value) && ! is_numeric($value))) {
            throw new InvalidArgumentException(
                message: sprintf("Key '%s' is required and must be an integer.", $key),
            );
        }

        return (int) $value;
    }

    public function getFloat(array $data, string $key, ?float $default = null): ?float
    {
        $value = $this->get($data, $key, $default);

        if ($value === null) {
            return $default;
        }

        return (float) $value;
    }

    public function getBool(array $data, string $key, ?bool $default = null): ?bool
    {
        $value = $this->get($data, $key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    public function getArray(array $data, string $key, array $default = []): array
    {
        $value = $this->get($data, $key, $default);

        if (! is_array($value)) {
            return $default;
        }

        return $value;
    }

    /**
     * Return the value at $key, failing hard if it does not exist.
     */
    public function require(array $data, string $key): mixed
    {
        if (! $this->has($data, $key)) {
            throw new InvalidArgumentException(
                message: sprintf("Required key '%s' is missing from the data array.", $key),
            );
        }

        return $data[$key];
    }

    public function has(array $data, string $key): bool
    {
        return array_key_exists($key, $data);
    }

    /**
     * Return the nested value at $path, failing hard if it does not exist.
     */
    public function requireNested(array $data, string $path): mixed
    {
        if (! $this->hasNested($data, $path)) {
            throw new InvalidArgumentException(
                message: sprintf("Required nested path '%s' is missing from the data array.", $path),
            );
        }

        return $this->getNested($data, $path);
    }

    public function hasNested(array $data, string $path): bool
    {
        $keys = explode(separator: '.', string: $path);
        $current = $data;

        foreach ($keys as $key) {
            if (! is_array($current) || ! array_key_exists($key, $current)) {
                return false;
            }

            $current = $current[$key];
        }

        return true;
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

    /**
     * Flatten a multi-dimensional array into a single level using dot notation.
     */
    public function dot(array $data, string $prefix = ''): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $compositeKey = $prefix !== '' ? sprintf('%s.%s', $prefix, $key) : (string) $key;

            if (is_array($value) && $value !== []) {
                $result = array_merge($result, $this->dot($value, $compositeKey));
            } else {
                $result[$compositeKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Expand a dot-notation flat array back into a nested array.
     */
    public function undot(array $data): array
    {
        $result = [];
        $arrayWriter = new ArrayWriter();

        foreach ($data as $key => $value) {
            $arrayWriter->setNested($result, (string) $key, $value);
        }

        return $result;
    }
}
