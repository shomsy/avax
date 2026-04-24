<?php

declare(strict_types=1);

namespace Avax\DataLayer\DistributeStoredData;

final readonly class DistributeStoredData
{
    public function __construct(
        private DataPartitionRoute $partitionRoute,
        private ChooseReadReplica  $readReplica,
        private ConsistentHashRing $hashRing
    ) {}

    public function describeResponsibility() : string
    {
        return 'groups replica, partition, shard, and consistent hash routing decisions.';
    }

    public function routeWrite(string $key) : DataRouteResult
    {
        $partition = $this->partitionRoute->route($key);
        $replica   = $this->readReplica->selectReplica($partition->replicas);

        return new DataRouteResult(
            targetKey  : $key,
            partitionId: $partition->id,
            replicaId  : $replica->id,
            endpoint   : $replica->endpoint,
            isPrimary  : true
        );
    }

    public function routeRead(string $key) : DataRouteResult
    {
        $partition = $this->partitionRoute->route($key);
        $replica   = $this->readReplica->selectReadReplica($partition->replicas);

        return new DataRouteResult(
            targetKey  : $key,
            partitionId: $partition->id,
            replicaId  : $replica->id,
            endpoint   : $replica->endpoint,
            isPrimary  : false
        );
    }

    public function toMetadata() : array
    {
        return [
            'partition_count' => $this->partitionRoute->count(),
            'replica_config'  => $this->readReplica->describeResponsibility(),
            'hash_ring'       => $this->hashRing->describeResponsibility(),
        ];
    }
}

final readonly class DataRouteResult
{
    public function __construct(
        public string $targetKey,
        public string $partitionId,
        public string $replicaId,
        public string $endpoint,
        public bool   $isPrimary
    ) {}
}