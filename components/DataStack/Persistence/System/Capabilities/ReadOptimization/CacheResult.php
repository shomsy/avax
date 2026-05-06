<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization;

/**
 * Result of a cache operation.
 */
final readonly class CacheResult
{
    public function __construct(
        public bool $hit,
        public mixed $value = null,
        public string $key = '',
        public float $ttl = 0.0,
    ) {
    }

    /**
     * Creates a cache hit result.
     */
    public static function hit(mixed $value, string $key = '', float $ttl = 0.0): self
    {
        return new self(hit: true, value: $value, key: $key, ttl: $ttl);
    }

    /**
     * Creates a cache miss result.
     */
    public static function miss(string $key = ''): self
    {
        return new self(hit: false, key: $key);
    }
}
