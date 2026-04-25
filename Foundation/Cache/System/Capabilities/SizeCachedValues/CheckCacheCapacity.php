<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\SizeCachedValues;

use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;

final readonly class CheckCacheCapacity
{
    public function __construct(
        private CacheCapacity           $capacity,
        private EstimateCachedValueSize $estimator = new EstimateCachedValueSize()
    ) {}

    public function canStore(CacheStore $store, mixed $value) : bool
    {
        if ($this->capacity->isValueTooLarge($this->estimator->estimate($value))) {
            return false;
        }

        return $this->capacity->canStore(
            $this->getCurrentEntryCount($store),
            $this->getCurrentSizeBytes($store)
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