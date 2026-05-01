<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\SizeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;

final readonly class CheckCacheCapacity
{
    public function __construct(
        private CacheCapacity $cacheCapacity,
        private EstimateCachedValueSize $estimateCachedValueSize = new EstimateCachedValueSize,
    ) {}

    public function canStore(CacheStore $cacheStore, mixed $value) : bool
    {
        if ($this->cacheCapacity->isValueTooLarge(valueSizeBytes: $this->estimateCachedValueSize->estimate(value: $value))) {
            return false;
        }

        return $this->cacheCapacity->canStore(
            currentCount    : $this->getCurrentEntryCount(),
            currentSizeBytes: $this->getCurrentSizeBytes(),
        );
    }

    public function getCurrentEntryCount() : int
    {
        return 0;
    }

    public function getCurrentSizeBytes() : int
    {
        return 0;
    }
}
