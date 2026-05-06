<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final readonly class OrderByNode
{
    public function __construct(
        public string $column,
        public string $direction = 'ASC',
        public ?int $nulls = null,
    ) {
    }

    public function getSql(GrammarInterface $grammar): string
    {
        $column = $grammar->wrap(value: $this->column);
        $direction = strtoupper(string: $this->direction);

        $sql = sprintf('%s %s', $column, $direction);

        if ($this->nulls !== null) {
            $nulls = $this->nulls === 1 ? 'FIRST' : 'LAST';
            $sql .= ' NULLS '.$nulls;
        }

        return $sql;
    }
}
