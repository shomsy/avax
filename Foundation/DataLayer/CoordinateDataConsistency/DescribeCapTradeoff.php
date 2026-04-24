<?php

declare(strict_types=1);

namespace Avax\DataLayer\CoordinateDataConsistency;

enum CapPreference: string
{
    case CONSISTENCY         = 'consistency';
    case AVAILABILITY        = 'availability';
    case PARTITION_TOLERANCE = 'partition_tolerance';
}

final readonly class DescribeCapTradeoff
{
    public function __construct(
        public CapPreference $preference,
        public string        $description,
        public array         $implications
    ) {}

    public function describeResponsibility() : string
    {
        return 'describes CAP tradeoff including preference, implications, and trade-offs.';
    }

    public static function availability() : self
    {
        return new self(
            preference  : CapPreference::AVAILABILITY,
            description : 'Prioritizes availability during partitions.',
            implications: ['allow_stale_reads' => true, 'eventual_consistency' => true]
        );
    }

    public static function consistency() : self
    {
        return new self(
            preference  : CapPreference::CONSISTENCY,
            description : 'Prioritizes consistency during partitions.',
            implications: ['allow_stale_reads' => false, 'reject_on_partition' => true]
        );
    }

    public function toMetadata() : array
    {
        return [
            'preference'   => $this->preference->value,
            'description'  => $this->description,
            'implications' => $this->implications,
        ];
    }
}