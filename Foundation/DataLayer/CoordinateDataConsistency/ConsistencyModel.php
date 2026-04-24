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
            throw new InvalidArgumentException('Max staleness must be non-negative.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'records a consistency model.';
    }

    public static function strong() : self
    {
        return new self(ConsistencyModelType::STRONG, 0, true);
    }

    public static function eventual() : self
    {
        return new self(ConsistencyModelType::EVENTUAL, PHP_INT_MAX, false);
    }

    public static function boundedStaleness(int $maxStalenessMs = 5000) : self
    {
        return new self(ConsistencyModelType::BOUNDED_STALENESS, $maxStalenessMs, false);
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