<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final readonly class JoinNode
{
    public function __construct(
        public string     $type,
        public string     $table,
        public ?string    $alias = null,
        public ?WhereNode $on = null,
        public ?string    $using = null,
    ) {}

    public function getSql(GrammarInterface $grammar): string
    {
        $type = strtoupper(string: $this->type);
        $table = $grammar->wrap(value: $this->table);

        if ($this->alias !== null) {
            $table .= ' AS ' . $grammar->wrap(value: $this->alias);
        }

        $sql = sprintf('%s JOIN %s', $type, $table);

        if ($this->using !== null) {
            $sql .= ' USING (' . $grammar->wrap(value: $this->using) . ')';
        } elseif ($this->on instanceof WhereNode) {
            $sql .= ' ON ' . $this->on->getSql(grammar: $grammar);
        }

        return $sql;
    }
}
