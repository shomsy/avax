<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Table\Traits;

use Avax\Database\Migration\Design\Column\DSL\ColumnDefinition;
use Avax\Database\Migration\Design\Column\Enums\ColumnType;

/**
 * Trait IndexDefinitionsTrait
 *
 * Provides fluent DSL methods for defining table-level indexes.
 */
trait IndexDefinitionsTrait
{
    /**
     * @var array<string, ColumnDefinition> Indexes keyed by index name
     */
    protected array $indexes = [];

    /**
     * Adds a general-purpose INDEX.
     *
     * @param string|array<int, string> $columns
     * @param string|null               $indexName
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function index(string|array $columns, string|null $indexName = null) : ColumnDefinition
    {
        return $this->addIndex(type: ColumnType::INDEX, columns: $columns, indexName: $indexName);
    }

    /**
     * Internal helper to create and register an index ColumnDefinition.
     *
     * @param ColumnType                $type
     * @param string|array<int, string> $columns
     * @param string|null               $indexName
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    private function addIndex(ColumnType $type, string|array $columns, string|null $indexName = null) : ColumnDefinition
    {
        $cols = (array) $columns;
        $name = $indexName ?? strtolower($type->value) . '_' . implode('_', $cols);

        $definition = ColumnDefinition::make(name: $name, type: $type)
            ->columns($cols);

        $this->indexes[$name] = $definition;

        return $definition;
    }

    /**
     * Adds a FULLTEXT index.
     *
     * @param string|array<int, string> $columns
     * @param string|null               $indexName
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function fullText(string|array $columns, string|null $indexName = null) : ColumnDefinition
    {
        return $this->addIndex(type: ColumnType::FULLTEXT, columns: $columns, indexName: $indexName);
    }

    /**
     * Adds a SPATIAL index.
     *
     * @param string|array<int, string> $columns
     * @param string|null               $indexName
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function spatialIndex(string|array $columns, string|null $indexName = null) : ColumnDefinition
    {
        return $this->addIndex(type: ColumnType::SPATIAL, columns: $columns, indexName: $indexName);
    }

    /**
     * Alias for unique composite keys.
     *
     * @param array<int, string> $columns
     * @param string|null        $indexName
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function uniqueComposite(array $columns, string|null $indexName = null) : ColumnDefinition
    {
        return $this->unique(columns: $columns, indexName: $indexName);
    }

    /**
     * Adds a UNIQUE index with optional composite support.
     *
     * @param string|array<int, string> $columns
     * @param string|null               $indexName
     *
     * @return ColumnDefinition
     * @throws \ReflectionException
     */
    public function unique(string|array $columns, string|null $indexName = null) : ColumnDefinition
    {
        return $this->addIndex(type: ColumnType::UNIQUE, columns: $columns, indexName: $indexName);
    }
}
