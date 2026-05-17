<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Advanced\WindowFunctions;

use Avax\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class LagLead
{
    public static function lag(GrammarInterface $grammar, string $column): WindowBuilder
    {
        return WindowBuilder::lag(grammar: $grammar, column: $column);
    }

    public static function lead(GrammarInterface $grammar, string $column): WindowBuilder
    {
        return WindowBuilder::lead(grammar: $grammar, column: $column);
    }
}
