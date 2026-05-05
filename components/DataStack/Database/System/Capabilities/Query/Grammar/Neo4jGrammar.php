<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;

/**
 * Neo4j Grammar - Graph Cypher queries.
 */
final class Neo4jGrammar extends BaseGrammar
{
    public function compileSelect(QueryState $queryState) : string
    {
        $pattern = $this->compileCypherPattern($queryState);

        $sql = 'MATCH ' . $pattern;

        if ($queryState->wheres !== []) {
            $sql .= ' WHERE ' . $this->compileCypherWhere($queryState);
        }

        $return = $queryState->columns === [] ? '*' : implode(separator: ', ', array: $queryState->columns);
        $sql    .= ' RETURN ' . $return;

        if ($queryState->orders !== []) {
            $sql .= ' ORDER BY ' . $this->compileCypherOrder($queryState);
        }

        if ($queryState->limit) {
            $sql .= ' LIMIT ' . $queryState->limit;
        }

        return $sql;
    }

    private function compileCypherPattern(QueryState $queryState) : string
    {
        $table = $queryState->from ?: 'n';
        $alias = 'n';

        if (str_contains(haystack: $table, needle: '_')) {
            $parts = explode(separator: '_', string: $table);
            $label = ucfirst(string: $parts[0]);
            $alias = $parts[1] ?? 'n';

            return sprintf('%s:%s', $alias, $label);
        }

        return sprintf('%s:%s', $alias, $table);
    }

    private function compileCypherWhere(QueryState $queryState) : string
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

    private function compileCypherOrder(QueryState $queryState) : string
    {
        $orders = [];
        foreach ($queryState->orders as $order) {
            $orders[] = sprintf('%s %s', $order->column, $order->direction);
        }

        return implode(separator: ', ', array: $orders);
    }

    public function compileInsert(QueryState $queryState) : string
    {
        $pattern = $this->compileCypherPattern($queryState);
        $props   = $this->compileCypherProperties($queryState->values);

        return sprintf('CREATE (%s %s)', $pattern, $props);
    }

    private function compileCypherProperties(array $values): string
    {
        $props = [];
        foreach ($values as $key => $value) {
            $val     = is_string(value: $value) ? sprintf("'%s'", $value) : $value;
            $props[] = sprintf('%s: %s', $key, $val);
        }

        return '{' . implode(separator: ', ', array: $props) . '}';
    }

    public function compileUpdate(QueryState $queryState) : string
    {
        $pattern = $this->compileCypherPattern($queryState);
        $props   = $this->compileCypherProperties($queryState->values);

        $sql = 'MATCH ' . $pattern;

        if ($queryState->wheres !== []) {
            $sql .= ' WHERE ' . $this->compileCypherWhere($queryState);
        }

        return $sql . (' SET ' . $props);
    }

    public function compileDelete(QueryState $queryState) : string
    {
        $pattern = $this->compileCypherPattern($queryState);

        return 'DETACH DELETE ' . $pattern;
    }

    public function compileUpsert(QueryState $queryState, array $uniqueBy, array $update) : string
    {
        $pattern = $this->compileCypherPattern($queryState);
        $props   = $this->compileCypherProperties($queryState->values);

        return sprintf('MERGE (%s %s)', $pattern, $props);
    }

    public function compileRelation(string $from, string $to, string $type, array $properties = []): string
    {
        $props = $this->compileCypherProperties(values: $properties);
        $propsPart = $properties === [] ? '' : ' ' . $props;

        return sprintf('(%s)-[:%s%s]->(%s)', $from, $type, $propsPart, $to);
    }

    public function compilePath(array $nodes, array $relations): string
    {
        $path = '(' . $nodes[0] . ')';
        $counter = count(value: $relations);

        for ($i = 0; $i < $counter; $i++) {
            $path .= sprintf('-[r:%s]->(', $relations[$i]) . $nodes[$i + 1] . ')';
        }

        return $path;
    }

    public function compileShortestPath(string $start, string $end): string
    {
        return sprintf('shortestPath((%s)-[*]->(%s))', $start, $end);
    }

    public function wrap(mixed $value): string
    {
        return (string) $value;
    }

    public function compileTruncate(string $table): string
    {
        return sprintf('MATCH (n:%s) DETACH DELETE n', $table);
    }
}
