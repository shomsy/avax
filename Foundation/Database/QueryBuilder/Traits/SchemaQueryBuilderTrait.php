<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Traits;

use Exception;
use Avax\Database\QueryBuilder\Exception\QueryBuilderException;

/**
 * **SchemaQueryBuilderTrait**
 *
 * Secure **Schema Management API** for QueryBuilder.
 *
 * ✅ **Features:**
 * - **Database Operations:** Create, Drop, Switch
 * - **Table Operations:** Rename, Drop, Exists Checks
 * - **OWASP Security:** Prevents **SQL Injection** & **Malicious Schema Manipulation**
 * - **Idempotency Checks:** Avoids unnecessary operations
 */
trait SchemaQueryBuilderTrait
{
    /**
     * **Switches to a different database** (if it exists).
     *
     * @param string $database The database name to switch to.
     *
     * @throws QueryBuilderException If the database does not exist.
     * @throws \Random\RandomException
     */
    public function useDatabase(string $database) : void
    {
        $this->validateDatabaseName(database: $database);

        if (! $this->databaseExists(database: $database)) {
            throw new QueryBuilderException(message: "Cannot switch: Database '{$database}' does not exist.");
        }

        try {
            $this->raw(sql: "USE {$this->quoteIdentifier(name:$database)}")->execute();
        } catch (Exception $e) {
            throw new QueryBuilderException(
                message: "Failed to switch to database '{$database}'", code: 0, previous: $e
            );
        }
    }

    /**
     * **Validates a database name against OWASP recommendations.**
     *
     * @param string $database The database name to validate.
     *
     * @throws QueryBuilderException If the name is invalid.
     */
    private function validateDatabaseName(string $database) : void
    {
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
            throw new QueryBuilderException(message: "Invalid database name: '{$database}'");
        }
    }

    /**
     * **Checks if a database exists.**
     *
     * @param string $database The database name.
     *
     * @return bool True if the database exists, otherwise false.
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Random\RandomException
     */
    public function databaseExists(string $database) : bool
    {
        $this->validateDatabaseName(database: $database);

        return $this
            ->table(tableName: 'information_schema.SCHEMATA')
            ->where(column: 'SCHEMA_NAME', value: $database)
            ->exists();
    }

    /**
     * **Creates a new database** (if it does not exist).
     *
     * @param string $database The database name.
     *
     * @throws QueryBuilderException If creation fails.
     * @throws \Random\RandomException
     */
    public function createDatabase(string $database) : void
    {
        $this->validateDatabaseName(database: $database);

        if ($this->databaseExists(database: $database)) {
            throw new QueryBuilderException(message: "Database '{$database}' already exists.");
        }

        try {
            $this->raw(sql: "CREATE DATABASE {$this->quoteIdentifier(name:$database)}")->execute();
        } catch (Exception $e) {
            throw new QueryBuilderException(message: "Failed to create database '{$database}'", code: 0, previous: $e);
        }
    }

    /**
     * **Drops an existing database** (if it exists).
     *
     * @param string $database The database name.
     *
     * @throws QueryBuilderException If deletion fails.
     * @throws \Random\RandomException
     */
    public function dropDatabase(string $database) : void
    {
        $this->validateDatabaseName(database: $database);

        if (! $this->databaseExists(database: $database)) {
            throw new QueryBuilderException(message: "Cannot drop: Database '{$database}' does not exist.");
        }

        try {
            $this->raw(sql: "DROP DATABASE IF EXISTS {$this->quoteIdentifier(name:$database)}")->execute();
        } catch (Exception $e) {
            throw new QueryBuilderException(message: "Failed to drop database '{$database}'", code: 0, previous: $e);
        }
    }

    /**
     * **Renames an existing table** (if it exists).
     *
     * @param string $oldName The current table name.
     * @param string $newName The new table name.
     *
     * @throws QueryBuilderException If renaming fails.
     * @throws \Random\RandomException
     * @throws \Random\RandomException
     */
    public function renameTable(string $oldName, string $newName) : void
    {
        $this->validateTableName(table: $oldName);
        $this->validateTableName(table: $newName);

        if (! $this->tableExists(table: $oldName)) {
            throw new QueryBuilderException(message: "Cannot rename: Table '{$oldName}' does not exist.");
        }

        if ($this->tableExists(table: $newName)) {
            throw new QueryBuilderException(message: "Cannot rename: Table '{$newName}' already exists.");
        }

        try {
            $this->raw(
                sql: "RENAME TABLE {$this->quoteIdentifier(name:$oldName)} TO {$this->quoteIdentifier(name:$newName)}"
            )->execute();
        } catch (Exception $e) {
            throw new QueryBuilderException(message: "Failed to rename table '{$oldName}'", code: 0, previous: $e);
        }
    }

    /**
     * **Validates a table name against OWASP recommendations.**
     *
     * @param string $table The table name to validate.
     *
     * @throws QueryBuilderException If the name is invalid.
     */
    private function validateTableName(string $table) : void
    {
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new QueryBuilderException(message: "Invalid table name: '{$table}'");
        }
    }

    /**
     * **Checks if a table exists in the current database.**
     *
     * @param string $table The table name.
     *
     * @return bool True if the table exists, otherwise false.
     * @throws \Avax\Database\QueryBuilder\Exception\QueryBuilderException
     * @throws \Random\RandomException
     */
    public function tableExists(string $table) : bool
    {
        $this->validateTableName(table: $table);

        return $this
            ->table(tableName: 'information_schema.tables')
            ->where(column: 'table_schema', value: $this->raw(sql: 'DATABASE()'))
            ->where(column: 'table_name', value: $table)
            ->exists();
    }

    /**
     * **Drops a table** (if it exists).
     *
     * @param string $table The table name.
     *
     * @throws QueryBuilderException If deletion fails.
     * @throws \Random\RandomException
     */
    public function dropTable(string $table) : void
    {
        $this->validateTableName(table: $table);

        if (! $this->tableExists(table: $table)) {
            throw new QueryBuilderException(message: "Cannot drop: Table '{$table}' does not exist.");
        }

        try {
            $this->raw(sql: "DROP TABLE IF EXISTS {$this->quoteIdentifier(name:$table)}")->execute();
        } catch (Exception $e) {
            throw new QueryBuilderException(message: "Failed to drop table '{$table}'", code: 0, previous: $e);
        }
    }
}
