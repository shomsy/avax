<?php

declare(strict_types=1);

namespace Avax\DataLayer\DistributeStoredData;

final readonly class DescribeShardKey
{
    public function __construct(
        public string $name,
        public array  $columns,
        public string $algorithm,
        public int    $virtualNodes
    ) {}

    public static function forTenant() : self
    {
        return new self(
            name        : 'tenant_shard_key',
            columns     : ['tenant_id'],
            algorithm   : 'hash',
            virtualNodes: 150
        );
    }

    public static function compound(string ...$columns) : self
    {
        return new self(
            name        : implode('_', $columns) . '_shard_key',
            columns     : $columns,
            algorithm   : 'composite_hash',
            virtualNodes: 150
        );
    }

    public function describeResponsibility() : string
    {
        return 'describes shard key including name, columns, algorithm, and virtual node count.';
    }

    public function toMetadata() : array
    {
        return [
            'name'          => $this->name,
            'columns'       => $this->columns,
            'algorithm'     => $this->algorithm,
            'virtual_nodes' => $this->virtualNodes,
        ];
    }
}