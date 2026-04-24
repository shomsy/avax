<?php

declare(strict_types=1);

namespace Avax\DataLayer\CoordinateDataConsistency;

use InvalidArgumentException;

enum CapTradeoffPreference: string
{
    case CONSISTENCY         = 'consistency';
    case AVAILABILITY        = 'availability';
    case PARTITION_TOLERANCE = 'partition_tolerance';
}

final readonly class CapTradeoff
{
    public function __construct(
        public CapTradeoffPreference $preference,
        public int                   $timeoutMs,
        public bool                  $allowStaleReads
    ) {}

    public function describeResponsibility() : string
    {
        return 'records CAP tradeoff preference between consistency, availability, and partition tolerance.';
    }

    public static function availabilityFirst(int $timeoutMs = 5000) : self
    {
        return new self(CapTradeoffPreference::AVAILABILITY, $timeoutMs, true);
    }

    public static function consistencyFirst() : self
    {
        return new self(CapTradeoffPreference::CONSISTENCY, 0, false);
    }

    public function shouldFailover(int $partitionDurationMs) : bool
    {
        return $this->preference === CapTradeoffPreference::AVAILABILITY
            && $partitionDurationMs >= $this->timeoutMs;
    }

    public function toMetadata() : array
    {
        return [
            'preference'        => $this->preference->value,
            'timeout_ms'        => $this->timeoutMs,
            'allow_stale_reads' => $this->allowStaleReads,
        ];
    }
}