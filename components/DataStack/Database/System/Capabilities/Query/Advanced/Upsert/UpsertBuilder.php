<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Advanced\Upsert;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class UpsertBuilder
{
    public function __construct(
        private readonly GrammarInterface $grammar,
        private readonly string           $table,
        private readonly array            $columns,
        private readonly OnConflict       $conflict,
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

        $conflictColumns = $this->wrapList(values: $this->conflict->columns);

        if ($this->conflict->doNothing) {
            $sql .= " ON CONFLICT ({$conflictColumns}) DO NOTHING";

            return ['sql' => $sql, 'bindings' => $bindings];
        }

        $updateColumns = $this->conflict->updateColumns === []
            ? array_values(array: array_diff($this->columns, $this->conflict->columns))
            : $this->conflict->updateColumns;

        $assignments = array_map(
            callback: fn ($column) => $this->grammar->wrap(value: $column) . ' = EXCLUDED.' . $this->grammar->wrap(value: $column),
            array   : $updateColumns
        );

        $sql .= " ON CONFLICT ({$conflictColumns}) DO UPDATE SET " . implode(separator: ', ', array: $assignments);

        return ['sql' => $sql, 'bindings' => $bindings];
    }

    private function wrapList(array $values) : string
    {
        return implode(separator: ', ', array: array_map(callback: fn ($value) => $this->grammar->wrap(value: $value), array: $values));
    }
}
