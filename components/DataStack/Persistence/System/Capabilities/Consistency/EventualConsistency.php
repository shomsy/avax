<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Consistency;

use Closure;
use RuntimeException;

/**
 * A versioned value with vector clock for conflict detection.
 */
final readonly class VersionedValue
{
    public function __construct(
        public mixed  $value,
        public VectorClock $clock,
        public string $nodeId,
        public float  $timestamp,
    ) {}

    /**
     * Creates a new versioned value.
     */
    public static function create(
        mixed       $value,
        string      $nodeId,
        VectorClock|null $clock = null,
        float       $timestamp = null,
    ) : self
    {
        $clock     ??= VectorClock::initial($nodeId);
        $timestamp ??= microtime(true);

        return new self(
            value    : $value,
            clock    : $clock,
            nodeId   : $nodeId,
            timestamp: $timestamp,
        );
    }

    /**
     * Creates an updated version of this value.
     */
    public function update(mixed $newValue) : self
    {
        return new self(
            value    : $newValue,
            clock    : $this->clock->increment($this->nodeId),
            nodeId   : $this->nodeId,
            timestamp: microtime(true),
        );
    }
}

/**
 * A detected conflict between concurrent writes.
 */
final readonly class Conflict
{
    public function __construct(
        public VersionedValue $valueA,
        public VersionedValue $valueB,
        public string $key,
        public float  $detectedAt,
    ) {}

    /**
     * Creates a conflict from two versioned values.
     */
    public static function fromValues(
        VersionedValue $a,
        VersionedValue $b,
        string $key,
    ) : self
    {
        return new self(
            valueA    : $a,
            valueB    : $b,
            key       : $key,
            detectedAt: microtime(true),
        );
    }
}

/**
 * Result of a conflict resolution operation.
 */
final readonly class ConflictResolutionResult
{
    public function __construct(
        public mixed $resolvedValue,
        public string $strategy,
        public bool  $wasConflict,
        public array $details = [],
    ) {}

    /**
     * Creates a result with no conflict (straightforward value).
     */
    public static function noConflict(mixed $value) : self
    {
        return new self(
            resolvedValue: $value,
            strategy     : 'no_conflict',
            wasConflict  : false,
        );
    }

    /**
     * Creates a result from a conflict resolution.
     */
    public static function resolved(mixed $value, string $strategy, array $details = []) : self
    {
        return new self(
            resolvedValue: $value,
            strategy     : $strategy,
            wasConflict  : true,
            details      : $details,
        );
    }
}

/**
 * Implementation of eventual consistency using vector clocks.
 *
 * This policy allows concurrent writes and detects conflicts
 * through vector clock comparison. Conflicts are resolved using
 * configurable strategies.
 *
 * Key properties:
 * - High availability: reads and writes always succeed
 * - Eventual convergence: all replicas converge given no new writes
 * - Conflict detection: vector clocks identify concurrent modifications
 */
final class EventualConsistency implements ConsistencyPolicy
{
    /**
     * @var Closure|null Custom conflict resolver callback
     */
    private Closure|null $customResolver;

    /**
     * @var ConflictResolution The conflict resolution strategy to use
     */
    private ConflictResolution $resolutionStrategy;

    /**
     * @var list<Conflict> Detected conflicts (for auditing/debugging)
     */
    private array $conflicts = [];

    /**
     * @var int Maximum conflicts to retain
     */
    private int $maxConflictHistory;

    public function __construct(
        ConflictResolution|null $resolutionStrategy = null,
        Closure            $customResolver = null,
        int                $maxConflictHistory = 100,
    )
    {
        $this->resolutionStrategy = $resolutionStrategy ?? ConflictResolution::lastWriteWins();
        $this->customResolver     = $customResolver;
        $this->maxConflictHistory = $maxConflictHistory;
    }

    public function canRead(mixed $currentValue, mixed $pendingWrite = null) : bool
    {
        // In eventual consistency, reads always succeed
        // The read may return stale data, but it will eventually be consistent
        return true;
    }

    public function canWrite(mixed $currentValue, mixed $newValue) : bool
    {
        // In eventual consistency, writes always succeed
        // Conflicts are detected and resolved later
        return true;
    }

    /**
     * Merges a set of versioned values from multiple replicas.
     *
     * @param list<VersionedValue> $values
     *
     * @return VersionedValue The merged value
     */
    public function mergeReplicas(array $values) : VersionedValue
    {
        if (empty($values)) {
            throw new RuntimeException('Cannot merge empty set of replica values');
        }

        if (count($values) === 1) {
            return $values[0];
        }

        // Find the latest value using vector clock comparison
        $latest = $values[0];

        for ($i = 1; $i < count($values); $i++) {
            $current = $values[$i];

            if ($current->clock->happenedAfter($latest->clock)) {
                $latest = $current;
            } elseif ($current->clock->isConcurrent($latest->clock)) {
                // Conflict - resolve using configured strategy
                $resolved = $this->resolveConflict($latest, $current, [
                    'key' => 'replica_merge',
                ]);

                // Create new versioned value with merged clock and resolved value
                $mergedClock = $latest->clock->merge($current->clock);
                $latest      = new VersionedValue(
                    value    : $resolved,
                    clock    : $mergedClock,
                    nodeId   : 'merged',
                    timestamp: microtime(true),
                );
            }
        }

        return $latest;
    }

    /**
     * Detects and resolves conflicts between two versioned values.
     *
     * Uses vector clocks to determine if values are concurrent
     * (conflict) or causally related (no conflict).
     */
    public function resolveConflict(mixed $valueA, mixed $valueB, array $context = []) : mixed
    {
        if (! $valueA instanceof VersionedValue || ! $valueB instanceof VersionedValue) {
            // Can't compare without vector clocks, default to valueA
            return $valueA;
        }

        // Check for conflict using vector clocks
        if (! $valueA->clock->isConcurrent($valueB->clock)) {
            // No conflict - one happened before the other
            if ($valueB->clock->happenedAfter($valueA->clock)) {
                return $valueB;
            }

            return $valueA;
        }

        // Conflict detected - concurrent writes
        $conflict = Conflict::fromValues(
            a  : $valueA,
            b  : $valueB,
            key: $context['key'] ?? 'unknown',
        );

        $this->recordConflict($conflict);

        // Use custom resolver if provided
        if ($this->customResolver !== null) {
            $resolved = ($this->customResolver)($valueA, $valueB, $context);

            return ConflictResolutionResult::resolved(
                value   : $resolved,
                strategy: 'custom',
                details : [
                              'conflict' => $conflict,
                              'context'  => $context,
                          ],
            );
        }

        // Use the configured resolution strategy
        return $this->resolutionStrategy->resolve(
            valueA : $valueA->value,
            valueB : $valueB->value,
            context: array_merge($context, [
                         'clockA'     => $valueA->clock,
                         'clockB'     => $valueB->clock,
                         'timestampA' => $valueA->timestamp,
                         'timestampB' => $valueB->timestamp,
                         'nodeIdA'    => $valueA->nodeId,
                         'nodeIdB'    => $valueB->nodeId,
                     ]),
        );
    }

    /**
     * Records a detected conflict.
     */
    private function recordConflict(Conflict $conflict) : void
    {
        $this->conflicts[] = $conflict;

        // Enforce max history
        if (count($this->conflicts) > $this->maxConflictHistory) {
            array_shift($this->conflicts);
        }
    }

    /**
     * Detects if two values are in conflict.
     */
    public function detectConflict(mixed $valueA, mixed $valueB) : bool
    {
        if (! $valueA instanceof VersionedValue || ! $valueB instanceof VersionedValue) {
            return false;
        }

        return $valueA->clock->isConcurrent($valueB->clock);
    }

    /**
     * Returns detected conflicts for analysis.
     *
     * @return list<Conflict>
     */
    public function getConflicts() : array
    {
        return $this->conflicts;
    }

    /**
     * Returns the number of detected conflicts.
     */
    public function conflictCount() : int
    {
        return count($this->conflicts);
    }

    /**
     * Returns the resolution strategy name.
     */
    public function resolutionStrategyName() : string
    {
        return $this->resolutionStrategy->name();
    }

    public function name() : string
    {
        return 'eventual';
    }

    public function description() : string
    {
        return 'Eventual consistency: writes always succeed, conflicts are detected via vector clocks and resolved using configured strategy. Data converges when no new writes occur.';
    }

    public function consistencyLevel() : float
    {
        return 0.3; // Low consistency, high availability
    }

    /**
     * Clears the conflict history.
     */
    public function clearConflicts() : void
    {
        $this->conflicts = [];
    }
}
