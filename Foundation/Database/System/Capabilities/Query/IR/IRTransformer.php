<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\IR;

use Avax\Database\System\Capabilities\Query\Grammar\GrammarInterface;
use Avax\Database\System\Capabilities\Query\IR\Nodes\CTENode;
use Avax\Database\System\Capabilities\Query\IR\Nodes\CTEType;
use Avax\Database\System\Capabilities\Query\IR\Nodes\JoinNode;
use Avax\Database\System\Capabilities\Query\IR\Nodes\OrderByNode;
use Avax\Database\System\Capabilities\Query\IR\Nodes\QueryNode;
use Avax\Database\System\Capabilities\Query\IR\Nodes\WhereNode;

final class IRTransformer
{
    public function __construct(
        private GrammarInterface $grammar
    ) {}

    public function toSql(QueryNode $query) : string
    {
        $components = [];

        $ctes = $query->getCTEs();
        if (! empty($ctes)) {
            $hasRecursive = array_any(
                array   : $ctes,
                callback: static fn (CTENode $cte) => $cte->type === CTEType::RECURSIVE
            );
            $cteSql       = array_map(
                callback: fn (CTENode $cte) => $cte->getSql(grammar: $this->grammar),
                array   : $ctes
            );
            $components[] = 'WITH ' . ($hasRecursive ? 'RECURSIVE ' : '') . implode(separator: ', ', array: $cteSql);
        }

        $components[] = $this->compileSelect(query: $query);

        return implode(separator: ' ', array: array_filter(array: $components));
    }

    private function compileSelect(QueryNode $query) : string
    {
        $components = [];

        $distinct = $query->isDistinct() ? 'SELECT DISTINCT ' : 'SELECT ';
        $columns  = $query->getSelect();

        if (empty($columns)) {
            $select = $distinct . '*';
        } else {
            $selectColumns = array_map(
                callback: fn ($col) => $this->grammar->wrap(value: $col),
                array   : $columns
            );
            $select        = $distinct . implode(separator: ', ', array: $selectColumns);
        }
        $components[] = $select;

        $from = $query->getFrom();
        if ($from !== null) {
            $components[] = 'FROM ' . $from->getSql(grammar: $this->grammar);
        }

        $joins = $query->getJoins();
        if (! empty($joins)) {
            $joinSql      = array_map(
                callback: fn (JoinNode $join) => $join->getSql(grammar: $this->grammar),
                array   : $joins
            );
            $components[] = implode(separator: ' ', array: $joinSql);
        }

        $wheres = $query->getWheres();
        if (! empty($wheres)) {
            $whereSql     = array_map(
                callback: fn (WhereNode $where) => $where->getSql(grammar: $this->grammar),
                array   : $wheres
            );
            $whereSql[0] = preg_replace(pattern: '/^(AND|OR)\s+/i', replacement: '', subject: $whereSql[0]);
            $components[] = 'WHERE ' . implode(separator: ' ', array: $whereSql);
        }

        $groups = $query->getGroups();
        if (! empty($groups)) {
            $groupColumns = array_map(
                callback: fn ($col) => $this->grammar->wrap(value: $col),
                array   : $groups
            );
            $components[] = 'GROUP BY ' . implode(separator: ', ', array: $groupColumns);
        }

        $orders = $query->getOrders();
        if (! empty($orders)) {
            $orderSql     = array_map(
                callback: fn (OrderByNode $order) => $order->getSql(grammar: $this->grammar),
                array   : $orders
            );
            $components[] = 'ORDER BY ' . implode(separator: ', ', array: $orderSql);
        }

        $limit = $query->getLimit();
        if ($limit !== null) {
            $components[] = 'LIMIT ' . $limit;
        }

        $offset = $query->getOffset();
        if ($offset !== null) {
            $components[] = 'OFFSET ' . $offset;
        }

        return implode(separator: ' ', array: array_filter(array: $components));
    }

    public function fingerprint(QueryNode $query) : string
    {
        $parts = [];

        $parts[] = 'SELECT';
        $parts[] = implode(separator: ',', array: $query->getSelect());

        $from = $query->getFrom();
        if ($from !== null) {
            $parts[] = 'FROM ' . $from->table;
        }

        $joins = $query->getJoins();
        foreach ($joins as $join) {
            $parts[] = 'JOIN ' . $join->table;
        }

        $wheres = $query->getWheres();
        foreach ($wheres as $where) {
            $parts[] = $where->column . $where->operator->value;
        }

        $groups = $query->getGroups();
        if (! empty($groups)) {
            $parts[] = 'GROUP BY ' . implode(separator: ',', array: $groups);
        }

        $orders = $query->getOrders();
        foreach ($orders as $order) {
            $parts[] = $order->column . $order->direction;
        }

        $parts[] = 'LIMIT ' . ($query->getLimit() ?? '0');
        $parts[] = 'OFFSET ' . ($query->getOffset() ?? '0');

        return md5(string: implode(separator: '|', array: $parts));
    }
}
