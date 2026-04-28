<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Query\State;

/**
 * Immutable value object encapsulating pagination parameters (page, perPage, total).
 *
 * @see /docs/Foundation/Database/DSL/QueryExecution.md
 */
final readonly class PaginationOptions
{
    public int|null $total;
    public int      $perPage;
    public int      $page;

    /**
     * @param int      $page    The current logical 1-based page index.
     * @param int      $perPage The technical volume of records to be retrieved per resulting page.
     * @param int|null $total   The optional total record count discovered for calculating pagination metadata.
     */
    public function __construct(
        int|null $page = null,
        int|null $perPage = null,
        int|null $total = null
    )
    {
        $page          ??= 1;
        $perPage       ??= 15;
        $this->page    = $page;
        $this->perPage = $perPage;
        $this->total   = $total;
    }

    /**
     * Calculate the SQL OFFSET for the current page.
     */
    public function getOffset() : int
    {
        return ($this->page - 1) * $this->perPage;
    }
}
