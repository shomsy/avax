<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\SizeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;

final readonly class CheckCacheCapacity
{
    public function __construct(
        private CacheCapacity           $capacity,
        private EstimateCachedValueSize $estimator = new EstimateCachedValueSize()
    ) {}

    public function canStore(CacheStore $store, mixed $value) : bool
    {
        if ($this->capacity->isValueTooLarge(valueSizeBytes: $this->estimator->estimate(value: $value))) {
            return false;
        }

        return $this->capacity->canStore(
            currentCount    : $this->getCurrentEntryCount(store: $store),
            currentSizeBytes: $this->getCurrentSizeBytes(store: $store)
        );
    }

    public function getCurrentEntryCount(CacheStore $store) : int
    {
        return 0;
    }

    public function getCurrentSizeBytes(CacheStore $store) : int
    {
        return 0;
    }
}