<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core;

/**
 * SessionData DTO
 *
 * Data Transfer Object for session data.
 *
 * This DTO encapsulates session data for transfer between layers,
 * providing type-safe serialization and deserialization.
 *
 * Enterprise Rules:
 * - Immutability: Once created, data cannot be modified.
 * - Type Safety: Enforces array structure.
 * - Serialization: Provides conversion methods.
 *
 * Usage:
 *   $data = SessionData::from(['user_id' => 123, 'name' => 'John']);
 *   $array = $data->toArray();
 *
 * @package Avax\HTTP\Session\Core
 */
final readonly class SessionData
{
    /**
     * SessionData Constructor.
     *
     * @param array<string, mixed> $data The session data.
     */
    public function __construct(
        private array $data
    ) {}

    /**
     * Create from array.
     *
     * @param array<string, mixed> $data The session data.
     *
     * @return self
     */
    public static function from(array $data): self
    {
        return new self(data: $data);
    }

    /**
     * Create empty session data.
     *
     * @return self
     */
    public static function empty(): self
    {
        return new self(data: []);
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Get a value by key.
     *
     * @param string $key     The key.
     * @param mixed  $default The default value.
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if key exists.
     *
     * @param string $key The key.
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Merge with another SessionData.
     *
     * @param SessionData $other The other session data.
     *
     * @return self New instance with merged data.
     */
    public function merge(SessionData $other): self
    {
        return new self(
            data: array_merge($this->data, $other->data)
        );
    }

    /**
     * Filter data by keys.
     *
     * @param array<string> $keys The keys to include.
     *
     * @return self New instance with filtered data.
     */
    public function only(array $keys): self
    {
        return new self(
            data: array_intersect_key(
                $this->data,
                array_flip($keys)
            )
        );
    }

    /**
     * Exclude keys from data.
     *
     * @param array<string> $keys The keys to exclude.
     *
     * @return self New instance without excluded keys.
     */
    public function except(array $keys): self
    {
        return new self(
            data: array_diff_key(
                $this->data,
                array_flip($keys)
            )
        );
    }

    /**
     * Check if data is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Get all keys.
     *
     * @return array<string>
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    /**
     * Get count of items.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }
}
