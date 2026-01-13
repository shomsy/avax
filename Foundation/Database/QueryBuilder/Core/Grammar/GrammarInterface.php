<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Grammar;

use Avax\Database\Query\QueryState;

/**
 * Technical contract for translating logical QueryState into dialect-specific SQL SNAPSHOTS.
 *
 * -- intent:
 * Decouples the abstract query representation (projection, filters, joins)
 * from the physical RDBMS dialects (MySQL, SQLite, etc.), allowing the
 * system to support multiple database engines through specialized
 * compiler implementations.
 *
 * -- invariants:
 * - Implementations must follow the established SQL standard while applying dialect-specific optimizations.
 * - Compilation must be strictly deterministic based on the provided QueryState.
 * - All technical identifiers must be securely wrapped to prevent keyword collisions.
 *
 * -- boundaries:
 * - Does NOT interact with the physical database connection (delegated to Executor).
 * - Does NOT perform parameter binding (handled by Connection via normalized placeholders).
 */
interface GrammarInterface
{
    /**
     * Coordinate the technical transformation of QueryState into a SELECT SQL statement.
     *
     * -- intent:
     * Compile a valid data retrieval instruction including projections,
     * relationships, filters, aggregations, and ordering.
     *
     * @param  QueryState  $state  The immutable container holding the current query metadata.
     * @return string THE compiled dialect-specific SQL SELECT string.
     */
    public function compileSelect(QueryState $state): string;

    /**
     * Coordinate the technical transformation of QueryState into an INSERT SQL statement.
     *
     * -- intent:
     * Compile a valid data creation instruction based on the provided mutation
     * values within the state.
     *
     * @param  QueryState  $state  The technical state containing the mutation payload.
     * @return string THE compiled dialect-specific SQL INSERT string.
     */
    public function compileInsert(QueryState $state): string;

    /**
     * Coordinate the technical transformation of QueryState into an UPDATE SQL statement.
     *
     * -- intent:
     * Compile a data modification instruction that applies specific value
     * changes to records matching the state's filtering criteria.
     *
     * @param  QueryState  $state  The technical state containing both mutation values and filters.
     * @return string THE compiled dialect-specific SQL UPDATE string.
     */
    public function compileUpdate(QueryState $state): string;

    /**
     * Coordinate the technical transformation of QueryState into a DELETE SQL statement.
     *
     * -- intent:
     * Compile a data removal instruction targeting records that satisfy
     * the state's filtering criteria.
     *
     * @param  QueryState  $state  The technical state defining the deletion boundary.
     * @return string THE compiled dialect-specific SQL DELETE string.
     */
    public function compileDelete(QueryState $state): string;

    /**
     * Coordinate the technical transformation of QueryState into an UPSERT SQL statement.
     *
     * -- intent:
     * Provide a dialect-safe mechanism for "Insert or Update on Conflict"
     * operations, resolving row collisions based on specified unique columns.
     *
     * @param  QueryState  $state  The technical state containing the mutation payload.
     * @param  array  $uniqueBy  The collection of technical column identifiers used for conflict detection.
     * @param  array  $update  The collection of technical column identifiers to be updated upon conflict.
     * @return string THE compiled dialect-specific SQL UPSERT/ON DUPLICATE KEY string.
     */
    public function compileUpsert(QueryState $state, array $uniqueBy, array $update): string;

    /**
     * Coordinate the technical generation of a TRUNCATE SQL statement.
     *
     * -- intent:
     * Provide a high-performance instruction for purging all records from
     * a specific data source while bypassing individual row deletion triggers.
     *
     * @param  string  $table  THE physical technical identifier of the database table.
     * @return string THE compiled dialect-specific SQL TRUNCATE string.
     */
    public function compileTruncate(string $table): string;

    /**
     * Coordinate the technical generation of a DROP TABLE IF EXISTS SQL statement.
     *
     * -- intent:
     * Provide a defensive destruction command for schema-level data sources.
     *
     * @param  string  $table  THE physical technical identifier of the database table.
     * @return string THE compiled dialect-specific SQL DROP string.
     */
    public function compileDropIfExists(string $table): string;

    /**
     * Coordinate the technical generation of a CREATE DATABASE SQL statement.
     *
     * @param  string  $name  THE physical technical identifier of the target database.
     * @return string THE compiled dialect-specific SQL CREATE DATABASE string.
     */
    public function compileCreateDatabase(string $name): string;

    /**
     * Coordinate the technical generation of a DROP DATABASE SQL statement.
     *
     * @param  string  $name  THE physical technical identifier of the target database.
     * @return string THE compiled dialect-specific SQL DROP DATABASE string.
     */
    public function compileDropDatabase(string $name): string;

    /**
     * Retrieve the dialect-specific technical expression for random result set ordering.
     *
     * @return string THE technical SQL expression (e.g., 'RAND()' or 'RANDOM()').
     */
    public function compileRandomOrder(): string;

    /**
     * Coordinate the secure technical wrapping of a database identifier.
     *
     * -- intent:
     * Protects SQL structural integrity and prevents reserved keyword
     * collisions by applying dialect-specific quote characters (e.g., backticks).
     *
     * @param  mixed  $value  THE technical name (string) or an Expression object to be wrapped.
     * @return string THE securely wrapped technical SQL identifier.
     */
    public function wrap(mixed $value): string;
}
