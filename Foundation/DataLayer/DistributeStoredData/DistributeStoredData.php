<?php

declare(strict_types=1);

namespace Avax\DataLayer\DistributeStoredData;

use SensitiveParameter;

final readonly class DistributeStoredData
{
    public function __construct(
        private DataPartitionRoute                       $partitionRoute,
        private ChooseReadReplica                        $readReplica,
        #[SensitiveParameter] private ConsistentHashRing $hashRing
    ) {}

    public function describeResponsibility() : string
    {
        return 'groups replica, partition, shard, and consistent hash routing decisions.';
    }

    public function routeWrite(string $key) : DataRouteResult
    {
        $partition = $this->partitionRoute->route(key: $key);
        $replica   = $this->readReplica->selectReplica(replicas: $partition->replicas);

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
        $partition = $this->partitionRoute->route(key: $key);
        $replica   = $this->readReplica->selectReadReplica(replicas: $partition->replicas);

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