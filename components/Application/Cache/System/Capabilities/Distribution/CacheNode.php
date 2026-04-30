<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution;

use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeStatus;
use Override;
use Stringable;

/**
 * Value object representing a cache node in a distributed cache cluster.
 */
final readonly class CacheNode implements Stringable
{
    public const int DEFAULT_VIRTUAL_NODES = 150;

    public function __construct(
        public string $id,
        public string $host,
        public int $port,
        public int             $weight = 100,
        public CacheNodeStatus $cacheNodeStatus = CacheNodeStatus::HEALTHY,
        public int|null        $virtualNodeCount = null,
    ) {}

    /**
     * Create a new cache node with default values.
     */
    public static function create(
        string          $id,
        string          $host,
        int             $port,
        int             $weight = 100,
        CacheNodeStatus $cacheNodeStatus = CacheNodeStatus::HEALTHY,
        ?int            $virtualNodeCount = null,
    ) : self
    {
        return new self(
            id              : $id,
            host            : $host,
            port            : $port,
            weight          : $weight,
            status          : $cacheNodeStatus,
            virtualNodeCount: $virtualNodeCount,
        );
    }

    /**
     * Get the effective virtual node count for this node.
     *
     * If not explicitly set, calculates based on weight:
     * (weight / 100) * DEFAULT_VIRTUAL_NODES
     */
    public function virtualNodeCount() : int
    {
        if ($this->virtualNodeCount !== null) {
            return $this->virtualNodeCount;
        }

        return (int) (($this->weight / 100) * self::DEFAULT_VIRTUAL_NODES);
    }

    /**
     * Check if the node is available for use.
     */
    public function isAvailable() : bool
    {
        return $this->cacheNodeStatus->isAvailable();
    }

    /**
     * Get the connection string for this node.
     */
    public function connectionString() : string
    {
        return sprintf('%s:%d', $this->host, $this->port);
    }

    /**
     * Create a copy of this node with a different status.
     */
    public function withStatus(CacheNodeStatus $cacheNodeStatus) : self
    {
        return new self(
            id              : $this->id,
            host            : $this->host,
            port            : $this->port,
            weight          : $this->weight,
            status          : $cacheNodeStatus,
            virtualNodeCount: $this->virtualNodeCount,
        );
    }

    /**
     * Create a copy of this node with a different weight.
     */
    public function withWeight(int $weight) : self
    {
        return new self(
            id              : $this->id,
            host            : $this->host,
            port            : $this->port,
            weight          : $weight,
            status          : $this->cacheNodeStatus,
            virtualNodeCount: $this->virtualNodeCount,
        );
    }

    /**
     * Convert to array representation.
     *
     * @return array{
     *     id: string,
     *     host: string,
     *     port: int,
     *     weight: int,
     *     status: string,
     *     virtualNodeCount: int
     * }
     */
    public function toArray() : array
    {
        return [
            'id'               => $this->id,
            'host'             => $this->host,
            'port'             => $this->port,
            'weight'           => $this->weight,
            'status' => $this->cacheNodeStatus->value,
            'virtualNodeCount' => $this->virtualNodeCount(),
        ];
    }

    /**
     * Create from array representation.
     *
     * @param array{
     *     id: string,
     *     host: string,
     *     port: int,
     *     weight?: int,
     *     status?: string,
     *     virtualNodeCount?: int|null
     * } $data
     */
    public static function fromArray(array $data) : self
    {
        $status = isset($data['status'])
            ? CacheNodeStatus::from($data['status'])
            : CacheNodeStatus::HEALTHY;

        return new self(
            id              : $data['id'],
            host            : $data['host'],
            port            : $data['port'],
            weight          : $data['weight'] ?? 100,
            status          : $status,
            virtualNodeCount: $data['virtualNodeCount'] ?? null,
        );
    }

    #[Override]
    public function __toString() : string
    {
        return sprintf(
            'CacheNode{id: %s, host: %s:%d, weight: %d, status: %s}',
            $this->id,
            $this->host,
            $this->port,
            $this->weight,
            $this->cacheNodeStatus->value,
        );
    }
}
