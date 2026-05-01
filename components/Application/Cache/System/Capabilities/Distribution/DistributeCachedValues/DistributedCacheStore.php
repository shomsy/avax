<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use ArrayIterator;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use IteratorAggregate;
use Override;
use Traversable;

final readonly class DistributedCacheStore implements CacheStore, IteratorAggregate
{
    public function __construct(
        private ConsistentHashRing $ring,
        private Clock              $clock,
        private array              $nodeStores = [],
    ) {}

    public function registerNodeStore(CacheNodeId $nodeId, CacheStore $store) : self
    {
        return new self(
            ring      : $this->ring,
            clock     : $this->clock,
            nodeStores: array_merge($this->nodeStores, [$nodeId->toString() => $store]),
        );
    }

    #[Override]
    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $node = $this->ring->getNodeForKey(key: $key);

        if ($node === null) {
            return new CacheStoreRecordWasMissing(key: $key);
        }

        $store = $this->getNodeStore(nodeId: $node->id);

        if (! $store instanceof CacheStore) {
            return new CacheStoreRecordWasMissing(key: $key);
        }

        return $store->read(key: $key, clock: $clock);
    }

    public function getNodeStore(CacheNodeId $nodeId) : CacheStore|null
    {
        return $this->nodeStores[$nodeId->toString()] ?? null;
    }

    #[Override]
    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        $node = $this->ring->getNodeForKey(key: $key);

        if ($node === null) {
            return;
        }

        $store = $this->resolveNodeStore(node: $node);

        if ($store instanceof CacheStore) {
            $store->write(key: $key, record: $record);
        }
    }

    private function resolveNodeStore(CacheNode $node) : CacheStore|null
    {
        return $this->nodeStores[$node->id->toString()] ?? null;
    }

    #[Override]
    public function forget(CacheKey $key) : void
    {
        $node = $this->ring->getNodeForKey(key: $key);

        if ($node === null) {
            return;
        }

        $store = $this->resolveNodeStore(node: $node);

        if ($store instanceof CacheStore) {
            $store->forget(key: $key);
        }
    }

    #[Override]
    public function clear() : void
    {
        foreach ($this->nodeStores as $nodeStore) {
            $nodeStore->clear();
        }
    }

    #[Override]
    public function exists(CacheKey $key) : bool
    {
        $node = $this->ring->getNodeForKey(key: $key);

        if ($node === null) {
            return false;
        }

        $store = $this->resolveNodeStore(node: $node);

        if (! $store instanceof CacheStore) {
            return false;
        }

        return $store->exists(key: $key);
    }

    #[Override]
    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->nodeStores);
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
