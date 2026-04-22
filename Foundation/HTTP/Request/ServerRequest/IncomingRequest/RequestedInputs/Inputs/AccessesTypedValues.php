<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs;

use BackedEnum;
use Stringable;

/**
 * AccessesTypedValues
 *
 * Shared typed read helpers for key-value input bags.
 *
 * Important semantic rule:
 * - key presence uses array_key_exists()
 * - null is treated as "present but null"
 *
 * That keeps request semantics honest.
 */
trait AccessesTypedValues
{
    public function hasNonNull(string $key) : bool
    {
        return $this->has(key: $key) && $this->get(key: $key) !== null;
    }

    public function has(string $key) : bool
    {
        return array_key_exists(key: $key, array: $this->data());
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function data() : array;

    public function get(string $key, mixed $default = null) : mixed
    {
        $data = $this->data();

        return array_key_exists(key: $key, array: $data)
            ? $data[$key]
            : $default;
    }

    /**
     * @param array<int, string> $keys
     *
     * @return array<string, mixed>
     */
    public function only(array $keys) : array
    {
        $result = [];
        $data   = $this->data();

        foreach ($keys as $key) {
            if (array_key_exists(key: $key, array: $data)) {
                $result[$key] = $data[$key];
            }
        }

        return $result;
    }

    /**
     * @param array<int, string> $keys
     *
     * @return array<string, mixed>
     */
    public function except(array $keys) : array
    {
        $result = $this->data();

        foreach ($keys as $key) {
            unset($result[$key]);
        }

        return $result;
    }

    public function string(string $key, string $default = '') : string
    {
        $value = $this->get(key: $key);

        return $this->toString(value: $value) ?? $default;
    }

    private function toString(mixed $value) : string|null
    {
        if (is_scalar(value: $value) || $value instanceof Stringable) {
            return (string) $value;
        }

        return null;
    }

    public function int(string $key, int $default = 0) : int
    {
        $value = $this->normalizeScalar(value: $this->get(key: $key));

        if ($value === null) {
            return $default;
        }

        $filtered = filter_var(value: $value, filter: FILTER_VALIDATE_INT);

        return $filtered !== false
            ? (int) $filtered
            : $default;
    }

    private function normalizeScalar(mixed $value) : string|int|float|bool|null
    {
        if (is_scalar(value: $value)) {
            return $value;
        }

        if ($value instanceof Stringable) {
            return (string) $value;
        }

        return null;
    }

    public function float(string $key, float $default = 0.0) : float
    {
        $value = $this->normalizeScalar(value: $this->get(key: $key));

        if ($value === null) {
            return $default;
        }

        $filtered = filter_var(value: $value, filter: FILTER_VALIDATE_FLOAT);

        return $filtered !== false
            ? (float) $filtered
            : $default;
    }

    public function bool(string $key, bool $default = false) : bool
    {
        $value = $this->normalizeScalar(value: $this->get(key: $key));

        if ($value === null) {
            return $default;
        }

        if (is_bool(value: $value)) {
            return $value;
        }

        $normalized = strtolower(string: (string) $value);
        if (in_array(needle: $normalized, haystack: ['true', '1', 'on', 'yes'], strict: true)) {
            return true;
        }
        if (in_array(needle: $normalized, haystack: ['false', '0', 'off', 'no'], strict: true)) {
            return false;
        }

        return $default;
    }

    /**
     * @param array<string, mixed> $default
     *
     * @return array<string, mixed>
     */
    public function array(string $key, array $default = []) : array
    {
        $value = $this->get(key: $key);

        return is_array(value: $value)
            ? $value
            : $default;
    }

    /**
     * @template T of BackedEnum
     *
     * @param class-string<T> $enumClass
     *
     * @return T|null
     */
    public function enum(string $key, string $enumClass, mixed $default = null) : BackedEnum|null
    {
        $value = $this->get(key: $key);

        if ($value === null || ! enum_exists(enum: $enumClass) || ! is_subclass_of(object_or_class: $enumClass, class: BackedEnum::class)) {
            return $default;
        }

        // tryFrom only accepts string or int
        if (! is_string(value: $value) && ! is_int(value: $value)) {
            return $default;
        }

        /** @var T|null $enum */
        $enum = $enumClass::tryFrom(value: $value);

        return $enum ?? $default;
    }
}
