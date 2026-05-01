<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\Tests\Unit\Cache\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNode;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeId;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\ConsistentHashRing;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\RebalanceCachePartitions;
use PHPUnit\Framework\TestCase;

final class RebalanceCachePartitionsTest extends TestCase
{
    public function test_add_node_calculates_partition_moves() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));

        $rebalanceCachePartitions = new RebalanceCachePartitions(partitionCount: 256, ring: $consistentHashRing);

        $moves = $rebalanceCachePartitions->addNode(node: CacheNode::create(id: 'node_b'));

        $this->assertIsArray(actual: $moves);
    }

    public function test_remove_node_calculates_partition_moves() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_b'));

        $rebalanceCachePartitions = new RebalanceCachePartitions(partitionCount: 256, ring: $consistentHashRing);

        $moves = $rebalanceCachePartitions->removeNode(nodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertIsArray(actual: $moves);
    }

    public function test_rebalance_returns_all_partitions() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_b'));

        $rebalanceCachePartitions = new RebalanceCachePartitions(partitionCount: 256, ring: $consistentHashRing);

        $distribution = $rebalanceCachePartitions->rebalance();

        $this->assertCount(expectedCount: 256, haystack: $distribution);
    }

    public function test_partition_moves_have_from_and_to() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_b'));
        $consistentHashRing->addNode(node: CacheNode::create(id: 'node_c'));

        $rebalanceCachePartitions = new RebalanceCachePartitions(partitionCount: 256, ring: $consistentHashRing);

        $moves = $rebalanceCachePartitions->addNode(node: CacheNode::create(id: 'node_d'));

        foreach ($moves as $move) {
            $this->assertArrayHasKey(key: 'from', array: $move);
            $this->assertArrayHasKey(key: 'to', array: $move);
            $this->assertEquals(expected: 'node_d', actual: $move['to']);

            break;
        }
    }

    public function test_empty_ring_returns_empty_distribution() : void
    {
        $consistentHashRing = new ConsistentHashRing();

        $rebalanceCachePartitions = new RebalanceCachePartitions(partitionCount: 256, ring: $consistentHashRing);

        $distribution = $rebalanceCachePartitions->rebalance();

        $this->assertEmpty(actual: $distribution);
    }
}
