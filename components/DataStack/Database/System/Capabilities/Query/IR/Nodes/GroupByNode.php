<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class GroupByNode
{
    public function __construct(public readonly array $columns) {}

    public function getSql(GrammarInterface $grammar) : string
    {
        $columns = array_map(callback: fn ($column) => $grammar->wrap(value: $column), array: $this->columns);

        return 'GROUP BY ' . implode(separator: ', ', array: $columns);
    }
}
