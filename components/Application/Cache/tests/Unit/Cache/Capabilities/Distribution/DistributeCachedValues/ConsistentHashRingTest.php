<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\Tests\Unit\Cache\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNode;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeId;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeStatus;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\ConsistentHashRing;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use PHPUnit\Framework\TestCase;

final class ConsistentHashRingTest extends TestCase
{
    public function test_add_node_increases_count() : void
    {
        $consistentHashRing = new ConsistentHashRing();

        $this->assertEquals(expected: 0, actual: $consistentHashRing->nodeCount());
        $this->assertTrue(condition: $consistentHashRing->isEmpty());

        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));

        $this->assertEquals(expected: 1, actual: $consistentHashRing->nodeCount());
        $this->assertFalse(condition: $consistentHashRing->isEmpty());
    }

    public function test_remove_node_decreases_count() : void
    {
        $consistentHashRing = new ConsistentHashRing();

        $cacheNode = CacheNode::create(id: 'node_a');
        $consistentHashRing->addNode(node: $cacheNode);

        $this->assertEquals(expected: 1, actual: $consistentHashRing->nodeCount());

        $consistentHashRing->removeNode(nodeId: $cacheNode->id);

        $this->assertEquals(expected: 0, actual: $consistentHashRing->nodeCount());
        $this->assertTrue(condition: $consistentHashRing->isEmpty());
    }

    public function test_same_key_routes_to_same_node() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_b'));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_c'));

        $cacheKey = CacheKey::create(key: 'test_key_123');

        $node1 = $consistentHashRing->getNodeForKey(key: $cacheKey);
        $node2 = $consistentHashRing->getNodeForKey(key: $cacheKey);
        $node3 = $consistentHashRing->getNodeForKey(key: $cacheKey);

        $this->assertNotNull(actual: $node1);
        $this->assertNotNull(actual: $node2);
        $this->assertNotNull(actual: $node3);

        $this->assertEquals(expected: $node1->id->toString(), actual: $node2->id->toString());
        $this->assertEquals(expected: $node2->id->toString(), actual: $node3->id->toString());
    }

    public function test_different_keys_distribute_across_nodes() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_b'));

        $keys = [
            'user:1', 'user:2', 'user:3', 'user:4', 'user:5',
            'user:6', 'user:7', 'user:8', 'user:9', 'user:10',
        ];

        $distribution = [];

        foreach ($keys as $keyStr) {
            $key  = CacheKey::create(key: $keyStr);
            $node = $consistentHashRing->getNodeForKey(key: $key);

            if ($node instanceof CacheNode) {
                $nodeId                = $node->id->toString();
                $distribution[$nodeId] = ($distribution[$nodeId] ?? 0) + 1;
            }
        }

        $this->assertCount(expectedCount: 2, haystack: $distribution);
    }

    public function test_empty_ring_returns_null() : void
    {
        $consistentHashRing = new ConsistentHashRing();

        $cacheKey = CacheKey::create(key: 'any_key');
        $node     = $consistentHashRing->getNodeForKey(key: $cacheKey);

        $this->assertNull(actual: $node);
    }

    public function test_get_node_for_partition() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_b'));

        $node = $consistentHashRing->getNodeForPartition(partitionIndex: 42);

        $this->assertNotNull(actual: $node);
    }

    public function test_node_removal_affects_routing() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_b'));

        $cacheKey = CacheKey::create(key: 'test_key');

        $nodeBefore = $consistentHashRing->getNodeForKey(key: $cacheKey);

        $consistentHashRing->removeNode(nodeId: CacheNodeId::from(id: 'node_a'));

        $nodeAfter = $consistentHashRing->getNodeForKey(key: $cacheKey);

        $this->assertNotEquals(expected: $nodeBefore?->id->toString(), actual: $nodeAfter?->id->toString());
    }

    public function test_add_node_chain_returns_self() : void
    {
        $consistentHashRing = new ConsistentHashRing();

        $result = $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));

        $this->assertSame(expected: $consistentHashRing, actual: $result);
    }

    public function test_remove_node_chain_returns_self() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));

        $result = $consistentHashRing->removeNode(nodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertSame(expected: $consistentHashRing, actual: $result);
    }

    public function test_healthy_nodes_only_selected() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a', status: CacheNodeStatus::HEALTHY));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_b', status: CacheNodeStatus::UNHEALTHY));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_c', status: CacheNodeStatus::HEALTHY));

        $cacheKey = CacheKey::create(key: 'test_key');
        $node     = $consistentHashRing->getNodeForKey(key: $cacheKey);

        $this->assertNotNull(actual: $node);
        $this->assertEquals(expected: 'node_a', actual: $node->id->toString());
    }
}
