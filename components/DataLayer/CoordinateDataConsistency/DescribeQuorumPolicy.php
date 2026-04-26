<?php

declare(strict_types=1);

namespace components\DataLayer\CoordinateDataConsistency;

final readonly class DescribeQuorumPolicy
{
    public function __construct(
        public QuorumPolicy $policy,
        public string       $description,
        public array        $requirements
    ) {}

    public static function forMajority() : self
    {
        $policy = QuorumPolicy::majority();

        return new self(
            policy      : $policy,
            description : 'Majority quorum requires acknowledgment from majority of replicas.',
            requirements: ['read_quorum' => 2, 'write_quorum' => 2]
        );
    }

    public static function forAll() : self
    {
        $policy = QuorumPolicy::all();

        return new self(
            policy      : $policy,
            description : 'All replicas must acknowledge for read and write success.',
            requirements: ['read_quorum' => 3, 'write_quorum' => 3]
        );
    }

    public function describeResponsibility() : string
    {
        return 'describes quorum policy requirements for reads and writes.';
    }

    public function toMetadata() : array
    {
        return [
            'policy'       => $this->policy->toMetadata(),
            'description'  => $this->description,
            'requirements' => $this->requirements,
        ];
    }
}