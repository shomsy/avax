<?php

declare(strict_types=1);

namespace Avax\DataLayer\CoordinateDataConsistency;

use InvalidArgumentException;

enum QuorumType: string
{
    case READ   = 'read';
    case WRITE  = 'write';
    case QUORUM = 'quorum';
}

final readonly class QuorumPolicy
{
    public function __construct(
        public int  $totalReplicas,
        public int  $readQuorum,
        public int  $writeQuorum,
        public bool $strict
    )
    {
        if ($this->totalReplicas < 1) {
            throw new InvalidArgumentException(message: 'Total replicas must be at least 1.');
        }
        if ($this->readQuorum < 1 || $this->readQuorum > $this->totalReplicas) {
            throw new InvalidArgumentException(message: 'Read quorum must be between 1 and total replicas.');
        }
        if ($this->writeQuorum < 1 || $this->writeQuorum > $this->totalReplicas) {
            throw new InvalidArgumentException(message: 'Write quorum must be between 1 and total replicas.');
        }
    }

    public static function majority() : self
    {
        return new self(totalReplicas: 3, readQuorum: 2, writeQuorum: 2, strict: true);
    }

    public static function all() : self
    {
        return new self(totalReplicas: 3, readQuorum: 3, writeQuorum: 3, strict: true);
    }

    public static function one(int $totalReplicas = 3) : self
    {
        return new self(totalReplicas: $totalReplicas, readQuorum: 1, writeQuorum: 1, strict: false);
    }

    public function describeResponsibility() : string
    {
        return 'records quorum requirements for reads and writes.';
    }

    public function isQuorumMet(int $ackedReplicas, QuorumType $type) : bool
    {
        return match ($type) {
            QuorumType::READ   => $ackedReplicas >= $this->readQuorum,
            QuorumType::WRITE  => $ackedReplicas >= $this->writeQuorum,
            QuorumType::QUORUM => $ackedReplicas >= $this->writeQuorum && $ackedReplicas >= $this->readQuorum,
        };
    }

    public function toMetadata() : array
    {
        return [
            'total_replicas' => $this->totalReplicas,
            'read_quorum'    => $this->readQuorum,
            'write_quorum'   => $this->writeQuorum,
            'strict'         => $this->strict,
        ];
    }
}