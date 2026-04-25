<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\DistributeCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Cache\System\Capabilities\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\Capabilities\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Foundation\Time\Clock;

final readonly class DistributedCacheStore implements CacheStore
{
    public function __construct(
        private ConsistentHashRing $ring,
        private Clock              $clock
    ) {}

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $node = $this->ring->getNodeForKey($key);

        if ($node === null) {
            return new CacheStoreRecordWasMissing($key);
        }

        return $this->getNodeStore($node->id)->read($key, $clock);
    }

    private function getNodeStore(CacheNodeId $nodeId) : CacheStore
    {
        return new InMemoryCacheStore($this->clock);
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        $node = $this->ring->getNodeForKey($key);

        if ($node === null) {
            return;
        }

        $this->getNodeStore($node->id)->write($key, $record);
    }

    public function forget(CacheKey $key) : void
    {
        $node = $this->ring->getNodeForKey($key);

        if ($node === null) {
            return;
        }

        $this->getNodeStore($node->id)->forget($key);
    }

    public function clear() : void
    {
        foreach ($this->ring as $node) {
            $this->getNodeStore($node->id)->clear();
        }
    }

    public function exists(CacheKey $key) : bool
    {
        $node = $this->ring->getNodeForKey($key);

        if ($node === null) {
            return false;
        }

        return $this->getNodeStore($node->id)->exists($key);
    }
}