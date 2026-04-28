<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class JoinNode
{
    public function __construct(
        public readonly string         $type,
        public readonly string         $table,
        public readonly string|null    $alias = null,
        public readonly WhereNode|null $on = null,
        public readonly string|null    $using = null
    ) {}

    public function getSql(GrammarInterface $grammar) : string
    {
        $type  = strtoupper(string: $this->type);
        $table = $grammar->wrap(value: $this->table);

        if ($this->alias !== null) {
            $table .= ' AS ' . $grammar->wrap(value: $this->alias);
        }

        $sql = "{$type} JOIN {$table}";

        if ($this->using !== null) {
            $sql .= ' USING (' . $grammar->wrap(value: $this->using) . ')';
        } elseif ($this->on !== null) {
            $sql .= ' ON ' . $this->on->getSql(grammar: $grammar);
        }

        return $sql;
    }
}
