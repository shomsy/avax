<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;
use Override;
use RuntimeException;

/**
 * Cassandra Grammar - Wide-column store CQL.
 */
final class CassandraGrammar extends BaseGrammar
{
    #[Override]
    public function compileSelect(QueryState $queryState) : string
    {
        parent::compileSelect($queryState);

        $columns = implode(separator: ', ', array: ($queryState->columns ?: ['*']));
        $table   = $this->wrap(value: $queryState->from);

        $sql = sprintf('SELECT %s FROM %s', $columns, $table);

        if ($queryState->wheres !== []) {
            $sql .= ' WHERE ' . $this->compileCassandraWhere($queryState);
        }

        if ($queryState->orders !== []) {
            $sql .= ' ORDER BY ' . $this->compileCassandraOrder($queryState);
        }

        if ($queryState->limit) {
            $sql .= ' LIMIT ' . $queryState->limit;
        }

        return $sql;
    }

    public function wrap(mixed $value): string
    {
        parent::wrap($value);

        return (string) $value;
    }

    private function compileCassandraWhere(QueryState $queryState) : string
    {
        $conditions = [];
        foreach ($queryState->wheres as $where) {
            $col = $where->column;
            $op = $where->operator;
            $val = is_string(value: $where->value) ? sprintf("'%s'", $where->value) : $where->value;

            $conditions[] = sprintf('%s %s %s', $col, $op, $val);
        }

        return implode(separator: ' AND ', array: $conditions);
    }

    private function compileCassandraOrder(QueryState $queryState) : string
    {
        $orders = [];
        foreach ($queryState->orders as $order) {
            $orders[] = sprintf('%s %s', $order->column, $order->direction);
        }

        return implode(separator: ', ', array: $orders);
    }

    #[Override]
    public function compileUpdate(QueryState $queryState) : string
    {
        parent::compileUpdate($queryState);

        $table = $this->wrap(value: $queryState->from);

        $sets = [];
        foreach ($queryState->values as $col => $val) {
            $sets[] = sprintf('%s = %s', $col, $val);
        }

        $sql = sprintf('UPDATE %s SET ', $table) . implode(separator: ', ', array: $sets);

        if ($queryState->wheres !== []) {
            $sql .= ' WHERE ' . $this->compileCassandraWhere($queryState);
        }

        return $sql;
    }

    #[Override]
    public function compileDelete(QueryState $queryState) : string
    {
        parent::compileDelete($queryState);

        $table = $this->wrap(value: $queryState->from);

        $sql = 'DELETE FROM ' . $table;

        if ($queryState->wheres !== []) {
            $sql .= ' WHERE ' . $this->compileCassandraWhere($queryState);
        }

        return $sql;
    }

    #[Override]
    public function compileUpsert(QueryState $queryState, array $uniqueBy, array $update) : string
    {
        try {
            parent::compileUpsert($queryState, $uniqueBy, $update);
        } catch (RuntimeException) {
        }

        return $this->compileInsert($queryState);
    }

    #[Override]
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

    public function compileBatch(array $statements): string
    {
        $stmts = implode(separator: '; ', array: $statements);

        return sprintf('BEGIN BATCH %s APPLY BATCH', $stmts);
    }

    public function compileCounterIncrement(string $column, int $amount): string
    {
        return sprintf('%s = %s + %d', $column, $column, $amount);
    }

    public function compileTTL(int $seconds): string
    {
        return 'USING TTL ' . $seconds;
    }

    public function compileTimestamp(int $timestamp): string
    {
        return 'USING TIMESTAMP ' . $timestamp;
    }

    #[Override]
    public function compileTruncate(string $table): string
    {
        return 'TRUNCATE ' . $this->wrap(value: $table);
    }
}
