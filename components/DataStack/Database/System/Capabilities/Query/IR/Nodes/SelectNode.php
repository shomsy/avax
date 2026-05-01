<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final readonly class SelectNode
{
    public function __construct(
        public array $columns = ['*'],
        public bool  $distinct = false,
    ) {}

    public function getSql(GrammarInterface $grammar): string
    {
        $columns = $this->columns === []
            ? ['*']
            : $this->columns;

        $wrapped = array_map(callback: static fn ($column) : string => $grammar->wrap(value: $column), array: $columns);

        return ($this->distinct ? 'SELECT DISTINCT ' : 'SELECT ') . implode(separator: ', ', array: $wrapped);
    }
}
