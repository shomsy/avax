<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Recovery;

use Avax\HTTP\Session\Audit\Audit;
use Avax\HTTP\Session\Shared\Contracts\Storage\StoreInterface;
use Avax\HTTP\Session\Shared\Exceptions\RecoveryException;
use Avax\HTTP\Session\Shared\Serialization\Serializer;
use Throwable;

/**
 * 🧠 Recovery - Session Recovery Manager (Enhanced)
 *
 * Adds enterprise-friendly method aliases for integration with
 * SessionEngine: snapshot(), beginTransaction(), commit(), rollback().
 *
 * Backups are stored in memory only and scoped to the current PHP
 * process/request. This is intended as a short-lived safety net for
 * snapshots and transactions, not a durable, cross-process backup.
 *
 * This version remains backward-compatible with backup()/transaction().
 */
final class Recovery
{
    private const string DEFAULT_BACKUP = 'default';

    /**
     * @var array<string, array> Backup storage
     */
    private array $backups = [];

    /**
     * @var bool Transaction state
     */
    private bool $inTransaction = false;

    /**
     * @var string|null Current transaction backup name
     */
    private string|null $currentTransaction = null;

    public function __construct(
        private readonly StoreInterface $store,
        private readonly Audit|null     $audit = null
    ) {}

    // -----------------------------------------------------------------
    // 🧱 Compatibility Layer (for SessionEngine)
    // -----------------------------------------------------------------

    /**
     * Create a snapshot of the current session state.
     *
     * Alias for backup() with a default logical name.
     */
    public function snapshot(string $name = self::DEFAULT_BACKUP) : void
    {
        $this->backup(name: $name);
    }

    /**
     * Create an in-memory backup of the entire session store.
     *
     * @param string $name Logical backup identifier within this process.
     */
    public function backup(string $name = self::DEFAULT_BACKUP) : void
    {
        $data = $this->store->all();

        $this->backups[$name] = [
            'data'      => $data,
            'timestamp' => time(),
            'hash'      => hash(algo: 'sha256', data: serialize(value: $data)),
        ];
    }

    /**
     * Start a transactional backup (atomic session operation).
     */
    public function beginTransaction() : void
    {
        if ($this->inTransaction) {
            throw RecoveryException::transactionAlreadyStarted();
        }

        $this->currentTransaction = 'tx_' . uniqid();
        $this->backup(name: $this->currentTransaction);
        $this->inTransaction = true;
    }

    /**
     * Commit a transaction (finalize and remove backup).
     *
     * @throws RecoveryException If there is no active transaction or
     *                           internal transaction state is invalid.
     */
    public function commit() : void
    {
        if (! $this->inTransaction) {
            throw RecoveryException::noActiveTransaction(operation: 'commit');
        }
        if ($this->currentTransaction === null) {
            throw RecoveryException::invalidTransactionState();
        }

        $backupName = $this->currentTransaction;
        $this->deleteBackup(name: $backupName);
        $this->currentTransaction = null;
        $this->inTransaction      = false;

        $this->audit?->record(
            event: 'transaction_committed',
            data : [
                'backup'    => $backupName,
                'timestamp' => time(),
            ]);
    }

    // -----------------------------------------------------------------
    // 🔹 Original API
    // -----------------------------------------------------------------

    /**
     * Delete a named in-memory backup.
     */
    public function deleteBackup(string $name = self::DEFAULT_BACKUP) : bool
    {
        if (! isset($this->backups[$name])) {
            return false;
        }

        unset($this->backups[$name]);

        return true;
    }

    /**
     * Rollback current transaction to last snapshot.
     *
     * @throws RecoveryException If there is no active transaction or
     *                           internal transaction state is invalid.
     */
    public function rollback() : void
    {
        if (! $this->inTransaction) {
            throw RecoveryException::noActiveTransaction(operation: 'rollback');
        }
        if ($this->currentTransaction === null) {
            throw RecoveryException::invalidTransactionState();
        }

        $backupName = $this->currentTransaction;
        $this->restore(name: $backupName);
        $this->deleteBackup(name: $backupName);
        $this->currentTransaction = null;
        $this->inTransaction      = false;

        if ($this->audit !== null) {
            $this->audit->record(event: 'transaction_rollback', data: [
                'backup'    => $backupName,
                'timestamp' => time(),
                'reason'    => 'rollback',
            ]);
        }
    }

    /**
     * Restore session state from a named in-memory backup.
     *
     * @throws RecoveryException If backup integrity check fails.
     */
    public function restore(string $name = self::DEFAULT_BACKUP) : bool
    {
        if (! isset($this->backups[$name])) {
            return false;
        }

        $backup = $this->backups[$name];

        // Optional integrity check to detect accidental or malicious changes
        if (isset($backup['hash'])) {
            $expected = $backup['hash'];
            $current  = hash(algo: 'sha256', data: serialize(value: $backup['data']));

            if (! hash_equals(known_string: $expected, user_string: $current)) {
                throw RecoveryException::integrityCheckFailed(name: $name);
            }
        }

        $this->store->flush();

        foreach ($backup['data'] as $key => $value) {
            $this->store->put(key: $key, value: $value);
        }

        return true;
    }

    /**
     * Check whether a named backup exists.
     */
    public function hasBackup(string $name = self::DEFAULT_BACKUP) : bool
    {
        return isset($this->backups[$name]);
    }

    /**
     * List all logical backup identifiers currently held in memory.
     *
     * @return array<int, string>
     */
    public function listBackups() : array
    {
        return array_keys(array: $this->backups);
    }

    /**
     * Get metadata about a named backup (age, size, timestamp).
     */
    public function getBackupInfo(string $name = self::DEFAULT_BACKUP) : array|null
    {
        if (! isset($this->backups[$name])) {
            return null;
        }

        return [
            'name'      => $name,
            'timestamp' => $this->backups[$name]['timestamp'],
            'size'      => count(value: $this->backups[$name]['data']),
            'age'       => time() - $this->backups[$name]['timestamp'],
        ];
    }

    /**
     * Drop all in-memory backups for this Recovery instance.
     */
    public function clearAllBackups() : void
    {
        $this->backups = [];
    }

    /**
     * Check whether a recovery transaction is currently active.
     *
     * This is a convenience helper for callers that want to branch
     * on state instead of handling RecoveryException from commit()
     * or rollback().
     */
    public function isInTransaction() : bool
    {
        return $this->inTransaction;
    }

    /**
     * Perform an operation with automatic backup and rollback on failure.
     *
     * Keeps the original exception as previous while surfacing a
     * domain-specific RecoveryException to callers.
     *
     * @throws RecoveryException If the wrapped operation fails.
     */
    public function transaction(callable $operation, string $backupName = '') : mixed
    {
        $backupName = $backupName ?: 'transaction_' . uniqid();
        $this->backup(name: $backupName);

        try {
            $result = $operation();
            $this->deleteBackup(name: $backupName);

            if ($this->audit !== null) {
                $this->audit->record(event: 'transaction_committed', data: [
                    'backup'    => $backupName,
                    'timestamp' => time(),
                ]);
            }

            return $result;
        } catch (Throwable $e) {
            $this->restore(name: $backupName);
            $this->deleteBackup(name: $backupName);

            if ($this->audit !== null) {
                $this->audit->record(event: 'transaction_rolled_back', data: [
                    'backup'    => $backupName,
                    'timestamp' => time(),
                    'error'     => $e->getMessage(),
                ]);
            }

            throw RecoveryException::transactionFailed(reason: $e->getMessage());
        }
    }

    /**
     * Export the current session data as a serialized string.
     *
     * Intended for trusted internal tooling (CLI, admin utilities).
     */
    public function export() : string
    {
        return serialize(value: $this->store->all());
    }

    /**
     * Import session data from a serialized snapshot string.
     *
     * Security:
     * - Intended for trusted inputs only (internal tools).
     * - Uses safeUnserialize() to disallow arbitrary object
     *   instantiation during import.
     */
    public function import(string $data) : bool
    {
        try {
            $serializer  = new Serializer();
            $sessionData = $serializer->safeUnserialize(data: $data);

            if (! is_array(value: $sessionData)) {
                return false;
            }

            $this->store->flush();

            foreach ($sessionData as $key => $value) {
                $this->store->put(key: $key, value: $value);
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
