<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Consistency;

/**
 * Interface for consistency policies.
 *
 * Defines the contract for different consistency strategies
 * used in distributed data systems.
 */
interface ConsistencyPolicy
{
    /**
     * Returns the name of this consistency policy.
     */
    public function name(): string;

    /**
     * Determines if a read is allowed given the current state.
     *
     * @param  mixed  $currentValue  The current value
     * @param  mixed  $pendingWrite  Any pending write
     */
    public function canRead(mixed $currentValue, mixed $pendingWrite = null): bool;

    /**
     * Determines if a write should be accepted.
     *
     * @param  mixed  $currentValue  The current value
     * @param  mixed  $newValue  The proposed new value
     */
    public function canWrite(mixed $currentValue, mixed $newValue): bool;

    /**
     * Resolves a conflict between two values.
     *
     * @param  mixed  $valueA  First conflicting value
     * @param  mixed  $valueB  Second conflicting value
     * @param  array<string, mixed>  $context  Additional context for resolution
     * @return mixed The resolved value
     */
    public function resolveConflict(mixed $valueA, mixed $valueB, array $context = []): mixed;

    /**
     * Returns a description of this policy's guarantees.
     */
    public function description(): string;

    /**
     * Returns the consistency level (0 = none, 1 = strong).
     */
    public function consistencyLevel(): float;
}

/**
 * A vector clock for tracking causality in distributed systems.
 *
 * Vector clocks provide a way to determine the partial ordering
 * of events and detect conflicts in distributed systems.
 */
final class VectorClock
{
    /**
     * @var array<string, int> Map of node ID to logical timestamp
     */
    private array $clock;

    public function __construct(array $clock = [])
    {
        $this->clock = $clock;
    }

    /**
     * Creates an empty vector clock.
     */
    public static function empty(): self
    {
        return new self;
    }

    /**
     * Creates a vector clock with a single node initialized to 1.
     */
    public static function initial(string $nodeId): self
    {
        return new self([$nodeId => 1]);
    }

    /**
     * Increments the clock for the given node.
     */
    public function increment(string $nodeId): self
    {
        $newClock = $this->clock;
        $newClock[$nodeId] = ($newClock[$nodeId] ?? 0) + 1;

        return new self($newClock);
    }

    /**
     * Merges this clock with another, taking the maximum of each component.
     */
    public function merge(self $other): self
    {
        $merged = $this->clock;

        foreach ($other->clock as $nodeId => $timestamp) {
            $merged[$nodeId] = max($merged[$nodeId] ?? 0, $timestamp);
        }

        return new self($merged);
    }

    /**
     * Checks if this clock happened-after another clock.
     */
    public function happenedAfter(self $other): bool
    {
        return $other->happenedBefore($this);
    }

    /**
     * Checks if this clock happened-before another clock.
     *
     * A < B if all components of A are <= corresponding components of B,
     * and at least one component is strictly less.
     */
    public function happenedBefore(self $other): bool
    {
        $allNodes = array_unique(array_merge(
            array_keys($this->clock),
            array_keys($other->clock),
        ));

        $allLessOrEqual = true;
        $atLeastOneLess = false;

        foreach ($allNodes as $nodeId) {
            $thisValue = $this->clock[$nodeId] ?? 0;
            $otherValue = $other->clock[$nodeId] ?? 0;

            if ($thisValue > $otherValue) {
                $allLessOrEqual = false;

                break;
            }

            if ($thisValue < $otherValue) {
                $atLeastOneLess = true;
            }
        }

        return $allLessOrEqual && $atLeastOneLess;
    }

    /**
     * Checks if two clocks are concurrent (neither happened-before the other).
     *
     * Concurrent clocks indicate a conflict that needs resolution.
     */
    public function isConcurrent(self $other): bool
    {
        return ! $this->happenedBefore($other)
            && ! $other->happenedBefore($this)
            && $this->clock !== $other->clock;
    }

    /**
     * Checks if two clocks are equal.
     */
    public function equals(self $other): bool
    {
        return $this->clock === $other->clock;
    }

    /**
     * Returns the raw clock array.
     *
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return $this->clock;
    }

    /**
     * Returns a string representation.
     */
    public function __toString(): string
    {
        $parts = [];

        foreach ($this->clock as $nodeId => $timestamp) {
            $parts[] = "{$nodeId}:{$timestamp}";
        }

        return '{'.implode(', ', $parts).'}';
    }
}
