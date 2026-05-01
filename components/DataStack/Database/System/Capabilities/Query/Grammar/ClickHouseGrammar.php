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
    #[Override]
    public function compileSelect(QueryState $queryState) : string
    {
        parent::compileSelect(state: $queryState);

        $components = [
            'select' => $this->compileColumns(state: $queryState),
            'from'   => $this->compileFrom(state: $queryState),
            'joins'  => $this->compileJoins(state: $queryState),
            'wheres' => $this->compileWheres(state: $queryState),
            'groups' => $this->compileGroups(state: $queryState),
            'having' => $this->compileHaving(state: $queryState),
            'orders' => $this->compileOrders(state: $queryState),
            'limit'  => $this->compileLimit(state: $queryState),
        ];

        return implode(separator: ' ', array: array_filter(array: $components));
    }

    #[Override]
    protected function compileColumns(QueryState $queryState) : string
    {
        parent::compileColumns(state: $queryState);

        $select = $queryState->distinct ? 'SELECT DISTINCT ' : 'SELECT ';

        $columns = array_map(callback: fn ($c) : string => $this->wrap(value: $c), array: $queryState->columns ?: ['*']);

        return $select . implode(separator: ', ', array: $columns);
    }

    #[Override]
    public function wrap(mixed $value): string
    {
        parent::wrap(value: $value);

        return (string) $value;
    }

    private function compileHaving(QueryState $queryState) : string
    {
        return '';
    }

    #[Override]
    public function compileUpdate(QueryState $queryState) : string
    {
        parent::compileUpdate(state: $queryState);

        $table = $this->wrap(value: $queryState->from);

        $sets = [];
        foreach ($queryState->values as $col => $val) {
            $sets[] = sprintf('%s = %s', $col, $val);
        }

        $sql = sprintf('ALTER TABLE %s UPDATE ', $table) . implode(separator: ', ', array: $sets);

        if ($queryState->wheres !== []) {
            $sql .= ' WHERE ' . $this->compileWheres(state: $queryState);
        }

        return $sql;
    }

    #[Override]
    public function compileDelete(QueryState $queryState) : string
    {
        parent::compileDelete(state: $queryState);

        $table  = $this->wrap(value: $queryState->from);
        $wheres = $this->compileWheres(state: $queryState);

        return sprintf('ALTER TABLE %s DELETE %s', $table, $wheres);
    }

    #[Override]
    public function compileUpsert(QueryState $queryState, array $uniqueBy, array $update) : string
    {
        try {
            parent::compileUpsert(uniqueBy: $uniqueBy, update: $update, state: $queryState);
        } catch (RuntimeException) {
        }

        return $this->compileInsert(state: $queryState);
    }

    #[Override]
    public function compileInsert(QueryState $queryState) : string
    {
        parent::compileInsert(state: $queryState);

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
