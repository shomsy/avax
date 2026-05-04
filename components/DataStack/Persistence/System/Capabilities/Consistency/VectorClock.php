<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Consistency;


use Stringable;

/**
 * A vector clock for tracking causality in distributed systems.
 *
 * Vector clocks provide a way to determine the partial ordering
 * of events and detect conflicts in distributed systems.
 */
final class VectorClock implements Stringable
{
    public function __construct(
        /**
         * @var array<string, int> Map of node ID to logical timestamp
         */
        private array $clock = []
    )
    {
    }

    /**
     * Creates an empty vector clock.
     */
    public static function empty(): self
    {
        return new self();
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

        foreach ($allNodes as $allNode) {
            $thisValue = $this->clock[$allNode] ?? 0;
            $otherValue = $other->clock[$allNode] ?? 0;

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
        return !$this->happenedBefore($other)
            && !$other->happenedBefore($this)
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
            $parts[] = sprintf('%s:%d', $nodeId, $timestamp);
        }

        return '{' . implode(', ', $parts) . '}';
    }
}
