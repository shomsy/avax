<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Configuration;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues\StaleValuePolicy;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\ChooseCachedValueForReplacement;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastRecentlyUsedReplacement;

final readonly class CacheConfiguration
{
    /**
     * @param array<string, mixed> $serializerOptions
     */
    public function __construct(
        public string $name = 'default',
        public ?int $defaultTtl = 3600,
        public ?int $maxCapacity = null,
        public StaleValuePolicy $staleValuePolicy = StaleValuePolicy::DO_NOT_SERVE_STALE,
        public ChooseCachedValueForReplacement $chooseCachedValueForReplacement = new LeastRecentlyUsedReplacement(),
        public bool $enableMetrics = true,
        public bool $enableTracing = false,
        public array $serializerOptions = [],
    ) {
    }
}
