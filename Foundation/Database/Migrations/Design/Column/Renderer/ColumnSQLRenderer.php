<?php

declare(strict_types=1);

namespace Avax\Migrations\Design\Column\Renderer;

use Avax\Database\QueryBuilder\Core\Grammar\GrammarInterface;
use Avax\Migrations\Design\Column\DSL\ColumnDefinition;

/**
 * Professional technician for translating column definitions into SQL fragments.
 *
 * -- intent: centralize the transformation logic from DSL attributes to dialect SQL.
 */
final class ColumnSQLRenderer
{
    /**
     * Transform a ColumnDefinition into a cohesive SQL string portion.
     *
     * -- intent: coordinate the rendering of name, type, and all active modifiers.
     *
     * @param ColumnDefinition $column  The design metadata
     * @param GrammarInterface $grammar The dialect technician for wrapping
     *
     * @return string
     */
    public function render(ColumnDefinition $column, GrammarInterface $grammar) : string
    {
        $sql = $grammar->wrap(value: $column->name) . ' ' . $column->type;

        // UNSIGNED modifier (must come before NULL/NOT NULL)
        if (isset($column->attributes['unsigned']) && $column->attributes['unsigned']) {
            $sql .= ' UNSIGNED';
        }

        // Character set and collation (MySQL specific)
        if (isset($column->attributes['charset'])) {
            $sql .= ' CHARACTER SET ' . $column->attributes['charset'];
        }

        if (isset($column->attributes['collation'])) {
            $sql .= ' COLLATE ' . $column->attributes['collation'];
        }

        // Generated/Computed columns
        if (isset($column->attributes['virtual_as'])) {
            $sql .= ' AS (' . $column->attributes['virtual_as'] . ') VIRTUAL';
        }

        if (isset($column->attributes['stored_as'])) {
            $sql .= ' AS (' . $column->attributes['stored_as'] . ') STORED';
        }

        // NULL/NOT NULL constraint
        if (isset($column->attributes['nullable'])) {
            $sql .= $column->attributes['nullable'] ? ' NULL' : ' NOT NULL';
        } else {
            // Default to NOT NULL if not specified
            $sql .= ' NOT NULL';
        }

        // DEFAULT value
        if (array_key_exists(key: 'default', array: $column->attributes)) {
            $sql .= ' DEFAULT ' . $this->formatDefault(value: $column->attributes['default']);
        }

        // CURRENT_TIMESTAMP defaults
        if (isset($column->attributes['use_current']) && $column->attributes['use_current']) {
            $sql .= ' DEFAULT CURRENT_TIMESTAMP';
        }

        // ON UPDATE CURRENT_TIMESTAMP
        if (isset($column->attributes['on_update_current']) && $column->attributes['on_update_current']) {
            $sql .= ' ON UPDATE CURRENT_TIMESTAMP';
        }

        // AUTO_INCREMENT (implies PRIMARY KEY)
        if (isset($column->attributes['auto_increment'])) {
            $sql .= ' AUTO_INCREMENT PRIMARY KEY';
        } // PRIMARY KEY (standalone)
        elseif (isset($column->attributes['primary']) && $column->attributes['primary']) {
            $sql .= ' PRIMARY KEY';
        }

        // UNIQUE constraint
        if (isset($column->attributes['unique']) && $column->attributes['unique']) {
            $sql .= ' UNIQUE';
        }

        // COMMENT
        if (isset($column->attributes['comment'])) {
            $sql .= " COMMENT '" . addslashes($column->attributes['comment']) . "'";
        }

        return $sql;
    }

    /**
     * Normalize default values for SQL concatenation.
     *
     * -- intent: ensure that data types are appropriately quoted or handled as keywords.
     *
     * @param mixed $value Raw data value
     *
     * @return string
     */
    private function formatDefault(mixed $value) : string
    {
        if (is_string(value: $value)) {
            return "'{$value}'";
        }

        if (is_bool(value: $value)) {
            return $value ? '1' : '0';
        }

        if ($value === null) {
            return 'NULL';
        }

        return (string) $value;
    }
}
