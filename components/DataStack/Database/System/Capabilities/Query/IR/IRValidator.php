<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Exceptions\QueryException;
use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\FromNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\QueryNode;

final class IRValidator
{
    public function assertValid(QueryNode $queryNode): void
    {
        $errors = $this->validate(query: $queryNode);

        if ($errors !== []) {
            throw new QueryException(message: implode(separator: ' ', array: $errors), sql: '');
        }
    }

    /**
     * @return list<string>
     */
    public function validate(QueryNode $queryNode): array
    {
        $errors = [];

        if (! $queryNode->getFrom() instanceof FromNode) {
            $errors[] = 'QueryNode must define a FROM source.';
        }

        if (($queryNode->getLimit() ?? 0) < 0) {
            $errors[] = 'QueryNode limit cannot be negative.';
        }

        if (($queryNode->getOffset() ?? 0) < 0) {
            $errors[] = 'QueryNode offset cannot be negative.';
        }

        foreach ($queryNode->getOrders() as $order) {
            if (! in_array(needle: strtoupper(string: $order->direction), haystack: ['ASC', 'DESC'], strict: true)) {
                $errors[] = sprintf('Invalid order direction [%s] for column [%s].', $order->direction, $order->column);
            }
        }

        foreach ($queryNode->getWheres() as $where) {
            if (! in_array(needle: strtoupper(string: $where->boolean), haystack: ['AND', 'OR'], strict: true)) {
                $errors[] = sprintf('Invalid boolean connector [%s] for column [%s].', $where->boolean, $where->column);
            }
        }

        return $errors;
    }
}
