<?php

declare(strict_types=1);

namespace Avax\Migrations\Design\Column\DSL;

/**
 * Functional technician for defining a database column's technical properties.
 *
 * -- intent: provide a domain-fluent DSL for structural column design.
 */
class ColumnDefinition
{
    /**
     * Constructor initializing the base technical identifiers via PHP 8.3 features.
     *
     * -- intent: capture the name and technical type of the column.
     *
     * @param string $name       Technical name of the column
     * @param string $type       Database-specific data type
     * @param array  $attributes Collection of column modifiers (nullable, default, etc)
     */
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public array           $attributes = []
    ) {}

    /**
     * Mark the column as allowing NULL values.
     *
     * -- intent: provide a pragmatic shorthand for the NULL attribute.
     *
     * @param bool $value Whether null is allowed
     *
     * @return $this
     */
    public function nullable(bool $value = true) : self
    {
        $this->attributes['nullable'] = $value;

        return $this;
    }

    /**
     * Define a default value for the column.
     *
     * -- intent: provide a pragmatic shorthand for the DEFAULT attribute.
     *
     * @param mixed $value Fallback data value
     *
     * @return $this
     */
    public function default(mixed $value) : self
    {
        $this->attributes['default'] = $value;

        return $this;
    }

    /**
     * Mark the column as an automatically incrementing primary key.
     *
     * -- intent: provide a domain-specific shorthand for identity columns.
     *
     * @return $this
     */
    public function autoIncrement() : self
    {
        $this->attributes['auto_increment'] = true;

        return $this;
    }

    /**
     * Mark the column as UNSIGNED (positive numbers only).
     *
     * -- intent: provide storage optimization for non-negative integers.
     *
     * @return $this
     */
    public function unsigned() : self
    {
        $this->attributes['unsigned'] = true;

        return $this;
    }

    /**
     * Mark the column as a PRIMARY KEY.
     *
     * -- intent: designate this column as the table's primary identifier.
     *
     * @return $this
     */
    public function primary() : self
    {
        $this->attributes['primary'] = true;

        return $this;
    }

    /**
     * Mark the column as UNIQUE.
     *
     * -- intent: enforce uniqueness constraint on this column.
     *
     * @return $this
     */
    public function unique() : self
    {
        $this->attributes['unique'] = true;

        return $this;
    }

    /**
     * Add an INDEX on this column.
     *
     * -- intent: optimize query performance for this column.
     *
     * @param string|null $name Optional index name
     *
     * @return $this
     */
    public function index(string|null $name = null) : self
    {
        $this->attributes['index'] = $name ?? true;

        return $this;
    }

    /**
     * Set the character set for this column (MySQL).
     *
     * -- intent: define encoding for string columns.
     *
     * @param string $charset Character set name (e.g., 'utf8mb4')
     *
     * @return $this
     */
    public function charset(string $charset) : self
    {
        $this->attributes['charset'] = $charset;

        return $this;
    }

    /**
     * Set the collation for this column (MySQL).
     *
     * -- intent: define sorting rules for string columns.
     *
     * @param string $collation Collation name (e.g., 'utf8mb4_unicode_ci')
     *
     * @return $this
     */
    public function collation(string $collation) : self
    {
        $this->attributes['collation'] = $collation;

        return $this;
    }

    /**
     * Mark the column to use CURRENT_TIMESTAMP as default.
     *
     * -- intent: auto-populate timestamp columns with current time.
     *
     * @return $this
     */
    public function useCurrent() : self
    {
        $this->attributes['use_current'] = true;

        return $this;
    }

    /**
     * Mark the column to update to CURRENT_TIMESTAMP on row update.
     *
     * -- intent: auto-track last modification time.
     *
     * @return $this
     */
    public function useCurrentOnUpdate() : self
    {
        $this->attributes['on_update_current'] = true;

        return $this;
    }

    /**
     * Set the column to be stored (computed column).
     *
     * -- intent: define a generated/computed column.
     *
     * @return $this
     */
    public function storedAs(string $expression) : self
    {
        $this->attributes['stored_as'] = $expression;

        return $this;
    }

    /**
     * Set the column to be virtual (computed column).
     *
     * -- intent: define a virtual generated column.
     *
     * @return $this
     */
    public function virtualAs(string $expression) : self
    {
        $this->attributes['virtual_as'] = $expression;

        return $this;
    }

    /**
     * Add a foreign key constraint.
     *
     * -- intent: establish referential integrity with another table.
     *
     * @param string      $table    Referenced table name
     * @param string|null $column   Referenced column name
     * @param string|null $onDelete ON DELETE action (CASCADE, SET NULL, etc.)
     * @param string      $onUpdate ON UPDATE action
     *
     * @return $this
     */
    public function references(string $table, string|null $column = null, string|null $onDelete = null, string $onUpdate = 'CASCADE') : self
    {
        $column                      ??= 'id';
        $onDelete                    ??= 'CASCADE';
        $this->attributes['foreign'] = [
            'table'     => $table,
            'column'    => $column,
            'on_delete' => $onDelete,
            'on_update' => $onUpdate,
        ];

        return $this;
    }

    /**
     * Attach a technical comment to the database column.
     *
     * -- intent: facilitate database self-documentation via column comments.
     *
     * @param string $text Explanatory description
     *
     * @return $this
     */
    public function comment(string $text) : self
    {
        $this->attributes['comment'] = $text;

        return $this;
    }
}
