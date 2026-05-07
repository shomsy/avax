<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Consistency\Models;

/**
 * Consistency model taxonomy.
 *
 * @experimental V3 labs
 *
 * Defines the consistency guarantee for a data path.
 * Each model carries specific properties and tradeoffs.
 */
enum ConsistencyModel: string
{
    case Strong          = 'strong';
    case Eventual        = 'eventual';
    case Causal          = 'causal';
    case Session         = 'session';
    case ReadYourWrites  = 'read_your_writes';
    case MonotonicReads  = 'monotonic';
    case MonotonicWrites = 'monotonic_writes';
    case None            = 'none';

    /**
     * Whether this model guarantees read-your-writes.
     */
    public function guaranteesReadYourWrites() : bool
    {
        return in_array($this, [
            self::Strong,
            self::Session,
            self::ReadYourWrites,
        ],              true);
    }

    /**
     * Whether this model guarantees monotonic reads.
     */
    public function guaranteesMonotonicReads() : bool
    {
        return in_array($this, [
            self::Strong,
            self::MonotonicReads,
            self::Session,
        ],              true);
    }

    /**
     * Whether writes are immediately visible to all readers.
     */
    public function isGloballyVisible() : bool
    {
        return $this === self::Strong;
    }

    /**
     * Estimated latency overhead in milliseconds compared to eventual consistency.
     *
     * These are analytical estimates, not benchmarks.
     */
    public function estimatedOverheadMs() : int
    {
        return match ($this) {
            self::Strong          => 20,
            self::Causal          => 10,
            self::Session         => 5,
            self::ReadYourWrites  => 5,
            self::MonotonicReads  => 3,
            self::MonotonicWrites => 3,
            self::Eventual        => 0,
            self::None            => 0,
        };
    }

    /**
     * Risk level for stale reads (low = fewer stale reads, high = more).
     */
    public function staleReadRisk() : string
    {
        return match ($this) {
            self::Strong          => 'low',
            self::Causal          => 'low',
            self::Session         => 'low',
            self::ReadYourWrites  => 'medium',
            self::MonotonicReads  => 'medium',
            self::MonotonicWrites => 'medium',
            self::Eventual        => 'high',
            self::None            => 'high',
        };
    }
}
