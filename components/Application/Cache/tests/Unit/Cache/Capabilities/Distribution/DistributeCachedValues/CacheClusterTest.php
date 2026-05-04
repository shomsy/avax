<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\tests\Unit\Cache\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheCluster;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNode;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeId;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeStatus;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\ConsistentHashRing;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use PHPUnit\Framework\TestCase;

final class CacheClusterTest extends TestCase
{
    public function test_add_node_registers_healthy_node(): void
    {
        $cacheCluster = new CacheCluster();

        $cacheCluster->addNode(node: CacheNode::create(id: 'node_a'));

        $node = $cacheCluster->getNode(nodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertNotNull(actual: $node);
        $this->assertEquals(expected: 'node_a', actual: $node->id->toString());
        $this->assertTrue(condition: $node->isHealthy());
    }

    public function test_get_healthy_nodes_returns_only_healthy(): void
    {
        $cacheCluster = new CacheCluster(virtualNodes: 3);

        $cacheCluster->addNode(node: CacheNode::create(id: 'node_a'));
        $cacheCluster->addNode(node: CacheNode::create(id: 'node_b'));

        $cacheCluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_b'));
        $cacheCluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_b'));
        $cacheCluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_b'));

        $healthyNodes = $cacheCluster->getHealthyNodes();

        $this->assertCount(expectedCount: 1, haystack: $healthyNodes);
    }

    public function test_record_failure_marks_node_unhealthy(): void
    {
        $cacheCluster = new CacheCluster(virtualNodes: 3);

        $cacheCluster->addNode(node: CacheNode::create(id: 'node_a'));

        $cacheCluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_a'));
        $cacheCluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_a'));
        $cacheCluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_a'));

        $node = $cacheCluster->getNode(nodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertNotNull(actual: $node);
        $this->assertEquals(expected: CacheNodeStatus::UNHEALTHY, actual: $node->status);
    }

    public function test_record_success_recovers_node(): void
    {
        $cacheCluster = new CacheCluster(virtualNodes: 3);

        $cacheCluster->addNode(node: CacheNode::create(id: 'node_a'));

        for ($i = 0; $i < 3; $i++) {
            $cacheCluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_a'));
        }

        for ($i = 0; $i < 5; $i++) {
            $cacheCluster->recordSuccess(nodeId: CacheNodeId::from(id: 'node_a'));
        }

        $node = $cacheCluster->getNode(nodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertNotNull(actual: $node);
        $this->assertEquals(expected: CacheNodeStatus::HEALTHY, actual: $node->status);
    }

    public function test_unhealthy_node_not_selected_for_reads(): void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_b'));

        $cacheCluster = new CacheCluster(virtualNodes: 3);
        $cacheCluster->addNode(node: CacheNode::create(id: 'node_a'));
        $cacheCluster->addNode(node: CacheNode::create(id: 'node_b'));

        for ($i = 0; $i < 3; $i++) {
            $cacheCluster->recordFailure(nodeId: CacheNodeId::from(id: 'node_a'));
        }

        $node = $cacheCluster->getNodeForKey(key: CacheKey::create(key: 'test_key'));

        $this->assertNotNull(actual: $node);
        $this->assertEquals(expected: 'node_b', actual: $node->id->toString());
    }

    public function test_remove_node_reduces_count(): void
    {
        $cacheCluster = new CacheCluster();

        $cacheCluster->addNode(node: CacheNode::create(id: 'node_a'));
        $cacheCluster->addNode(node: CacheNode::create(id: 'node_b'));

        $this->assertEquals(expected: 2, actual: $cacheCluster->nodeCount());

        $cacheCluster->removeNode(nodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertEquals(expected: 1, actual: $cacheCluster->nodeCount());
    }

    public function test_node_addition_affects_routing(): void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));

        $cacheCluster = new CacheCluster();
        $cacheCluster->addNode(node: CacheNode::create(id: 'node_a'));

        $nodeBefore = $cacheCluster->getNodeForKey(key: CacheKey::create(key: 'test_key'));

        $cacheCluster->addNode(node: CacheNode::create(id: 'node_b'));

        $nodeAfter = $cacheCluster->getNodeForKey(key: CacheKey::create(key: 'test_key'));

        $this->assertNotNull(actual: $nodeBefore);
        $this->assertNotNull(actual: $nodeAfter);
    }
}
