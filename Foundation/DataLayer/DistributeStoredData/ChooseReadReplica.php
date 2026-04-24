<?php

declare(strict_types=1);

namespace Avax\DataLayer\DistributeStoredData;

use InvalidArgumentException;

final readonly class ChooseReadReplica
{
    public function __construct(
        private ReplicationPolicy $policy
    ) {}

    public function describeResponsibility() : string
    {
        return 'chooses a read replica based on replication lag and selection policy.';
    }

    public function selectReplica(array $replicas) : ReplicaInfo
    {
        if (empty($replicas)) {
            throw new InvalidArgumentException('No replicas available.');
        }

        $primary = array_filter($replicas, fn ($r) => $r->isPrimary);

        return ! empty($primary) ? reset($primary) : $replicas[0];
    }

    public function selectReadReplica(array $replicas) : ReplicaInfo
    {
        if (empty($replicas)) {
            throw new InvalidArgumentException('No replicas available.');
        }

        if ($this->policy->replicaSelection === ReplicaSelectionPolicy::LOWEST_LAG) {
            return $this->selectLowestLag($replicas);
        }

        if ($this->policy->replicaSelection === ReplicaSelectionPolicy::RANDOM) {
            return $replicas[array_rand($replicas)];
        }

        if ($this->policy->replicaSelection === ReplicaSelectionPolicy::ROUND_ROBIN) {
            static $lastIndex = 0;
            $index = $lastIndex++ % count($replicas);

            return $replicas[$index];
        }

        return $replicas[0];
    }

    private function selectLowestLag(array $replicas) : ReplicaInfo
    {
        usort($replicas, fn ($a, $b) => $a->lagMs <=> $b->lagMs);

        $healthy = array_filter($replicas, fn ($r) => $r->lagMs < 5000);

        return ! empty($healthy) ? reset($healthy) : $replicas[0];
    }

    public function toMetadata() : array
    {
        return ['policy' => $this->policy->toMetadata()];
    }
}