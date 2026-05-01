<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Advanced\Upsert;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final readonly class UpsertBuilder
{
    public function __construct(
        private GrammarInterface $grammar,
        private string           $table,
        private array            $columns,
        private OnConflict       $onConflict,
    ) {}

    /**
     * @return array{sql: string, bindings: list<mixed>}
     */
    public function build(array $rows) : array
    {
        $bindings = [];
        $groups   = [];

        foreach ($rows as $row) {
            $groups[] = '(' . implode(separator: ', ', array: array_fill(start_index: 0, count: count(value: $this->columns), value: '?')) . ')';

            foreach ($this->columns as $column) {
                $bindings[] = $row[$column] ?? null;
            }
        }

        $sql = 'INSERT INTO ' . $this->grammar->wrap(value: $this->table)
            . ' (' . $this->wrapList(values: $this->columns) . ') VALUES '
            . implode(separator: ', ', array: $groups);

        $conflictColumns = $this->wrapList(values: $this->onConflict->columns);

        if ($this->onConflict->doNothing) {
            $sql .= sprintf(' ON CONFLICT (%s) DO NOTHING', $conflictColumns);

            return ['sql' => $sql, 'bindings' => $bindings];
        }

        $updateColumns = $this->onConflict->updateColumns === []
            ? array_values(array: array_diff($this->columns, $this->onConflict->columns))
            : $this->onConflict->updateColumns;

        $assignments = array_map(
            callback: fn ($column) : string => $this->grammar->wrap(value: $column) . ' = EXCLUDED.' . $this->grammar->wrap(value: $column),
            array   : $updateColumns,
        );

        $sql .= sprintf(' ON CONFLICT (%s) DO UPDATE SET ', $conflictColumns) . implode(separator: ', ', array: $assignments);

        return ['sql' => $sql, 'bindings' => $bindings];
    }

    private function wrapList(array $values) : string
    {
        return implode(separator: ', ', array: array_map(callback: fn ($value) : string => $this->grammar->wrap(value: $value), array: $values));
    }
}
