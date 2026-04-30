<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Observability;

/**
 * A single recorded query execution entry in the timeline.
 *
 * Readonly value object capturing a query's execution details.
 */
final readonly class QueryEntry
{
    public function __construct(
        public string $sql,
        public array  $bindings = [],
        public float  $timestamp = 0.0,
        public float  $durationMs = 0.0,
        public string $type = 'unknown',
        public string $connection = '',
        public int    $affectedRows = 0,
        public string|null $error = null,
    ) {}

    /**
     * Creates a QueryEntry with the current timestamp.
     */
    public static function create(
        string $sql,
        array  $bindings = [],
        float  $durationMs = 0.0,
        string $type = 'unknown',
        string $connection = '',
        int    $affectedRows = 0,
        string|null $error = null,
    ) : self
    {
        return new self(
            sql         : $sql,
            bindings    : $bindings,
            timestamp   : microtime(true),
            durationMs  : $durationMs,
            type        : $type,
            connection  : $connection,
            affectedRows: $affectedRows,
            error       : $error,
        );
    }

    /**
     * Checks if this query resulted in an error.
     */
    public function hasError() : bool
    {
        return $this->error !== null;
    }

    /**
     * Returns a formatted duration string.
     */
    public function formattedDuration() : string
    {
        if ($this->durationMs < 1) {
            return number_format($this->durationMs * 1000, 2) . 'μs';
        }

        if ($this->durationMs < 1000) {
            return number_format($this->durationMs, 2) . 'ms';
        }

        return number_format($this->durationMs / 1000, 2) . 's';
    }

    /**
     * Converts to an associative array.
     */
    public function toArray() : array
    {
        return [
            'sql'           => $this->sql,
            'bindings'      => $this->bindings,
            'timestamp'     => $this->timestamp,
            'duration_ms'   => $this->durationMs,
            'type'          => $this->getType(),
            'connection'    => $this->connection,
            'affected_rows' => $this->affectedRows,
            'error'         => $this->error,
        ];
    }

    /**
     * Returns the query type (SELECT, INSERT, UPDATE, DELETE, etc.).
     */
    public function getType() : string
    {
        if ($this->type !== 'unknown') {
            return $this->type;
        }

        $trimmed = ltrim($this->sql);

        return strtoupper(substr($trimmed, 0, 6));
    }
}

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

    /**
     * @var int Maximum number of entries to keep (0 = unlimited)
     */
    private int $maxEntries;

    public function __construct(int $maxEntries = 0)
    {
        $this->startTime  = microtime(true);
        $this->maxEntries = $maxEntries;
    }

    /**
     * Records a query execution entry.
     */
    public function record(
        string $sql,
        array  $bindings = [],
        float  $durationMs = 0.0,
        string $type = 'unknown',
        string $connection = '',
        int    $affectedRows = 0,
        string|null $error = null,
    ) : QueryEntry
    {
        $entry = new QueryEntry(
            sql         : $sql,
            bindings    : $bindings,
            timestamp   : microtime(true),
            durationMs  : $durationMs,
            type        : $type,
            connection  : $connection,
            affectedRows: $affectedRows,
            error       : $error,
        );

        $this->entries[] = $entry;

        // Enforce max entries limit
        if ($this->maxEntries > 0 && count($this->entries) > $this->maxEntries) {
            array_shift($this->entries);
        }

        return $entry;
    }

    /**
     * Records a query entry directly from a QueryEntry object.
     */
    public function add(QueryEntry $entry) : self
    {
        $this->entries[] = $entry;

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
    public function all() : array
    {
        return $this->entries;
    }

    /**
     * Returns entries filtered by type.
     *
     * @return list<QueryEntry>
     */
    public function ofType(string $type) : array
    {
        return array_values(array_filter(
                                $this->entries,
                                static fn (QueryEntry $entry) : bool => strtoupper($entry->getType()) === strtoupper($type),
                            ));
    }

    /**
     * Returns entries that resulted in errors.
     *
     * @return list<QueryEntry>
     */
    public function errors() : array
    {
        return array_values(array_filter(
                                $this->entries,
                                static fn (QueryEntry $entry) : bool => $entry->hasError(),
                            ));
    }

    /**
     * Returns entries from a specific connection.
     *
     * @return list<QueryEntry>
     */
    public function forConnection(string $connection) : array
    {
        return array_values(array_filter(
                                $this->entries,
                                static fn (QueryEntry $entry) : bool => $entry->connection === $connection,
                            ));
    }

    /**
     * Returns the total number of recorded queries.
     */
    public function count() : int
    {
        return count($this->entries);
    }

    /**
     * Returns the average query duration in milliseconds.
     */
    public function averageDurationMs() : float
    {
        if (empty($this->entries)) {
            return 0.0;
        }

        return $this->totalDurationMs() / count($this->entries);
    }

    /**
     * Returns the total execution time of all queries in milliseconds.
     */
    public function totalDurationMs() : float
    {
        return array_sum(array_map(
                             static fn (QueryEntry $entry) : float => $entry->durationMs,
                             $this->entries,
                         ));
    }

    /**
     * Returns the maximum query duration in milliseconds.
     */
    public function maxDurationMs() : float
    {
        if (empty($this->entries)) {
            return 0.0;
        }

        return max(array_map(
                       static fn (QueryEntry $entry) : float => $entry->durationMs,
                       $this->entries,
                   ));
    }

    /**
     * Returns the slowest query entry.
     */
    public function slowest() : QueryEntry|null
    {
        if (empty($this->entries)) {
            return null;
        }

        $slowest     = null;
        $maxDuration = -1.0;

        foreach ($this->entries as $entry) {
            if ($entry->durationMs > $maxDuration) {
                $maxDuration = $entry->durationMs;
                $slowest     = $entry;
            }
        }

        return $slowest;
    }

    /**
     * Returns the elapsed time since the timeline started recording.
     */
    public function elapsedTimeMs() : float
    {
        return (microtime(true) - $this->startTime) * 1000;
    }

    /**
     * Returns a summary of query counts by type.
     *
     * @return array<string, int>
     */
    public function countsByType() : array
    {
        $counts = [];

        foreach ($this->entries as $entry) {
            $type          = $entry->getType();
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * Resets the timeline, clearing all entries.
     */
    public function reset() : void
    {
        $this->entries   = [];
        $this->startTime = microtime(true);
    }
}
