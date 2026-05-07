<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Capabilities\Distribution\DistributeCachedValues;

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

        $this->assertEquals(0, $consistentHashRing->nodeCount());
        $this->assertTrue($consistentHashRing->isEmpty());

        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));

        $this->assertEquals(1, $consistentHashRing->nodeCount());
        $this->assertFalse($consistentHashRing->isEmpty());
    }

    public function test_remove_node_decreases_count() : void
    {
        $consistentHashRing = new ConsistentHashRing();

        $cacheNode = CacheNode::create(id: 'node_a');
        $consistentHashRing->addNode(cacheNode: $cacheNode);

        $this->assertEquals(1, $consistentHashRing->nodeCount());

        $consistentHashRing->removeNode(cacheNodeId: $cacheNode->id);

        $this->assertEquals(0, $consistentHashRing->nodeCount());
        $this->assertTrue($consistentHashRing->isEmpty());
    }

    public function test_same_key_routes_to_same_node() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_b'));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_c'));

        $cacheKey = CacheKey::create(key: 'test_key_123');

        $node1 = $consistentHashRing->getNodeForKey(cacheKey: $cacheKey);
        $node2 = $consistentHashRing->getNodeForKey(cacheKey: $cacheKey);
        $node3 = $consistentHashRing->getNodeForKey(cacheKey: $cacheKey);

        $this->assertNotNull($node1);
        $this->assertNotNull($node2);
        $this->assertNotNull($node3);

        $this->assertEquals($node1->id->toString(), $node2->id->toString());
        $this->assertEquals($node2->id->toString(), $node3->id->toString());
    }

    public function test_different_keys_distribute_across_nodes() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_b'));

        $keys = [
            'user:1', 'user:2', 'user:3', 'user:4', 'user:5',
            'user:6', 'user:7', 'user:8', 'user:9', 'user:10',
        ];

        $distribution = [];

        foreach ($keys as $keyStr) {
            $key  = CacheKey::create(key: $keyStr);
            $node = $consistentHashRing->getNodeForKey(cacheKey: $key);

            if ($node instanceof CacheNode) {
                $nodeId                = $node->id->toString();
                $distribution[$nodeId] = ($distribution[$nodeId] ?? 0) + 1;
            }
        }

        $this->assertCount(2, $distribution);
    }

    public function test_empty_ring_returns_null() : void
    {
        $consistentHashRing = new ConsistentHashRing();

        $cacheKey = CacheKey::create(key: 'any_key');
        $node     = $consistentHashRing->getNodeForKey(cacheKey: $cacheKey);

        $this->assertNull($node);
    }

    public function test_get_node_for_partition() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_b'));

        $node = $consistentHashRing->getNodeForPartition(partitionIndex: 42);

        $this->assertNotNull($node);
    }

    public function test_node_removal_affects_routing() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_b'));

        $cacheKey = CacheKey::create(key: 'test_key');

        $nodeBefore = $consistentHashRing->getNodeForKey(cacheKey: $cacheKey);

        $consistentHashRing->removeNode(cacheNodeId: CacheNodeId::from(id: 'node_a'));

        $nodeAfter = $consistentHashRing->getNodeForKey(cacheKey: $cacheKey);

        $this->assertNotEquals($nodeBefore?->id->toString(), $nodeAfter?->id->toString());
    }

    public function test_add_node_chain_returns_self() : void
    {
        $consistentHashRing = new ConsistentHashRing();

        $result = $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));

        $this->assertSame($consistentHashRing, $result);
    }

    public function test_remove_node_chain_returns_self() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));

        $result = $consistentHashRing->removeNode(cacheNodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertSame($consistentHashRing, $result);
    }

    public function test_healthy_nodes_only_selected() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a', cacheNodeStatus: CacheNodeStatus::HEALTHY));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_b', cacheNodeStatus: CacheNodeStatus::UNHEALTHY));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_c', cacheNodeStatus: CacheNodeStatus::HEALTHY));

        $cacheKey = CacheKey::create(key: 'test_key');
        $node     = $consistentHashRing->getNodeForKey(cacheKey: $cacheKey);

        $this->assertNotNull($node);
        $this->assertEquals('node_a', $node->id->toString());
    }
}
