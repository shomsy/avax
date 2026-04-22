<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\IR;

use Avax\Database\System\Capabilities\Query\Grammar\GrammarInterface;

enum CTEType
{
    case SIMPLE;
    case RECURSIVE;
}

final class CTENode
{
    public function __construct(
        public readonly string  $name,
        public readonly array   $columns,
        public readonly string  $query,
        public readonly CTEType $type = CTEType::SIMPLE
    ) {}

    public function getSql(GrammarInterface $grammar) : string
    {
        $columns = empty($this->columns)
            ? ''
            : '(' . implode(separator: ', ', array: array_map(
                callback: fn ($col) => $grammar->wrap(value: $col),
                array   : $this->columns
            )) . ')';

        $recursive = $this->type === CTEType::RECURSIVE ? 'RECURSIVE ' : '';

        return "WITH {$recursive}{$this->name}{$columns} AS ({$this->query})";
    }
}
