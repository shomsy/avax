<?php

/**
 * Migration Status Value Object
 *
 * This file is part of the Gemini Database Migration System.
 *
 * @copyright Gemini Team 2024
 */

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\Enum;

/**
 * MigrationStatus Value Object represents the lifecycle states of a database migration.
 *
 * This immutable enum encapsulates all possible states a migration can transition through
 * during its lifecycle, ensuring type safety and domain integrity. Each state represents
 * a distinct phase in the migration process, making the domain model explicit and enforcing
 * business rules through type constraints.
 *
 * @api
 * @final
 * @since   1.0.0
 * @package Gemini\Database\Migration
 */
enum MigrationStatus: string
{
    /**
     * Represents a migration that is scheduled but not yet executed.
     * This is the initial state of any new migration.
     */
    case Pending = 'pending';

    /**
     * Represents a migration that has been successfully applied to the database.
     * Transitions from Pending state after successful execution.
     */
    case Executed = 'executed';

    /**
     * Represents a migration that has been reversed to its previous state.
     * Only migrations in Executed state can transition to RolledBack.
     */
    case RolledBack = 'rolled_back';

    /**
     * Represents a migration that encountered an error during execution or rollback.
     * Can transition from any state when an operation fails.
     */
    case Failed = 'failed';

    /**
     * Determines if the migration can be executed.
     *
     * @return bool True if the migration is in a state where it can be executed
     */
    public function canBeExecuted() : bool
    {
        return $this === self::Pending || $this === self::RolledBack;
    }

    /**
     * Determines if the migration can be rolled back.
     *
     * @return bool True if the migration is in a state where it can be rolled back
     */
    public function canBeRolledBack() : bool
    {
        return $this === self::Executed;
    }
}