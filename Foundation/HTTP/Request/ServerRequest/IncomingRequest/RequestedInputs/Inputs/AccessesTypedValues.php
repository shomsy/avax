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
        return array_key_exists($key, $this->data());
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function data() : array;

    public function get(string $key, mixed $default = null) : mixed
    {
        $data = $this->data();

        return array_key_exists($key, $data)
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
            if (array_key_exists($key, $data)) {
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

    private function toString(mixed $value) : ?string
    {
        if (is_scalar($value) || $value instanceof Stringable) {
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

        $filtered = filter_var($value, FILTER_VALIDATE_INT);

        return $filtered !== false
            ? (int) $filtered
            : $default;
    }

    private function normalizeScalar(mixed $value) : string|int|float|bool|null
    {
        if (is_scalar($value)) {
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

        $filtered = filter_var($value, FILTER_VALIDATE_FLOAT);

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

        $lower = strtolower($value);
        if (in_array($lower, ['true', '1', 'on'], true)) {
            return true;
        }
        if (in_array($lower, ['false', '0', 'off'], true)) {
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

        return is_array($value)
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
    public function enum(string $key, string $enumClass, mixed $default = null) : ?BackedEnum
    {
        $value = $this->get(key: $key);

        if ($value === null) {
            return $default;
        }

        if (! enum_exists($enumClass) || ! is_subclass_of($enumClass, BackedEnum::class)) {
            return $default;
        }

        /** @var T|null $enum */
        $enum = $enumClass::tryFrom(value: $value);

        return $enum ?? $default;
    }
}
