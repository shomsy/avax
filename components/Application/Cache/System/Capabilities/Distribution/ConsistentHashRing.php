<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution;

use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNode;
use RuntimeException;

/**
 * Consistent hash ring implementation for distributed cache node selection.
 *
 * Uses virtual nodes for better distribution across physical nodes.
 * Each physical node is represented by multiple virtual nodes on the ring,
 * weighted by the node's weight property for uneven capacity distribution.
 */
final class ConsistentHashRing
{
    /** @var array<int, CacheNode> */
    private array $ring = [];

    /** @var array<string, CacheNode> */
    private array $physicalNodes = [];

    /**
     * Add a cache node to the hash ring.
     */
    public function addNode(CacheNode $cacheNode): self
    {
        $nodeId = $cacheNode->id->toString();

        $this->physicalNodes[$nodeId] = $cacheNode;

        $virtualNodeCount = max(1, (int) round(150 * $cacheNode->weight));

        for ($i = 0; $i < $virtualNodeCount; $i++) {
            $hash = $this->hash($nodeId.'#'.$i);
            $this->ring[$hash] = $cacheNode;
        }

        ksort($this->ring);

        return $this;
    }

    /**
     * Remove a cache node from the hash ring.
     */
    public function removeNode(string $nodeId): self
    {
        if (! isset($this->physicalNodes[$nodeId])) {
            return $this;
        }

        $node = $this->physicalNodes[$nodeId];
        $virtualNodeCount = max(1, (int) round(150 * $node->weight));

        for ($i = 0; $i < $virtualNodeCount; $i++) {
            $hash = $this->hash($nodeId.'#'.$i);
            unset($this->ring[$hash]);
        }

        unset($this->physicalNodes[$nodeId]);

        return $this;
    }

    /**
     * Get the node responsible for a given key.
     *
     * @throws RuntimeException if the ring has no nodes
     */
    public function getNode(string $key): CacheNode
    {
        if ($this->ring === []) {
            throw new RuntimeException('Cannot get node: hash ring is empty');
        }

        $hash = $this->hash($key);

        foreach ($this->ring as $ringHash => $node) {
            if ($hash <= $ringHash) {
                return $node;
            }
        }

        // Wrap around to the first node
        $cacheNode = reset($this->ring);

        return $cacheNode;
    }

    /**
     * Get multiple nodes responsible for a given key (for replication).
     *
     * @param  positive-int  $count  Number of nodes to return
     * @return list<CacheNode>
     *
     * @throws RuntimeException if the ring has fewer nodes than requested
     */
    public function getNodes(string $key, int $count): array
    {
        if ($this->ring === []) {
            throw new RuntimeException('Cannot get nodes: hash ring is empty');
        }

        $physicalNodeCount = count($this->physicalNodes);

        if ($count > $physicalNodeCount) {
            throw new RuntimeException(
                sprintf(
                    'Cannot get %d nodes: only %d physical nodes available',
                    $count,
                    $physicalNodeCount,
                ),
            );
        }

        $hash = $this->hash($key);
        $selected = [];
        $seen = [];

        // Walk the ring forward from the key's hash position
        $ringKeys = array_keys($this->ring);
        $ringLength = count($ringKeys);

        // Find starting position
        $startIndex = 0;
        foreach ($ringKeys as $index => $ringHash) {
            if ($hash <= $ringHash) {
                $startIndex = $index;

                break;
            }

            $startIndex = $index;
        }

        // If we're past the last hash, start from beginning
        if ($hash > end($ringKeys)) {
            $startIndex = 0;
        }

        $position = $startIndex;
        $iterations = 0;
        $maxIterations = $ringLength;

        while (count($selected) < $count && $iterations < $maxIterations) {
            $node = $this->ring[$ringKeys[$position]];

            $nodeId = $node->id->toString();

            if (! isset($seen[$nodeId])) {
                $seen[$nodeId] = true;
                $selected[] = $node;
            }

            $position = ($position + 1) % $ringLength;
            $iterations++;
        }

        return $selected;
    }

    /**
     * Get all physical nodes on the ring.
     *
     * @return array<string, CacheNode>
     */
    public function getAllNodes(): array
    {
        return $this->physicalNodes;
    }

    /**
     * Get the total number of virtual nodes on the ring.
     */
    public function getVirtualNodeCount(): int
    {
        return count($this->ring);
    }

    /**
     * Get the number of physical nodes on the ring.
     */
    public function getPhysicalNodeCount(): int
    {
        return count($this->physicalNodes);
    }

    /**
     * Check if the ring has any nodes.
     */
    public function isEmpty(): bool
    {
        return $this->physicalNodes === [];
    }

    /**
     * Hash a key using CRC32 combined with additional mixing for better distribution.
     */
    private function hash(string $key): int
    {
        $crc = crc32($key);

        // Additional mixing to improve distribution
        $mixed = $crc ^ ($crc >> 16);
        $mixed = ($mixed * 0x45D9F3B) & 0xFFFFFFFF;

        return ($mixed ^ ($mixed >> 16)) & 0xFFFFFFFF;
    }
}
