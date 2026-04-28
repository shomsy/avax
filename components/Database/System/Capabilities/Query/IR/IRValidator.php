<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Query\IR;

use Avax\Components\Database\System\Capabilities\Query\Exceptions\QueryException;
use Avax\Components\Database\System\Capabilities\Query\IR\Nodes\QueryNode;

final class IRValidator
{
    public function assertValid(QueryNode $query) : void
    {
        $errors = $this->validate(query: $query);

        if ($errors !== []) {
            throw new QueryException(message: implode(separator: ' ', array: $errors), sql: '');
        }
    }

    /**
     * @return list<string>
     */
    public function validate(QueryNode $query) : array
    {
        $errors = [];

        if ($query->getFrom() === null) {
            $errors[] = 'QueryNode must define a FROM source.';
        }

        if (($query->getLimit() ?? 0) < 0) {
            $errors[] = 'QueryNode limit cannot be negative.';
        }

        if (($query->getOffset() ?? 0) < 0) {
            $errors[] = 'QueryNode offset cannot be negative.';
        }

        foreach ($query->getOrders() as $order) {
            if (! in_array(needle: strtoupper(string: $order->direction), haystack: ['ASC', 'DESC'], strict: true)) {
                $errors[] = "Invalid order direction [{$order->direction}] for column [{$order->column}].";
            }
        }

        foreach ($query->getWheres() as $where) {
            if (! in_array(needle: strtoupper(string: $where->boolean), haystack: ['AND', 'OR'], strict: true)) {
                $errors[] = "Invalid boolean connector [{$where->boolean}] for column [{$where->column}].";
            }
        }

        return $errors;
    }
}
