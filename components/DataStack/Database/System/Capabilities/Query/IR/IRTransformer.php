<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\CTENode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\CTEType;
use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\FromNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\JoinNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\OrderByNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\QueryNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\WhereNode;

final readonly class IRTransformer
{
    public function __construct(
        private GrammarInterface $grammar,
    ) {}

    public function toSql(QueryNode $queryNode): string
    {
        $components = [];

        $ctes = $queryNode->getCTEs();
        if ($ctes !== []) {
            $hasRecursive = array_any(
                array   : $ctes,
                callback: static fn (CTENode $cteNode): bool => $cteNode->type === CTEType::RECURSIVE,
            );
            $cteSql = array_map(
                callback: fn (CTENode $cteNode): string => $cteNode->getSql(grammar: $this->grammar),
                array   : $ctes,
            );
            $components[] = 'WITH ' . ($hasRecursive ? 'RECURSIVE ' : '') . implode(separator: ', ', array: $cteSql);
        }

        $components[] = $this->compileSelect(query: $queryNode);

        return implode(separator: ' ', array: array_filter(array: $components));
    }

    private function compileSelect(QueryNode $queryNode): string
    {
        $components = [];

        $distinct = $queryNode->isDistinct() ? 'SELECT DISTINCT ' : 'SELECT ';
        $columns = $queryNode->getSelect();

        if ($columns === []) {
            $select = $distinct . '*';
        } else {
            $selectColumns = array_map(
                callback: fn ($col): string => $this->grammar->wrap(value: $col),
                array   : $columns,
            );
            $select = $distinct . implode(separator: ', ', array: $selectColumns);
        }

        $components[] = $select;

        $from = $queryNode->getFrom();
        if ($from instanceof FromNode) {
            $components[] = 'FROM ' . $from->getSql(grammar: $this->grammar);
        }

        $joins = $queryNode->getJoins();
        if ($joins !== []) {
            $joinSql = array_map(
                callback: fn (JoinNode $joinNode): string => $joinNode->getSql(grammar: $this->grammar),
                array   : $joins,
            );
            $components[] = implode(separator: ' ', array: $joinSql);
        }

        $wheres = $queryNode->getWheres();
        if ($wheres !== []) {
            $whereSql = array_map(
                callback: fn (WhereNode $whereNode): string => $whereNode->getSql(grammar: $this->grammar),
                array   : $wheres,
            );
            $whereSql[0] = preg_replace(pattern: '/^(AND|OR)\s+/i', replacement: '', subject: $whereSql[0]);
            $components[] = 'WHERE ' . implode(separator: ' ', array: $whereSql);
        }

        $groups = $queryNode->getGroups();
        if ($groups !== []) {
            $groupColumns = array_map(
                callback: fn ($col): string => $this->grammar->wrap(value: $col),
                array   : $groups,
            );
            $components[] = 'GROUP BY ' . implode(separator: ', ', array: $groupColumns);
        }

        $orders = $queryNode->getOrders();
        if ($orders !== []) {
            $orderSql = array_map(
                callback: fn (OrderByNode $orderByNode): string => $orderByNode->getSql(grammar: $this->grammar),
                array   : $orders,
            );
            $components[] = 'ORDER BY ' . implode(separator: ', ', array: $orderSql);
        }

        $limit = $queryNode->getLimit();
        if ($limit !== null) {
            $components[] = 'LIMIT ' . $limit;
        }

        $offset = $queryNode->getOffset();
        if ($offset !== null) {
            $components[] = 'OFFSET ' . $offset;
        }

        return implode(separator: ' ', array: array_filter(array: $components));
    }

    public function fingerprint(QueryNode $queryNode): string
    {
        $parts = [];

        $parts[] = 'SELECT';
        $parts[] = implode(separator: ',', array: $queryNode->getSelect());

        $from = $queryNode->getFrom();
        if ($from instanceof FromNode) {
            $parts[] = 'FROM ' . $from->table;
        }

        $joins = $queryNode->getJoins();
        foreach ($joins as $join) {
            $parts[] = 'JOIN ' . $join->table;
        }

        $wheres = $queryNode->getWheres();
        foreach ($wheres as $where) {
            $parts[] = $where->column . $where->operator->value;
        }

        $groups = $queryNode->getGroups();
        if ($groups !== []) {
            $parts[] = 'GROUP BY ' . implode(separator: ',', array: $groups);
        }

        $orders = $queryNode->getOrders();
        foreach ($orders as $order) {
            $parts[] = $order->column . $order->direction;
        }

        $parts[] = 'LIMIT ' . ($queryNode->getLimit() ?? '0');
        $parts[] = 'OFFSET ' . ($queryNode->getOffset() ?? '0');

        return md5(string: implode(separator: '|', array: $parts));
    }
}
