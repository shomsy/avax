<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Cache;

use Avax\Components\Application\Cache\System\Capabilities\Distribution\CacheNode;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\ConsistentHashRing;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeStatus;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ConsistentHashRingTest extends TestCase
{
    public function test_add_single_node_increases_physical_count() : void
    {
        $ring = new ConsistentHashRing();
        $this->assertTrue($ring->isEmpty());
        $this->assertSame(0, $ring->getPhysicalNodeCount());

        $ring->addNode($this->makeNode('node-a'));

        $this->assertFalse($ring->isEmpty());
        $this->assertSame(1, $ring->getPhysicalNodeCount());
    }

    // --- Adding nodes to the ring ---

    private function makeNode(string $id, int $weight = 100, CacheNodeStatus|null $status = null) : CacheNode
    {
        return new CacheNode(
            id    : $id,
            host  : '127.0.0.1',
            port  : 6379,
            weight: $weight,
            status: $status ?? CacheNodeStatus::HEALTHY
        );
    }

    public function test_add_multiple_nodes_increases_physical_count() : void
    {
        $ring = new ConsistentHashRing();

        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));
        $ring->addNode($this->makeNode('node-c'));

        $this->assertSame(3, $ring->getPhysicalNodeCount());
    }

    public function test_add_node_returns_self_for_chaining() : void
    {
        $ring = new ConsistentHashRing();
        $node = $this->makeNode('node-a');

        $result = $ring->addNode($node);

        $this->assertSame($ring, $result);
    }

    public function test_adding_same_node_twice_overwrites() : void
    {
        $ring = new ConsistentHashRing();
        $node = $this->makeNode('node-a');

        $ring->addNode($node);
        $ring->addNode($node);

        $this->assertSame(1, $ring->getPhysicalNodeCount());
    }

    public function test_virtual_node_count_scales_with_weight() : void
    {
        $ring = new ConsistentHashRing(virtualNodesPerWeightUnit: 100);

        // CacheNode virtualNodeCount = (weight / 100) * 150 (DEFAULT_VIRTUAL_NODES)
        $node1 = $this->makeNode('node-a', weight: 100);  // (100/100)*150 = 150
        $node2 = $this->makeNode('node-b', weight: 200);  // (200/100)*150 = 300

        $ring->addNode($node1);
        $initialVirtualCount = $ring->getVirtualNodeCount();

        $ring->addNode($node2);
        $finalVirtualCount = $ring->getVirtualNodeCount();

        $node2VirtualCount = $finalVirtualCount - $initialVirtualCount;

        $this->assertSame(150, $node1->virtualNodeCount());
        $this->assertSame(300, $node2->virtualNodeCount());
    }

    // --- Removing nodes from the ring ---

    public function test_remove_node_decreases_physical_count() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));

        $this->assertSame(2, $ring->getPhysicalNodeCount());

        $ring->removeNode('node-a');

        $this->assertSame(1, $ring->getPhysicalNodeCount());
    }

    public function test_remove_node_returns_self_for_chaining() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('node-a'));

        $result = $ring->removeNode('node-a');

        $this->assertSame($ring, $result);
    }

    public function test_remove_nonexistent_node_is_noop() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('node-a'));

        $ring->removeNode('nonexistent-node');

        $this->assertSame(1, $ring->getPhysicalNodeCount());
    }

    public function test_remove_all_nodes_makes_ring_empty() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));

        $ring->removeNode('node-a');
        $ring->removeNode('node-b');

        $this->assertTrue($ring->isEmpty());
        $this->assertSame(0, $ring->getPhysicalNodeCount());
        $this->assertSame(0, $ring->getVirtualNodeCount());
    }

    // --- getNode(key) returns consistent node ---

    public function test_same_key_always_returns_same_node() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));
        $ring->addNode($this->makeNode('node-c'));

        $key = 'user:1234';

        $node1 = $ring->getNode($key);
        $node2 = $ring->getNode($key);
        $node3 = $ring->getNode($key);

        $this->assertSame($node1->id, $node2->id);
        $this->assertSame($node2->id, $node3->id);
    }

    public function test_deterministic_hashing_same_key_same_node_every_time() : void
    {
        $ring1 = new ConsistentHashRing();
        $ring1->addNode($this->makeNode('node-a'));
        $ring1->addNode($this->makeNode('node-b'));

        $ring2 = new ConsistentHashRing();
        $ring2->addNode($this->makeNode('node-a'));
        $ring2->addNode($this->makeNode('node-b'));

        $key = 'session:abc123';

        $this->assertSame(
            $ring1->getNode($key)->id,
            $ring2->getNode($key)->id
        );
    }

    public function test_get_node_on_single_node_ring() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('only-node'));

        $node = $ring->getNode('any-key');

        $this->assertSame('only-node', $node->id);
    }

    public function test_get_node_on_empty_ring_throws_exception() : void
    {
        $ring = new ConsistentHashRing();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot get node: hash ring is empty');

        $ring->getNode('some-key');
    }

    // --- Different keys distribute across nodes ---

    public function test_different_keys_distribute_across_multiple_nodes() : void
    {
        $ring = new ConsistentHashRing(virtualNodesPerWeightUnit: 150);
        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));
        $ring->addNode($this->makeNode('node-c'));

        $distribution = [];

        for ($i = 0; $i < 1000; $i++) {
            $key                     = "key:{$i}";
            $node                    = $ring->getNode($key);
            $distribution[$node->id] = ($distribution[$node->id] ?? 0) + 1;
        }

        // All three nodes should receive some keys
        $this->assertCount(3, $distribution);

        // Each node should have at least 10% of keys
        foreach ($distribution as $nodeId => $count) {
            $percentage = ($count / 1000) * 100;
            $this->assertGreaterThan(10, $percentage, "Node {$nodeId} got less than 10% of keys: {$percentage}%");
        }
    }

    public function test_distribution_with_1000_keys_across_3_nodes() : void
    {
        $ring = new ConsistentHashRing(virtualNodesPerWeightUnit: 150);
        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));
        $ring->addNode($this->makeNode('node-c'));

        $distribution = ['node-a' => 0, 'node-b' => 0, 'node-c' => 0];

        for ($i = 0; $i < 1000; $i++) {
            $key  = "item:{$i}";
            $node = $ring->getNode($key);
            $distribution[$node->id]++;
        }

        // Each node should have roughly 33% (allow 15%-50% range)
        foreach ($distribution as $nodeId => $count) {
            $percentage = ($count / 1000) * 100;
            $this->assertGreaterThanOrEqual(15, $percentage, "Node {$nodeId} under-represented: {$percentage}%");
            $this->assertLessThanOrEqual(50, $percentage, "Node {$nodeId} over-represented: {$percentage}%");
        }

        // All keys must be accounted for
        $this->assertSame(1000, array_sum($distribution));
    }

    // --- Virtual nodes affect distribution evenness ---

    public function test_higher_virtual_nodes_per_weight_improves_evenness() : void
    {
        $ringLow = new ConsistentHashRing(virtualNodesPerWeightUnit: 10);
        $ringLow->addNode($this->makeNode('node-a'));
        $ringLow->addNode($this->makeNode('node-b'));
        $ringLow->addNode($this->makeNode('node-c'));

        $ringHigh = new ConsistentHashRing(virtualNodesPerWeightUnit: 200);
        $ringHigh->addNode($this->makeNode('node-a'));
        $ringHigh->addNode($this->makeNode('node-b'));
        $ringHigh->addNode($this->makeNode('node-c'));

        $countDistribution = function (ConsistentHashRing $ring, int $sampleSize) : float {
            $dist = [];
            for ($i = 0; $i < $sampleSize; $i++) {
                $node            = $ring->getNode("key:{$i}");
                $dist[$node->id] = ($dist[$node->id] ?? 0) + 1;
            }
            $percentages = array_map(fn ($c) => ($c / $sampleSize) * 100, $dist);

            return max($percentages) - min($percentages);
        };

        $spreadLow  = $countDistribution($ringLow, 1000);
        $spreadHigh = $countDistribution($ringHigh, 1000);

        $this->assertLessThan(60, $spreadHigh, "High virtual node spread too large: {$spreadHigh}");
    }

    public function test_virtual_node_count_reflects_weight() : void
    {
        // CacheNode virtualNodeCount = (weight / 100) * 150
        $lightNode = $this->makeNode('light', weight: 50);   // (50/100)*150 = 75
        $heavyNode = $this->makeNode('heavy', weight: 300);  // (300/100)*150 = 450

        $this->assertSame(75, $lightNode->virtualNodeCount());
        $this->assertSame(450, $heavyNode->virtualNodeCount());
    }

    // --- getNodes(key, count) for replication ---

    public function test_getNodes_returns_requested_count() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));
        $ring->addNode($this->makeNode('node-c'));

        $nodes = $ring->getNodes('key:1', 2);

        $this->assertCount(2, $nodes);
    }

    public function test_getNodes_returns_different_physical_nodes() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));
        $ring->addNode($this->makeNode('node-c'));
        $ring->addNode($this->makeNode('node-d'));

        $nodes = $ring->getNodes('key:replication', 3);

        $ids       = array_map(fn ($n) => $n->id, $nodes);
        $uniqueIds = array_unique($ids);

        $this->assertCount(3, $uniqueIds, 'getNodes should return distinct physical nodes');
    }

    public function test_getNodes_with_count_equal_to_physical_nodes() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));
        $ring->addNode($this->makeNode('node-c'));

        $nodes = $ring->getNodes('key:all', 3);

        $this->assertCount(3, $nodes);
    }

    public function test_getNodes_throws_when_count_exceeds_physical_nodes() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot get 3 nodes: only 2 physical nodes available');

        $ring->getNodes('key:fail', 3);
    }

    public function test_getNodes_on_empty_ring_throws() : void
    {
        $ring = new ConsistentHashRing();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot get nodes: hash ring is empty');

        $ring->getNodes('key:fail', 1);
    }

    public function test_getNodes_single_node_ring() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('solo'));

        $nodes = $ring->getNodes('key:1', 1);

        $this->assertCount(1, $nodes);
        $this->assertSame('solo', $nodes[0]->id);
    }

    public function test_getNodes_consistent_for_same_key() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));
        $ring->addNode($this->makeNode('node-c'));

        $nodes1 = $ring->getNodes('key:consistent', 2);
        $nodes2 = $ring->getNodes('key:consistent', 2);

        $ids1 = array_map(fn ($n) => $n->id, $nodes1);
        $ids2 = array_map(fn ($n) => $n->id, $nodes2);

        $this->assertSame($ids1, $ids2);
    }

    public function test_different_keys_return_different_node_sets() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));
        $ring->addNode($this->makeNode('node-c'));
        $ring->addNode($this->makeNode('node-d'));
        $ring->addNode($this->makeNode('node-e'));

        $nodes1 = $ring->getNodes('key:alpha', 3);
        $nodes2 = $ring->getNodes('key:beta', 3);

        $ids1 = array_map(fn ($n) => $n->id, $nodes1);
        $ids2 = array_map(fn ($n) => $n->id, $nodes2);

        $this->assertNotSame($ids1, $ids2);
    }

    // --- getAllNodes ---

    public function test_getAllNodes_returns_all_physical_nodes() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));

        $all = $ring->getAllNodes();

        $this->assertCount(2, $all);
        $this->assertArrayHasKey('node-a', $all);
        $this->assertArrayHasKey('node-b', $all);
    }

    public function test_getAllNodes_returns_empty_on_empty_ring() : void
    {
        $ring = new ConsistentHashRing();

        $this->assertSame([], $ring->getAllNodes());
    }

    // --- Edge cases ---

    public function test_remove_node_redistributes_affected_keys() : void
    {
        $ring = new ConsistentHashRing();
        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));

        // Find a key that maps to node-a
        $keyForA = null;
        for ($i = 0; $i < 100; $i++) {
            $key = "test:{$i}";
            if ($ring->getNode($key)->id === 'node-a') {
                $keyForA = $key;
                break;
            }
        }

        $this->assertNotNull($keyForA, 'Should find a key mapping to node-a');

        $nodeBefore = $ring->getNode($keyForA);
        $this->assertSame('node-a', $nodeBefore->id);

        $ring->removeNode('node-a');

        $nodeAfter = $ring->getNode($keyForA);
        $this->assertSame('node-b', $nodeAfter->id);
    }

    public function test_ring_with_many_nodes() : void
    {
        $ring = new ConsistentHashRing(virtualNodesPerWeightUnit: 50);

        for ($i = 0; $i < 20; $i++) {
            $ring->addNode($this->makeNode("node-{$i}"));
        }

        $this->assertSame(20, $ring->getPhysicalNodeCount());
        // Each node has (100/100)*150 = 150 virtual nodes, so 20 * 150 = 3000
        $this->assertSame(3000, $ring->getVirtualNodeCount());

        $node = $ring->getNode('some-key');
        $this->assertNotNull($node);
    }

    public function test_multiple_add_and_remove_cycles() : void
    {
        $ring = new ConsistentHashRing();

        $ring->addNode($this->makeNode('node-a'));
        $ring->addNode($this->makeNode('node-b'));
        $ring->removeNode('node-a');
        $ring->addNode($this->makeNode('node-c'));
        $ring->removeNode('node-b');
        $ring->addNode($this->makeNode('node-a'));

        $this->assertSame(2, $ring->getPhysicalNodeCount());

        $nodes = $ring->getAllNodes();
        $this->assertArrayHasKey('node-a', $nodes);
        $this->assertArrayHasKey('node-c', $nodes);
        $this->assertArrayNotHasKey('node-b', $nodes);
    }

    public function test_node_weights_affect_distribution_percentage() : void
    {
        $ring = new ConsistentHashRing(virtualNodesPerWeightUnit: 100);

        $ring->addNode($this->makeNode('light', weight: 100));
        $ring->addNode($this->makeNode('heavy', weight: 300));

        $distribution = [];
        for ($i = 0; $i < 2000; $i++) {
            $node                    = $ring->getNode("weight-test:{$i}");
            $distribution[$node->id] = ($distribution[$node->id] ?? 0) + 1;
        }

        $lightPct = ($distribution['light'] ?? 0) / 2000;
        $heavyPct = ($distribution['heavy'] ?? 0) / 2000;

        // Heavy node (3x weight) should get more keys
        $this->assertGreaterThan(0.10, $lightPct, 'Light node should get at least 10%');
        $this->assertLessThan(0.45, $lightPct, 'Light node should get less than 45%');
        $this->assertGreaterThan(0.55, $heavyPct, 'Heavy node should get more than 55%');
        $this->assertLessThan(0.90, $heavyPct, 'Heavy node should get less than 90%');
    }
}
