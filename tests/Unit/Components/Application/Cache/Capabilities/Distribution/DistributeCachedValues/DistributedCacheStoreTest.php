<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNode;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeId;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\ConsistentHashRing;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\DistributedCacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastRecentlyUsedReplacement;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use Override;
use PHPUnit\Framework\TestCase;

final class DistributedCacheStoreTest extends TestCase
{
    private FrozenClock $frozenClock;

    public function test_write_then_read_uses_same_resolved_node() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));

        $inMemoryCacheStore = new InMemoryCacheStore(clock: $this->frozenClock, chooseCachedValueForReplacement: new LeastRecentlyUsedReplacement(), maxEntries: 100);

        $distributedCacheStore = new DistributedCacheStore(clock: $this->frozenClock, consistentHashRing: $consistentHashRing)
            ->registerNodeStore(cacheNodeId: CacheNodeId::from(id: 'node_a'), cacheStore: $inMemoryCacheStore);

        $distributedCacheStore->write(cacheKey: $this->makeKey(key: 'user:1'), storedCacheRecord: $this->makeRecord(value: 'value_1'));

        $result = $distributedCacheStore->read(clock: $this->frozenClock, cacheKey: $this->makeKey(key: 'user:1'));

        $this->assertInstanceOf(CacheStoreRecordWasFound::class, $result);
    }

    private function makeKey(string $key) : CacheKey
    {
        return CacheKey::create(key: $key);
    }

    private function makeRecord(string $value) : StoredCacheRecord
    {
        $now                  = $this->frozenClock->now();
        $cachedValueLifecycle = CachedValueLifecycle::create(
            createdAt: $now,
            expiresAt: $now->add(duration: Duration::ofSeconds(seconds: 3600)),
            clock    : $this->frozenClock,
        );

        return new StoredCacheRecord(value: $value, cachedValueLifecycle: $cachedValueLifecycle);
    }

    public function test_distributed_store_fails_when_node_store_is_missing() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_b'));

        $inMemoryCacheStore = new InMemoryCacheStore(clock: $this->frozenClock, chooseCachedValueForReplacement: new LeastRecentlyUsedReplacement(), maxEntries: 100);

        $distributedCacheStore = new DistributedCacheStore(clock: $this->frozenClock, consistentHashRing: $consistentHashRing)
            ->registerNodeStore(cacheNodeId: CacheNodeId::from(id: 'node_a'), cacheStore: $inMemoryCacheStore);

        $result = $distributedCacheStore->read(clock: $this->frozenClock, cacheKey: $this->makeKey(key: 'user:2'));

        $this->assertInstanceOf(CacheStoreRecordWasMissing::class, $result);
    }

    public function test_forget_removes_from_correct_node() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));

        $inMemoryCacheStore = new InMemoryCacheStore(clock: $this->frozenClock, chooseCachedValueForReplacement: new LeastRecentlyUsedReplacement(), maxEntries: 100);

        $distributedCacheStore = new DistributedCacheStore(clock: $this->frozenClock, consistentHashRing: $consistentHashRing)
            ->registerNodeStore(cacheNodeId: CacheNodeId::from(id: 'node_a'), cacheStore: $inMemoryCacheStore);

        $distributedCacheStore->write(cacheKey: $this->makeKey(key: 'key_1'), storedCacheRecord: $this->makeRecord(value: 'value_1'));

        $this->assertTrue($inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_1')));

        $distributedCacheStore->forget(cacheKey: $this->makeKey(key: 'key_1'));

        $this->assertFalse($inMemoryCacheStore->exists(cacheKey: $this->makeKey(key: 'key_1')));
    }

    public function test_clear_removes_from_all_nodes() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_b'));

        $storeA = new InMemoryCacheStore(clock: $this->frozenClock, chooseCachedValueForReplacement: new LeastRecentlyUsedReplacement(), maxEntries: 100);
        $storeB = new InMemoryCacheStore(clock: $this->frozenClock, chooseCachedValueForReplacement: new LeastRecentlyUsedReplacement(), maxEntries: 100);

        $distributedCacheStore = new DistributedCacheStore(clock: $this->frozenClock, consistentHashRing: $consistentHashRing)
            ->registerNodeStore(cacheNodeId: CacheNodeId::from(id: 'node_a'), cacheStore: $storeA)
            ->registerNodeStore(cacheNodeId: CacheNodeId::from(id: 'node_b'), cacheStore: $storeB);

        $distributedCacheStore->write(cacheKey: $this->makeKey(key: 'key_1'), storedCacheRecord: $this->makeRecord(value: 'value_1'));
        $distributedCacheStore->write(cacheKey: $this->makeKey(key: 'key_2'), storedCacheRecord: $this->makeRecord(value: 'value_2'));

        $distributedCacheStore->clear();

        $this->assertEquals(0, $storeA->count());
        $this->assertEquals(0, $storeB->count());
    }

    public function test_node_count_returns_ring_count() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_b'));

        $distributedCacheStore = new DistributedCacheStore(clock: $this->frozenClock, consistentHashRing: $consistentHashRing);

        $this->assertEquals(2, $distributedCacheStore->nodeCount());
    }

    public function test_has_node_store_returns_true_when_registered() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));

        $inMemoryCacheStore = new InMemoryCacheStore(clock: $this->frozenClock, chooseCachedValueForReplacement: new LeastRecentlyUsedReplacement(), maxEntries: 100);

        $distributedCacheStore = new DistributedCacheStore(clock: $this->frozenClock, consistentHashRing: $consistentHashRing)
            ->registerNodeStore(cacheNodeId: CacheNodeId::from(id: 'node_a'), cacheStore: $inMemoryCacheStore);

        $this->assertTrue($distributedCacheStore->hasNodeStore(cacheNodeId: CacheNodeId::from(id: 'node_a')));
        $this->assertFalse($distributedCacheStore->hasNodeStore(cacheNodeId: CacheNodeId::from(id: 'node_b')));
    }

    #[Override]
    protected function setUp() : void
    {
        $this->frozenClock = new FrozenClock(timestamp: Timestamp::now());
    }
}
