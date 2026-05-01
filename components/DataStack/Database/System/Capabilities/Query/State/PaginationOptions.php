<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\State;

/**
 * Immutable value object encapsulating pagination parameters (page, perPage, total).
 *
 * @see /docs/Foundation/Database/DSL/QueryExecution.md
 */
final readonly class PaginationOptions
{
    public int $perPage;

    public int $page;

    /**
     * @param int      $page    The current logical 1-based page index.
     * @param int      $perPage The technical volume of records to be retrieved per resulting page.
     * @param int|null $total   The optional total record count discovered for calculating pagination metadata.
     */
    public function __construct(
        ?int        $page = null,
        ?int        $perPage = null,
        public ?int $total = null,
    ) {
        $page        ??= 1;
        $perPage ??= 15;
        $this->page  = $page;
        $this->perPage = $perPage;
    }

    /**
     * Calculate the SQL OFFSET for the current page.
     */
    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}
