<?php

declare(strict_types=1);

namespace Avax\Cache\System\Configuration;

use Avax\Cache\System\Capabilities\ManageCacheLifecycle\ReplacementPolicies\ChooseCachedValueForReplacement;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\ReplacementPolicies\LeastRecentlyUsedReplacement;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\StaleValuePolicies\StaleValuePolicy;

final readonly class CacheConfiguration
{
    public function __construct(
        public string                          $name = 'default',
        public null|int                        $defaultTtl = 3600,
        public null|int                        $maxCapacity = null,
        public StaleValuePolicy                $stalePolicy = StaleValuePolicy::DO_NOT_SERVE_STALE,
        public ChooseCachedValueForReplacement $replacementPolicy = new LeastRecentlyUsedReplacement(),
        public bool                            $enableMetrics = true,
        public bool                            $enableTracing = false,
        public array                           $serializerOptions = []
    ) {}
}