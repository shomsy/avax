<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

final readonly class ChooseReplicaForRead
{
    public function __construct(
        private ReplicaCount $replicaCount,
        private ReplicationPolicy $replicationPolicy = ReplicationPolicy::SYNCHRONOUS,
    ) {}

    public function choosePrimary() : PrimaryReplica
    {
        return new PrimaryReplica(index: 0);
    }

    public function chooseSecondary() : ?SecondaryReplica
    {
        if ($this->replicaCount->secondaries === 0) {
            return null;
        }

        return new SecondaryReplica(index: 1);
    }

    public function chooseAny() : PrimaryReplica|SecondaryReplica
    {
        return new PrimaryReplica(index: 0);
    }

    public function getAllReplicas() : array
    {
        $replicas = [new PrimaryReplica(index: 0)];

        for ($i = 1; $i <= $this->replicaCount->secondaries; $i++) {
            $replicas[] = SecondaryReplica::fromIndex(index: $i);
        }

        return $replicas;
    }

    public function replicaCount() : ReplicaCount
    {
        return $this->replicaCount;
    }

    public function requiresQuorum() : bool
    {
        return $this->replicationPolicy === ReplicationPolicy::QUORUM;
    }
}
