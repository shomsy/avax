<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Components\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class SelectNode
{
    public function __construct(
        public readonly array $columns = ['*'],
        public readonly bool  $distinct = false,
    ) {}

    public function getSql(GrammarInterface $grammar) : string
    {
        $columns = $this->columns === []
            ? ['*']
            : $this->columns;

        $wrapped = array_map(callback: fn ($column) => $grammar->wrap(value: $column), array: $columns);

        return ($this->distinct ? 'SELECT DISTINCT ' : 'SELECT ') . implode(separator: ', ', array: $wrapped);
    }
}
