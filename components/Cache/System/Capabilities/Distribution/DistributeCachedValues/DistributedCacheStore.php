<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Foundation\Time\Clock;
use IteratorAggregate;
use Traversable;

final readonly class DistributedCacheStore implements CacheStore, IteratorAggregate
{
    public function __construct(
        private ConsistentHashRing $ring,
        private Clock              $clock,
        private array              $nodeStores = []
    ) {}

    public function registerNodeStore(CacheNodeId $nodeId, CacheStore $store) : self
    {
        return new self(
            ring      : $this->ring,
            clock     : $this->clock,
            nodeStores: array_merge($this->nodeStores, [$nodeId->toString() => $store])
        );
    }

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $node = $this->ring->getNodeForKey(key: $key);

        if ($node === null) {
            return new CacheStoreRecordWasMissing(key: $key);
        }

        $store = $this->getNodeStore(nodeId: $node->id);

        if ($store === null) {
            return new CacheStoreRecordWasMissing(key: $key);
        }

        return $store->read(key: $key, clock: $clock);
    }

    public function getNodeStore(CacheNodeId $nodeId) : CacheStore|null
    {
        return $this->nodeStores[$nodeId->toString()] ?? null;
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        $node = $this->ring->getNodeForKey(key: $key);

        if ($node === null) {
            return;
        }

        $store = $this->resolveNodeStore(node: $node);

        if ($store !== null) {
            $store->write(key: $key, record: $record);
        }
    }

    private function resolveNodeStore(CacheNode $node) : CacheStore|null
    {
        return $this->nodeStores[$node->id->toString()] ?? null;
    }

    public function forget(CacheKey $key) : void
    {
        $node = $this->ring->getNodeForKey(key: $key);

        if ($node === null) {
            return;
        }

        $store = $this->resolveNodeStore(node: $node);

        if ($store !== null) {
            $store->forget(key: $key);
        }
    }

    public function clear() : void
    {
        foreach ($this->nodeStores as $store) {
            $store->clear();
        }
    }

    public function exists(CacheKey $key) : bool
    {
        $node = $this->ring->getNodeForKey(key: $key);

        if ($node === null) {
            return false;
        }

        $store = $this->resolveNodeStore(node: $node);

        if ($store === null) {
            return false;
        }

        return $store->exists(key: $key);
    }

    public function getIterator() : Traversable
    {
        return new Traversable();
    }

    public function nodeCount() : int
    {
        return $this->ring->nodeCount();
    }

    public function hasNodeStore(CacheNodeId $nodeId) : bool
    {
        return isset($this->nodeStores[$nodeId->toString()]);
    }
}