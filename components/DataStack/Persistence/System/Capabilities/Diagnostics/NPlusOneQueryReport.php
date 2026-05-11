<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics;

/**
 * Report of detected N+1 query patterns.
 *
 * Contains information about a detected N+1 query problem including
 * the query pattern, execution count, time span, and suggestions.
 */
final readonly class NPlusOneQueryReport
{
    /**
     * @param  array<string>  $sampleQueries
     */
    public function __construct(
        public QueryFingerprint $pattern,
        public int $count,
        public float $timeSpanMs,
        public array $sampleQueries = [],
        public string|null $suggestion = null,
    ) {
    }

    /**
     * Returns the number of times this pattern was executed.
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Returns the time span over which these queries were executed.
     */
    public function timeSpanMs(): float
    {
        return $this->timeSpanMs;
    }

    /**
     * Returns sample queries matching this pattern.
     *
     * @return array<string>
     */
    public function sampleQueries(): array
    {
        return $this->sampleQueries;
    }

    /**
     * Returns the optimization suggestion.
     */
    public function suggestion() : string|null
    {
        return $this->suggestion;
    }

    /**
     * Checks if this is a severe N+1 problem.
     */
    public function isSevere(int $threshold = 50): bool
    {
        return $this->count >= $threshold;
    }

    /**
     * Returns a string summary of the report.
     */
    public function summary(): string
    {
        $summary = "N+1 Query Detected:\n";
        $summary .= sprintf('  Pattern: %s%s', $this->pattern->pattern(), PHP_EOL);
        $summary .= sprintf('  Count: %d%s', $this->count, PHP_EOL);
        $summary .= "  Time Span: {$this->timeSpanMs}ms\n";

        if ($this->suggestion !== null) {
            $summary .= sprintf('  Suggestion: %s%s', $this->suggestion, PHP_EOL);
        }

        return $summary;
    }

    /**
     * Returns the query pattern.
     */
    public function pattern(): QueryFingerprint
    {
        return $this->pattern;
    }

    /**
     * Creates a report with a default suggestion.
     */
    public function withSuggestion(string $suggestion): NPlusOneQueryReport
    {
        return new NPlusOneQueryReport(
            pattern      : $this->pattern,
            count        : $this->count,
            timeSpanMs   : $this->timeSpanMs,
            sampleQueries: $this->sampleQueries,
            suggestion   : $suggestion,
        );
    }
}
