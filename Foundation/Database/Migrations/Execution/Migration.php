<?php

declare(strict_types=1);

namespace Avax\Migrations\Execution;

/**
 * Technical contract for all database migration executions.
 *
 * -- intent: define the structural lifecycle (Up/Down) that the Execution Engine triggers.
 */
interface Migration
{
    /**
     * Apply the structural database modifications.
     */
    public function up(): void;

    /**
     * Revert the structural database modifications.
     */
    public function down(): void;
}
