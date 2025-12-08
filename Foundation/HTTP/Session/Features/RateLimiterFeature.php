<?php

declare(strict_types=1);

namespace Foundation\HTTP\Session\Features;

use Redis;
use DateTimeImmutable;

/**
 * RateLimiterFeature
 *
 * Implements distributed rate limiting using Redis (token bucket algorithm).
 * Fallback to in-memory array for development environments.
 *
 * @package Foundation\HTTP\Session\Features
 */
final class RateLimiterFeature
{
    private array $localCache = [];

    public function __construct(
        private readonly ?Redis $redis = null,
        private readonly int $limit = 100,
        private readonly int $windowSeconds = 60
    ) {}

    public function allow(string $key): bool
    {
        $now = time();

        if ($this->redis) {
            $bucket = sprintf('ratelimit:%s', $key);
            $count = $this->redis->incr($bucket);

            if ($count === 1) {
                $this->redis->expire($bucket, $this->windowSeconds);
            }

            return $count <= $this->limit;
        }

        // In-memory fallback
        $window = (int) floor($now / $this->windowSeconds);

        if (!isset($this->localCache[$key])) {
            $this->localCache[$key] = ['count' => 0, 'window' => $window];
        }

        if ($this->localCache[$key]['window'] !== $window) {
            $this->localCache[$key] = ['count' => 0, 'window' => $window];
        }

        $this->localCache[$key]['count']++;

        return $this->localCache[$key]['count'] <= $this->limit;
    }

    public function getResetTime(string $key): DateTimeImmutable
    {
        $now = time();
        $reset = $now + $this->windowSeconds;
        return new DateTimeImmutable("@{$reset}");
    }
}
