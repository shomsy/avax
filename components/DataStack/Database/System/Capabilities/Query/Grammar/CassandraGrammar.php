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
    public function compileSelect(QueryState $state) : string
    {
        parent::compileSelect(state: $state);

        $columns = implode(separator: ', ', array: ($state->columns ?: ['*']));
        $table   = $this->wrap(value: $state->from);

        $sql = "SELECT {$columns} FROM {$table}";

        if (! empty($state->wheres)) {
            $sql .= ' WHERE ' . $this->compileCassandraWhere(state: $state);
        }

        if (! empty($state->orders)) {
            $sql .= ' ORDER BY ' . $this->compileCassandraOrder(state: $state);
        }

        if ($state->limit) {
            $sql .= ' LIMIT ' . $state->limit;
        }

        return $sql;
    }

    #[Override]
    public function wrap(mixed $value) : string
    {
        parent::wrap(value: $value);

        return (string) $value;
    }

    private function compileCassandraWhere(QueryState $state) : string
    {
        $conditions = [];
        foreach ($state->wheres as $where) {
            $col = $where->column;
            $op  = $where->operator;
            $val = is_string(value: $where->value) ? "'{$where->value}'" : $where->value;

            $conditions[] = "{$col} {$op} {$val}";
        }

        return implode(separator: ' AND ', array: $conditions);
    }

    private function compileCassandraOrder(QueryState $state) : string
    {
        $orders = [];
        foreach ($state->orders as $order) {
            $orders[] = "{$order->column} {$order->direction}";
        }

        return implode(separator: ', ', array: $orders);
    }

    #[Override]
    public function compileUpdate(QueryState $state) : string
    {
        parent::compileUpdate(state: $state);

        $table = $this->wrap(value: $state->from);

        $sets = [];
        foreach ($state->values as $col => $val) {
            $sets[] = "{$col} = {$val}";
        }

        $sql = "UPDATE {$table} SET " . implode(separator: ', ', array: $sets);

        if (! empty($state->wheres)) {
            $sql .= ' WHERE ' . $this->compileCassandraWhere(state: $state);
        }

        return $sql;
    }

    #[Override]
    public function compileDelete(QueryState $state) : string
    {
        parent::compileDelete(state: $state);

        $table = $this->wrap(value: $state->from);

        $sql = "DELETE FROM {$table}";

        if (! empty($state->wheres)) {
            $sql .= ' WHERE ' . $this->compileCassandraWhere(state: $state);
        }

        return $sql;
    }

    #[Override]
    public function compileUpsert(QueryState $state, array $uniqueBy, array $update) : string
    {
        try {
            parent::compileUpsert(state: $state, uniqueBy: $uniqueBy, update: $update);
        } catch (RuntimeException) {
        }

        return $this->compileInsert(state: $state);
    }

    #[Override]
    public function compileInsert(QueryState $state) : string
    {
        parent::compileInsert(state: $state);

        $table   = $this->wrap(value: $state->from);
        $columns = implode(separator: ', ', array: array_keys(array: $state->values));
        $values  = implode(separator: ', ', array: array_map(
            callback: static fn ($v) => is_string(value: $v) ? "'{$v}'" : $v,
            array   : $state->values
        ));

        return "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
    }

    public function compileBatch(array $statements) : string
    {
        $stmts = implode(separator: '; ', array: $statements);

        return "BEGIN BATCH {$stmts} APPLY BATCH";
    }

    public function compileCounterIncrement(string $column, int $amount) : string
    {
        return "{$column} = {$column} + {$amount}";
    }

    public function compileTTL(int $seconds) : string
    {
        return "USING TTL {$seconds}";
    }

    public function compileTimestamp(int $timestamp) : string
    {
        return "USING TIMESTAMP {$timestamp}";
    }

    #[Override]
    public function compileTruncate(string $table) : string
    {
        return "TRUNCATE {$this->wrap(value: $table)}";
    }
}
