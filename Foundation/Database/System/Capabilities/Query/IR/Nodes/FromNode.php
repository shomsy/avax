<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class FromNode
{
    public function __construct(
        public readonly string  $table,
        public readonly ?string $alias = null,
    ) {}

    public function getSql(GrammarInterface $grammar) : string
    {
        $sql = $grammar->wrap(value: $this->table);

        if ($this->alias !== null) {
            $sql .= ' AS ' . $grammar->wrap(value: $this->alias);
        }

        return $sql;
    }
}
