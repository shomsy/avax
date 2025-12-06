<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\TTL;

/**
 * TTLContext
 *
 * Value object for TTL metadata.
 *
 * @package Avax\HTTP\Session\Features\TTL
 */
final readonly class TTLContext
{
    /**
     * TTLContext Constructor.
     *
     * @param int $createdAt  Creation timestamp.
     * @param int $expiresAt  Expiration timestamp.
     * @param int $touches    Number of touches.
     */
    public function __construct(
        public int $createdAt,
        public int $expiresAt,
        public int $touches = 0
    ) {}

    /**
     * Create from TTL seconds.
     *
     * @param int $seconds TTL in seconds.
     *
     * @return self
     */
    public static function fromTTL(int $seconds): self
    {
        $now = time();
        return new self(
            createdAt: $now,
            expiresAt: $now + $seconds,
            touches: 0
        );
    }

    /**
     * Check if expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return time() >= $this->expiresAt;
    }

    /**
     * Touch (extend TTL).
     *
     * @param int $seconds Additional seconds.
     *
     * @return self
     */
    public function touch(int $seconds): self
    {
        return new self(
            createdAt: $this->createdAt,
            expiresAt: time() + $seconds,
            touches: $this->touches + 1
        );
    }
}
