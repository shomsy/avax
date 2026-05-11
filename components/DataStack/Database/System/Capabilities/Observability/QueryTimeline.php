<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Observability;

/**
 * Records and manages a timeline of query executions with timestamps and durations.
 *
 * Maintains an ordered history of all queries executed, enabling
 * profiling, debugging, and analysis of query patterns over time.
 */
final class QueryTimeline
{
    /**
     * @var list<QueryEntry>
     */
    private array $entries = [];

    /**
     * @var float The timestamp when the timeline started recording
     */
    private float $startTime;

    public function __construct(/**
     * @var int Maximum number of entries to keep (0 = unlimited)
     */
        private readonly int $maxEntries = 0,
    ) {
        $this->startTime = microtime(true);
    }

    /**
     * Records a query execution entry.
     */
    public function record(
        string $sql,
        array $bindings = [],
        float $durationMs = 0.0,
        string $type = 'unknown',
        string $connection = '',
        int $affectedRows = 0, string|null $error = null,
    ): QueryEntry {
        $queryEntry = new QueryEntry(
            sql         : $sql,
            bindings    : $bindings,
            timestamp   : microtime(true),
            durationMs  : $durationMs,
            type        : $type,
            connection  : $connection,
            affectedRows: $affectedRows,
            error       : $error,
        );

        $this->entries[] = $queryEntry;

        // Enforce max entries limit
        if ($this->maxEntries > 0 && count($this->entries) > $this->maxEntries) {
            array_shift($this->entries);
        }

        return $queryEntry;
    }

    /**
     * Records a query entry directly from a QueryEntry object.
     */
    public function add(QueryEntry $queryEntry): self
    {
        $this->entries[] = $queryEntry;

        if ($this->maxEntries > 0 && count($this->entries) > $this->maxEntries) {
            array_shift($this->entries);
        }

        return $this;
    }

    /**
     * Returns all recorded query entries.
     *
     * @return list<QueryEntry>
     */
    public function all(): array
    {
        return $this->entries;
    }

    /**
     * Returns entries filtered by type.
     *
     * @return list<QueryEntry>
     */
    public function ofType(string $type): array
    {
        return array_values(array_filter(
            $this->entries,
            static fn (QueryEntry $queryEntry): bool => strtoupper($queryEntry->getType()) === strtoupper($type),
        ));
    }

    /**
     * Returns entries that resulted in errors.
     *
     * @return list<QueryEntry>
     */
    public function errors(): array
    {
        return array_values(array_filter(
            $this->entries,
            static fn (QueryEntry $queryEntry): bool => $queryEntry->hasError(),
        ));
    }

    /**
     * Returns entries from a specific connection.
     *
     * @return list<QueryEntry>
     */
    public function forConnection(string $connection): array
    {
        return array_values(array_filter(
            $this->entries,
            static fn (QueryEntry $queryEntry): bool => $queryEntry->connection === $connection,
        ));
    }

    /**
     * Returns the total number of recorded queries.
     */
    public function count(): int
    {
        return count($this->entries);
    }

    /**
     * Returns the average query duration in milliseconds.
     */
    public function averageDurationMs(): float
    {
        if ($this->entries === []) {
            return 0.0;
        }

        return $this->totalDurationMs() / count($this->entries);
    }

    /**
     * Returns the total execution time of all queries in milliseconds.
     */
    public function totalDurationMs(): float
    {
        return array_sum(array_map(
            static fn (QueryEntry $queryEntry): float => $queryEntry->durationMs,
            $this->entries,
        ));
    }

    /**
     * Returns the maximum query duration in milliseconds.
     */
    public function maxDurationMs(): float
    {
        if ($this->entries === []) {
            return 0.0;
        }

        return max(array_map(
            static fn (QueryEntry $queryEntry): float => $queryEntry->durationMs,
            $this->entries,
        ));
    }

    /**
     * Returns the slowest query entry.
     */
    public function slowest() : QueryEntry|null
    {
        if ($this->entries === []) {
            return null;
        }

        $slowest = null;
        $maxDuration = -1.0;

        foreach ($this->entries as $entry) {
            if ($entry->durationMs > $maxDuration) {
                $maxDuration = $entry->durationMs;
                $slowest = $entry;
            }
        }

        return $slowest;
    }

    /**
     * Returns the elapsed time since the timeline started recording.
     */
    public function elapsedTimeMs(): float
    {
        return (microtime(true) - $this->startTime) * 1000;
    }

    /**
     * Returns a summary of query counts by type.
     *
     * @return array<string, int>
     */
    public function countsByType(): array
    {
        $counts = [];

        foreach ($this->entries as $entry) {
            $type = $entry->getType();
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * Resets the timeline, clearing all entries.
     */
    public function reset(): void
    {
        $this->entries = [];
        $this->startTime = microtime(true);
    }
}
