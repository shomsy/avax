<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Column\DSL\FluentModifiers;

use Avax\Database\Migration\Design\Column\DSL\ColumnDefinition;
use Avax\Database\Migration\Design\Column\Enums\ColumnType;
use InvalidArgumentException;

/**
 * Trait ColumnDSLSemantics
 *
 * Domain-Specific Language (DSL) fluent modifiers for expressive schema definitions.
 *
 * Each method adheres to value object principles by avoiding direct mutation.
 * Instead, modifications are applied via a `withModifiedAttributes()` builder contract,
 * ensuring immutability and enabling composability of modifiers.
 *
 * @see     ColumnDefinition
 * @see     ColumnDefinition::withModifiedAttributes()
 *
 * @package Avax\Database\Migration\Design\Column\DSL\FluentModifiers
 */
trait ColumnDSLSemantics
{
    /**
     * Defines an ENUM type column with a fixed set of string values.
     *
     * @param array<int, string> $values Acceptable string values for ENUM constraint
     *
     * @return ColumnDefinition New column instance with ENUM type
     *
     * @throws InvalidArgumentException When the $values array is empty
     * @throws \ReflectionException
     */
    public function enum(array $values) : ColumnDefinition
    {
        if (empty($values)) {
            throw new InvalidArgumentException(message: "Enum values cannot be empty.");
        }

        return $this->withModifiedAttributes(
            [
                'type' => ColumnType::ENUM,
                'enum' => $values,
            ]
        );
    }

    /**
     * Contract: Fluent modifier mutation through immutability contract.
     *
     * @param array<string, mixed> $attributes New values to apply to builder state
     *
     * @return ColumnDefinition Mutated copy with applied attributes
     */
    abstract protected function withModifiedAttributes(array $attributes) : ColumnDefinition;

    /**
     * Defines a STORED generated column from a SQL expression.
     *
     * @param string $expression SQL expression evaluated and persisted
     *
     * @return ColumnDefinition New column with stored generated behavior
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    public function storedAs(string $expression) : ColumnDefinition
    {
        return $this->withModifiedAttributes(['generated' => "AS ({$expression}) STORED"]);
    }

    /**
     * Defines a VIRTUAL generated column from a SQL expression.
     *
     * @param string $expression SQL expression evaluated on read
     *
     * @return ColumnDefinition New column with virtual generated behavior
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    public function virtualAs(string $expression) : ColumnDefinition
    {
        return $this->withModifiedAttributes(['generated' => "AS ({$expression}) VIRTUAL"]);
    }

    /**
     * Specifies the placement of the column relative to another column.
     *
     * @param string $column Name of the reference column
     *
     * @return ColumnDefinition Column with `AFTER` clause applied
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    public function after(string $column) : ColumnDefinition
    {
        return $this->withModifiedAttributes(['after' => $column]);
    }

    /**
     * Sets the default value to CURRENT_TIMESTAMP for temporal columns.
     *
     * @return ColumnDefinition Column with default timestamp behavior
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    public function useCurrent() : ColumnDefinition
    {
        return $this->withModifiedAttributes(['useCurrent' => true]);
    }

    /**
     * Enables ON UPDATE CURRENT_TIMESTAMP behavior for automatic updates.
     *
     * @return ColumnDefinition Column with auto-update timestamp logic
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    public function useCurrentOnUpdate() : ColumnDefinition
    {
        return $this->withModifiedAttributes(['useCurrentOnUpdate' => true]);
    }

    /**
     * Sets the compound index column list for multi-column indexes.
     *
     * @param array<int, string> $columns Array of column names to index together
     *
     * @return ColumnDefinition Column with compound index config
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    public function columns(array $columns) : ColumnDefinition
    {
        return $this->withModifiedAttributes(['columns' => $columns]);
    }

    /**
     * Assigns an alias to the column for use in views or generated columns.
     *
     * @param string $name Alias identifier
     *
     * @return ColumnDefinition Column with alias assigned
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    public function alias(string $name) : ColumnDefinition
    {
        return $this->withModifiedAttributes(['alias' => $name]);
    }

    /**
     * Attaches a comment to the column definition for metadata purposes.
     *
     * @param string $text SQL comment text
     *
     * @return ColumnDefinition Column with comment metadata
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    public function comment(string $text) : ColumnDefinition
    {
        return $this->withModifiedAttributes(['comment' => $text]);
    }

    /**
     * Sets a default value to be applied during insert operations.
     *
     * @param string|int|float|bool|null $value Default value to apply
     *
     * @return ColumnDefinition Column with default constraint
     * @throws \ReflectionException
     * @throws \ReflectionException
     */
    public function default(string|int|float|bool|null $value) : ColumnDefinition
    {
        return $this->withModifiedAttributes(['default' => $value]);
    }
}
