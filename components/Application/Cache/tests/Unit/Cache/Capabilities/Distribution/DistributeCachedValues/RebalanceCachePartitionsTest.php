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
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));

        $rebalancer = new RebalanceCachePartitions(ring: $ring, partitionCount: 256);

        $moves = $rebalancer->addNode(node: CacheNode::create(id: 'node_b'));

        $this->assertIsArray(actual: $moves);
    }

    public function test_remove_node_calculates_partition_moves() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));
        $ring->addNode(node: CacheNode::create(id: 'node_b'));

        $rebalancer = new RebalanceCachePartitions(ring: $ring, partitionCount: 256);

        $moves = $rebalancer->removeNode(nodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertIsArray(actual: $moves);
    }

    public function test_rebalance_returns_all_partitions() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));
        $ring->addNode(node: CacheNode::create(id: 'node_b'));

        $rebalancer = new RebalanceCachePartitions(ring: $ring, partitionCount: 256);

        $distribution = $rebalancer->rebalance();

        $this->assertCount(expectedCount: 256, haystack: $distribution);
    }

    public function test_partition_moves_have_from_and_to() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode(node: CacheNode::create(id: 'node_a'));
        $ring->addNode(node: CacheNode::create(id: 'node_b'));
        $ring->addNode(node: CacheNode::create(id: 'node_c'));

        $rebalancer = new RebalanceCachePartitions(ring: $ring, partitionCount: 256);

        $moves = $rebalancer->addNode(node: CacheNode::create(id: 'node_d'));

        foreach ($moves as $partition => $move) {
            $this->assertArrayHasKey(key: 'from', array: $move);
            $this->assertArrayHasKey(key: 'to', array: $move);
            $this->assertEquals(expected: 'node_d', actual: $move['to']);
            break;
        }
    }

    public function test_empty_ring_returns_empty_distribution() : void
    {
        $ring = new ConsistentHashRing();

        $rebalancer = new RebalanceCachePartitions(ring: $ring, partitionCount: 256);

        $distribution = $rebalancer->rebalance();

        $this->assertEmpty(actual: $distribution);
    }
}