<?php

declare(strict_types=1);

namespace Avax\Database\Query;

/**
 * Pragmatic value object encapsulating pagination parameters for large datasets.
 *
 * -- intent: provide a structured way to handle record offsets and limits.
 */
final readonly class PaginationOptions
{
    /**
     * Constructor promoting pagination settings via PHP 8.3 features.
     *
     * -- intent: define the current window of the result set.
     *
     * @param int      $page    Current page index (1-based)
     * @param int      $perPage Number of records per resulting page
     * @param int|null $total   Optional total record count for metadata calculation
     */
    public function __construct(
        public int      $page = 1,
        public int      $perPage = 15,
        public int|null $total = null
    ) {}

    /**
     * Calculate the technical offset value for SQL retrieval.
     *
     * -- intent: transform logical page number into physical SQL offset.
     *
     * @return int
     */
    public function getOffset() : int
    {
        return ($this->page - 1) * $this->perPage;
    }
}


