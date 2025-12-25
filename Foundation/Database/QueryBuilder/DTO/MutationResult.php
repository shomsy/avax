<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\DTO;

/**
 * Immutable value object representing the result of a database mutation operation.
 *
 * -- intent: provide both success status and affected row count for INSERT/UPDATE/DELETE operations.
 * -- design: enables flexible return type handling while maintaining type safety.
 */
final readonly class MutationResult
{
    /**
     * @param int $affectedRows Number of rows affected by the mutation
     */
    public function __construct(public int $affectedRows) {}

    /**
     * Static factory for creating a successful result.
     *
     * @param int $count Number of affected rows
     *
     * @return self
     */
    public static function success(int $count) : self
    {
        return new self(affectedRows: $count);
    }

    /**
     * Static factory for creating a failed result (no rows affected).
     *
     * @return self
     */
    public static function none() : self
    {
        return new self(affectedRows: 0);
    }

    /**
     * Check if the mutation was successful (at least one row affected).
     *
     * @return bool
     */
    public function isSuccessful() : bool
    {
        return $this->affectedRows > 0;
    }

    /**
     * Get the number of affected rows.
     *
     * @return int
     */
    public function getAffectedRows() : int
    {
        return $this->affectedRows;
    }
}
