<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\Execution;

/**
 * Interface MigrationExecutionServiceInterface
 *
 * Defines high-level operations for applying, rolling back,
 * and previewing schema migrations in a transactional and declarative manner.
 */
interface MigrationExecutionServiceInterface
{
    /**
     * Executes all pending migrations in order.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function executeUp() : void;

    /**
     * Rolls back the most recent batch of migrations.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function rollbackBatch() : void;

    /**
     * Simulates execution and returns SQL preview.
     *
     * @return array<string>
     */
    public function pretend() : array;
}
