<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Capacity\Cache;

/**
 * Cache hit ratio target.
 *
 * @experimental V3 labs
 */
final readonly class CacheHitRatio
{
    public function __construct(
        public float $target,
    ) {}

    /**
     * Cache miss ratio (1 - hit_ratio).
     */
    public function missRatio() : float
    {
        return 1.0 - $this->target;
    }

    /**
     * Estimated effective requests served from cache per second.
     */
    public function cacheHitsPerSecond(int $totalRps) : float
    {
        return $totalRps * $this->target;
    }

    /**
     * Estimated cache misses per second (backend load).
     */
    public function cacheMissesPerSecond(int $totalRps) : float
    {
        return $totalRps * $this->missRatio();
    }

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->target < 0 || $this->target > 1) {
            $errors[] = 'cache hit_ratio_target must be between 0 and 1.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
