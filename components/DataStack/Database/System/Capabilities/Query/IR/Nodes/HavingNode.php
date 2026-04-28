<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class HavingNode
{
    public function __construct(public readonly WhereNode $condition) {}

    public function getSql(GrammarInterface $grammar) : string
    {
        return 'HAVING ' . preg_replace(
                pattern    : '/^(AND|OR)\s+/i',
                replacement: '',
                subject    : $this->condition->getSql(grammar: $grammar)
            );
    }
}
