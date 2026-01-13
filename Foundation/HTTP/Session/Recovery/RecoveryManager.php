<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Recovery;

use Avax\HTTP\Session\Audit\Audit;
use Throwable;

/**
 * 🧠 RecoveryManager - Session Recovery Operations Orchestrator
 * ============================================================
 *
 * The RecoveryManager orchestrates all session recovery operations
 * including snapshots, rollbacks, and transactional execution.
 *
 * This manager provides:
 * - Snapshot creation and restoration
 * - Transactional session operations (begin/commit/rollback)
 * - Backup management (list, delete, info)
 * - Import/export functionality for session state
 *
 * 💡 Design Philosophy:
 * Recovery operations are critical for maintaining session integrity.
 * This manager ensures that session state can be safely backed up,
 * restored, and managed transactionally without affecting other domains.
 *
 * @author  Milos
 *
 * @version 5.0
 */
final readonly class RecoveryManager
{
    /**
     * RecoveryManager Constructor.
     *
     * @param Recovery   $recovery The recovery engine.
     * @param Audit|null $audit    Optional audit logger for recovery events.
     */
    public function __construct(
        private Recovery   $recovery,
        private Audit|null $audit = null
    ) {}

    // ----------------------------------------------------------------
    // 🔹 Snapshot Operations
    // ----------------------------------------------------------------

    /**
     * Create a snapshot of the current session state.
     *
     * Snapshots are in-memory backups that can be restored later.
     * Useful for implementing undo functionality or safe operations.
     *
     * @param string $name Snapshot identifier (default: 'default').
     */
    public function snapshot(string $name = 'default') : void
    {
        $this->recovery->snapshot(name: $name);
        $this->audit?->record(event: 'recovery.snapshot.created', data: compact(var_name: 'name'));
    }

    /**
     * Restore session state from a named snapshot.
     *
     * Replaces current session data with the snapshot contents.
     *
     * @param string $name Snapshot identifier (default: 'default').
     *
     * @throws \Avax\HTTP\Session\Exceptions\RecoveryException If snapshot doesn't exist or is corrupted.
     */
    public function restore(string $name = 'default') : void
    {
        $this->recovery->restore(name: $name);
        $this->audit?->record(event: 'recovery.snapshot.restored', data: compact(var_name: 'name'));
    }

    /**
     * Check if a snapshot exists.
     *
     * @param string $name Snapshot identifier.
     *
     * @return bool True if snapshot exists, false otherwise.
     */
    public function hasBackup(string $name = 'default') : bool
    {
        return $this->recovery->hasBackup(name: $name);
    }

    /**
     * List all available snapshots.
     *
     * Returns an array of snapshot names currently held in memory.
     *
     * @return array<int, string> List of snapshot identifiers.
     */
    public function listBackups() : array
    {
        return $this->recovery->listBackups();
    }

    /**
     * Get metadata about a snapshot.
     *
     * Returns information like creation timestamp, size, etc.
     *
     * @param string $name Snapshot identifier.
     *
     * @return array<string, mixed> Snapshot metadata.
     */
    public function getBackupInfo(string $name = 'default') : array
    {
        return $this->recovery->getBackupInfo(name: $name);
    }

    /**
     * Delete a specific snapshot.
     *
     * @param string $name Snapshot identifier.
     */
    public function deleteBackup(string $name = 'default') : void
    {
        $this->recovery->deleteBackup(name: $name);
        $this->audit?->record(event: 'recovery.snapshot.deleted', data: compact(var_name: 'name'));
    }

    /**
     * Clear all snapshots from memory.
     *
     * Removes all backup data. Use with caution.
     */
    public function clearAllBackups() : void
    {
        $this->recovery->clearAllBackups();
        $this->audit?->record(event: 'recovery.snapshots.cleared');
    }

    // ----------------------------------------------------------------
    // 🔹 Transactional Operations
    // ----------------------------------------------------------------

    /**
     * Check if a transaction is currently active.
     *
     * @return bool True if transaction is active, false otherwise.
     */
    public function isInTransaction() : bool
    {
        return $this->recovery->isInTransaction();
    }

    /**
     * Execute a callback within a transactional context.
     *
     * Automatically begins a transaction, executes the callback,
     * and commits on success or rolls back on failure.
     *
     * @param callable $callback The operation to execute.
     *
     * @throws Throwable Re-throws the original exception after rollback.
     */
    public function transaction(callable $callback) : void
    {
        try {
            $this->beginTransaction();
            $callback($this);
            $this->commit();
        } catch (Throwable $e) {
            $this->rollback();
            $this->audit?->record(
                event: 'recovery.transaction.failed',
                data : [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

            throw $e;
        }
    }

    /**
     * Begin a session transaction.
     *
     * Creates an automatic snapshot that will be used for rollback
     * if the transaction fails.
     */
    public function beginTransaction() : void
    {
        $this->recovery->beginTransaction();
        $this->audit?->record(event: 'recovery.transaction.began');
    }

    /**
     * Commit the current transaction.
     *
     * Finalizes the transaction and removes the automatic snapshot.
     *
     *
     * @throws \Avax\HTTP\Session\Exceptions\RecoveryException If no transaction is active.
     */
    public function commit() : void
    {
        $this->recovery->commit();
        $this->audit?->record(event: 'recovery.transaction.committed');
    }

    /**
     * Rollback the current transaction.
     *
     * Restores session state to the snapshot taken at transaction start.
     *
     *
     * @throws \Avax\HTTP\Session\Exceptions\RecoveryException If no transaction is active.
     */
    public function rollback() : void
    {
        $this->recovery->rollback();
        $this->audit?->record(event: 'recovery.transaction.rolled_back');
    }

    // ----------------------------------------------------------------
    // 🔹 Import/Export Operations
    // ----------------------------------------------------------------

    /**
     * Export current session state as a serialized string.
     *
     * Useful for backup, migration, or debugging purposes.
     *
     * ⚠️ Security Note: Intended for trusted internal use only.
     *
     * @return string Serialized session data.
     */
    public function export() : string
    {
        $data = $this->recovery->export();
        $this->audit?->record(event: 'recovery.session.exported');

        return $data;
    }

    /**
     * Import session state from a serialized string.
     *
     * Replaces current session data with imported state.
     *
     * ⚠️ Security Note: Only import data from trusted sources.
     * Uses safe unserialization to prevent object injection attacks.
     *
     * @param string $data Serialized session data.
     */
    public function import(string $data) : void
    {
        $this->recovery->import(data: $data);
        $this->audit?->record(event: 'recovery.session.imported');
    }

    // ----------------------------------------------------------------
    // 🔹 Internal Access
    // ----------------------------------------------------------------

    /**
     * Get the underlying Recovery instance.
     *
     * Provides direct access to the recovery engine for advanced operations.
     *
     * @return Recovery The recovery instance.
     */
    public function recovery() : Recovery
    {
        return $this->recovery;
    }
}
