<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics;

use function count;

/**
 * Detects N+1 query patterns by tracking query executions.
 *
 * An N+1 query occurs when the same query pattern is executed many times
 * with different parameters, typically in a loop.
 */
final class DetectNPlusOneQuery
{
    /**
     * @var array<string, array{count: int, queries: array<string>, firstSeen: float, lastSeen: float}>
     */
    private array $queryPatterns = [];

    /**
     * @var array<NPlusOneQueryReport>
     */
    private array $reports = [];

    /**
     * @var int
     */
    private int $threshold;

    /**
     * @param int $threshold Number of same-pattern queries to trigger detection (default: 20)
     */
    public function __construct(int $threshold = 20)
    {
        $this->threshold = $threshold;
    }

    /**
     * Records a query execution for analysis.
     *
     * @param string     $query     The SQL query string
     * @param float|null $timestamp Optional timestamp in milliseconds
     */
    public function record(string $query, float|null $timestamp = null) : void
    {
        $timestamp   = $timestamp ?? microtime(true) * 1000;
        $fingerprint = QueryFingerprint::fromQuery($query);
        $hash        = $fingerprint->hash;

        if (! isset($this->queryPatterns[$hash])) {
            $this->queryPatterns[$hash] = [
                'count'     => 0,
                'queries'   => [],
                'firstSeen' => $timestamp,
                'lastSeen'  => $timestamp,
                'pattern'   => $fingerprint,
            ];
        }

        $this->queryPatterns[$hash]['count']++;
        $this->queryPatterns[$hash]['lastSeen'] = $timestamp;

        // Keep only a few sample queries to avoid memory issues
        if (count($this->queryPatterns[$hash]['queries']) < 5) {
            $this->queryPatterns[$hash]['queries'][] = $query;
        }

        // Check if threshold exceeded
        if ($this->queryPatterns[$hash]['count'] >= $this->threshold) {
            $this->generateReport($hash);
        }
    }

    /**
     * Generates a report for a detected N+1 pattern.
     */
    private function generateReport(string $hash) : void
    {
        if (isset($this->reports[$hash])) {
            return;
        }

        $data     = $this->queryPatterns[$hash];
        $timeSpan = $data['lastSeen'] - $data['firstSeen'];

        $report = new NPlusOneQueryReport(
            pattern      : $data['pattern'],
            count        : $data['count'],
            timeSpanMs   : $timeSpan,
            sampleQueries: $data['queries'],
            suggestion   : $this->generateSuggestion($data['pattern']->pattern()),
        );

        $this->reports[$hash] = $report;
    }

    /**
     * Generates an optimization suggestion based on the query pattern.
     */
    private function generateSuggestion(string $pattern) : string
    {
        $upperPattern = strtoupper($pattern);

        if (str_contains($upperPattern, 'WHERE') && str_contains($upperPattern, 'IN')) {
            return 'Use a single query with IN clause instead of multiple individual queries';
        }

        if (str_contains($upperPattern, 'SELECT') && str_contains($upperPattern, 'JOIN') === false) {
            return 'Consider using JOINs or eager loading to fetch related data in a single query';
        }

        return 'Use batch querying or eager loading to reduce the number of queries';
    }

    /**
     * Checks for N+1 patterns and returns any detected reports.
     *
     * @return array<NPlusOneQueryReport>
     */
    public function detect() : array
    {
        foreach ($this->queryPatterns as $hash => $data) {
            if ($data['count'] >= $this->threshold) {
                // Update or generate report with latest count
                $this->generateOrUpdateReport($hash);
            }
        }

        return array_values($this->reports);
    }

    /**
     * Updates an existing report with the latest count.
     */
    private function generateOrUpdateReport(string $hash) : void
    {
        $data     = $this->queryPatterns[$hash];
        $timeSpan = $data['lastSeen'] - $data['firstSeen'];

        $report = new NPlusOneQueryReport(
            pattern      : $data['pattern'],
            count        : $data['count'],
            timeSpanMs   : $timeSpan,
            sampleQueries: $data['queries'],
            suggestion   : isset($this->reports[$hash]) ? $this->reports[$hash]->suggestion : $this->generateSuggestion($data['pattern']->pattern()),
        );

        $this->reports[$hash] = $report;
    }

    /**
     * Returns all recorded query patterns.
     *
     * @return array<string, array{count: int, queries: array<string>, firstSeen: float, lastSeen: float}>
     */
    public function getPatterns() : array
    {
        return $this->queryPatterns;
    }

    /**
     * Returns the count of unique query patterns.
     */
    public function getPatternCount() : int
    {
        return count($this->queryPatterns);
    }

    /**
     * Resets all tracked patterns and reports.
     */
    public function reset() : void
    {
        $this->queryPatterns = [];
        $this->reports       = [];
    }

    /**
     * Gets the current detection threshold.
     */
    public function getThreshold() : int
    {
        return $this->threshold;
    }

    /**
     * Sets a new detection threshold.
     */
    public function setThreshold(int $threshold) : void
    {
        $this->threshold = $threshold;
    }
}
