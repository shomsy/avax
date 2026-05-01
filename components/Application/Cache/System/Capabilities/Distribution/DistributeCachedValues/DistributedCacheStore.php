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
        private ConsistentHashRing $consistentHashRing,
        private Clock $clock,
        private array $nodeStores = [],
    ) {
    }

    public function registerNodeStore(CacheNodeId $cacheNodeId, CacheStore $cacheStore): self
    {
        return new self(
            clock     : $this->clock,
            nodeStores: array_merge($this->nodeStores, [$cacheNodeId->toString() => $cacheStore]),
            ring      : $this->consistentHashRing,
        );
    }

    #[Override]
    public function read(CacheKey $cacheKey, Clock $clock): CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $node = $this->consistentHashRing->getNodeForKey(key: $cacheKey);

        if (! $node instanceof CacheNode) {
            return new CacheStoreRecordWasMissing(key: $cacheKey);
        }

        $store = $this->getNodeStore(nodeId: $node->id);

        if (! $store instanceof CacheStore) {
            return new CacheStoreRecordWasMissing(key: $cacheKey);
        }

        return $store->read(clock: $clock, key: $cacheKey);
    }

    public function getNodeStore(CacheNodeId $cacheNodeId): ?CacheStore
    {
        return $this->nodeStores[$cacheNodeId->toString()] ?? null;
    }

    #[Override]
    public function write(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord): void
    {
        $node = $this->consistentHashRing->getNodeForKey(key: $cacheKey);

        if (! $node instanceof CacheNode) {
            return;
        }

        $store = $this->resolveNodeStore(node: $node);

        if ($store instanceof CacheStore) {
            $store->write(key: $cacheKey, record: $storedCacheRecord);
        }
    }

    private function resolveNodeStore(CacheNode $cacheNode): ?CacheStore
    {
        return $this->nodeStores[$cacheNode->id->toString()] ?? null;
    }

    #[Override]
    public function forget(CacheKey $cacheKey): void
    {
        $node = $this->consistentHashRing->getNodeForKey(key: $cacheKey);

        if (! $node instanceof CacheNode) {
            return;
        }

        $store = $this->resolveNodeStore(node: $node);

        if ($store instanceof CacheStore) {
            $store->forget(key: $cacheKey);
        }
    }

    #[Override]
    public function clear(): void
    {
        foreach ($this->nodeStores as $nodeStore) {
            $nodeStore->clear();
        }
    }

    #[Override]
    public function exists(CacheKey $cacheKey): bool
    {
        $node = $this->consistentHashRing->getNodeForKey(key: $cacheKey);

        if (! $node instanceof CacheNode) {
            return false;
        }

        $store = $this->resolveNodeStore(node: $node);

        if (! $store instanceof CacheStore) {
            return false;
        }

        return $store->exists(key: $cacheKey);
    }

    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->nodeStores);
    }

    public function nodeCount(): int
    {
        return $this->consistentHashRing->nodeCount();
    }

    public function hasNodeStore(CacheNodeId $cacheNodeId): bool
    {
        return isset($this->nodeStores[$cacheNodeId->toString()]);
    }
}
