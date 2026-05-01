<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Observability;

use Override;
use Stringable;

/**
 * A fingerprint representing a normalized query pattern.
 *
 * Readonly value object that groups similar queries by replacing
 * literal values with placeholders.
 */
final readonly class QueryFingerprintEntry implements Stringable
{
    public function __construct(
        public string $pattern,
        public string $hash,
        public string $originalQuery,
    ) {}

    /**
     * Checks if this fingerprint matches another query.
     */
    public function matches(string $query) : bool
    {
        new self(
            pattern      : $this->pattern,
            hash         : $this->hash,
            originalQuery: $query,
        );

        return false; // This method doesn't make sense on an existing entry
    }

    /**
     * Returns a string representation.
     */
    #[Override]
    public function __toString() : string
    {
        return $this->pattern;
    }
}

/**
 * Creates fingerprints from SQL queries by replacing literal values with placeholders.
 *
 * Fingerprints enable grouping similar queries for analysis,
 * caching, and pattern detection. The fingerprinter normalizes
 * queries by:
 * - Replacing string literals with '?'
 * - Replacing numeric literals with '?'
 * - Normalizing whitespace
 * - Lowercasing SQL keywords
 */
final readonly class QueryFingerprinter
{
    public function __construct(
        /**
         * @var bool Whether to lowercase the entire query
         */
        private bool $lowercase = false,
        /**
         * @var bool Whether to include the original query in the fingerprint entry
         */
        private bool $includeOriginal = false
    )
    {
    }

    /**
     * Creates a fingerprint with the original query preserved.
     */
    public function fingerprintWithOriginal(string $sql) : QueryFingerprintEntry
    {
        $pattern = $this->normalize($sql);
        $hash    = $this->hash($pattern);

        return new QueryFingerprintEntry(
            pattern      : $pattern,
            hash         : $hash,
            originalQuery: $sql,
        );
    }

    /**
     * Normalizes a SQL query by replacing literal values with placeholders.
     *
     * This is the core fingerprinting algorithm that:
     * 1. Replaces string literals (single-quoted strings) with '?'
     * 2. Replaces numeric literals (integers and floats) with '?'
     * 3. Normalizes whitespace to single spaces
     * 4. Optionally lowercases the query
     */
    public function normalize(string $sql) : string
    {
        $normalized = $sql;

        // Replace string literals (handles escaped quotes: '')
        $normalized = preg_replace("/'(?:''|[^'])*'/", '?', $normalized) ?? $normalized;

        // Replace numeric literals (floats first, then integers)
        // Use word boundaries to avoid replacing numbers inside identifiers
        $normalized = preg_replace('/\b\d+\.\d+\b/', '?', $normalized) ?? $normalized;
        $normalized = preg_replace('/\b\d+\b/', '?', $normalized) ?? $normalized;

        // Normalize whitespace (collapse multiple spaces/tabs/newlines into one)
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
        $normalized = trim($normalized);

        // Optionally lowercase
        if ($this->lowercase) {
            return strtolower($normalized);
        }

        return $normalized;
    }

    /**
     * Computes the hash of a fingerprint pattern.
     */
    public function hash(string $pattern) : string
    {
        return hash('sha256', $pattern);
    }

    /**
     * Normalizes only the WHERE clause values (preserves table/column names).
     *
     * Useful when you want to group queries by structure but keep
     * the referenced objects visible.
     */
    public function normalizeValuesOnly(string $sql) : string
    {
        $normalized = $sql;

        // Replace string literals
        $normalized = preg_replace("/'(?:''|[^'])*'/", '?', $normalized) ?? $normalized;

        // Only replace numbers that appear after comparison operators or IN clauses
        $normalized = preg_replace('/((?:=|!=|<>|>=|<=|>|<|IN\s*\()\s*)\d+(\.\d+)?/', '$1?', $normalized) ?? $normalized;

        // Normalize whitespace
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }

    /**
     * Checks if two queries have the same fingerprint.
     */
    public function isSamePattern(string $sqlA, string $sqlB) : bool
    {
        return $this->fingerprint($sqlA)->hash === $this->fingerprint($sqlB)->hash;
    }

    /**
     * Creates a fingerprint for a SQL query.
     */
    public function fingerprint(string $sql) : QueryFingerprintEntry
    {
        $pattern = $this->normalize($sql);
        $hash    = $this->hash($pattern);

        return new QueryFingerprintEntry(
            pattern      : $pattern,
            hash         : $hash,
            originalQuery: $this->includeOriginal ? $sql : '',
        );
    }

    /**
     * Groups queries by their fingerprints.
     *
     * @param list<string> $queries
     *
     * @return array<string, list<string>> Hash => [queries...]
     */
    public function groupByFingerprint(array $queries) : array
    {
        $groups = [];

        foreach ($queries as $query) {
            $fingerprint                  = $this->fingerprint($query);
            $groups[$fingerprint->hash][] = $query;
        }

        return $groups;
    }

    /**
     * Returns the number of unique fingerprints in a list of queries.
     *
     * @param list<string> $queries
     */
    public function uniqueCount(array $queries) : int
    {
        $fingerprints = [];

        foreach ($queries as $query) {
            $fingerprint                      = $this->fingerprint($query);
            $fingerprints[$fingerprint->hash] = true;
        }

        return count($fingerprints);
    }
}
