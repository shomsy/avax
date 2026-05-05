<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;
use Override;
use RuntimeException;

/**
 * ClickHouse Grammar - Columnar analytics database.
 */
final class ClickHouseGrammar extends BaseGrammar
{
    public function compileSelect(QueryState $queryState) : string
    {
        parent::compileSelect($queryState);

        $components = [
            'select' => $this->compileColumns($queryState),
            'from'   => $this->compileFrom($queryState),
            'joins'  => $this->compileJoins($queryState),
            'wheres' => $this->compileWheres($queryState),
            'groups' => $this->compileGroups($queryState),
            'having' => $this->compileHaving($queryState),
            'orders' => $this->compileOrders($queryState),
            'limit'  => $this->compileLimit($queryState),
        ];

        return implode(separator: ' ', array: array_filter(array: $components));
    }

    protected function compileColumns(QueryState $queryState) : string
    {
        parent::compileColumns($queryState);

        $select = $queryState->distinct ? 'SELECT DISTINCT ' : 'SELECT ';

        $columns = array_map(callback: fn ($c) : string => $this->wrap(value: $c), array: $queryState->columns ?: ['*']);

        return $select . implode(separator: ', ', array: $columns);
    }

    public function wrap(mixed $value): string
    {
        parent::wrap(value: $value);

        return (string) $value;
    }

    private function compileHaving(QueryState $queryState) : string
    {
        return '';
    }

    public function compileUpdate(QueryState $queryState) : string
    {
        parent::compileUpdate($queryState);

        $table = $this->wrap(value: $queryState->from);

        $sets = [];
        foreach ($queryState->values as $col => $val) {
            $sets[] = sprintf('%s = %s', $col, $val);
        }

        $sql = sprintf('ALTER TABLE %s UPDATE ', $table) . implode(separator: ', ', array: $sets);

        if ($queryState->wheres !== []) {
            $sql .= ' WHERE ' . $this->compileWheres($queryState);
        }

        return $sql;
    }

    public function compileDelete(QueryState $queryState) : string
    {
        parent::compileDelete($queryState);

        $table  = $this->wrap(value: $queryState->from);
        $wheres = $this->compileWheres($queryState);

        return sprintf('ALTER TABLE %s DELETE %s', $table, $wheres);
    }

    public function compileUpsert(QueryState $queryState, array $uniqueBy, array $update) : string
    {
        try {
            parent::compileUpsert($queryState, $uniqueBy, $update);
        } catch (RuntimeException) {
        }

        return $this->compileInsert($queryState);
    }

    public function compileInsert(QueryState $queryState) : string
    {
        parent::compileInsert($queryState);

        $table   = $this->wrap(value: $queryState->from);
        $columns = implode(separator: ', ', array: array_keys(array: $queryState->values));
        $values = implode(separator: ', ', array: array_map(
            callback: static fn ($v) => is_string(value: $v) ? sprintf("'%s'", $v) : $v,
            array   : $queryState->values,
        ));

        return sprintf('INSERT INTO %s (%s) VALUES (%s)', $table, $columns, $values);
    }

    public function compileSample(int $percent): string
    {
        return sprintf('SAMPLE %d/100', $percent);
    }

    public function compileArrayJoin(string $column): string
    {
        return 'ARRAY JOIN ' . $this->wrap(value: $column);
    }

    public function compilePrewhere(array $columns): string
    {
        return 'PREWHERE ' . implode(separator: ', ', array: $columns);
    }

    public function compileFinal(): string
    {
        return 'FINAL';
    }

    public function compileWithRollingWindow(string $column, string $function): string
    {
        return sprintf("rollingWindow('%s', %s)", $function, $column);
    }

    public function compileWithSeries(int $start, int $end): string
    {
        return sprintf('WITH series AS (SELECT toUInt64(number) AS n FROM numbers(%d, ', $start) . ($end - $start) . '))';
    }

    public function compileUsing(array $columns): string
    {
        return 'USING ' . implode(separator: ', ', array: $columns);
    }

    public function compileGlobal(array $columns): string
    {
        return 'GLOBAL ' . implode(separator: ', ', array: $columns);
    }

    public function compileGroupArray(string $column): string
    {
        return 'groupArray(' . $this->wrap(value: $column) . ')';
    }

    public function compileGroupUniqArray(string $column): string
    {
        return 'groupUniqArray(' . $this->wrap(value: $column) . ')';
    }

    public function compileQuantile(float $q, string $column): string
    {
        return sprintf('quantile(%s)(', $q) . $this->wrap(value: $column) . ')';
    }

    #[Override]
    public function compileTruncate(string $table): string
    {
        return 'DROP TABLE IF EXISTS ' . $this->wrap(value: $table);
    }
}
