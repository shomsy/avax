<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\Inputs\DTO;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;

/**
 * InputsDTO - Unified request inputs DTO.
 *
 * Combines query parameters and parsed body into a single strongly-typed DTO.
 * Provides priority-based access (body takes precedence over query).
 *
 * Usage:
 * ```php
 * // Define a typed DTO
 * class UserCreateDTO extends AbstractDTO {
 *     public string $email;
 *     public string $name;
 *     public int $age = 18;
 * }
 *
 * // Use with InputsDTO
 * $inputs = InputsDTO::fromSlices(
 *     queryParams: ['page' => '1'],
 *     parsedBody: ['email' => 'test@example.com', 'name' => 'John']
 * );
 *
 * // Get as typed DTO
 * $user = $inputs->as(UserCreateDTO::class);
 * ```
 */
class InputsDTO extends AbstractDTO
{
    /** @var array<string, mixed> */
    public array $query = [];

    /** @var array<string, mixed> */
    public array $body = [];

    public static function fromSlices(array $queryParams, array|object|null $parsedBody): self
    {
        $body = self::normalizeBody($parsedBody);

        return new self(data: [
            'query' => $queryParams,
            'body' => $body,
        ]);
    }

    public static function fromArray(array $data): self
    {
        return new self(data: $data);
    }

    private static function normalizeBody(array|object|null $parsedBody): array
    {
        if ($parsedBody === null) {
            return [];
        }

        if (is_object($parsedBody) && !$parsedBody instanceof self) {
            return (array) $parsedBody;
        }

        return is_array($parsedBody) ? $parsedBody : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    /**
     * Get merged inputs (body takes precedence).
     *
     * @return array<string, mixed>
     */
    public function merged(): array
    {
        return $this->all();
    }

    /**
     * Get input value with priority: body > query.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->body[$key])) {
            return $this->body[$key];
        }

        return $this->query[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->body[$key]) || isset($this->query[$key]);
    }

    public function hasInBody(string $key): bool
    {
        return isset($this->body[$key]);
    }

    public function hasInQuery(string $key): bool
    {
        return isset($this->query[$key]);
    }

    /**
     * Get input value only from body.
     */
    public function fromBody(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    /**
     * Get input value only from query.
     */
    public function fromQuery(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Map inputs to a typed DTO class.
     *
     * @template T of AbstractDTO
     * @param class-string<T> $dtoClass
     * @return T
     */
    public function as(string $dtoClass): AbstractDTO
    {
        return new $dtoClass($this->all());
    }

    /**
     * Get only specified keys from merged inputs.
     *
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public function only(array $keys): array
    {
        $result = [];
        $merged = $this->all();

        foreach ($keys as $key) {
            if (isset($merged[$key])) {
                $result[$key] = $merged[$key];
            }
        }

        return $result;
    }

    /**
     * Get all keys except specified from merged inputs.
     *
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public function except(array $keys): array
    {
        $result = [];
        $merged = $this->all();

        foreach ($merged as $key => $value) {
            if (!in_array($key, $keys, true)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Get input as typed value.
     */
    public function string(string $key, string $default = ''): string
    {
        $value = $this->get($key);
        return is_scalar($value) ? (string) $value : $default;
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->get($key);
        return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int) $value : $default;
    }

    public function float(string $key, float $default = 0.0): float
    {
        $value = $this->get($key);
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false ? (float) $value : $default;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    /**
     * @param array<string, mixed> $default
     * @return array<string, mixed>
     */
    public function array(string $key, array $default = []): array
    {
        $value = $this->get($key);
        return is_array($value) ? $value : $default;
    }

    /**
     * Get input as enum value.
     *
     * @template T of \BackedEnum
     * @param string $key
     * @param class-string<T> $enumClass
     * @param T|null $default
     * @return T|null
     */
    public function enum(string $key, string $enumClass, mixed $default = null): ?\BackedEnum
    {
        $value = $this->get($key);

        if ($value === null) {
            return $default;
        }

        return $enumClass::tryFrom($value) ?? $default;
    }
}
