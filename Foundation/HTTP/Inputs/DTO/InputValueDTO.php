<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\Inputs\DTO;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;

/**
 * InputValueDTO - Single input value with type information.
 *
 * Wraps a single input value with its key and provides
 * convenient accessors for different types.
 */
class InputValueDTO extends AbstractDTO
{
    public function __construct(
        public readonly string $key,
        public readonly mixed $value,
        public readonly string $source = 'auto'
    ) {
    }

    /**
     * @throws \ReflectionException
     */
    public static function fromQuery(string $key, mixed $value): self
    {
        return new self(key: $key, value: $value, source: 'query');
    }

    /**
     * @throws \ReflectionException
     */
    public static function fromBody(string $key, mixed $value): self
    {
        return new self(key: $key, value: $value, source: 'body');
    }

    public function isNull(): bool
    {
        return $this->value === null;
    }

    public function isEmpty(): bool
    {
        return $this->value === null || $this->value === '' || $this->value === [];
    }

    public function string(string $default = ''): string
    {
        if ($this->value === null) {
            return $default;
        }
        return is_scalar($this->value) ? (string) $this->value : $default;
    }

    public function int(int $default = 0): int
    {
        if ($this->value === null) {
            return $default;
        }
        return filter_var($this->value, FILTER_VALIDATE_INT) !== false
            ? (int) $this->value
            : $default;
    }

    public function float(float $default = 0.0): float
    {
        if ($this->value === null) {
            return $default;
        }
        return filter_var($this->value, FILTER_VALIDATE_FLOAT) !== false
            ? (float) $this->value
            : $default;
    }

    public function bool(bool $default = false): bool
    {
        if ($this->value === null) {
            return $default;
        }
        return filter_var($this->value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    public function array(array $default = []): array
    {
        if ($this->value === null) {
            return $default;
        }
        return is_array($this->value) ? $this->value : $default;
    }

    public function enum(string $enumClass, mixed $default = null): mixed
    {
        if ($this->value === null || !enum_exists($enumClass)) {
            return $default;
        }

        $enum = $enumClass::tryFrom($this->value);
        return $enum ?? $default;
    }
}
