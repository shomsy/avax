<?php

declare(strict_types=1);

namespace components\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

enum ReplicationPolicy: string
{
    case SYNCHRONOUS  = 'synchronous';
    case ASYNCHRONOUS = 'asynchronous';
    case QUORUM       = 'quorum';
}

final readonly class ReplicaCount
{
    public function __construct(
        public int $primary = 1,
        public int $secondaries = 2
    ) {}

    public static function single() : self
    {
        return new self(primary: 1, secondaries: 0);
    }

    public static function withSecondaries(int $count) : self
    {
        return new self(primary: 1, secondaries: $count);
    }

    public static function quorum() : self
    {
        return new self(primary: 1, secondaries: 2);
    }

    public function quorumSize() : int
    {
        return (int) floor($this->totalReplicas() / 2) + 1;
    }

    public function totalReplicas() : int
    {
        return $this->primary + $this->secondaries;
    }
}