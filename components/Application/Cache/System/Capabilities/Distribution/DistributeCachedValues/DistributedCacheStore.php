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

/**
 * @implements IteratorAggregate<string, CacheStore>
 */
final readonly class DistributedCacheStore implements CacheStore, IteratorAggregate
{
    /**
     * @param  array<string, CacheStore>  $nodeStores
     */
    public function __construct(
        private ConsistentHashRing $consistentHashRing,
        private Clock $clock,
        private array $nodeStores = [],
    ) {
    }

    public function registerNodeStore(CacheNodeId $cacheNodeId, CacheStore $cacheStore): self
    {
        return new self(
            consistentHashRing: $this->consistentHashRing,
            clock             : $this->clock,
            nodeStores        : array_merge($this->nodeStores, [$cacheNodeId->toString() => $cacheStore]),
        );
    }

    #[Override]
    public function read(CacheKey $cacheKey, Clock $clock): CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $node = $this->consistentHashRing->getNodeForKey(cacheKey: $cacheKey);

        if (! $node instanceof CacheNode) {
            return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
        }

        $store = $this->getNodeStore(cacheNodeId: $node->id);

        if (! $store instanceof CacheStore) {
            return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
        }

        return $store->read(cacheKey: $cacheKey, clock: $clock);
    }

    public function getNodeStore(CacheNodeId $cacheNodeId): ?CacheStore
    {
        return $this->nodeStores[$cacheNodeId->toString()] ?? null;
    }

    #[Override]
    public function write(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord): void
    {
        $node = $this->consistentHashRing->getNodeForKey(cacheKey: $cacheKey);

        if (! $node instanceof CacheNode) {
            return;
        }

        $store = $this->resolveNodeStore(cacheNode: $node);

        if ($store instanceof CacheStore) {
            $store->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord);
        }
    }

    private function resolveNodeStore(CacheNode $cacheNode): ?CacheStore
    {
        return $this->nodeStores[$cacheNode->id->toString()] ?? null;
    }

    #[Override]
    public function forget(CacheKey $cacheKey): void
    {
        $node = $this->consistentHashRing->getNodeForKey(cacheKey: $cacheKey);

        if (! $node instanceof CacheNode) {
            return;
        }

        $store = $this->resolveNodeStore(cacheNode: $node);

        if ($store instanceof CacheStore) {
            $store->forget(cacheKey: $cacheKey);
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
        $node = $this->consistentHashRing->getNodeForKey(cacheKey: $cacheKey);

        if (! $node instanceof CacheNode) {
            return false;
        }

        $store = $this->resolveNodeStore(cacheNode: $node);

        if (! $store instanceof CacheStore) {
            return false;
        }

        return $store->exists(cacheKey: $cacheKey);
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
