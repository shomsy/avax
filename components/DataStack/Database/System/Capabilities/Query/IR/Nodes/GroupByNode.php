<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final readonly class GroupByNode
{
    public function __construct(public array $columns) {}

    public function getSql(GrammarInterface $grammar) : string
    {
        $columns = array_map(callback: static fn ($column) : string => $grammar->wrap(value: $column), array: $this->columns);

        return 'GROUP BY ' . implode(separator: ', ', array: $columns);
    }
}
