<?php

declare(strict_types=1);

namespace components\Cache\System\Configuration;

use components\Cache\System\Capabilities\Lifecycle\ExpireCachedValues\StaleValuePolicy;
use components\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\ChooseCachedValueForReplacement;
use components\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastRecentlyUsedReplacement;

final readonly class CacheConfiguration
{
    public function __construct(
        public string                          $name = 'default',
        public int|null                        $defaultTtl = 3600,
        public int|null                        $maxCapacity = null,
        public StaleValuePolicy                $stalePolicy = StaleValuePolicy::DO_NOT_SERVE_STALE,
        public ChooseCachedValueForReplacement $replacementPolicy = new LeastRecentlyUsedReplacement(),
        public bool                            $enableMetrics = true,
        public bool                            $enableTracing = false,
        public array                           $serializerOptions = []
    ) {}
}