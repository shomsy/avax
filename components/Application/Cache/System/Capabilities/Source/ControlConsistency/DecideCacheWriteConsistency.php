<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ControlConsistency;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class DecideCacheWriteConsistency
{
    public function __construct(
        private CacheConsistencyLevel  $cacheConsistencyLevel,
        private ConsistencyWindow|null $consistencyWindow = null,
    ) {}

    public function shouldWriteSynchronously() : bool
    {
        return match ($this->cacheConsistencyLevel) {
            CacheConsistencyLevel::STRONG           => true,
            CacheConsistencyLevel::LOCAL            => true,
            CacheConsistencyLevel::EVENTUAL         => false,
            CacheConsistencyLevel::READ_YOUR_WRITES => true,
        };
    }

    public function shouldWaitForPropagation() : bool
    {
        return match ($this->cacheConsistencyLevel) {
            CacheConsistencyLevel::STRONG           => true,
            CacheConsistencyLevel::LOCAL            => false,
            CacheConsistencyLevel::EVENTUAL         => false,
            CacheConsistencyLevel::READ_YOUR_WRITES => true,
        };
    }

    public function propagationDelayMs() : int
    {
        return $this->consistencyWindow?->propagationDelayMs ?? 100;
    }
}
