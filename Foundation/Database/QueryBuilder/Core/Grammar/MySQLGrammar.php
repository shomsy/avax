<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Grammar;

use Avax\Database\Query\QueryState;
use Override;

/**
 * Specialized technician for compiling SQL according to the MySQL/MariaDB dialect.
 *
 * -- intent: override base grammar logic for MySQL-specific syntax (Backticks, Upsert, etc).
 */
final class MySQLGrammar extends BaseGrammar
{
    /**
     * Compile an UPSERT (INSERT ... ON DUPLICATE KEY UPDATE) statement.
     *
     * -- intent: leverage MySQL's specific atomicity logic for conditional data mutations.
     *
     * @param QueryState $state    Target metadata
     * @param array      $uniqueBy Unused in MySQL implementation as it relies on schema keys
     * @param array      $update   Columns to refresh on conflict
     *
     * @return string
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
     * Securely wrap a database identifier using MySQL's backtick convention.
     *
     * -- intent: provide dialect-safe escaping for tables and columns in MySQL.
     *
     * @param string $value Technical name
     *
     * @return string
     */
    #[Override]
    public function wrap(string $value) : string
    {
        if ($value === '*' || str_contains(haystack: $value, needle: '(')) {
            return $value;
        }

        return '`' . str_replace(search: '`', replace: '``', subject: $value) . '`';
    }

    /**
     * Generate the MySQL-specific random ordering expression.
     *
     * -- intent: provide a pragmatic shorthand for the RAND() function.
     *
     * @return string
     */
    public function compileRandomOrder() : string
    {
        return 'RAND()';
    }

    /**
     * Compile a MySQL TRUNCATE statement to purge a table.
     *
     * -- intent: provide a high-performance command for record resets in MySQL.
     *
     * @param string $table Target table
     *
     * @return string
     */
    public function compileTruncate(string $table) : string
    {
        return 'TRUNCATE TABLE ' . $this->wrap(value: $table);
    }

    /**
     * Compile a MySQL DROP TABLE IF EXISTS statement.
     *
     * -- intent: provide a safe structural modification command for MySQL.
     *
     * @param string $table Target table
     *
     * @return string
     */
    public function compileDropIfExists(string $table) : string
    {
        return 'DROP TABLE IF EXISTS ' . $this->wrap(value: $table);
    }
}


