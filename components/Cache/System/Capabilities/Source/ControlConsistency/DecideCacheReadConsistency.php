<?php

declare(strict_types=1);

namespace Avax\Components\Cache\System\Capabilities\Source\ControlConsistency;

use Avax\Components\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class DecideCacheReadConsistency
{
    public function __construct(
        private CacheConsistencyLevel  $level,
        private ConsistencyWindow|null $window = null
    ) {}

    public function shouldReadFromPrimary(CacheKey $key, CachedValueLifecycle $lifecycle) : bool
    {
        return match ($this->level) {
            CacheConsistencyLevel::STRONG           => true,
            CacheConsistencyLevel::LOCAL            => true,
            CacheConsistencyLevel::EVENTUAL         => false,
            CacheConsistencyLevel::READ_YOUR_WRITES => $this->shouldCheckWriteTimestamp(key: $key),
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