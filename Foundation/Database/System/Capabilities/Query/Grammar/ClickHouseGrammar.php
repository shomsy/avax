<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Grammar;

use Avax\Database\System\Capabilities\Query\State\QueryState;
use Override;

/**
 * ClickHouse Grammar - Columnar analytics database.
 */
final class ClickHouseGrammar extends BaseGrammar
{
    #[Override]
    public function compileSelect(QueryState $state) : string
    {
        $components = [
            'select' => $this->compileColumns(state: $state),
            'from'   => $this->compileFrom(state: $state),
            'joins'  => $this->compileJoins(state: $state),
            'wheres' => $this->compileWheres(state: $state),
            'groups' => $this->compileGroups(state: $state),
            'having' => $this->compileHaving(state: $state),
            'orders' => $this->compileOrders(state: $state),
            'limit'  => $this->compileLimit(state: $state),
        ];

        return implode(separator: ' ', array: array_filter(array: $components));
    }

    private function compileColumns(QueryState $state) : string
    {
        $select = $state->distinct ? 'SELECT DISTINCT ' : 'SELECT ';

        $columns = array_map(callback: fn ($c) => $this->wrap(value: $c), array: $state->columns ?: ['*']);

        return $select . implode(separator: ', ', array: $columns);
    }

    #[Override]
    public function wrap(mixed $value) : string
    {
        return (string) $value;
    }

    private function compileHaving(QueryState $state) : string
    {
        return '';
    }

    #[Override]
    public function compileUpdate(QueryState $state) : string
    {
        $table = $this->wrap(value: $state->from);

        $sets = [];
        foreach ($state->values as $col => $val) {
            $sets[] = "{$col} = {$val}";
        }

        $sql = "ALTER TABLE {$table} UPDATE " . implode(separator: ', ', array: $sets);

        if (! empty($state->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres(state: $state);
        }

        return $sql;
    }

    #[Override]
    public function compileDelete(QueryState $state) : string
    {
        $table  = $this->wrap(value: $state->from);
        $wheres = $this->compileWheres(state: $state);

        return "ALTER TABLE {$table} DELETE {$wheres}";
    }

    #[Override]
    public function compileUpsert(QueryState $state, array $uniqueBy, array $update) : string
    {
        return $this->compileInsert(state: $state);
    }

    #[Override]
    public function compileInsert(QueryState $state) : string
    {
        $table   = $this->wrap(value: $state->from);
        $columns = implode(separator: ', ', array: array_keys(array: $state->values));
        $values  = implode(separator: ', ', array: array_map(
            callback: fn ($v) => is_string(value: $v) ? "'{$v}'" : $v,
            array   : $state->values
        ));

        return "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
    }

    public function compileSample(int $percent) : string
    {
        return "SAMPLE {$percent}/100";
    }

    public function compileArrayJoin(string $column) : string
    {
        return 'ARRAY JOIN ' . $this->wrap(value: $column);
    }

    public function compilePrewhere(array $columns) : string
    {
        return 'PREWHERE ' . implode(separator: ', ', array: $columns);
    }

    public function compileFinal() : string
    {
        return 'FINAL';
    }

    public function compileWithRollingWindow(string $column, string $function) : string
    {
        return "rollingWindow('{$function}', {$column})";
    }

    public function compileWithSeries(int $start, int $end) : string
    {
        return "WITH series AS (SELECT toUInt64(number) AS n FROM numbers({$start}, " . ($end - $start) . '))';
    }

    public function compileUsing(array $columns) : string
    {
        return 'USING ' . implode(separator: ', ', array: $columns);
    }

    public function compileGlobal(array $columns) : string
    {
        return 'GLOBAL ' . implode(separator: ', ', array: $columns);
    }

    public function compileGroupArray(string $column) : string
    {
        return 'groupArray(' . $this->wrap(value: $column) . ')';
    }

    public function compileGroupUniqArray(string $column) : string
    {
        return 'groupUniqArray(' . $this->wrap(value: $column) . ')';
    }

    public function compileQuantile(float $q, string $column) : string
    {
        return "quantile({$q})(" . $this->wrap(value: $column) . ')';
    }

    #[Override]
    public function compileTruncate(string $table) : string
    {
        return 'DROP TABLE IF EXISTS ' . $this->wrap(value: $table);
    }
}
