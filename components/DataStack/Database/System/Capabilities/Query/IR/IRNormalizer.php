<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR;

use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\QueryNode;
use JsonException;

final class IRNormalizer
{
    /**
     * @throws JsonException
     */
    public function fingerprint(QueryNode $queryNode) : string
    {
        return hash(algo: 'sha256', data: json_encode(value: $this->toCanonicalArray(query: $queryNode), flags: JSON_THROW_ON_ERROR));
    }

    /**
     * Build a deterministic payload suitable for cache keys, diffing and telemetry.
     */
    public function toCanonicalArray(QueryNode $queryNode) : array
    {
        return [
            'select'   => array_values(array: $queryNode->getSelect()),
            'from'     => $queryNode->getFrom()?->table,
            'joins'    => array_map(
                callback: static fn ($join) : array => [
                    'type'  => strtoupper(string: $join->type),
                    'table' => $join->table,
                    'alias' => $join->alias,
                ],
                array   : $queryNode->getJoins(),
            ),
            'wheres'   => array_map(
                callback: static fn ($where) : array => [
                    'column'   => $where->column,
                    'operator' => $where->operator->value,
                    'boolean'  => strtoupper(string: $where->boolean),
                ],
                array   : $queryNode->getWheres(),
            ),
            'groups'   => array_values(array: $queryNode->getGroups()),
            'orders'   => array_map(
                callback: static fn ($order) : array => [
                    'column'    => $order->column,
                    'direction' => strtoupper(string: $order->direction),
                    'nulls'     => $order->nulls,
                ],
                array   : $queryNode->getOrders(),
            ),
            'limit'    => $queryNode->getLimit(),
            'offset'   => $queryNode->getOffset(),
            'distinct' => $queryNode->isDistinct(),
            'ctes'     => array_map(
                callback: static fn ($cte) : array => [
                    'name'    => $cte->name,
                    'columns' => $cte->columns,
                    'type'    => $cte->type->name,
                ],
                array   : $queryNode->getCTEs(),
            ),
        ];
    }
}
