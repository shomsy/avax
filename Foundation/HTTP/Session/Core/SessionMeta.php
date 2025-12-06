<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core;

/**
 * SessionMeta Value Object
 *
 * Immutable metadata for session values.
 *
 * This VO encapsulates all metadata associated with a session value,
 * keeping it separate from the actual data and enabling clean queries.
 *
 * Enterprise Rules:
 * - Immutability: Once created, cannot be modified.
 * - Namespace: Uses `_meta.*` prefix to avoid pollution.
 * - Type Safety: Strict typing throughout.
 *
 * Usage:
 *   $meta = new SessionMeta(encrypted: true, expiresAt: time() + 3600);
 *   $metaKey = SessionMeta::keyFor('user_token');
 *
 * @package Avax\HTTP\Session\Core
 */
final readonly class SessionMeta
{
    /**
     * SessionMeta Constructor.
     *
     * @param bool     $encrypted Whether the value is encrypted.
     * @param int|null $expiresAt Unix timestamp when value expires.
     * @param array<string> $tags      Semantic tags for categorization.
     * @param array<string, mixed> $custom    Custom metadata.
     */
    public function __construct(
        public bool $encrypted = false,
        public int|null $expiresAt = null,
        public array $tags = [],
        public array $custom = []
    ) {}

    /**
     * Get the meta key for a given session key.
     *
     * @param string $key The session key.
     *
     * @return string The meta key.
     */
    public static function keyFor(string $key): string
    {
        return "_meta.{$key}";
    }

    /**
     * Check if value has expired.
     *
     * @return bool True if expired.
     */
    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        return time() >= $this->expiresAt;
    }

    /**
     * Convert to array for storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'encrypted' => $this->encrypted,
            'expires_at' => $this->expiresAt,
            'tags' => $this->tags,
            'custom' => $this->custom,
        ];
    }

    /**
     * Create from array.
     *
     * @param array<string, mixed> $data The array data.
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            encrypted: $data['encrypted'] ?? false,
            expiresAt: $data['expires_at'] ?? null,
            tags: $data['tags'] ?? [],
            custom: $data['custom'] ?? []
        );
    }
}
