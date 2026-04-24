<?php

declare(strict_types=1);

namespace Avax\DataLayer\CoordinateDataConsistency;

use InvalidArgumentException;

enum ConsistencyModelType: string
{
    case STRONG            = 'strong';
    case EVENTUAL          = 'eventual';
    case CAUSAL            = 'causal';
    case READ_YOUR_WRITES  = 'read_your_writes';
    case BOUNDED_STALENESS = 'bounded_staleness';
}

final readonly class DescribeConsistencyModel
{
    public function __construct(
        public ConsistencyModelType $type,
        public string               $description,
        public array                $guarantees
    ) {}

    public function describeResponsibility() : string
    {
        return 'describes consistency model type, guarantees, and behavior.';
    }

    public static function strong() : self
    {
        return new self(
            type       : ConsistencyModelType::STRONG,
            description: 'Strong consistency guarantees linearizability.',
            guarantees : ['linearizability', 'read-your-writes', 'monotonic-reads']
        );
    }

    public static function eventual() : self
    {
        return new self(
            type       : ConsistencyModelType::EVENTUAL,
            description: 'Eventual consistency guarantees convergence.',
            guarantees : ['convergence', 'causal_consistency_optional']
        );
    }

    public function toMetadata() : array
    {
        return [
            'type'        => $this->type->value,
            'description' => $this->description,
            'guarantees'  => $this->guarantees,
        ];
    }
}