<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent;

use Avax\Components\DataStack\Persistence\System\Capabilities\PersistenceDiagnostics\QueryFingerprint;
use function count;

/**
 * Query execution results with metadata.
 *
 * Encapsulates the result set along with execution metrics.
 */
final readonly class QueryResult
{
    /**
     * @param  array<array<string, mixed>>  $rows
     */
    public function __construct(
        private array $rows = [],
        public int|null              $count = null,
        public float|null            $tookMs = null,
        public QueryFingerprint|null $fingerprint = null,
    ) {
    }

    /**
     * Returns the result rows.
     *
     * @return array<array<string, mixed>>
     */
    public function rows(): array
    {
        return $this->rows;
    }

    /**
     * Returns the total count of rows.
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Returns the execution time in milliseconds.
     */
    public function tookMs() : float|null
    {
        return $this->tookMs;
    }

    /**
     * Checks if the result set is empty.
     */
    public function isEmpty(): bool
    {
        return $this->rows === [];
    }

    /**
     * Returns the first row or null if empty.
     *
     * @return array<string, mixed>|null
     */
    public function first() : array|null
    {
        return $this->rows[0] ?? null;
    }

    /**
     * Converts the result to an array (alias of rows()).
     *
     * @return array<array<string, mixed>>
     */
    public function toArray(): array
    {
        return $this->rows;
    }

    /**
     * Returns a new instance with the specified rows.
     *
     * @param  array<array<string, mixed>>  $rows
     */
    public function withRows(array $rows): QueryResult
    {
        return new QueryResult(
            rows       : $rows,
            count      : count($rows),
            tookMs     : $this->tookMs,
            fingerprint: $this->fingerprint,
        );
    }

    /**
     * Returns a new instance with the specified execution time.
     */
    public function withTookMs(float $tookMs): QueryResult
    {
        return new QueryResult(
            rows       : $this->rows,
            count      : $this->count,
            tookMs     : $tookMs,
            fingerprint: $this->fingerprint,
        );
    }

    /**
     * Returns a new instance with the specified fingerprint.
     */
    public function withFingerprint(QueryFingerprint $queryFingerprint): QueryResult
    {
        return new QueryResult(
            rows       : $this->rows,
            count      : $this->count,
            tookMs     : $this->tookMs,
            fingerprint: $queryFingerprint,
        );
    }
}
