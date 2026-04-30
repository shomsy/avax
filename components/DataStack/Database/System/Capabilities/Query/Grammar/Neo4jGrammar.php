<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;
use Override;

/**
 * Neo4j Grammar - Graph Cypher queries.
 */
final class Neo4jGrammar extends BaseGrammar
{
    #[Override]
    public function compileSelect(QueryState $state) : string
    {
        $pattern = $this->compileCypherPattern(state: $state);

        $sql = "MATCH {$pattern}";

        if (! empty($state->wheres)) {
            $sql .= ' WHERE ' . $this->compileCypherWhere(state: $state);
        }

        $return = empty($state->columns) ? '*' : implode(separator: ', ', array: $state->columns);
        $sql .= " RETURN {$return}";

        if (! empty($state->orders)) {
            $sql .= ' ORDER BY ' . $this->compileCypherOrder(state: $state);
        }

        if ($state->limit) {
            $sql .= ' LIMIT ' . $state->limit;
        }

        return $sql;
    }

    private function compileCypherPattern(QueryState $state) : string
    {
        $table = $state->from ?: 'n';
        $alias = 'n';

        if (str_contains(haystack: $table, needle: '_')) {
            $parts = explode(separator: '_', string: $table);
            $label = ucfirst(string: $parts[0]);
            $alias = $parts[1] ?? 'n';

            return "{$alias}:{$label}";
        }

        return "{$alias}:{$table}";
    }

    private function compileCypherWhere(QueryState $state) : string
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

    private function compileCypherOrder(QueryState $state) : string
    {
        $orders = [];
        foreach ($state->orders as $order) {
            $orders[] = "{$order->column} {$order->direction}";
        }

        return implode(separator: ', ', array: $orders);
    }

    #[Override]
    public function compileInsert(QueryState $state) : string
    {
        $pattern = $this->compileCypherPattern(state: $state);
        $props   = $this->compileCypherProperties(values: $state->values);

        return "CREATE ({$pattern} {$props})";
    }

    private function compileCypherProperties(array $values) : string
    {
        $props = [];
        foreach ($values as $key => $value) {
            $val     = is_string(value: $value) ? "'{$value}'" : $value;
            $props[] = "{$key}: {$val}";
        }

        return '{' . implode(separator: ', ', array: $props) . '}';
    }

    #[Override]
    public function compileUpdate(QueryState $state) : string
    {
        $pattern = $this->compileCypherPattern(state: $state);
        $props   = $this->compileCypherProperties(values: $state->values);

        $sql = "MATCH {$pattern}";

        if (! empty($state->wheres)) {
            $sql .= ' WHERE ' . $this->compileCypherWhere(state: $state);
        }

        $sql .= " SET {$props}";

        return $sql;
    }

    #[Override]
    public function compileDelete(QueryState $state) : string
    {
        $pattern = $this->compileCypherPattern(state: $state);

        return "DETACH DELETE {$pattern}";
    }

    #[Override]
    public function compileUpsert(QueryState $state, array $uniqueBy, array $update) : string
    {
        $pattern = $this->compileCypherPattern(state: $state);
        $props   = $this->compileCypherProperties(values: $state->values);

        return "MERGE ({$pattern} {$props})";
    }

    public function compileRelation(string $from, string $to, string $type, array $properties = []) : string
    {
        $props     = $this->compileCypherProperties(values: $properties);
        $propsPart = empty($properties) ? '' : " {$props}";

        return "({$from})-[:{$type}{$propsPart}]->({$to})";
    }

    public function compilePath(array $nodes, array $relations) : string
    {
        $path = '(' . $nodes[0] . ')';

        for ($i = 0; $i < count(value: $relations); $i++) {
            $path .= "-[r:{$relations[$i]}]->(" . $nodes[$i + 1] . ')';
        }

        return $path;
    }

    public function compileShortestPath(string $start, string $end) : string
    {
        return "shortestPath(({$start})-[*]->({$end}))";
    }

    #[Override]
    public function wrap(mixed $value) : string
    {
        return (string) $value;
    }

    #[Override]
    public function compileTruncate(string $table) : string
    {
        return "MATCH (n:{$table}) DETACH DELETE n";
    }
}
