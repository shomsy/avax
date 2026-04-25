<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ControlConsistency;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueLifecycle;

final readonly class DecideCacheReadConsistency
{
    public function __construct(
        private CacheConsistencyLevel $level,
        private ?ConsistencyWindow    $window = null
    ) {}

    public function shouldReadFromPrimary(CacheKey $key, CachedValueLifecycle $lifecycle) : bool
    {
        return match ($this->level) {
            CacheConsistencyLevel::STRONG           => true,
            CacheConsistencyLevel::LOCAL            => true,
            CacheConsistencyLevel::EVENTUAL         => false,
            CacheConsistencyLevel::READ_YOUR_WRITES => $this->shouldCheckWriteTimestamp($key),
        };
    }

    private function shouldCheckWriteTimestamp(CacheKey $key) : bool
    {
        return true;
    }

    public function allowStaleRead(CacheKey $key, CachedValueLifecycle $lifecycle) : bool
    {
        return match ($this->level) {
            CacheConsistencyLevel::STRONG           => false,
            CacheConsistencyLevel::LOCAL            => false,
            CacheConsistencyLevel::EVENTUAL         => true,
            CacheConsistencyLevel::READ_YOUR_WRITES => false,
        };
    }
}