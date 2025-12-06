<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Column\DSL\FluentModifiers;

use Avax\Database\Migration\Design\Column\DSL\ColumnDefinition;
use Avax\Database\Migration\Design\Column\Enums\ColumnType;
use Avax\Database\Migration\Design\Column\Enums\ReferentialAction;

/**
 * Trait ColumnDSLKeyConstraints
 *
 * Provides fluent methods for defining column-level constraints.
 * Applies strict immutability via DTO-based state mutation.
 */
trait ColumnDSLKeyConstraints
{
    /**
     * Shortcut for setting column as PRIMARY KEY
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function pk() : ColumnDefinition
    {
        return $this->primary();
    }

    /**
     * Marks the column as PRIMARY KEY
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function primary() : ColumnDefinition
    {
        return $this->withModifiedAttributes(['primary' => true]);
    }

    /**
     * Shortcut for setting AUTO_INCREMENT
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function inc() : ColumnDefinition
    {
        return $this->autoIncrement();
    }

    /**
     * Marks column as AUTO_INCREMENT
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function autoIncrement() : ColumnDefinition
    {
        return $this->withModifiedAttributes(['autoIncrement' => true]);
    }

    /**
     * Defines a FOREIGN KEY constraint
     *
     * @param string $references Referenced column
     * @param string $onTable    Target table
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function foreignKey(string $references, string $onTable) : ColumnDefinition
    {
        return $this->withModifiedAttributes(
            [
                'type'    => ColumnType::FOREIGN_KEY,
                'foreign' => [
                    'references' => $references,
                    'on'         => $onTable,
                ],
            ]
        );
    }

    /**
     * Sets ON DELETE behavior for foreign key
     *
     * @param ReferentialAction $action Referential action enum
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function onDelete(ReferentialAction $action) : ColumnDefinition
    {
        $foreign = $this->getBuilder()->foreign ?? [];

        return $this->withModifiedAttributes(
            [
                'foreign' => array_merge($foreign, [
                    'onDelete' => $action->value,
                ]),
            ]
        );
    }

    /**
     * Sets ON UPDATE behavior for foreign key
     *
     * @param ReferentialAction $action Referential action enum
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function onUpdate(ReferentialAction $action) : ColumnDefinition
    {
        $foreign = $this->getBuilder()->foreign ?? [];

        return $this->withModifiedAttributes(
            [
                'foreign' => array_merge($foreign, [
                    'onUpdate' => $action->value,
                ]),
            ]
        );
    }

    /**
     * Defines INDEX constraint
     *
     * @param string        $name    Index name
     * @param array<string> $columns Affected columns
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function index(string $name, array $columns) : ColumnDefinition
    {
        return ColumnDefinition::make(name: $name, type: ColumnType::INDEX)
            ->columns($columns);
    }

    /**
     * Defines UNIQUE constraint
     *
     * @param string        $name    Constraint name
     * @param array<string> $columns Affected columns
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function unique(string $name, array $columns) : ColumnDefinition
    {
        return ColumnDefinition::make(name: $name, type: ColumnType::UNIQUE)
            ->columns($columns);
    }

    /**
     * Defines FULLTEXT index
     *
     * @param string        $name    Index name
     * @param array<string> $columns Affected columns
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function fullText(string $name, array $columns) : ColumnDefinition
    {
        return ColumnDefinition::make(name: $name, type: ColumnType::FULLTEXT)
            ->columns($columns);
    }

    /**
     * Defines SPATIAL index
     *
     * @param string        $name    Index name
     * @param array<string> $columns Affected columns
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function spatial(string $name, array $columns) : ColumnDefinition
    {
        return ColumnDefinition::make(name: $name, type: ColumnType::SPATIAL)
            ->columns($columns);
    }
}
