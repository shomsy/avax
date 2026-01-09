<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Grammar;

use Avax\Database\Query\QueryState;
use Avax\Database\QueryBuilder\ValueObjects\Expression;

/**
 * The "MySQL Translator" (Grammar).
 *
 * -- what is it?
 * This is a "Translator" that knows how to speak the MySQL language perfectly.
 * While the `QueryBuilder` knows WHAT you want to do, the `MySQLGrammar`
 * knows exactly HOW to say it in the specific syntax that MySQL understands.
 *
 * -- how to imagine it:
 * Think of an "Interpreter". You speak in high-level commands (like "I want
 * to insert or update this user"), and the interpreter writes down the
 * exact MySQL sentence (like "INSERT ... ON DUPLICATE KEY UPDATE").
 *
 * -- why this exists:
 * 1. Dialect Handling: Every database (MySQL, Postgres, SQLite) has slightly
 *    different rules for quotes and special features. This class isolates all
 *    the MySQL-specific quirks.
 * 2. Security (Quoting): It handles "Backticks" (`). Wrapping column names
 *    in backticks prevents errors if you accidentally use a "Reserved Word"
 *    (like calling a column `order` or `select`).
 * 3. Atomic Features: It implements MySQL's powerful "Upsert" (Insert or Update)
 *    syntax, which allows the database to handle conflicts automatically.
 *
 * -- mental models:
 * - "Grammar": The set of rules for building valid sentences (SQL).
 * - "Backticks" (`): The special quotes used by MySQL to identify table
 *    and column names correctly.
 */
final class MySQLGrammar extends BaseGrammar
{
    /**
     * Compile an UPSERT (Insert or Update) statement for MySQL.
     *
     * -- how to imagine it:
     * This is the "Change if exists" instruction. It tells MySQL: "Try to
     * insert this row. But if you find someone with the same ID already
     * there, just update these specific columns instead."
     *
     * @param QueryState $state    The instructions of what to insert.
     * @param array      $uniqueBy Ignored in MySQL (MySQL figures this out from your DB keys).
     * @param array      $update   The list of columns to change if a conflict happens.
     */
    public function compileUpsert(QueryState $state, array $uniqueBy, array $update) : string
    {
        $sql = $this->compileInsert(state: $state);
        $sql .= " ON DUPLICATE KEY UPDATE ";

        $updates = [];
        foreach ($update as $column) {
            $updates[] = $this->wrap(value: $column) . " = VALUES(" . $this->wrap(value: $column) . ")";
        }

        return $sql . implode(separator: ', ', array: $updates);
    }

    /**
     * Securely wrap column or table names in MySQL backticks.
     *
     * -- why do this?
     * This is the "Safety Quote". Without it, a table called `users.order`
     * would break because `order` is a special MySQL command. By wrapping
     * it as `` `users`.`order` ``, we tell MySQL: "This is a name, not a command."
     *
     * @param mixed $value The name (e.g., 'users.name').
     */
    public function wrap(mixed $value) : string
    {
        if ($value instanceof Expression) {
            return $value->getValue();
        }

        $value = (string) $value;

        // We don't wrap the asterisk (*) or functions with parentheses.
        if ($value === '*' || str_contains(haystack: $value, needle: '(')) {
            return $value;
        }

        // Handle names with dots (e.g., 'users.email').
        if (str_contains(haystack: $value, needle: '.')) {
            $segments = explode(separator: '.', string: $value);

            return implode(
                separator: '.',
                array    : array_map(
                    callback: fn($segment) => $this->wrapSegment(segment: $segment),
                    array   : $segments
                )
            );
        }

        return $this->wrapSegment(segment: $value);
    }

    /**
     * The internal "Backtick Printer" for a single name.
     */
    protected function wrapSegment(string $segment) : string
    {
        if ($segment === '*' || $segment === '') {
            return $segment;
        }

        // We wrap in backticks and handle escaping if the segment already contains a backtick.
        return '`' . str_replace(search: '`', replace: '``', subject: $segment) . '`';
    }

    /**
     * Get the MySQL snippet for random ordering.
     */
    public function compileRandomOrder() : string
    {
        return 'RAND()';
    }

    /**
     * Build the command to completely empty a table.
     */
    public function compileTruncate(string $table) : string
    {
        return 'TRUNCATE TABLE ' . $this->wrap(value: $table);
    }

    /**
     * Build the command to delete a table if it exists.
     */
    public function compileDropIfExists(string $table) : string
    {
        return 'DROP TABLE IF EXISTS ' . $this->wrap(value: $table);
    }
}
