<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ControlConsistency;

final readonly class DecideCacheReadConsistency
{
    public function __construct(
        private CacheConsistencyLevel $cacheConsistencyLevel,
    ) {}

    public function shouldReadFromPrimary() : bool
    {
        return match ($this->cacheConsistencyLevel) {
            CacheConsistencyLevel::STRONG           => true,
            CacheConsistencyLevel::LOCAL            => true,
            CacheConsistencyLevel::EVENTUAL         => false,
            CacheConsistencyLevel::READ_YOUR_WRITES => $this->shouldCheckWriteTimestamp(),
        };
    }

    private function shouldCheckWriteTimestamp() : bool
    {
        return true;
    }

    public function allowStaleRead() : bool
    {
        return match ($this->cacheConsistencyLevel) {
            CacheConsistencyLevel::STRONG           => false,
            CacheConsistencyLevel::LOCAL            => false,
            CacheConsistencyLevel::EVENTUAL         => true,
            CacheConsistencyLevel::READ_YOUR_WRITES => false,
        };
    }
}
