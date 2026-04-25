<?php

declare(strict_types=1);

namespace Avax\DataLayer\CoordinateDataConsistency;

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
        return new self(preference: CapTradeoffPreference::AVAILABILITY, timeoutMs: $timeoutMs, allowStaleReads: true);
    }

    public static function consistencyFirst() : self
    {
        return new self(preference: CapTradeoffPreference::CONSISTENCY, timeoutMs: 0, allowStaleReads: false);
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