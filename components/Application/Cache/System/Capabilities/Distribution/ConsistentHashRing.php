<?php
declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution;

final class ConsistentHashRing
{
    private array $nodes = [];
    private array $ring = [];
    private int $replicas;

    public function __construct(int $replicas = 64)
    {
        $this->replicas = $replicas;
    }

    public function addNode(string $node): void
    {
        $this->nodes[] = $node;
        for ($i = 0; $i < $this->replicas; $i++) {
            $hash = crc32($node . $i);
            $this->ring[$hash] = $node;
        }
        ksort($this->ring);
    }

    public function getNode(string $key): string
    {
        if (empty($this->ring)) {
            throw new \RuntimeException("No nodes in hash ring.");
        }

        $hash = crc32($key);
        foreach ($this->ring as $nodeHash => $node) {
            if ($hash <= $nodeHash) {
                return $node;
            }
        }

        return reset($this->ring);
    }
}
