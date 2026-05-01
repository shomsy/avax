<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final readonly class FromNode
{
    public function __construct(
        public string $table,
        public ?string $alias = null,
    ) {}

    public function getSql(GrammarInterface $grammar): string
    {
        $sql = $grammar->wrap(value: $this->table);

        if ($this->alias !== null) {
            $sql .= ' AS '.$grammar->wrap(value: $this->alias);
        }

        return $sql;
    }
}
