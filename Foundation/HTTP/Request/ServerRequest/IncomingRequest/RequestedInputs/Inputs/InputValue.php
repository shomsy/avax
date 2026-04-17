<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs;

use BackedEnum;
use Stringable;

/**
 * InputValue
 *
 * Single requested input value with source information.
 */
final readonly class InputValue
{
    public const string SOURCE_BODY  = 'body';
    public const string SOURCE_QUERY = 'query';
    public const string SOURCE_MISSING = 'missing';

    public function __construct(
        public string $key,
        public mixed  $value,
        public string $source,
    ) {}

    public static function fromQuery(string $key, mixed $value) : self
    {
        return new self(
            key   : $key,
            value : $value,
            source: self::SOURCE_QUERY,
        );
    }

    public static function fromBody(string $key, mixed $value) : self
    {
        return new self(
            key   : $key,
            value : $value,
            source: self::SOURCE_BODY,
        );
    }

    public static function missing(string $key, mixed $default = null) : self
    {
        return new self(
            key   : $key,
            value : $default,
            source: self::SOURCE_MISSING,
        );
    }

    public function exists() : bool
    {
        return $this->source !== self::SOURCE_MISSING;
    }

    public function isFromBody() : bool
    {
        return $this->source === self::SOURCE_BODY;
    }

    public function isFromQuery() : bool
    {
        return $this->source === self::SOURCE_QUERY;
    }

    public function isNull() : bool
    {
        return $this->value === null;
    }

    public function isEmpty() : bool
    {
        return $this->value === null
            || $this->value === ''
            || $this->value === [];
    }

    public function string(string $default = '') : string
    {
        if (is_scalar($this->value) || $this->value instanceof Stringable) {
            return (string) $this->value;
        }

        return $default;
    }

    public function int(int $default = 0) : int
    {
        $value = $this->normalizeScalar(value: $this->value);

        if ($value === null) {
            return $default;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_INT);

        return $filtered !== false
            ? (int) $filtered
            : $default;
    }

    /**
     * @return string|int|float|bool|null
     */
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

    public function float(float $default = 0.0) : float
    {
        $value = $this->normalizeScalar(value: $this->value);

        if ($value === null) {
            return $default;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_FLOAT);

        return $filtered !== false
            ? (float) $filtered
            : $default;
    }

    public function bool(bool $default = false) : bool
    {
        $value = $this->normalizeScalar(value: $this->value);

        if ($value === null) {
            return $default;
        }

        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE,
        ) ?? $default;
    }

    /**
     * @param array<string, mixed> $default
     *
     * @return array<string, mixed>
     */
    public function array(array $default = []) : array
    {
        return is_array($this->value)
            ? $this->value
            : $default;
    }

    /**
     * @template T of BackedEnum
     * @param class-string<T> $enumClass
     *
     * @return T|null
     */
    public function enum(string $enumClass, mixed $default = null) : BackedEnum|null
    {
        if ($this->value === null || ! enum_exists($enumClass) || ! is_subclass_of($enumClass, BackedEnum::class)) {
            return $default;
        }

        /** @var T|null $enum */
        $enum = $enumClass::tryFrom(value: $this->value);

        return $enum ?? $default;
    }
}
