<?php

declare(strict_types=1);

namespace Avax\Cache\System;

final readonly class CachedValue
{
    public function __construct(
        public mixed $value,
        public int   $ttl,
        public int   $createdAt
    ) {}

    public static function fromMixed(mixed $value, int $ttl, ?int $createdAt = null) : self
    {
        return new self(
            value    : $value,
            ttl      : $ttl,
            createdAt: $createdAt ?? time()
        );
    }

    public function isExpired(int $currentTime) : bool
    {
        if ($this->ttl === 0) {
            return false;
        }

        return ($this->createdAt + $this->ttl) < $currentTime;
    }

    public function timeToLive(int $currentTime) : int
    {
        if ($this->ttl === 0) {
            return PHP_INT_MAX;
        }

        $expiresAt = $this->createdAt + $this->ttl;
        $ttl       = $expiresAt - $currentTime;

        return max(0, $ttl);
    }
}