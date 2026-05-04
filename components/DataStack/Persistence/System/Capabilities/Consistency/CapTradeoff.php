<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Consistency;

/**
 * CAP theorem tradeoff configuration.
 *
 * The CAP theorem states that a distributed system can only guarantee
 * two of three properties simultaneously:
 * - Consistency (C) : Every read receives the most recent write
 * - Availability (A) : Every request receives a response
 * - Partition tolerance (P) : System continues despite network partitions
 *
 * Since partitions are inevitable in distributed systems, the practical
 * choice is between CP (Consistency + Partition tolerance) and
 * AP (Availability + Partition tolerance).
 */
enum CapTradeoff: string
{
    /**
     * Consistency + Partition Tolerance.
     *
     * During a network partition, the system prioritizes data consistency
     * over availability. Some operations may be unavailable to prevent
     * inconsistent data.
     *
     * Use cases: Financial systems, inventory management, anything where
     * data correctness is more important than uptime.
     */
    case CP = 'CP';

    /**
     * Availability + Partition Tolerance.
     *
     * During a network partition, the system remains available but may
     * return stale or inconsistent data.
     *
     * Use cases: Social media feeds, content delivery, caching layers,
     * anything where availability is more important than perfect consistency.
     */
    case AP = 'AP';

    /**
     * Balanced approach: tries to provide both with graceful degradation.
     *
     * Prefers consistency but falls back to availability under extended
     * partitions. Uses conflict resolution to reconcile divergent data.
     */
    case BALANCED = 'BALANCED';

    /**
     * Returns a human-readable description.
     */
    public function description(): string
    {
        return match ($this) {
            self::CP => 'Consistency over Availability: Operations may be rejected during network partitions to maintain data consistency.',
            self::AP => 'Availability over Consistency: Operations always succeed, but may return stale data during network partitions.',
            self::BALANCED => 'Balanced: Prefers consistency with graceful fallback to availability during extended partitions.',
        };
    }

    /**
     * Returns the consistency level (0.0 to 1.0).
     */
    public function consistencyLevel(): float
    {
        return match ($this) {
            self::CP => 1.0,
            self::AP => 0.3,
            self::BALANCED => 0.7,
        };
    }

    /**
     * Returns the availability level (0.0 to 1.0).
     */
    public function availabilityLevel(): float
    {
        return match ($this) {
            self::CP => 0.5,
            self::AP => 1.0,
            self::BALANCED => 0.8,
        };
    }

    /**
     * Returns whether reads should wait for the latest data.
     */
    public function shouldWaitForConsistentRead(): bool
    {
        return match ($this) {
            self::CP => true,
            self::AP => false,
            self::BALANCED => true,
        };
    }

    /**
     * Returns whether writes should be rejected if consistency cannot be guaranteed.
     */
    public function shouldRejectInconsistentWrites(): bool
    {
        return match ($this) {
            self::CP => true,
            self::AP => false,
            self::BALANCED => true,
        };
    }

    /**
     * Returns the recommended conflict resolution strategy for this tradeoff.
     */
    public function recommendedConflictStrategy(): ConflictResolution
    {
        return match ($this) {
            self::CP => ConflictResolution::lastWriteWins(),
            self::AP => ConflictResolution::merge(),
            self::BALANCED => ConflictResolution::lastWriteWins(),
        };
    }

    /**
     * Returns whether this tradeoff prioritizes consistency.
     */
    public function prioritizesConsistency(): bool
    {
        return $this === self::CP || $this === self::BALANCED;
    }

    /**
     * Returns whether this tradeoff prioritizes availability.
     */
    public function prioritizesAvailability(): bool
    {
        return $this === self::AP || $this === self::BALANCED;
    }
}
