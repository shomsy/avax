<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization;

/**
 * A single cache entry with TTL support.
 */
final readonly class CacheEntry
{
    public function __construct(
        public mixed $value,
        public float $createdAt,
        public float $ttl,
        public string $fingerprint = '',
        public string $key = '',
    ) {
    }

    /**
     * Checks if this cache entry has expired.
     */
    public function isExpired(float|null $now = null) : bool
    {
        $now ??= microtime(true);

        return ($now - $this->createdAt) >= $this->ttl;
    }

    /**
     * Returns the remaining time-to-live in seconds.
     */
    public function remainingTtl(float|null $now = null) : float
    {
        $now ??= microtime(true);
        $elapsed = $now - $this->createdAt;

        return max(0.0, $this->ttl - $elapsed);
    }

    /**
     * Returns the age of this entry in seconds.
     */
    public function age(float|null $now = null) : float
    {
        $now ??= microtime(true);

        return $now - $this->createdAt;
    }
}
