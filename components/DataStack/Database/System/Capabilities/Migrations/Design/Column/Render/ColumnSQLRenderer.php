<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Column\Render;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Column\DSL\ColumnDefinition;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

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
     * @param ColumnDefinition $columnDefinition The design metadata
     * @param GrammarInterface $grammar The dialect technician for wrapping
     */
    public function render(ColumnDefinition $columnDefinition, GrammarInterface $grammar) : string
    {
        $sql = $grammar->wrap(value: $columnDefinition->name) . ' ' . $columnDefinition->type;

        // UNSIGNED modifier (must come before NULL/NOT NULL)
        if (isset($columnDefinition->attributes['unsigned']) && $columnDefinition->attributes['unsigned']) {
            $sql .= ' UNSIGNED';
        }

        // Character set and collation (MySQL specific)
        if (isset($columnDefinition->attributes['charset'])) {
            $sql .= ' CHARACTER SET ' . $columnDefinition->attributes['charset'];
        }

        if (isset($columnDefinition->attributes['collation'])) {
            $sql .= ' COLLATE ' . $columnDefinition->attributes['collation'];
        }

        // Generated/Computed columns
        if (isset($columnDefinition->attributes['virtual_as'])) {
            $sql .= ' AS (' . $columnDefinition->attributes['virtual_as'] . ') VIRTUAL';
        }

        if (isset($columnDefinition->attributes['stored_as'])) {
            $sql .= ' AS (' . $columnDefinition->attributes['stored_as'] . ') STORED';
        }

        // NULL/NOT NULL constraint
        if (isset($columnDefinition->attributes['nullable'])) {
            $sql .= $columnDefinition->attributes['nullable'] ? ' NULL' : ' NOT NULL';
        } else {
            // Default to NOT NULL if not specified
            $sql .= ' NOT NULL';
        }

        // DEFAULT value
        if (array_key_exists(key: 'default', array: $columnDefinition->attributes)) {
            $sql .= ' DEFAULT ' . $this->formatDefault(value: $columnDefinition->attributes['default']);
        }

        // CURRENT_TIMESTAMP defaults
        if (isset($columnDefinition->attributes['use_current']) && $columnDefinition->attributes['use_current']) {
            $sql .= ' DEFAULT CURRENT_TIMESTAMP';
        }

        // ON UPDATE CURRENT_TIMESTAMP
        if (isset($columnDefinition->attributes['on_update_current']) && $columnDefinition->attributes['on_update_current']) {
            $sql .= ' ON UPDATE CURRENT_TIMESTAMP';
        }

        // AUTO_INCREMENT (implies PRIMARY KEY)
        if (isset($columnDefinition->attributes['auto_increment'])) {
            $sql .= ' AUTO_INCREMENT PRIMARY KEY';
        } // PRIMARY KEY (standalone)
        elseif (isset($columnDefinition->attributes['primary']) && $columnDefinition->attributes['primary']) {
            $sql .= ' PRIMARY KEY';
        }

        // UNIQUE constraint
        if (isset($columnDefinition->attributes['unique']) && $columnDefinition->attributes['unique']) {
            $sql .= ' UNIQUE';
        }

        // COMMENT
        if (isset($columnDefinition->attributes['comment'])) {
            $sql .= " COMMENT '" . str_replace(search: "'", replace: "''", subject: $columnDefinition->attributes['comment']) . "'";
        }

        return $sql;
    }

    /**
     * Normalize default values for SQL concatenation.
     *
     * -- intent: ensure that data types are appropriately quoted or handled as keywords.
     *
     * @param mixed $value Raw data value
     */
    private function formatDefault(mixed $value) : string
    {
        if (is_string(value: $value)) {
            return sprintf("'%s'", $value);
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
