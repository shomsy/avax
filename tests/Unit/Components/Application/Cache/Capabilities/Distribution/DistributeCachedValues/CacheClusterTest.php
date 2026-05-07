<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Capabilities\Distribution\DistributeCachedValues;

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
        $cacheCluster = new CacheCluster();

        $cacheCluster->addNode(cacheNode: CacheNode::create(id: 'node_a'));

        $node = $cacheCluster->getNode(cacheNodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertNotNull($node);
        $this->assertEquals('node_a', $node->id->toString());
        $this->assertTrue($node->isHealthy());
    }

    public function test_get_healthy_nodes_returns_only_healthy() : void
    {
        $cacheCluster = new CacheCluster(virtualNodes: 3);

        $cacheCluster->addNode(cacheNode: CacheNode::create(id: 'node_a'));
        $cacheCluster->addNode(cacheNode: CacheNode::create(id: 'node_b'));

        $cacheCluster->recordFailure(cacheNodeId: CacheNodeId::from(id: 'node_b'));
        $cacheCluster->recordFailure(cacheNodeId: CacheNodeId::from(id: 'node_b'));
        $cacheCluster->recordFailure(cacheNodeId: CacheNodeId::from(id: 'node_b'));

        $healthyNodes = $cacheCluster->getHealthyNodes();

        $this->assertCount(1, $healthyNodes);
    }

    public function test_record_failure_marks_node_unhealthy() : void
    {
        $cacheCluster = new CacheCluster(virtualNodes: 3);

        $cacheCluster->addNode(cacheNode: CacheNode::create(id: 'node_a'));

        $cacheCluster->recordFailure(cacheNodeId: CacheNodeId::from(id: 'node_a'));
        $cacheCluster->recordFailure(cacheNodeId: CacheNodeId::from(id: 'node_a'));
        $cacheCluster->recordFailure(cacheNodeId: CacheNodeId::from(id: 'node_a'));

        $node = $cacheCluster->getNode(cacheNodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertNotNull($node);
        $this->assertEquals(CacheNodeStatus::UNHEALTHY, $node->status);
    }

    public function test_record_success_recovers_node() : void
    {
        $cacheCluster = new CacheCluster(virtualNodes: 3);

        $cacheCluster->addNode(cacheNode: CacheNode::create(id: 'node_a'));

        for ($i = 0; $i < 3; $i++) {
            $cacheCluster->recordFailure(cacheNodeId: CacheNodeId::from(id: 'node_a'));
        }

        for ($i = 0; $i < 5; $i++) {
            $cacheCluster->recordSuccess(cacheNodeId: CacheNodeId::from(id: 'node_a'));
        }

        $node = $cacheCluster->getNode(cacheNodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertNotNull($node);
        $this->assertEquals(CacheNodeStatus::HEALTHY, $node->status);
    }

    public function test_unhealthy_node_not_selected_for_reads() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_b'));

        $cacheCluster = new CacheCluster(virtualNodes: 3);
        $cacheCluster->addNode(cacheNode: CacheNode::create(id: 'node_a'));
        $cacheCluster->addNode(cacheNode: CacheNode::create(id: 'node_b'));

        for ($i = 0; $i < 3; $i++) {
            $cacheCluster->recordFailure(cacheNodeId: CacheNodeId::from(id: 'node_a'));
        }

        $node = $cacheCluster->getNodeForKey(cacheKey: CacheKey::create(key: 'test_key'));

        $this->assertNotNull($node);
        $this->assertEquals('node_b', $node->id->toString());
    }

    public function test_remove_node_reduces_count() : void
    {
        $cacheCluster = new CacheCluster();

        $cacheCluster->addNode(cacheNode: CacheNode::create(id: 'node_a'));
        $cacheCluster->addNode(cacheNode: CacheNode::create(id: 'node_b'));

        $this->assertEquals(2, $cacheCluster->nodeCount());

        $cacheCluster->removeNode(cacheNodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertEquals(1, $cacheCluster->nodeCount());
    }

    public function test_node_addition_affects_routing() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));

        $cacheCluster = new CacheCluster();
        $cacheCluster->addNode(cacheNode: CacheNode::create(id: 'node_a'));

        $nodeBefore = $cacheCluster->getNodeForKey(cacheKey: CacheKey::create(key: 'test_key'));

        $cacheCluster->addNode(cacheNode: CacheNode::create(id: 'node_b'));

        $nodeAfter = $cacheCluster->getNodeForKey(cacheKey: CacheKey::create(key: 'test_key'));

        $this->assertNotNull($nodeBefore);
        $this->assertNotNull($nodeAfter);
    }
}
