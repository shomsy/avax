<?php

declare(strict_types=1);

namespace Avax\DataLayer\DistributeStoredData;

use InvalidArgumentException;

final readonly class DataPartitionRoute
{
    public function __construct(
        public string $partitionKey,
        public int    $partitionCount,
        public array  $partitionMap
    )
    {
        if ($this->partitionCount < 1) {
            throw new InvalidArgumentException(message: 'Partition count must be at least 1.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'routes data to partitions based on key and partition count.';
    }

    public static function create(string $key, int $partitions) : self
    {
        $map = range(0, $partitions - 1);

        return new self(partitionKey: $key, partitionCount: $partitions, partitionMap: $map);
    }

    public function route(string $key) : PartitionRouteResult
    {
        $hash = $this->hashKey(key: $key);
        $index       = $hash % $this->partitionCount;
        $partitionId = $this->partitionMap[$index] ?? $index;

        return new PartitionRouteResult(
            partitionId: (string) $partitionId,
            index      : $index,
            replicas   : $this->getReplicas(partitionId: $partitionId)
        );
    }

    private function hashKey(string $key) : int
    {
        return crc32($key) & 0x7FFFFFFF;
    }

    private function getReplicas(int $partitionId) : array
    {
        $replicas     = [];
        $replicaCount = 3;

        for ($i = 0; $i < $replicaCount; $i++) {
            $replicas[] = new ReplicaInfo(
                id       : "replica_{$partitionId}_{$i}",
                endpoint : "db-{$partitionId}-{$i}.internal",
                isPrimary: $i === 0,
                lagMs    : 0
            );
        }

        return $replicas;
    }

    public function count() : int
    {
        return $this->partitionCount;
    }

    public function toMetadata() : array
    {
        return [
            'partition_key'   => $this->partitionKey,
            'partition_count' => $this->partitionCount,
        ];
    }
}

final readonly class PartitionRouteResult
{
    public function __construct(
        public string $partitionId,
        public int    $index,
        public array  $replicas
    ) {}
}

final readonly class ReplicaInfo
{
    public function __construct(
        public string $id,
        public string $endpoint,
        public bool   $isPrimary,
        public int    $lagMs
    ) {}
}