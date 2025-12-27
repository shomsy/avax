<?php

declare(strict_types=1);

namespace Avax\Database\Query;

/**
 * Immutable value object encapsulating pagination parameters (page, perPage, total).
 *
 * @see docs/DSL/QueryExecution.md
 */
final readonly class PaginationOptions
{
    /**
     * @param int      $page    The current logical 1-based page index.
     * @param int      $perPage The technical volume of records to be retrieved per resulting page.
     * @param int|null $total   The optional total record count discovered for calculating pagination metadata.
     */
    public function __construct(
        public int      $page = 1,
        public int      $perPage = 15,
        public int|null $total = null
    ) {}

    /**
     * Calculate the SQL OFFSET for the current page.
     *
     * @return int
     */
    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}
