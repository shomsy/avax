<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\Tests\Unit\Cache\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNode;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeId;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\ConsistentHashRing;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\DistributedCacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
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
        $consistentHashRing = new ConsistentHashRing;
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));

        $inMemoryCacheStore = new InMemoryCacheStore(clock: $this->frozenClock, maxEntries: 100);

        $distributedCacheStore = (new DistributedCacheStore(ring: $consistentHashRing, clock: $this->frozenClock))
            ->registerNodeStore(nodeId: CacheNodeId::from(id: 'node_a'), store: $inMemoryCacheStore);

        $distributedCacheStore->write(key: $this->makeKey(key: 'user:1'), record: $this->makeRecord(value: 'value_1'));

        $result = $distributedCacheStore->read(key: $this->makeKey(key: 'user:1'), clock: $this->frozenClock);

        $this->assertInstanceOf(expected: CacheStoreRecordWasFound::class, actual: $result);
    }

    private function makeKey(string $key) : CacheKey
    {
        return CacheKey::create(key: $key);
    }

    private function makeRecord(string $value) : StoredCacheRecord
    {
        $now = $this->frozenClock->now();
        $cachedValueLifecycle = CachedValueLifecycle::create(
            createdAt: $now,
            expiresAt: $now->add(duration: Duration::ofSeconds(seconds: 3600)),
            clock    : $this->frozenClock,
        );

        return new StoredCacheRecord(value: $value, lifecycle: $cachedValueLifecycle);
    }

    public function test_distributed_store_fails_when_node_store_is_missing() : void
    {
        $consistentHashRing = new ConsistentHashRing;
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_b'));

        $inMemoryCacheStore = new InMemoryCacheStore(clock: $this->frozenClock, maxEntries: 100);

        $distributedCacheStore = (new DistributedCacheStore(ring: $consistentHashRing, clock: $this->frozenClock))
            ->registerNodeStore(nodeId: CacheNodeId::from(id: 'node_a'), store: $inMemoryCacheStore);

        $result = $distributedCacheStore->read(key: $this->makeKey(key: 'user:2'), clock: $this->frozenClock);

        $this->assertInstanceOf(expected: CacheStoreRecordWasMissing::class, actual: $result);
    }

    public function test_forget_removes_from_correct_node() : void
    {
        $consistentHashRing = new ConsistentHashRing;
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));

        $inMemoryCacheStore = new InMemoryCacheStore(clock: $this->frozenClock, maxEntries: 100);

        $distributedCacheStore = (new DistributedCacheStore(ring: $consistentHashRing, clock: $this->frozenClock))
            ->registerNodeStore(nodeId: CacheNodeId::from(id: 'node_a'), store: $inMemoryCacheStore);

        $distributedCacheStore->write(key: $this->makeKey(key: 'key_1'), record: $this->makeRecord(value: 'value_1'));

        $this->assertTrue(condition: $inMemoryCacheStore->exists(key: $this->makeKey(key: 'key_1')));

        $distributedCacheStore->forget(key: $this->makeKey(key: 'key_1'));

        $this->assertFalse(condition: $inMemoryCacheStore->exists(key: $this->makeKey(key: 'key_1')));
    }

    public function test_clear_removes_from_all_nodes() : void
    {
        $consistentHashRing = new ConsistentHashRing;
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_b'));

        $storeA = new InMemoryCacheStore(clock: $this->frozenClock, maxEntries: 100);
        $storeB = new InMemoryCacheStore(clock: $this->frozenClock, maxEntries: 100);

        $distributedCacheStore = (new DistributedCacheStore(ring: $consistentHashRing, clock: $this->frozenClock))
            ->registerNodeStore(nodeId: CacheNodeId::from(id: 'node_a'), store: $storeA)
            ->registerNodeStore(nodeId: CacheNodeId::from(id: 'node_b'), store: $storeB);

        $distributedCacheStore->write(key: $this->makeKey(key: 'key_1'), record: $this->makeRecord(value: 'value_1'));
        $distributedCacheStore->write(key: $this->makeKey(key: 'key_2'), record: $this->makeRecord(value: 'value_2'));

        $distributedCacheStore->clear();

        $this->assertEquals(expected: 0, actual: $storeA->count());
        $this->assertEquals(expected: 0, actual: $storeB->count());
    }

    public function test_node_count_returns_ring_count() : void
    {
        $consistentHashRing = new ConsistentHashRing;
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_b'));

        $distributedCacheStore = new DistributedCacheStore(ring: $consistentHashRing, clock: $this->frozenClock);

        $this->assertEquals(expected: 2, actual: $distributedCacheStore->nodeCount());
    }

    public function test_has_node_store_returns_true_when_registered() : void
    {
        $consistentHashRing = new ConsistentHashRing;
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));

        $inMemoryCacheStore = new InMemoryCacheStore(clock: $this->frozenClock, maxEntries: 100);

        $distributedCacheStore = (new DistributedCacheStore(ring: $consistentHashRing, clock: $this->frozenClock))
            ->registerNodeStore(nodeId: CacheNodeId::from(id: 'node_a'), store: $inMemoryCacheStore);

        $this->assertTrue(condition: $distributedCacheStore->hasNodeStore(nodeId: CacheNodeId::from(id: 'node_a')));
        $this->assertFalse(condition: $distributedCacheStore->hasNodeStore(nodeId: CacheNodeId::from(id: 'node_b')));
    }

    #[Override]
    protected function setUp() : void
    {
        $this->frozenClock = new FrozenClock(timestamp: Timestamp::now());
    }
}
