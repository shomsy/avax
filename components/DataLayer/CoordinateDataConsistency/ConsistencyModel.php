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

final readonly class ConsistencyModel
{
    public function __construct(
        public ConsistencyModelType $type,
        public int                  $maxStalenessMs,
        public bool                 $readYourWrites
    )
    {
        if ($this->maxStalenessMs < 0) {
            throw new InvalidArgumentException(message: 'Max staleness must be non-negative.');
        }
    }

    public static function strong() : self
    {
        return new self(type: ConsistencyModelType::STRONG, maxStalenessMs: 0, readYourWrites: true);
    }

    public static function eventual() : self
    {
        return new self(type: ConsistencyModelType::EVENTUAL, maxStalenessMs: PHP_INT_MAX, readYourWrites: false);
    }

    public static function boundedStaleness(int $maxStalenessMs = 5000) : self
    {
        return new self(type: ConsistencyModelType::BOUNDED_STALENESS, maxStalenessMs: $maxStalenessMs, readYourWrites: false);
    }

    public function describeResponsibility() : string
    {
        return 'records a consistency model.';
    }

    public function toMetadata() : array
    {
        return [
            'type'             => $this->type->value,
            'max_staleness_ms' => $this->maxStalenessMs,
            'read_your_writes' => $this->readYourWrites,
        ];
    }
}