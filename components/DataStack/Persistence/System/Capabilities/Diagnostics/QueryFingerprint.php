<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics;

use Stringable;

/**
 * Normalizes queries to patterns for grouping and comparison.
 *
 * Replaces literal values with placeholders to create a fingerprint
 * that can be used to group similar queries.
 */
final readonly class QueryFingerprint implements Stringable
{
    public function __construct(
        public string $originalQuery,
        public ?string $pattern = null,
        public ?string $hash = null,
    ) {
    }

    /**
     * Returns the normalized query pattern.
     */
    public function pattern(): string
    {
        return $this->pattern;
    }

    /**
     * Returns the hash of the pattern.
     */
    public function hash(): string
    {
        return $this->hash;
    }

    /**
     * Checks if this fingerprint matches another query.
     */
    public function matches(string $query): bool
    {
        $queryFingerprint = self::fromQuery($query);

        return $this->hash === $queryFingerprint->hash;
    }

    /**
     * Factory method to create a QueryFingerprint with auto-computed values.
     */
    public static function fromQuery(string $query): QueryFingerprint
    {
        $normalizer = new self(originalQuery: $query, pattern: '', hash: '');
        $pattern = $normalizer->normalize($query);
        $hash = hash('sha256', $pattern);

        return new self(
            originalQuery: $query,
            pattern      : $pattern,
            hash         : $hash,
        );
    }

    /**
     * Normalizes a query by replacing literal values with placeholders.
     */
    public function normalize(string $query): string
    {
        // Replace string literals (single quotes)
        $normalized = preg_replace("/'[^']*'/", '?', $query);

        // Replace numeric literals (integers and floats)
        // Be careful not to replace numbers in table/column names
        $normalized = preg_replace('/\b\d+\.\d+\b/', '?', (string) $normalized);
        $normalized = preg_replace('/\b\d+\b/', '?', (string) $normalized);

        // Normalize whitespace
        $normalized = preg_replace('/\s+/', ' ', (string) $normalized);

        return trim((string) $normalized);
    }

    /**
     * Checks if this fingerprint matches another fingerprint.
     */
    public function matchesFingerprint(QueryFingerprint $queryFingerprint): bool
    {
        return $this->hash === $queryFingerprint->hash;
    }

    /**
     * Returns a string representation.
     */
    public function __toString(): string
    {
        return (string) $this->pattern;
    }
}
