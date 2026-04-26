<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Source\ControlConsistency;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class DecideCacheWriteConsistency
{
    public function __construct(
        private CacheConsistencyLevel  $level,
        private ConsistencyWindow|null $window = null
    ) {}

    public function shouldWriteSynchronously(CacheKey $key) : bool
    {
        return match ($this->level) {
            CacheConsistencyLevel::STRONG           => true,
            CacheConsistencyLevel::LOCAL            => true,
            CacheConsistencyLevel::EVENTUAL         => false,
            CacheConsistencyLevel::READ_YOUR_WRITES => true,
        };
    }

    public function shouldWaitForPropagation(CacheKey $key) : bool
    {
        return match ($this->level) {
            CacheConsistencyLevel::STRONG           => true,
            CacheConsistencyLevel::LOCAL            => false,
            CacheConsistencyLevel::EVENTUAL         => false,
            CacheConsistencyLevel::READ_YOUR_WRITES => true,
        };
    }

    public function propagationDelayMs() : int
    {
        return $this->window?->propagationDelayMs ?? 100;
    }
}