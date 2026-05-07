<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Capabilities\Distribution\DistributeCachedValues;

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
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));

        $rebalanceCachePartitions = new RebalanceCachePartitions(partitionCount: 256, consistentHashRing: $consistentHashRing);

        $moves = $rebalanceCachePartitions->addNode(cacheNode: CacheNode::create(id: 'node_b'));

        $this->assertIsArray($moves);
    }

    public function test_remove_node_calculates_partition_moves() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_b'));

        $rebalanceCachePartitions = new RebalanceCachePartitions(partitionCount: 256, consistentHashRing: $consistentHashRing);

        $moves = $rebalanceCachePartitions->removeNode(cacheNodeId: CacheNodeId::from(id: 'node_a'));

        $this->assertIsArray($moves);
    }

    public function test_rebalance_returns_all_partitions() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_b'));

        $rebalanceCachePartitions = new RebalanceCachePartitions(partitionCount: 256, consistentHashRing: $consistentHashRing);

        $distribution = $rebalanceCachePartitions->rebalance();

        $this->assertCount(256, $distribution);
    }

    public function test_partition_moves_have_from_and_to() : void
    {
        $consistentHashRing = new ConsistentHashRing();
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_a'));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_b'));
        $consistentHashRing->addNode(cacheNode: CacheNode::create(id: 'node_c'));

        $rebalanceCachePartitions = new RebalanceCachePartitions(partitionCount: 256, consistentHashRing: $consistentHashRing);

        $moves = $rebalanceCachePartitions->addNode(cacheNode: CacheNode::create(id: 'node_d'));

        foreach ($moves as $move) {
            $this->assertArrayHasKey(key: 'from', array: $move);
            $this->assertArrayHasKey(key: 'to', array: $move);
            $this->assertEquals('node_d', $move['to']);

            break;
        }
    }

    public function test_empty_ring_returns_empty_distribution() : void
    {
        $consistentHashRing = new ConsistentHashRing();

        $rebalanceCachePartitions = new RebalanceCachePartitions(partitionCount: 256, consistentHashRing: $consistentHashRing);

        $distribution = $rebalanceCachePartitions->rebalance();

        $this->assertEmpty($distribution);
    }
}
