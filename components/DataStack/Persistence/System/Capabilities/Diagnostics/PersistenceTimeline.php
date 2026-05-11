<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics;

use function count;

/**
 * Timeline of query executions for diagnostic purposes.
 *
 * Records query executions with timing information to provide
 * a chronological view of database operations.
 */
final class PersistenceTimeline
{
    /**
     * @var array<array{query: string, duration: float, timestamp: float, fingerprint: QueryFingerprint}>
     */
    private array $entries = [];

    private float|null $startTime = null;

    private float|null $endTime = null;

    /**
     * Records a query execution in the timeline.
     *
     * @param  string  $query  The SQL query
     * @param  float  $duration  Duration in milliseconds
     * @param  float|null  $timestamp  Optional timestamp in milliseconds
     */
    public function record(string $query, float $duration, float|null $timestamp = null) : void
    {
        $timestamp ??= microtime(true) * 1000;
        $queryFingerprint = QueryFingerprint::fromQuery($query);

        $this->entries[] = [
            'query' => $query,
            'duration' => $duration,
            'timestamp' => $timestamp,
            'fingerprint' => $queryFingerprint,
        ];

        if ($this->startTime === null || $timestamp < $this->startTime) {
            $this->startTime = $timestamp;
        }

        if ($this->endTime === null || $timestamp > $this->endTime) {
            $this->endTime = $timestamp;
        }
    }

    /**
     * Returns the full timeline of query executions.
     *
     * @return array<array{query: string, duration: float, timestamp: float, fingerprint: QueryFingerprint}>
     */
    public function getTimeline(): array
    {
        return $this->entries;
    }

    /**
     * Returns queries matching a specific fingerprint pattern.
     *
     * @return array<array{query: string, duration: float, timestamp: float, fingerprint: QueryFingerprint}>
     */
    public function getByFingerprint(QueryFingerprint $queryFingerprint): array
    {
        return array_values(array_filter(
            $this->entries,
            static fn (array $entry): bool => $entry['fingerprint']->matchesFingerprint($queryFingerprint),
        ));
    }

    /**
     * Returns queries that took longer than the specified threshold.
     *
     * @param  float  $thresholdMs  Threshold in milliseconds
     * @return array<array{query: string, duration: float, timestamp: float, fingerprint: QueryFingerprint}>
     */
    public function getSlowQueries(float $thresholdMs): array
    {
        return array_values(array_filter(
            $this->entries,
            static fn (array $entry): bool => $entry['duration'] > $thresholdMs,
        ));
    }

    /**
     * Resets the timeline.
     */
    public function reset(): void
    {
        $this->entries = [];
        $this->startTime = null;
        $this->endTime = null;
    }

    /**
     * Returns a summary of the timeline.
     *
     * @return array{
     *     count: int,
     *     totalTime: float,
     *     averageTime: float,
     *     timeSpan: float,
     *     slowestQuery: array{query: string, duration: float, timestamp: float, fingerprint: QueryFingerprint}|null
     * }
     */
    public function summary(): array
    {
        return [
            'count' => $this->getQueryCount(),
            'totalTime' => $this->getTotalTime(),
            'averageTime' => $this->getAverageTime(),
            'timeSpan' => $this->getTimeSpan(),
            'slowestQuery' => $this->getSlowestQuery(),
        ];
    }

    /**
     * Returns the total number of queries recorded.
     */
    public function getQueryCount(): int
    {
        return count($this->entries);
    }

    /**
     * Returns the total execution time of all queries.
     */
    public function getTotalTime(): float
    {
        $total = 0.0;

        foreach ($this->entries as $entry) {
            $total += $entry['duration'];
        }

        return $total;
    }

    /**
     * Returns the average query execution time.
     */
    public function getAverageTime(): float
    {
        if ($this->entries === []) {
            return 0.0;
        }

        return $this->getTotalTime() / $this->getQueryCount();
    }

    /**
     * Returns the time span of the timeline.
     */
    public function getTimeSpan(): float
    {
        if ($this->startTime === null || $this->endTime === null) {
            return 0.0;
        }

        return $this->endTime - $this->startTime;
    }

    /**
     * Returns the slowest query entry.
     *
     * @return array{query: string, duration: float, timestamp: float, fingerprint: QueryFingerprint}|null
     */
    public function getSlowestQuery() : array|null
    {
        if ($this->entries === []) {
            return null;
        }

        $slowest = null;
        $maxDuration = -1.0;

        foreach ($this->entries as $entry) {
            if ($entry['duration'] > $maxDuration) {
                $maxDuration = $entry['duration'];
                $slowest = $entry;
            }
        }

        return $slowest;
    }
}
