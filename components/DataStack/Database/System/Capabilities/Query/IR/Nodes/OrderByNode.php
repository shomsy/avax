<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class OrderByNode
{
    public function __construct(
        public readonly string   $column,
        public readonly string   $direction = 'ASC',
        public readonly int|null $nulls = null,
    ) {}

    public function getSql(GrammarInterface $grammar) : string
    {
        $column    = $grammar->wrap(value: $this->column);
        $direction = strtoupper(string: $this->direction);

        $sql = "{$column} {$direction}";

        if ($this->nulls !== null) {
            $nulls = $this->nulls === 1 ? 'FIRST' : 'LAST';
            $sql .= " NULLS {$nulls}";
        }

        return $sql;
    }
}
