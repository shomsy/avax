<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Unit\Cache\Capabilities\Distribution\DistributeCachedValues;

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
use PHPUnit\Framework\TestCase;

final class DistributedCacheStoreTest extends TestCase
{
    private FrozenClock $clock;

    public function test_write_then_read_uses_same_resolved_node() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));

        $storeA = new InMemoryCacheStore(clock: $this->clock, maxEntries: 100);

        $cache = (new DistributedCacheStore(ring: $ring, clock: $this->clock))
            ->registerNodeStore(nodeId: CacheNodeId::from(id: 'node_a'), store: $storeA);

        $cache->write(key: $this->makeKey(key: 'user:1'), record: $this->makeRecord(value: 'value_1'));

        $result = $cache->read(key: $this->makeKey(key: 'user:1'), clock: $this->clock);

        $this->assertInstanceOf(expected: CacheStoreRecordWasFound::class, actual: $result);
    }

    private function makeKey(string $key) : CacheKey
    {
        return CacheKey::create(key: $key);
    }

    private function makeRecord(string $value) : StoredCacheRecord
    {
        $now       = $this->clock->now();
        $lifecycle = CachedValueLifecycle::create(
            createdAt: $now,
            expiresAt: $now->add(duration: Duration::ofSeconds(seconds: 3600)),
            clock    : $this->clock,
        );

        return new StoredCacheRecord(value: $value, lifecycle: $lifecycle);
    }

    public function test_distributed_store_fails_when_node_store_is_missing() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));
        $ring->addNode(node: CacheNode::create(id: 'node_b'));

        $storeA = new InMemoryCacheStore(clock: $this->clock, maxEntries: 100);

        $cache = (new DistributedCacheStore(ring: $ring, clock: $this->clock))
            ->registerNodeStore(nodeId: CacheNodeId::from(id: 'node_a'), store: $storeA);

        $result = $cache->read(key: $this->makeKey(key: 'user:2'), clock: $this->clock);

        $this->assertInstanceOf(expected: CacheStoreRecordWasMissing::class, actual: $result);
    }

    public function test_forget_removes_from_correct_node() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));

        $storeA = new InMemoryCacheStore(clock: $this->clock, maxEntries: 100);

        $cache = (new DistributedCacheStore(ring: $ring, clock: $this->clock))
            ->registerNodeStore(nodeId: CacheNodeId::from(id: 'node_a'), store: $storeA);

        $cache->write(key: $this->makeKey(key: 'key_1'), record: $this->makeRecord(value: 'value_1'));

        $this->assertTrue(condition: $storeA->exists(key: $this->makeKey(key: 'key_1')));

        $cache->forget(key: $this->makeKey(key: 'key_1'));

        $this->assertFalse(condition: $storeA->exists(key: $this->makeKey(key: 'key_1')));
    }

    public function test_clear_removes_from_all_nodes() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));
        $ring->addNode(node: CacheNode::create(id: 'node_b'));

        $storeA = new InMemoryCacheStore(clock: $this->clock, maxEntries: 100);
        $storeB = new InMemoryCacheStore(clock: $this->clock, maxEntries: 100);

        $cache = (new DistributedCacheStore(ring: $ring, clock: $this->clock))
            ->registerNodeStore(nodeId: CacheNodeId::from(id: 'node_a'), store: $storeA)
            ->registerNodeStore(nodeId: CacheNodeId::from(id: 'node_b'), store: $storeB);

        $cache->write(key: $this->makeKey(key: 'key_1'), record: $this->makeRecord(value: 'value_1'));
        $cache->write(key: $this->makeKey(key: 'key_2'), record: $this->makeRecord(value: 'value_2'));

        $cache->clear();

        $this->assertEquals(expected: 0, actual: $storeA->count());
        $this->assertEquals(expected: 0, actual: $storeB->count());
    }

    public function test_node_count_returns_ring_count() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));
        $ring->addNode(node: CacheNode::create(id: 'node_b'));

        $cache = new DistributedCacheStore(ring: $ring, clock: $this->clock);

        $this->assertEquals(expected: 2, actual: $cache->nodeCount());
    }

    public function test_has_node_store_returns_true_when_registered() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));

        $storeA = new InMemoryCacheStore(clock: $this->clock, maxEntries: 100);

        $cache = (new DistributedCacheStore(ring: $ring, clock: $this->clock))
            ->registerNodeStore(nodeId: CacheNodeId::from(id: 'node_a'), store: $storeA);

        $this->assertTrue(condition: $cache->hasNodeStore(nodeId: CacheNodeId::from(id: 'node_a')));
        $this->assertFalse(condition: $cache->hasNodeStore(nodeId: CacheNodeId::from(id: 'node_b')));
    }

    protected function setUp() : void
    {
        $this->clock = new FrozenClock(timestamp: Timestamp::now());
    }
}
