<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Query\IR;

use Avax\Components\Database\System\Capabilities\Query\IR\Nodes\QueryNode;
use JsonException;

final class IRNormalizer
{
    /**
     * @throws JsonException
     */
    public function fingerprint(QueryNode $query) : string
    {
        return hash(algo: 'sha256', data: json_encode(value: $this->toCanonicalArray(query: $query), flags: JSON_THROW_ON_ERROR));
    }

    /**
     * Build a deterministic payload suitable for cache keys, diffing and telemetry.
     */
    public function toCanonicalArray(QueryNode $query) : array
    {
        return [
            'select'   => array_values(array: $query->getSelect()),
            'from'     => $query->getFrom()?->table,
            'joins'    => array_map(
                callback: static fn ($join) => [
                    'type'  => strtoupper(string: $join->type),
                    'table' => $join->table,
                    'alias' => $join->alias,
                ],
                array   : $query->getJoins()
            ),
            'wheres'   => array_map(
                callback: static fn ($where) => [
                    'column'   => $where->column,
                    'operator' => $where->operator->value,
                    'boolean'  => strtoupper(string: $where->boolean),
                ],
                array   : $query->getWheres()
            ),
            'groups'   => array_values(array: $query->getGroups()),
            'orders'   => array_map(
                callback: static fn ($order) => [
                    'column'    => $order->column,
                    'direction' => strtoupper(string: $order->direction),
                    'nulls'     => $order->nulls,
                ],
                array   : $query->getOrders()
            ),
            'limit'    => $query->getLimit(),
            'offset'   => $query->getOffset(),
            'distinct' => $query->isDistinct(),
            'ctes'     => array_map(
                callback: static fn ($cte) => [
                    'name'    => $cte->name,
                    'columns' => $cte->columns,
                    'type'    => $cte->type->name,
                ],
                array   : $query->getCTEs()
            ),
        ];
    }
}
