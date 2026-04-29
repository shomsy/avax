<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Unit\Cache\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheCluster;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNode;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeId;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeStatus;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\ConsistentHashRing;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use PHPUnit\Framework\TestCase;

final class CacheClusterTest extends TestCase
{
    public function test_add_node_registers_healthy_node() : void
    {
        $cluster = new CacheCluster();

        $cluster->addNode(node: CacheNode::create(id: 'node_a'));

        $node = $cluster->getNode(nodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertNotNull(actual: $node);
        $this->assertEquals(expected: 'node_a', actual: $node->id->toString());
        $this->assertTrue(condition: $node->isHealthy());
    }

    public function test_get_healthy_nodes_returns_only_healthy() : void
    {
        $cluster = new CacheCluster(virtualNodes: 3);

        $cluster->addNode(node: CacheNode::create(id: 'node_a'));
        $cluster->addNode(node: CacheNode::create(id: 'node_b'));

        $cluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_b'));
        $cluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_b'));
        $cluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_b'));

        $healthyNodes = $cluster->getHealthyNodes();

        $this->assertCount(expectedCount: 1, haystack: $healthyNodes);
    }

    public function test_record_failure_marks_node_unhealthy() : void
    {
        $cluster = new CacheCluster(virtualNodes: 3);

        $cluster->addNode(node: CacheNode::create(id: 'node_a'));

        $cluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_a'));
        $cluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_a'));
        $cluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_a'));

        $node = $cluster->getNode(nodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertNotNull(actual: $node);
        $this->assertEquals(expected: CacheNodeStatus::UNHEALTHY, actual: $node->status);
    }

    public function test_record_success_recovers_node() : void
    {
        $cluster = new CacheCluster(virtualNodes: 3);

        $cluster->addNode(node: CacheNode::create(id: 'node_a'));

        for ($i = 0; $i < 3; $i++) {
            $cluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_a'));
        }

        for ($i = 0; $i < 5; $i++) {
            $cluster->recordSuccess(nodeId: CacheNodeId::from(id: 'node_a'));
        }

        $node = $cluster->getNode(nodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertNotNull(actual: $node);
        $this->assertEquals(expected: CacheNodeStatus::HEALTHY, actual: $node->status);
    }

    public function test_unhealthy_node_not_selected_for_reads() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));
        $ring->addNode(node: CacheNode::create(id: 'node_b'));

        $cluster = new CacheCluster(virtualNodes: 3);
        $cluster->addNode(node: CacheNode::create(id: 'node_a'));
        $cluster->addNode(node: CacheNode::create(id: 'node_b'));

        for ($i = 0; $i < 3; $i++) {
            $cluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_a'));
        }

        $node = $cluster->getNodeForKey(key: CacheKey::create(key: 'test_key'));

        $this->assertNotNull(actual: $node);
        $this->assertEquals(expected: 'node_b', actual: $node->id->toString());
    }

    public function test_remove_node_reduces_count() : void
    {
        $cluster = new CacheCluster();

        $cluster->addNode(node: CacheNode::create(id: 'node_a'));
        $cluster->addNode(node: CacheNode::create(id: 'node_b'));

        $this->assertEquals(expected: 2, actual: $cluster->nodeCount());

        $cluster->removeNode(nodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertEquals(expected: 1, actual: $cluster->nodeCount());
    }

    public function test_node_addition_affects_routing() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));

        $cluster = new CacheCluster();
        $cluster->addNode(node: CacheNode::create(id: 'node_a'));

        $nodeBefore = $cluster->getNodeForKey(key: CacheKey::create(key: 'test_key'));

        $cluster->addNode(node: CacheNode::create(id: 'node_b'));

        $nodeAfter = $cluster->getNodeForKey(key: CacheKey::create(key: 'test_key'));

        $this->assertNotNull(actual: $nodeBefore);
        $this->assertNotNull(actual: $nodeAfter);
    }
}