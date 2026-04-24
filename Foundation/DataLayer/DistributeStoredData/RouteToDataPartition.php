<?php

declare(strict_types=1);

namespace Avax\DataLayer\DistributeStoredData;

use InvalidArgumentException;

final readonly class RouteToDataPartition
{
    public function __construct(
        private DataPartitionRoute $route,
        private ShardKey           $shardKey
    ) {}

    public function describeResponsibility() : string
    {
        return 'routes data to a partition based on shard key.';
    }

    public function route(mixed $keyValue) : PartitionRouteResult
    {
        if ($keyValue === null) {
            throw new InvalidArgumentException('Partition key value cannot be null.');
        }

        $shard = $this->shardKey->calculateShard($keyValue);
        $key   = $this->buildKey($shard, $keyValue);

        return $this->route->route($key);
    }

    private function buildKey(int $shard, mixed $value) : string
    {
        return sprintf('%d:%s', $shard, serialize($value));
    }

    public function toMetadata() : array
    {
        return [
            'route'     => $this->route->toMetadata(),
            'shard_key' => $this->shardKey->toMetadata(),
        ];
    }
}