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
            throw new InvalidArgumentException('Total replicas must be at least 1.');
        }
        if ($this->readQuorum < 1 || $this->readQuorum > $this->totalReplicas) {
            throw new InvalidArgumentException('Read quorum must be between 1 and total replicas.');
        }
        if ($this->writeQuorum < 1 || $this->writeQuorum > $this->totalReplicas) {
            throw new InvalidArgumentException('Write quorum must be between 1 and total replicas.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'records quorum requirements for reads and writes.';
    }

    public static function majority() : self
    {
        return new self(3, 2, 2, true);
    }

    public static function all() : self
    {
        return new self(3, 3, 3, true);
    }

    public static function one(int $totalReplicas = 3) : self
    {
        return new self($totalReplicas, 1, 1, false);
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