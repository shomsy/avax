<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Design\Column\Renderer;

use Avax\Database\Migration\Design\Column\Builder\ColumnAttributes;
use Avax\Database\Migration\Design\Column\Enums\ColumnType;

final class ColumnSQLRenderer
{
    /**
     * Static entrypoint for rendering a column definition.
     *
     * @param ColumnAttributes $column The column to render
     *
     * @return string SQL representation of the column
     */
    public static function render(ColumnAttributes $column) : string
    {
        return (new self())->format($column);
    }

    public function format(ColumnAttributes $column) : string
    {
        return match (true) {
            $column->type === ColumnType::FOREIGN_KEY => $this->buildForeignKey($column),
            $column->type->isIndex()                  => $this->buildIndex($column),
            default                                   => $this->buildColumn($column),
        };
    }

    private function buildForeignKey(ColumnAttributes $c) : string
    {
        $sql = sprintf(
            'FOREIGN KEY (`%s`) REFERENCES `%s`(`%s`)',
            $c->name,
            $c->foreign['on'] ?? 'unknown_table',
            $c->foreign['references'] ?? 'id'
        );

        if (! empty($c->foreign['onDelete'])) {
            $sql .= ' ON DELETE ' . $c->foreign['onDelete'];
        }

        if (! empty($c->foreign['onUpdate'])) {
            $sql .= ' ON UPDATE ' . $c->foreign['onUpdate'];
        }

        return $sql;
    }

    private function buildIndex(ColumnAttributes $c) : string
    {
        return strtoupper($c->type->toSqlType()) . " `{$c->name}` (" . implode(', ', $c->columns) . ")";
    }

    private function buildColumn(ColumnAttributes $c) : string
    {
        $sql = "`{$c->name}` " . $this->typeDeclaration($c);

        $sql .= $c->unsigned ? ' UNSIGNED' : '';
        $sql .= $c->nullable ? ' NULL' : ' NOT NULL';
        $sql .= $this->defaultClause($c);
        $sql .= $c->autoIncrement ? ' AUTO_INCREMENT' : '';
        $sql .= $c->unique ? ' UNIQUE' : '';
        $sql .= $c->primary ? ' PRIMARY KEY' : '';
        $sql .= $c->generated ? " {$c->generated}" : '';
        $sql .= $c->after ? " AFTER `{$c->after}`" : '';
        $sql .= $c->alias !== null ? " AS `{$c->alias}`" : '';

        return trim($sql);
    }

    private function typeDeclaration(ColumnAttributes $c) : string
    {
        if ($c->type === ColumnType::ENUM && $c->enum !== null) {
            $quoted = array_map(static fn(string $v) : string => "'{$v}'", $c->enum);

            return 'ENUM(' . implode(', ', $quoted) . ')';
        }

        return match (true) {
            $c->type === ColumnType::DECIMAL                  =>
                "DECIMAL(" . ($c->precision ?? 8) . ", " . ($c->scale ?? 2) . ")",
            $c->type->requiresLength() && $c->length !== null =>
                $c->type->toSqlType() . "({$c->length})",
            default                                           => $c->type->toSqlType(),
        };
    }

    private function defaultClause(ColumnAttributes $c) : string
    {
        if ($c->default !== null) {
            $escaped = match (true) {
                is_string($c->default) => "'{$c->default}'",
                $c->default === true   => '1',
                $c->default === false  => '0',
                default                => $c->default
            };

            return " DEFAULT {$escaped}";
        }

        if ($c->useCurrent) {
            return ' DEFAULT CURRENT_TIMESTAMP';
        }

        return '';
    }
}
