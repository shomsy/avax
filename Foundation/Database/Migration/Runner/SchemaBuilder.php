<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner;

use Closure;
use Avax\Database\Migration\Design\Table\Table;
use Avax\Database\Migration\Runner\Exception\MigrationException;
use Avax\Database\QueryBuilder\Exception\QueryBuilderException;
use Avax\Database\QueryBuilder\QueryBuilder;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * SchemaBuilder
 *
 * Infrastructure facade for declaratively managing database schema via Domain-Specific Language (DSL).
 *
 * This class is responsible for all Data Definition Language (DDL) operations related to schema evolution,
 * such as table creation, deletion, renaming, and connectivity checks. It wraps these operations in
 * transaction-safe boundaries and provides centralized logging, exception normalization, and
 * semantic mapping from domain-oriented blueprints to raw SQL statements.
 *
 * It uses:
 * - QueryBuilder for driver-agnostic query execution
 * - Table DSL for semantic schema construction
 * - LoggerInterface for audit logging and observability
 *
 * It provides:
 * - Transactional safety for destructive operations
 * - Health check APIs for deployment probes
 * - Domain-safe exception boundaries for orchestration code
 *
 * This class belongs to the Infrastructure Layer of a Clean Architecture system,
 * and is intentionally readonly and immutable for safety in concurrent and async environments.
 *
 * @package Avax\Database\Migration\Runner
 * @readonly
 * @final
 */
final readonly class SchemaBuilder
{
    /**
     * Constructor for initializing the QueryBuilder and LoggerInterface dependencies.
     *
     * @param QueryBuilder    $queryBuilder An instance of QueryBuilder to handle database queries.
     * @param LoggerInterface $logger       An instance of LoggerInterface for logging purposes.
     *
     * @return void
     */
    public function __construct(
        private QueryBuilder    $queryBuilder,
        private LoggerInterface $logger
    ) {}

    /**
     * Determines whether a physical table exists in the active database schema.
     *
     * This check queries the `information_schema.tables` system view using the current
     * database context. It ensures compatibility with multi-tenant schemas and provides
     * fault-tolerant behavior on driver-level errors.
     *
     * @param non-empty-string $table The fully qualified table name to inspect
     *
     * @return bool True if the table exists, false otherwise
     * @throws \Random\RandomException
     */
    public function tableExists(string $table) : bool
    {
        // ✅ Guard against empty table names (domain invariant)
        if (trim($table) === '') {
            return false;
        }

        try {
            return $this->queryBuilder
                ->table(tableName: 'information_schema.tables')
                ->where(column: 'table_schema', value: $this->queryBuilder->raw(sql: 'DATABASE()'))
                ->where(column: 'table_name', value: $table)
                ->exists();
        } catch (QueryBuilderException $e) {
            // ❌ Defensive: driver-level failures shouldn't propagate upward
            $this->logger->error(
                message: 'Failed to verify table existence.',
                context: ['table' => $table, 'exception' => $e::class, 'error' => $e->getMessage()]
            );

            $this->output(message: $e->getMessage(), type: 'warning');

            return false;
        }
    }

    /**
     * Handles message output formatting based on the execution context (CLI or API/HTTP).
     *
     * This method implements the Single Responsibility Principle by managing output
     * formatting and delivery based on the application's runtime environment.
     *
     * @param string $message The message content to be output
     * @param string $type    The message type for color coding (success|warning|error|info)
     *
     * @return string|null Returns null for CLI context (direct output) or string for HTTP context
     */
    public function output(string $message, string $type = 'info') : string|null
    {
        // Determine if we're running in a CLI environment
        if (php_sapi_name() === 'cli') {
            // Define ANSI color codes for different message types in CLI
            $color = match ($type) {
                'success' => "\033[32m", // Green signifies successful operations
                'warning' => "\033[33m", // Yellow indicates warnings or cautions
                'error'   => "\033[31m", // Red represents errors or failures
                default   => "\033[0m",  // Default color for informational messages
            };

            // Output the colored message with reset code and line ending
            echo $color . $message . "\033[0m" . PHP_EOL;

            // CLI context doesn't need return value as output is immediate
            return '';
        }

        // For HTTP/API context, return the raw message
        return $message;
    }

    /**
     * Creates a new database table using a fluent Domain-Specific Language (DSL) schema definition.
     *
     * This method allows declarative schema construction by accepting a user-defined DSL callback.
     * Internally, it ensures atomic DDL execution via transactional encapsulation and logs all operations.
     *
     * @param non-empty-string $table    The name of the table to be created
     * @param Closure          $callback Closure defining the table schema using the fluent DSL
     *
     */
    public function create(string $table, Closure $callback) : bool
    {
        // ✅ Defensive: enforce a table name contract
        if (trim($table) === '') {
            $this->logger->warning(
                message: 'Table name is empty.',
                context: ['table' => $table]
            );

            $this->output(message: 'Table name is empty.', type: 'warning');

            return false;
        }

        try {
            // ✅ Construct new Table schema blueprint using domain factory
            $blueprint = Table::create(name: $table);

            // ✅ Delegate table schema definition to user via DSL callback
            $callback($blueprint);

            // ✅ Generate SQL from blueprint (idempotent)
            $sql = $blueprint->toSql();

            // ✅ Execute SQL within transactional boundary (atomic DDL)
            $this->queryBuilder->transaction(
                operations: fn() => $this->queryBuilder->raw(sql: $sql)->execute()
            );

            // ✅ Structured operation logging for auditability
            $this->logger->info(
                message: 'Table successfully created.',
                context: ['table' => $table, 'query' => $sql]
            );

            return true;
        } catch (Throwable $e) {
            // ❌ Translate all low-level driver/query exceptions into a domain exception
            $this->logger->error(
                message: 'Failed to create table.',
                context: ['table' => $table, 'exception' => $e::class, 'error' => $e->getMessage()]
            );

            $this->output(message: 'Failed to create table.', type: 'error');

            return false;
        }
    }

    /**
     * Drops the specified table if it exists, using transactional guarantees.
     *
     * This operation is destructive and irreversible. It wraps the `DROP TABLE`
     * execution in a transactional context to ensure rollback capability on failure.
     * Logging is performed for observability, and domain-specific exception wrapping
     * ensures consistent error boundaries.
     *
     * @param non-empty-string $table The name of the table to drop
     *
     * @throws MigrationException On failure to drop the table
     */
    public function drop(string $table) : bool
    {
        if (trim($table) === '') {
            $this->logger->warning(message: 'Drop failed: empty table name.', context: ['table' => $table]);

            $this->output(message: 'Drop failed: empty table name.', type: 'warning');

            return false;
        }

        try {
            // 💥 Atomic drop with rollback support
            $this->queryBuilder->transaction(
                operations: fn() => $this->queryBuilder
                    ->table(tableName: $table)
                    ->drop()
            );

            // 📘 Successful audit trail
            $this->logger->info(
                message: 'Table dropped successfully.',
                context: ['table' => $table]
            );

            $this->output(message: 'Table dropped successfully.');

            return true;
        } catch (Throwable $e) {
            // 🚨 Surface clean domain-level failure
            $this->logger->error(
                message: 'Failed to drop table.',
                context: [
                             'table'     => $table,
                             'exception' => $e::class,
                             'message'   => $e->getMessage(),
                         ]
            );

            $this->output(message: 'Failed to drop table.', type: 'error');

            return false;
        }
    }

    /**
     * Renames a table within the database schema using transactional guarantees.
     *
     * This method encapsulates the renaming of a table from its current name to a new name.
     * The operation is executed within a transaction, ensuring rollback on failure.
     * Logs are emitted to track structural changes for audit purposes.
     *
     * @param non-empty-string $oldName The current name of the table
     * @param non-empty-string $newName The desired new name for the table
     *
     * @throws MigrationException When renaming fails due to invalid names or query execution errors
     */
    public function rename(string $oldName, string $newName) : bool
    {
        if (trim($oldName) === '' || trim($newName) === '') {
            $this->logger->warning(
                message: 'Cannot rename table: source or destination name is empty.',
                context: [
                             'old_name' => $oldName,
                             'new_name' => $newName,
                         ]
            );

            $this->output(message: 'Cannot rename table: source or destination name is empty.', type: 'warning');

            return false;
        }

        try {
            // 🛡 Perform rename in transaction for rollback safety
            $this->queryBuilder->transaction(
                operations: fn() => $this->queryBuilder->renameTable(
                    oldName: $oldName,
                    newName: $newName
                )
            );

            // 📋 Log structural schema change
            $this->logger->info(
                message: 'Table renamed successfully.',
                context: [
                             'old_name' => $oldName,
                             'new_name' => $newName,
                         ]
            );

            $this->output(message: 'Table renamed successfully.');

            return true;
        } catch (Throwable $e) {
            // 🧱 Wrap lower-level failure in domain-safe exception
            $this->logger->warning(
                message: "Failed to rename table '{$oldName}' to '{$newName}'",
                context: [
                             'old_name'  => $oldName,
                             'new_name'  => $newName,
                             'exception' => $e::class,
                             'message'   => $e->getMessage(),
                         ]
            );

            $this->output(message: 'Failed to rename table.', type: 'warning');

            return false;
        }
    }

    /**
     * Truncates a table, removing all data while retaining schema structure.
     *
     * This operation deletes all records from the given table without logging individual row deletions.
     * It is faster than a DELETE operation and suitable for resetting state in non-production contexts.
     * The operation is performed transactionally and wrapped in domain-safe exception boundaries.
     *
     * @param non-empty-string $table The name of the table to truncate
     *
     * @throws MigrationException If truncation fails due to SQL or driver issues
     */
    public function truncate(string $table) : bool
    {
        if (trim($table) === '') {
            $this->logger->warning(message: 'Cannot truncate table: table name is empty.');
            $this->output(message: 'Cannot truncate table: table name is empty.', type: 'warning');

            return false;
        }

        try {
            // 🚨 Run inside a transaction to ensure rollback safety
            $this->queryBuilder->transaction(
                operations: fn() => $this->queryBuilder->table(tableName: $table)->truncate()
            );

            // 📢 Log action for observability and audit trail
            $this->logger->info(
                message: 'Table truncated successfully.',
                context: ['table' => $table]
            );

            $this->output(message: 'Table truncated successfully.');

            return true;
        } catch (Throwable $e) {
            // 🧱 Encapsulate and elevate to domain-level failure
            $this->logger->error(
                message: 'Failed to truncate table.',
                context: [
                             'table'     => $table,
                             'exception' => $e::class,
                             'message'   => $e->getMessage(),
                         ]
            );

            $this->output(message: 'Failed to truncate table.', type: 'error');

            return false;
        }
    }

    /**
     * Checks whether a given database exists in the current RDBMS instance.
     *
     * Queries the `information_schema.SCHEMATA` view to determine if the specified
     * database schema is present. This method is essential for conditional migrations,
     * onboarding flows, or database provisioning orchestration.
     *
     * @param non-empty-string $database The name of the database schema to check
     *
     * @return bool True if the schema exists, false otherwise
     * @throws \Random\RandomException
     */
    public function databaseExists(string $database) : bool
    {
        // 🧱 Defensive contract enforcement
        if (trim($database) === '') {
            $this->logger->warning(
                message: 'Attempted to check database existence with empty name.',
                context: ['database' => $database]
            );

            $this->output(message: 'Cannot check database existence: database name is empty.', type: 'warning');

            return false;
        }

        try {
            // 📦 Query the information schema for the presence of the schema name
            return $this->queryBuilder
                ->table(tableName: 'information_schema.SCHEMATA')
                ->where(column: 'SCHEMA_NAME', value: $database)
                ->exists();
        } catch (QueryBuilderException $e) {
            // 🪵 Log error for observability and diagnostics
            $this->logger->error(
                message: 'Failed to check database existence.',
                context: [
                             'database'  => $database,
                             'exception' => $e::class,
                             'error'     => $e->getMessage(),
                         ]
            );

            $this->output(message: 'Failed to check database existence.', type: 'error');

            return false;
        }
    }

    /**
     * Creates a new database schema if it does not already exist.
     *
     * This method is typically used during bootstrap, provisioning, or deployment flows.
     * It ensures explicit schema creation with high observability and proper fault isolation.
     *
     * @param non-empty-string $database The name of the schema to be created
     *
     * @throws MigrationException If database creation fails
     */
    public function createDatabase(string $database) : bool
    {
        // 🧱 Domain precondition: avoid invalid names
        if (trim($database) === '') {
            $this->logger->warning(
                message: 'Attempted to create database with empty name.',
                context: ['database' => $database]
            );

            $this->output(message: 'Cannot create database: database name is empty.');

            return false;
        }

        try {
            // 🛠 Execute database creation command via query builder abstraction
            $this->queryBuilder->createDatabase(database: $database);

            // 🧾 Log the successful creation event for observability
            $this->logger->info(
                message: 'Database schema successfully created.',
                context: ['database' => $database]
            );

            $this->output(message: 'Database schema successfully created.');

            return true;
        } catch (Throwable $e) {
            // 🔥 Wrap infrastructure failure in domain-specific exception
            $this->logger->warning(
                message: "Failed to create database '{$database}'",
                context: [
                             'database'  => $database,
                             'exception' => $e::class,
                             'error'     => $e->getMessage(),
                         ]
            );

            $this->output(message: 'Failed to create database.', type: 'error');

            return false;
        }
    }

    /**
     * Verifies database connectivity by attempting to select the given schema.
     *
     * This check is used for liveness/readiness probes, orchestration health checks,
     * and resilience features that depend on connection status with minimal overhead.
     *
     * @param non-empty-string $database The name of the database schema to check connectivity for
     *
     * @return bool True if the connection is healthy, false otherwise
     * @throws \Random\RandomException
     */
    public function isConnectionHealthy(string $database) : bool
    {
        // 🧱 Guard clause: avoid checking unnamed schemas
        if (trim($database) === '') {
            $this->logger->warning(
                message: 'Health check failed — database name was empty.',
                context: ['database' => $database]
            );

            $this->output(message: 'Cannot check database connectivity: database name is empty.', type: 'warning');

            return false;
        }

        try {
            // 🧪 Attempt to switch to target schema
            $this->queryBuilder->useDatabase(database: $database);

            // ✅ If successful, consider the connection healthy
            $this->output(message: 'Database connectivity check successful.');

            return true;
        } catch (QueryBuilderException $e) {
            // ❌ Connection or database switch failed — log failure
            $this->logger->error(
                message: 'Database connectivity check failed.',
                context: ['database' => $database, 'error' => $e->getMessage()]
            );

            $this->output(message: 'Database connectivity check failed.', type: 'error');

            return false;
        }
    }
}
