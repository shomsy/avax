<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\Capabilities\Distribution\DistributeCachedValues;

use Avax\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNode;
use Avax\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeId;
use Avax\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeStatus;
use Avax\Cache\System\Capabilities\Distribution\DistributeCachedValues\ConsistentHashRing;
use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use PHPUnit\Framework\TestCase;

final class ConsistentHashRingTest extends TestCase
{
    public function test_add_node_increases_count() : void
    {
        $ring = new ConsistentHashRing();

        $this->assertEquals(expected: 0, actual: $ring->nodeCount());
        $this->assertTrue(condition: $ring->isEmpty());

        $ring->addNode(node: CacheNode::create(id: 'node_a'));

        $this->assertEquals(expected: 1, actual: $ring->nodeCount());
        $this->assertFalse(condition: $ring->isEmpty());
    }

    public function test_remove_node_decreases_count() : void
    {
        $ring = new ConsistentHashRing();

        $node = CacheNode::create(id: 'node_a');
        $ring->addNode(node: $node);

        $this->assertEquals(expected: 1, actual: $ring->nodeCount());

        $ring->removeNode(nodeId: $node->id);

        $this->assertEquals(expected: 0, actual: $ring->nodeCount());
        $this->assertTrue(condition: $ring->isEmpty());
    }

    public function test_same_key_routes_to_same_node() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));
        $ring->addNode(node: CacheNode::create(id: 'node_b'));
        $ring->addNode(node: CacheNode::create(id: 'node_c'));

        $key = CacheKey::create(key: 'test_key_123');

        $node1 = $ring->getNodeForKey(key: $key);
        $node2 = $ring->getNodeForKey(key: $key);
        $node3 = $ring->getNodeForKey(key: $key);

        $this->assertNotNull(actual: $node1);
        $this->assertNotNull(actual: $node2);
        $this->assertNotNull(actual: $node3);

        $this->assertEquals(expected: $node1->id->toString(), actual: $node2->id->toString());
        $this->assertEquals(expected: $node2->id->toString(), actual: $node3->id->toString());
    }

    public function test_different_keys_distribute_across_nodes() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));
        $ring->addNode(node: CacheNode::create(id: 'node_b'));

        $keys = [
            'user:1', 'user:2', 'user:3', 'user:4', 'user:5',
            'user:6', 'user:7', 'user:8', 'user:9', 'user:10',
        ];

        $distribution = [];

        foreach ($keys as $keyStr) {
            $key  = CacheKey::create(key: $keyStr);
            $node = $ring->getNodeForKey(key: $key);

            if ($node !== null) {
                $nodeId                = $node->id->toString();
                $distribution[$nodeId] = ($distribution[$nodeId] ?? 0) + 1;
            }
        }

        $this->assertCount(expectedCount: 2, haystack: $distribution);
    }

    public function test_empty_ring_returns_null() : void
    {
        $ring = new ConsistentHashRing();

        $key  = CacheKey::create(key: 'any_key');
        $node = $ring->getNodeForKey(key: $key);

        $this->assertNull(actual: $node);
    }

    public function test_get_node_for_partition() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));
        $ring->addNode(node: CacheNode::create(id: 'node_b'));

        $node = $ring->getNodeForPartition(partitionIndex: 42);

        $this->assertNotNull(actual: $node);
    }

    public function test_node_removal_affects_routing() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));
        $ring->addNode(node: CacheNode::create(id: 'node_b'));

        $key = CacheKey::create(key: 'test_key');

        $nodeBefore = $ring->getNodeForKey(key: $key);

        $ring->removeNode(nodeId: CacheNodeId::from(id: 'node_a'));

        $nodeAfter = $ring->getNodeForKey(key: $key);

        $this->assertNotEquals(expected: $nodeBefore?->id->toString(), actual: $nodeAfter?->id->toString());
    }

    public function test_add_node_chain_returns_self() : void
    {
        $ring = new ConsistentHashRing();

        $result = $ring->addNode(node: CacheNode::create(id: 'node_a'));

        $this->assertSame(expected: $ring, actual: $result);
    }

    public function test_remove_node_chain_returns_self() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));

        $result = $ring->removeNode(nodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertSame(expected: $ring, actual: $result);
    }

    public function test_healthy_nodes_only_selected() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a', status: CacheNodeStatus::HEALTHY));
        $ring->addNode(node: CacheNode::create(id: 'node_b', status: CacheNodeStatus::UNHEALTHY));
        $ring->addNode(node: CacheNode::create(id: 'node_c', status: CacheNodeStatus::HEALTHY));

        $key  = CacheKey::create(key: 'test_key');
        $node = $ring->getNodeForKey(key: $key);

        $this->assertNotNull(actual: $node);
        $this->assertEquals(expected: 'node_a', actual: $node->id->toString());
    }
}