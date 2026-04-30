<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Consistency;

use Closure;

/**
 * Strategies for resolving write conflicts in distributed systems.
 *
 * Provides multiple conflict resolution strategies that can be
 * selected based on application requirements.
 */
final class ConflictResolution
{
    /**
     * @var string Strategy name
     */
    private readonly string $name;

    /**
     * @var Closure The resolution function
     */
    private readonly Closure $resolver;

    /**
     * @param Closure $resolver Function(mixed $a, mixed $b, array $context): mixed
     * @param string $name Strategy name
     */
    private function __construct(Closure $resolver, string $name)
    {
        $this->resolver = $resolver;
        $this->name     = $name;
    }

    /**
     * Last Write Wins strategy.
     *
     * Selects the value with the most recent timestamp.
     * Simple but can lose concurrent writes silently.
     */
    public static function lastWriteWins() : self
    {
        return new self(
            resolver: static function (mixed $valueA, mixed $valueB, array $context) : mixed {
                $timestampA = $context['timestampA'] ?? 0.0;
                $timestampB = $context['timestampB'] ?? 0.0;

                return $timestampB >= $timestampA ? $valueB : $valueA;
            },
            name    : 'last_write_wins',
        );
    }

    /**
     * First Write Wins strategy.
     *
     * Selects the value with the earliest timestamp.
     * Useful when the first write is considered authoritative.
     */
    public static function firstWriteWins() : self
    {
        return new self(
            resolver: static function (mixed $valueA, mixed $valueB, array $context) : mixed {
                $timestampA = $context['timestampA'] ?? 0.0;
                $timestampB = $context['timestampB'] ?? 0.0;

                return $timestampA <= $timestampB ? $valueA : $valueB;
            },
            name    : 'first_write_wins',
        );
    }

    /**
     * Custom resolver strategy.
     *
     * Uses a provided closure to resolve conflicts.
     *
     * @param Closure(mixed, mixed, array<string, mixed>): mixed $resolver
     */
    public static function custom(Closure $resolver) : self
    {
        return new self(
            resolver: $resolver,
            name    : 'custom',
        );
    }

    /**
     * Merge strategy.
     *
     * Attempts to merge both values. Works well for append-only
     * data structures and sets.
     */
    public static function merge() : self
    {
        return new self(
            resolver: static function (mixed $valueA, mixed $valueB, array $context) : mixed {
                if (is_array($valueA) && is_array($valueB)) {
                    return array_merge($valueA, $valueB);
                }

                if (is_string($valueA) && is_string($valueB)) {
                    return $valueA . $valueB;
                }

                // Fall back to last write wins
                $timestampA = $context['timestampA'] ?? 0.0;
                $timestampB = $context['timestampB'] ?? 0.0;

                return $timestampB >= $timestampA ? $valueB : $valueA;
            },
            name    : 'merge',
        );
    }

    /**
     * Highest value wins strategy.
     *
     * Selects the numerically higher value. Useful for counters
     * and incrementing metrics.
     */
    public static function highestValueWins() : self
    {
        return new self(
            resolver: static function (mixed $valueA, mixed $valueB, array $context) : mixed {
                if (is_numeric($valueA) && is_numeric($valueB)) {
                    return $valueB >= $valueA ? $valueB : $valueA;
                }

                // Fall back to last write wins
                return $context['timestampB'] >= $context['timestampA']
                    ? $valueB
                    : $valueA;
            },
            name    : 'highest_value_wins',
        );
    }

    /**
     * Lowest value wins strategy.
     *
     * Selects the numerically lower value. Useful for finding
     * minimums across replicas.
     */
    public static function lowestValueWins() : self
    {
        return new self(
            resolver: static function (mixed $valueA, mixed $valueB, array $context) : mixed {
                if (is_numeric($valueA) && is_numeric($valueB)) {
                    return $valueB <= $valueA ? $valueB : $valueA;
                }

                return $context['timestampB'] >= $context['timestampA']
                    ? $valueB
                    : $valueA;
            },
            name    : 'lowest_value_wins',
        );
    }

    /**
     * Manual intervention strategy.
     *
     * Marks the conflict for manual resolution. Returns both values
     * wrapped in a conflict structure.
     */
    public static function manualIntervention() : self
    {
        return new self(
            resolver: static fn (mixed $valueA, mixed $valueB, array $context) : mixed => new ConflictPair(
                valueA   : $valueA,
                valueB   : $valueB,
                context  : $context,
                createdAt: microtime(true),
            ),
            name    : 'manual_intervention',
        );
    }

    /**
     * Node priority strategy.
     *
     * Prefer values from specific nodes (defined by priority order).
     *
     * @param list<string> $nodePriority Ordered list of node IDs (highest priority first)
     */
    public static function nodePriority(array $nodePriority) : self
    {
        return new self(
            resolver: static function (mixed $valueA, mixed $valueB, array $context) use ($nodePriority) : mixed {
                $nodeA = $context['nodeIdA'] ?? '';
                $nodeB = $context['nodeIdB'] ?? '';

                $priorityA = array_search($nodeA, $nodePriority, true);
                $priorityB = array_search($nodeB, $nodePriority, true);

                // Lower index = higher priority
                if ($priorityA !== false && $priorityB !== false) {
                    return $priorityA < $priorityB ? $valueA : $valueB;
                }

                if ($priorityA !== false) {
                    return $valueA;
                }

                if ($priorityB !== false) {
                    return $valueB;
                }

                // Neither node in priority list, fall back to last write wins
                $timestampA = $context['timestampA'] ?? 0.0;
                $timestampB = $context['timestampB'] ?? 0.0;

                return $timestampB >= $timestampA ? $valueB : $valueA;
            },
            name    : 'node_priority',
        );
    }

    /**
     * Executes the resolution strategy.
     *
     * @param mixed $valueA First conflicting value
     * @param mixed $valueB Second conflicting value
     * @param array<string, mixed> $context Additional context
     *
     * @return mixed The resolved value
     */
    public function resolve(mixed $valueA, mixed $valueB, array $context = []) : mixed
    {
        return ($this->resolver)($valueA, $valueB, $context);
    }

    /**
     * Returns the strategy name.
     */
    public function name() : string
    {
        return $this->name;
    }

    /**
     * Returns a description of the strategy.
     */
    public function description() : string
    {
        return match ($this->name) {
            'last_write_wins'     => 'Selects the value with the most recent timestamp',
            'first_write_wins'    => 'Selects the value with the earliest timestamp',
            'merge'               => 'Attempts to merge both values together',
            'highest_value_wins'  => 'Selects the numerically highest value',
            'lowest_value_wins'   => 'Selects the numerically lowest value',
            'manual_intervention' => 'Preserves both values for manual resolution',
            'node_priority'       => 'Prefers values from higher-priority nodes',
            'custom'              => 'Uses a custom resolution function',
            default               => 'Unknown strategy',
        };
    }
}

/**
 * A pair of conflicting values awaiting manual resolution.
 */
final readonly class ConflictPair
{
    public function __construct(
        public mixed $valueA,
        public mixed $valueB,
        public array $context = [],
        public float $createdAt = 0.0,
    ) {}

    /**
     * Resolves the conflict by choosing value A.
     */
    public function chooseA() : mixed
    {
        return $this->valueA;
    }

    /**
     * Resolves the conflict by choosing value B.
     */
    public function chooseB() : mixed
    {
        return $this->valueB;
    }

    /**
     * Returns a summary string.
     */
    public function summary() : string
    {
        return sprintf(
            "Conflict (age: %.1fs):\n  A: %s\n  B: %s",
            $this->age(),
            var_export($this->valueA, true),
            var_export($this->valueB, true),
        );
    }

    /**
     * Returns the age of the conflict in seconds.
     */
    public function age() : float
    {
        return microtime(true) - $this->createdAt;
    }
}
