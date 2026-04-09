<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\DTO;

/**
 * The "Operation Result" (Execution Result).
 *
 * -- what is it?
 * This is a standardized report that tells you whether a database operation
 * succeeded and how many rows were affected. Unlike a simple boolean, it
 * carries both the success status and the physical impact (count).
 *
 * -- why this exists:
 * 1. DDL Support: DDL statements (like CREATE TABLE) often return 0 affected
 *    rows but are still successful. A separate 'success' flag handles this.
 * 2. Enterprise Patterns: It provides a consistent, immutable object for
 *    checking any "Change" query.
 */
final readonly class ExecutionResult
{
    /**
     * @param bool $success      Whether the database accepted and performed the instruction.
     * @param int  $affectedRows The number of records touched (if applicable).
     */
    public function __construct(
        private bool $success,
        private int  $affectedRows = 0
    ) {}

    /**
     * Create a success report.
     */
    public static function success(int $affectedRows = 0) : self
    {
        return new self(success: true, affectedRows: $affectedRows);
    }

    /**
     * Create a failure report.
     */
    public static function failure() : self
    {
        return new self(success: false, affectedRows: 0);
    }

    /**
     * Was the operation successful?
     */
    public function isSuccessful() : bool
    {
        return $this->success;
    }

    /**
     * Get the number of affected rows.
     */
    public function getAffectedRows() : int
    {
        return $this->affectedRows;
    }
}
