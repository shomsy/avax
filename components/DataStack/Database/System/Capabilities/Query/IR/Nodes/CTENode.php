<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class CTENode
{
    public function __construct(
        public readonly string  $name,
        public readonly array   $columns,
        public readonly string  $query,
        public readonly CTEType $type = CTEType::SIMPLE,
    ) {}

    public function getSql(GrammarInterface $grammar) : string
    {
        $columns = empty($this->columns)
            ? ''
            : '(' . implode(separator: ', ', array: array_map(
                callback: static fn ($col) => $grammar->wrap(value: $col),
                array   : $this->columns,
            )) . ')';

        return "{$this->name}{$columns} AS ({$this->query})";
    }
}
